<?php

class OpenSID_Konektor_DB extends OpenSID
{
    public $config = [];
    /**
     * @var mysqli
     */
    public $db;

    function __construct( $config )
    {
        $this->config = $config;
    }

    function connect()
    {
        if ( empty( $this->config['konektor']['setting'] ) || empty( $this->config['konektor']['setting']['username'] ) ) {
            return 'Silahkan atur konfigurasi';
        }
        $db = new mysqli(
            @$this->config['konektor']['setting']['host'],
            @$this->config['konektor']['setting']['username'],
            @$this->config['konektor']['setting']['password'],
            @$this->config['konektor']['setting']['database']
        );

        if ( $db->connect_errno ) {
            return $db->connect_error;
        }
        $this->db = $db;
        return true;
    }

    function listAparat()
    {
        $ret = $this->db->query( "SELECT * from tweb_desa_pamong where pamong_status = 1" );
        $data = [];
        foreach ( $ret->fetch_all( MYSQLI_ASSOC ) as $row ) {
            $data[] = [
                'nama' => $row['pamong_nama'],
                'nik' => $row['pamong_nik'],
                'jabatan' => $row['jabatan'],
                'foto' => $this->getFotoUrl($row['foto'])
            ];

        }
        $ret->free();
        return $data;
    }
}
return new OpenSID_Konektor_DB( self::$config );
