<?php
namespace Daycry\RestServer\Config;

use CodeIgniter\Config\BaseConfig;

class JWT extends BaseConfig
{
    /**
     * In Base64 encode
     */
    public $signer = 'mBC5v1sOKVvbdEitdSBenu59nfNfhwkedkJVNabosTw=';

    public $issuer = 'http://example.local';
    
    public $audience = 'http://example.local';
    
    public $identifier = '4f1g23a12aa';

    public $canOnlyBeUsedAfter = '+0 minute';

    public $expiresAt = '+24 hour';
    
}