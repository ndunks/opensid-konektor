<?php
/**
 * Plugin Name: OpenSID Konektor
 * Description: Plugin untuk pengembang WP yang membutuhkan akses data ke Aplikasi OpenSID
 * Plugin URI: https://github.com/ndunks/opensid-konektor
 * Author: Mochamad Arifin
 * Author URI: http://klampok.id/
 */

define( 'OPENSID_KONEKTOR_DIR', plugin_dir_path( __FILE__ ) );
define( 'OPENSID_KONEKTOR_URL', plugins_url( '', __FILE__ ) . '/' );

class OpenSID_Konektor
{
    public static $title = 'OpenSID Konektor';
    public static $name = 'opensid-konektor';
    public static $version = '1.0.0';
    public static $me = null;
    public static $konektor = null;
    public static $config = null;

    public function __construct()
    {
        add_action( 'init', array( $this, 'init' ) );
        add_action( 'wp_ajax_opensid_konektor_setting', array( $this, 'ajax_setting' ) );

        $saved_config = get_option( self::$name );
        if ( !empty( $saved_config ) && is_array( $saved_config ) ) {
            self::$config = $saved_config;
        }

        if ( empty( self::$config ) || self::$config['version'] != self::$version ) {
            include OPENSID_KONEKTOR_DIR . 'upgrade.php';
        }
        $konektor = OPENSID_KONEKTOR_DIR . 'konektor/' . self::$config['konektor']['type'] . '/driver.php';

        if ( file_exists( $konektor ) ) {
            include OPENSID_KONEKTOR_DIR . 'opensid.php';
            self::$konektor = include $konektor;
        }
    }

    public function init()
    {
        add_action( 'admin_menu', array( $this, 'admin_menu' ) );
    }

    public function ajax_setting()
    {
        if ( !wp_verify_nonce( @$_POST['nonce'] ) ) {
            die( 'Tolong reload halaman.' );
        }
        $type = $_POST['type'];
        if ( !empty( $_POST['setting'] ) && is_array( $_POST['setting'] ) ) {
            $setting = $_POST['setting'];
        } else {
            $setting = [];
        }

        $konektor_setting = OPENSID_KONEKTOR_DIR . 'konektor/' . self::$config['konektor']['type'] . '/setting.php';

        if ( file_exists( $konektor_setting ) ) {
            include $konektor_setting;
        } else {
            echo "Not found.";
        }
        die();
    }

    public function admin_menu()
    {
        add_submenu_page(
            'options-general.php',
            'Pengaturan OpenSID Konektor',
            'OpenSID Konektor',
            'administrator',
            'opensid-konektor',
            array( $this, 'setting' ) );
    }

    public function setting()
    {
        if ( isset( $_POST['konektor'] ) && is_array( $_POST['konektor'] ) ) {
            if ( !wp_verify_nonce( @$_POST['nonce'] ) ) {
                wp_die( 'Tolong reload halaman.' );
            };
            // sanitize to prevent dir travesal
            $_POST['konektor']['type'] = strtr(
                $_POST['konektor']['type'],
                ".,=:$@*~%&^!/|\\",
                "_______________" );
            self::$config['konektor'] = $_POST['konektor'];
            update_option( self::$name, self::$config, true );
        }

        include OPENSID_KONEKTOR_DIR . 'setting.php';
    }

}

if ( is_null( OpenSID_Konektor::$me ) ) {
    OpenSID_Konektor::$me = new OpenSID_Konektor();
    $GLOBALS['OpenSID'] = OpenSID_Konektor::$konektor;
}
