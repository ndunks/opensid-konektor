<?php
/**
 * Semua konektor baik melalui API ataupun Database harus meng-ekstend kelas ini.
 */
abstract class OpenSID
{
    abstract function __construct( $config );
    abstract function listAparat();
}
