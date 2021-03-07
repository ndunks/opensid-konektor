<?php
/**
 * Plugin Name: OpenSID Konektor
 * Description: Plugin untuk pengembang WP yang membutuhkan akses data ke Aplikasi OpenSID
 * Plugin URI: https://github.com/ndunks/opensid-konektor
 * Author: Mochamad Arifin
 * Author URI: http://klampok.id/
 */

define( 'OPENSID_KONEKTOR', plugin_dir_path( __FILE__ ) );

class OpenSID_Konektor
{
    public static $title = 'OpenSID Konektor';
    public static $name = 'opensid-konektor';
    public static $version = '1.0.0';
    public static $me = null;
    public static $config = null;
    private $konektor_state = null;

    public function __construct()
    {
        add_action( 'init', array( $this, 'init' ) );
        add_action( 'wp_ajax_opensid_konektor_setting', array( $this, 'ajax_setting' ) );

        $saved_config = get_option( self::$name );
        if ( !empty( $saved_config ) && is_array( $saved_config ) ) {
            self::$config = $saved_config;
        }

        if ( empty( self::$config ) || self::$config['version'] != self::$version ) {
            include OPENSID_KONEKTOR . 'upgrade.php';
        }

        // don't do in setting page, will test later
        if ( @$_GET['page'] != self::$name ) {
            $driver = OPENSID_KONEKTOR . 'konektor/' . self::$config['konektor']['type'] . '/driver.php';
            if ( file_exists( $driver ) ) {
                include OPENSID_KONEKTOR . 'opensid.php';
                $konektor = include $driver;
                $this->konektor_state = @$konektor->connect();
                if ( $this->konektor_state !== true ) {
                    add_action( 'admin_notices', [$this, 'notice_konektor_state'] );
                }
                $GLOBALS['OpenSID'] = $konektor;
            }
        }
    }

    public function init()
    {
        add_action( 'admin_menu', array( $this, 'admin_menu' ) );
    }

    public function notice_konektor_state()
    {
        printf( '<div class="error notice"><h2>OpenSID Konektor Error</h2><p>
    %s<br/><a href="/wp-admin/options-general.php?page=%s">Ubah Pengaturan</a>
    </p></div>', $this->konektor_state, self::$name );
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

        $konektor_setting = OPENSID_KONEKTOR . 'konektor/' . self::$config['konektor']['type'] . '/setting.php';

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
            self::$name,
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
            self::$config['opensid'] = $_POST['opensid'];
            update_option( self::$name, self::$config, true );
            // Test the setting
            $driver = OPENSID_KONEKTOR . 'konektor/' . self::$config['konektor']['type'] . '/driver.php';
            if ( file_exists( $driver ) ) {
                include OPENSID_KONEKTOR . 'opensid.php';
                $konektor = include $driver;
                $this->konektor_state = @$konektor->connect();
            } else {
                $this->konektor_state = 'Invalid Konektor Type!';
            }
            if ( $this->konektor_state === true ) {
                echo '<div class="notice-success notice"><p>Berhasil terhubung dengan Aplikasi OpenSID</div>';
            } else {
                $this->notice_konektor_state();
            }
        }

        include OPENSID_KONEKTOR . 'setting.php';
    }

}

if ( is_null( OpenSID_Konektor::$me ) ) {
    OpenSID_Konektor::$me = new OpenSID_Konektor();
}
