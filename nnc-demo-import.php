<?php
/*
  Plugin Name: NNC Demo Import
  Description: Import your content, widgets and theme settings with one click
  Version: 1.0.0
  Author: 99colorthemes
  Author URI: https://99colorthemes.com/
  License:     GPLv2 or later
  License URI: https://www.gnu.org/licenses/gpl-2.0.html
  Domain Path: /languages
  Text Domain: nnc-demo-import
 */

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

define( 'NNC_DEMO_IMPORT_PATH', plugin_dir_path( __FILE__ ) );
define( 'NNC_DEMO_IMPORT_URL', plugin_dir_url( __FILE__ ) );
define( 'NNC_DEMO_PLUGIN_NAME', 'nnc-demo-import' );
define( 'NNC_DEMO_VERSION', '1.0.0' );
define( 'NNC_DEMO_IMPORT_THIRD_PARTY_API_URL', 'https://app.99colorthemes.com/api/');

require_once NNC_DEMO_IMPORT_PATH . 'inc/nnc-demo-import-functions.php';

/**
 * Class Nnc_Export
 */
class Nnc_Demo_Import {

    public static function load_dependencies() {
        require_once NNC_DEMO_IMPORT_PATH . 'inc/nnc-demo-import-page.php';
        require_once NNC_DEMO_IMPORT_PATH . 'inc/nnc-demo-import-admin.php';

        require_once NNC_DEMO_IMPORT_PATH . 'inc/nnc-demo-import-api-calls.php';
        require_once NNC_DEMO_IMPORT_PATH . 'inc/nnc-demo-import-api-url.php';
    }

    public static function run() {
        static::load_dependencies();

        add_action('admin_menu', array(new Nnc_Demo_Import_Page(), 'init'));

        /**
         * Importing phase
         */
        add_filter( 'wp_ajax_nnc_demo_import_attachments',
            array( new Nnc_Demo_Import_Admin(), 'import_attachments' ), 10, 1 );

        add_filter( 'wp_ajax_nnc_demo_import_contents',
            array( new Nnc_Demo_Import_Admin(), 'import_contents' ), 10, 1 );

        add_filter( 'wp_ajax_nnc_demo_import_widgets',
            array( new Nnc_Demo_Import_Admin(), 'import_widgets' ), 10, 1 );

        add_filter( 'wp_ajax_nnc_demo_import_customizer',
            array( new Nnc_Demo_Import_Admin(), 'import_customizer' ), 10, 1 );
    }

}

/**
 * Run plugin
 */
Nnc_Demo_Import::run();