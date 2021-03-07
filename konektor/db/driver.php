<?php

class OpenSID_Konektor_DB extends OpenSID
{
    public $config = [];

    function __construct( $config )
    {
        $this->config = $config;
    }

    function listAparat()
    {
        return ['APARAT'];
    }
}
return new OpenSID_Konektor_DB(self::$config);
