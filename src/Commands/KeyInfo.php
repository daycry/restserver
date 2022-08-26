<?php

namespace Daycry\RestServer\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use CodeIgniter\Config\BaseConfig;
use Daycry\RestServer\Exceptions\UnauthorizedException;

class KeyInfo extends BaseCommand
{
    protected $group       = 'Rest Server';
    protected $name        = 'restserver:apikeyinfo';
    protected $description = 'Get information about api key.';
    protected $usage = 'restserver:apikeyinfo [Options]';
    protected $options = [ '-key' => 'Api Key' ];

    protected BaseConfig $config;

    public function run(array $params)
    {
        $this->config = config('RestServer');
        $this->db = db_connect( $this->config->restDatabaseGroup);

        try
        {
            $key = $params[ 'key' ] ?? CLI::getOption( 'key' );

            if( empty( $key ) )
            {
                $key = CLI::prompt( lang( 'RestCommand.insertApiKey' ), null, 'required|max_length['.$this->config->restKeyLength.']' );
                CLI::newLine();
            }

            $keyModel = new \Daycry\RestServer\Models\KeyModel();
            $row = $keyModel->where($this->config->restKeyColumn, $key)->first();

            if(!$row)
            {
                throw UnauthorizedException::forInvalidApiKey($key);
            }

            $this->_getKeyInfo($row);

        }catch( \Exception $ex )
        {
            CLI::newLine();
            CLI::error( $ex->getMessage() );
            CLI::newLine();
        }
    }

    private function _getKeyInfo( \Daycry\RestServer\Entities\KeyEntity $row )
    {
        CLI::write('****' . lang( 'RestCommand.apiKeyInfo' ) . '****', 'green', 'light_gray');

        $thead = $this->_getTableColumns($this->config->restKeysTable);
        $tbody = [
            [$row->id, $row->{$this->config->restKeyColumn}, $row->level, $row->ignore_limits, $row->is_private_key, $row->ip_addresses, $row->created_at, $row->updated_at, $row->deleted_at]
        ];

        CLI::table($tbody, $thead);
    }

    private function _getTableColumns(string $table) :array
    {
        return $this->db->getFieldNames($table);
    }
}
