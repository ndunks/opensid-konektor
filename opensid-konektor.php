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

    public function filter_callback($value){
        $value[] = [
            'name' => self::$name,
            'title' => self::$title,
            'file' => __FILE__,
            'update_git' => true,
            'version' => self::$version,
        ];
        return $value;
    }

    public function init()
    {
        add_action( 'admin_menu', array( $this, 'admin_menu' ) );
        add_filter( 'opensid_extensions', [$this, 'filter_callback'], 1 );
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
            // No return, keep display the setting page
        }elseif ( isset($_GET['update_git'])) {
            $plugin_name = $_GET['update_git'];
            $plugin = null;
            $items = apply_filters( "opensid_extensions", [] );
            $restore_cwd = getcwd();
            ob_start();
            do {
                foreach($items as $key => $item ){
                    if(@$item['name'] == $plugin_name){
                        $plugin = $item;
                        break;
                    }
                }
                if(!$plugin){
                    echo '<h3>Plugin not found</h3>';
                    break;
                }

                if(!function_exists('system')){
                    echo '<h3>Failed</h3>';
                    echo '<p><b><code>system()</code></b> function is disabled.</p>';
                    break;
                }

                $dir = dirname($plugin['file']);
                if(!is_dir($dir)){
                    echo '<h3>Invalid plugin directory.</h3>';
                    break;
                }
                chdir($dir);
    
                $ret = -1;
                system('which git > /dev/null', $ret);
                if($ret != 0){
                    echo '<h3>Error, GIT binary not found in server.</h3>';
                    break;
                }
                $git_remote = @$plugin['git_remote'] ?: 'origin';
                $git_branch = @$plugin['git_branch'] ?: 'master';
                $cmd_prefix = '';
                if( null != @$_GET['runas']){
                    $cmd_prefix = 'sudo -u ' . escapeshellarg($_GET['runas']) . ' ';
                }
                // Warn command injection
                $command_lists = [
                    "$cmd_prefix git --version",
                    "$cmd_prefix git log --oneline -n 1",
                    "$cmd_prefix git fetch --no-tags $git_remote $git_branch",
                    "$cmd_prefix git log --oneline -n 1",
                    "$cmd_prefix git reset --hard $git_branch",
                    "$cmd_prefix git clean -fdn",
                    // echo "Run bellow command to clean"
                    // echo "$cmd_prefix git clean -fd"
                    "$cmd_prefix git status"
                ];

                foreach($command_lists as $k => $cmd) {
                    echo "<b>-> $cmd</b>\n";
                    system("$cmd 2>&1", $ret);
                    if($ret != 0){
                        echo "<h3>(!) Failed, return <b>$ret</b></h3>";
                        break;
                    }
                }
                

            }while(false);
            $result = ob_get_clean();
            chdir($restore_cwd);
            $link = esc_url( add_query_arg(
                [
                    'page' => $_GET['page'],
                ],
                get_admin_url() . 'options-general.php?'
            ) );

            echo '<h1>Updating with git</h1>';
            echo '<pre>';
            echo $result;
            echo '</pre>';
            
            if($ret != 0){
                $link = esc_url( add_query_arg(
                    [
                        'page' => $_GET['page'],
                        'update_git' => $_GET['update_git'],
                        'runas' => 'admin'
                    ],
                    get_admin_url() . 'options-general.php?'
                ) );
                echo '<p><i>TIPS:</i> Add <a href="' . $link .
                    '"><code>&amp;runas=admin</code></a> to run as admin.</p>';
            }

            echo '<h3><a href="' . $link . '">Back</a></h3>';
            return;
        }

        include OPENSID_KONEKTOR . 'setting.php';
    }

}

if ( is_null( OpenSID_Konektor::$me ) ) {
    OpenSID_Konektor::$me = new OpenSID_Konektor();
}
