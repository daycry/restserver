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

class RestServerApiKeyCreator extends BaseCommand
{
    protected $group       = 'Rest Server';
    protected $name        = 'restserver:apikeycreator';
    protected $description = 'Cerate api key.';
    protected $usage = 'restserver:apikeycreator [Options]';
    protected $options = [ '-level' => 'Api Key Level','-ignoreLimits' => 'Ignore limits', '-noPrivate' => 'Private IP adresses', '-ip' => 'IP adresses' ];

    private BaseConfig $config;
    private $parser;

    public function run(array $params)
    {
        $this->parser = \Config\Services::parser();
        $this->config = config('RestServer');
        $this->db = db_connect($this->config->restDatabaseGroup);

        helper('text');

        try {
            if ($this->config->restEnableKeys != true) {
                $this->_printError(lang('RestCommand.apiKeyDisabled'));
                exit;
            }

            $keyModel = new \Daycry\RestServer\Models\KeyModel();
            $key = new \Daycry\RestServer\Entities\KeyEntity();

            $level = $params[ 'level' ] ?? CLI::getOption('level');
            $ignoreLimits = $params[ 'ignoreLimits' ] ?? CLI::getOption('ignoreLimits');
            $private = !($params[ 'noPrivate' ] ?? CLI::getOption('noPrivate'));
            $ip = $params[ 'ip' ] ?? CLI::getOption('ip');

            if (!$level) {
                $level = CLI::prompt(lang('RestCommand.insertLevelApiKey'), null, 'required|greater_than_equal_to[0]|less_than_equal_to[10]');
            }

            if ($private) {
                if (!$ip) {
                    $ip = CLI::prompt(lang('RestCommand.insertIpAdressApiKey'), null, 'required|valid_ip[ipv4]');
                }
            }

            $key->fill(array( $this->config->restKeyColumn => random_string('alnum', $this->config->restKeyLength), 'level' => $level, 'ignore_limits' => boolval($ignoreLimits), 'is_private_key' => boolval($private), 'ip_addresses' => $ip ));

            if ($keyModel->save($key)) {
                CLI::write('**** CREATED. ****', 'white', 'green');
            }
        } catch (\Exception $ex) {
            CLI::newLine();
            CLI::error('**** ' . $ex->getMessage() . ' ****');
            CLI::newLine();
        }
    }

    private function _printError(string $error)
    {
        CLI::newLine();
        CLI::error('**** ' . $error . ' ****');
        CLI::newLine();
    }
}
