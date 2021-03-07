<?php

$opensid_konektor_default_config = [
    'version' => OpenSID_Konektor::$version,
    'konektor' => [
        'type' => "db",
        'setting' => [
            "host" => "localhost"
        ],
    ],
];

if ( empty( OpenSID_Konektor::$config ) ) {

    // Default Config
    OpenSID_Konektor::$config = $opensid_konektor_default_config;
    update_option( OpenSID_Konektor::$name, OpenSID_Konektor::$config, true );
} elseif ( OpenSID_Konektor::$config['version'] != OpenSID_Konektor::$version ) {

    // Merge Config
    $ignore = ['konektor'];

    foreach ( $opensid_konektor_default_config as $key => $value ) {
        if ( in_array( $key, $ignore ) && !empty( @OpenSID_Konektor::$config[$key] ) ) {
            continue;
        }

        if ( empty( @OpenSID_Konektor::$config[$key] ) ) {
            OpenSID_Konektor::$config[$key] = $value;
        }
    }

    OpenSID_Konektor::$config['version'] = infoPendatang::$version;
    update_option( OpenSID_Konektor::$name, OpenSID_Konektor::$config, true );
}
