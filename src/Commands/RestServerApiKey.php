<?php

namespace Daycry\RestServer\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use CodeIgniter\Config\BaseConfig;

use Daycry\RestServer\Models\KeyModel;
use Daycry\RestServer\Entities\KeyEntity;
use Exception;

class RestServerApiKey extends BaseCommand
{
    protected $group       = 'Rest Server';
    protected $name        = 'restserver:apikey';
    protected $description = 'Create an api key.';
    protected $usage = 'restserver:apikey [Options]';
    protected $options = [ '-level' => 'Api Key Level','-ignoreLimits' => 'Ignore limits', '-noPrivate' => 'Private IP adresses', '-ip' => 'IP adresses' ];

    private BaseConfig $config;

    public function run(array $params)
    {
        $this->config = config('RestServer');

        helper('text');

        try {
            if ($this->config->restEnableKeys != true) {
                $this->_printError(lang('RestCommand.apiKeyDisabled'));
                exit;
            }

            $keyModel = new KeyModel();
            $key = new KeyEntity();

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
        } catch (Exception $ex) {
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
