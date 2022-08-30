<?php

namespace Daycry\RestServer\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use CodeIgniter\Config\BaseConfig;
use Daycry\RestServer\Exceptions\UnauthorizedException;
use Daycry\RestServer\Entities\AccessEntity;
use Daycry\RestServer\Entities\LimitEntity;
use Daycry\RestServer\Entities\PetitionEntity;
use DateTime;

class KeyInfo extends BaseCommand
{
    protected $group       = 'Rest Server';
    protected $name        = 'restserver:apikeyinfo';
    protected $description = 'Get information about api key.';
    protected $usage = 'restserver:apikeyinfo [Options]';
    protected $options = [ '-key' => 'Api Key' ];

    private $_key = null;
    private BaseConfig $config;
    private $parser;

    public function run(array $params)
    {
        $this->parser = \Config\Services::parser();
        $this->config = config('RestServer');
        $this->db = db_connect($this->config->restDatabaseGroup);

        try {
            if ($this->config->restEnableKeys != true) {
                $this->_printError(lang('RestCommand.apiKeyDisabled'));
                exit;
            }

            $this->_key = $params[ 'key' ] ?? CLI::getOption('key');

            if (empty($this->_key)) {
                $this->_key = CLI::prompt(lang('RestCommand.insertApiKey'), null, 'required|max_length['.$this->config->restKeyLength.']');
                CLI::newLine();
            }

            $keyModel = new \Daycry\RestServer\Models\KeyModel();
            $row = $keyModel->where($this->config->restKeyColumn, $this->_key)->first();

            if (!$row) {
                throw UnauthorizedException::forInvalidApiKey($this->_key);
            }

            $this->_getKeyInfo($row);
            $this->_getKeyUser($row);
            $this->_getKeyAccess($row);
            $this->_getKeyLocks($row);
        } catch (\Exception $ex) {
            CLI::newLine();
            CLI::error('**** ' . $ex->getMessage() . ' ****');
            CLI::newLine();
        }
    }

    private function _getKeyInfo(\Daycry\RestServer\Entities\KeyEntity $row)
    {
        $this->_printHeader(lang('RestCommand.apiKeyInfo'));

        $row = [
            [$row->id, $row->{$this->config->restKeyColumn}, $row->level, $row->ignore_limits, $row->is_private_key, $row->ip_addresses, $row->created_at, $row->updated_at, $row->deleted_at]
        ];

        $this->_printTable($this->_getTableColumns($this->config->restKeysTable), $row);
    }

    private function _getKeyUser(\Daycry\RestServer\Entities\KeyEntity $row)
    {
        $this->_printHeader(lang('RestCommand.apiKeyUser'));

        $users = $row->{$this->config->restUsersTable};
        $body = $columns = [];

        if (!$users) {
            $this->_printError(lang('RestCommand.apiKeyNoUsers'));
        } else {
            foreach ($users as $user) {
                array_push($body, array( $user->id, $user->{$this->config->userNameColumn}, $user->created_at, $user->updated_at, $user->deleted_at ));
            }

            $columns = array( 'id', $this->config->userNameColumn, 'created_at', 'updated_at', 'deleted_at' );

            $this->_printTable($columns, $body);
        }
    }

    private function _getKeyAccess(\Daycry\RestServer\Entities\KeyEntity $row)
    {
        $this->_printHeader(lang('RestCommand.apiKeyAccess'));

        if ($this->config->restEnableAccess != true) {
            $this->_printError(lang('RestCommand.apiKeyAccessDisabled'));
        }

        $body = [];
        $accesses = $row->{$this->config->restAccessTable};

        if (!$accesses) {
            $this->_printError(lang('RestCommand.apiKeyNoAccesses'));
        } else {
            foreach ($row->{$this->config->restAccessTable} as $access) {
                // @codeCoverageIgnoreStart
                if (!$access instanceof AccessEntity) {
                    $access = new AccessEntity((array)$access);
                }
                // @codeCoverageIgnoreEnd

                $access->namespace_id = $access->{$this->config->restNamespaceTable}->controller;

                array_push($body, array($access->id, $access->all_access, $access->namespace_id, $access->method, $access->created_at, $access->updated_at, $access->deleted_at));
            }

            $columns = $this->_getTableColumns($this->config->restAccessTable);
            $columns = \array_filter($columns, static function ($element) {
                return $element !== "key_id";
            });

            $this->_printTable($columns, $body);
        }
    }

    private function _getKeyLocks(\Daycry\RestServer\Entities\KeyEntity $row)
    {
        $this->_printHeader(lang('RestCommand.apiKeyLocks'));

        if ($this->config->restEnableLimits != true || $this->config->restEnableOverridePetition != true) {
            $this->_printError($this->parser->setData(array( 'table' => $this->config->configRestPetitionsTable ))->renderString(lang('RestCommand.apiKeyLimitsDisabled')));
        } else {
            $limits = $row->{$this->config->restLimitsTable};
            $body = [];

            if (!$limits) {
                $this->_printError(lang('RestCommand.apiKeyNoLocks'));
            } else {
                foreach ($limits as $limit) {
                    // @codeCoverageIgnoreStart
                    if (!$limit instanceof LimitEntity) {
                        $limit = new LimitEntity((array)$limit);
                    }
                    // @codeCoverageIgnoreEnd

                    $r = $limit->{$this->config->configRestPetitionsTable};
                    if ($r) {
                        // @codeCoverageIgnoreStart
                        if (!$r instanceof PetitionEntity) {
                            $r = new PetitionEntity((array)$r);
                        }
                        // @codeCoverageIgnoreEnd

                        $timeLimit = (isset($r->time)) ? $r->time : 3600;
                        if ($limit->count >= $r->limit && $limit->hour_started > (time() - $timeLimit)) {
                            $timeLeft = $this->_getTimeLeft($limit->hour_started - (time() - $timeLimit));

                            $limit->hour_started = date('Y-m-d H:i:s', $limit->hour_started);
                            array_push($body, array( $timeLeft, $limit->id, $r->{$this->config->restNamespaceTable}->controller, $limit->uri, $limit->count, $limit->hour_started, $limit->created_at, $limit->updated_at, $limit->deleted_at ));
                        }
                    }
                }

                if (!$body) {
                    $this->_printError(lang('RestCommand.apiKeyNoLocks'));
                } else {
                    $columns = $this->_getTableColumns($this->config->restLimitsTable);
                    $columns = \array_filter($columns, static function ($element) {
                        return $element !== "key_id";
                    });

                    array_unshift($columns, 'Time Left');
                    $this->_printTable($columns, $body);
                }
            }
        }
    }

    private function _getTimeLeft(int $timeLeft)
    {
        $init = new DateTime('now');
        $end = new DateTime();
        $end->setTimestamp(time() + $timeLeft);

        $interval = $init->diff($end);

        return $interval->format('%H:%I:%S');
    }

    private function _getTableColumns(string $table): array
    {
        return $this->db->getFieldNames($table);
    }

    private function _printTable($header, $rows)
    {
        $tbody = [];
        $thead = $header;
        foreach ($rows as $row) {
            array_push($tbody, $row);
        }

        CLI::table($tbody, $thead);
    }

    private function _printHeader(string $header)
    {
        CLI::write('**** ' . $header . ' ****', 'green', 'light_gray');
    }

    private function _printError(string $error)
    {
        CLI::newLine();
        CLI::error('**** ' . $error . ' ****');
        CLI::newLine();
    }
}
