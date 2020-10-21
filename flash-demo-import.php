<?php
/*
  Plugin Name: Flash Demo Import
  Description: Import themes demo with just one click. Themes it currently supports only for 99colorthemes
  Version: 1.0.0
  Author: 99colorthemes
  Author URI: https://99colorthemes.com/
  License:     GPLv2 or later
  License URI: https://www.gnu.org/licenses/gpl-2.0.html
  Domain Path: /languages
  Text Domain: flash-demo-import
 */

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

define( 'FLASH_DEMO_IMPORT_VERSION', '1.0.0' );
define( 'FLASH_DEMO_IMPORT_PLUGIN_DOMAIN', 'flash-demo-import' );
define( 'FLASH_DEMO_IMPORT_URL', plugin_dir_url( __FILE__ ) );
define( 'FLASH_DEMO_IMPORT_PATH', plugin_dir_path( __FILE__ ) );
define( 'FLASH_DEMO_IMPORT_THIRD_PARTY_API_URL', 'https://app.99colorthemes.com/api/');

// helper functions for plugin
require_once FLASH_DEMO_IMPORT_PATH . 'inc/flash-demo-import-functions.php';

/**
 * Class Flash_Demo_Import
 */
class Flash_Demo_Import {

    public static function load_dependencies() {
        require_once FLASH_DEMO_IMPORT_PATH . 'inc/flash-demo-import-page.php';
        require_once FLASH_DEMO_IMPORT_PATH . 'inc/flash-demo-import-admin.php';

        require_once FLASH_DEMO_IMPORT_PATH . 'inc/flash-demo-import-api-calls.php';
        require_once FLASH_DEMO_IMPORT_PATH . 'inc/flash-demo-import-api-url.php';
    }

    public static function run() {
        static::load_dependencies();

        add_action('admin_menu', array(new Flash_Demo_Import_Page(), 'init'));

        /**
         * Importing phase
         */
        add_filter( 'wp_ajax_flash_demo_import_attachments',
            array( new Flash_Demo_Import_Admin(), 'import_attachments' ), 10, 1 );

        add_filter( 'wp_ajax_flash_demo_import_contents',
            array( new Flash_Demo_Import_Admin(), 'import_contents' ), 10, 1 );

        add_filter( 'wp_ajax_flash_demo_import_customizer_and_widgets',
            array( new Flash_Demo_Import_Admin(), 'import_customizer_and_widgets' ), 10, 1 );
    }

}

/**
 * Run plugin
 */
Flash_Demo_Import::run();