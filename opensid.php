<?php
/**
 * Semua konektor baik melalui API ataupun Database harus meng-ekstend kelas ini.
 */
abstract class OpenSID
{
    public $config;
    abstract function __construct( $config );
    abstract function connect();
    abstract function listAparat();

    function getUrl( $path )
    {
        return $this->config['opensid']['url'] . $path;
    }

    function getFotoUrl( $file )
    {
        if ( empty( $file ) ) {
            $file = $this->config['opensid']['foto_default'];
        } else {
            $file = $this->config['opensid']['foto_path'] . $file;
        }
        return $this->getUrl( $file );
    }
}
