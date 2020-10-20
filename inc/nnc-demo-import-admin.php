<?php

defined( 'ABSPATH' ) || exit;

/**
 * Nnc demo importing functions
 *
 * Class Nnc_Demo_Import_Admin
 */
class Nnc_Demo_Import_Admin {

    function __construct()
    {
        $this->load_dependencies();
    }

    /**
     * load dependencies
     */
    function load_dependencies() {
        require_once NNC_DEMO_IMPORT_PATH . 'inc/importers/class-widget-importers.php';
        require_once NNC_DEMO_IMPORT_PATH . 'inc/importers/class-content-importers.php';
        require_once NNC_DEMO_IMPORT_PATH . 'inc/importers/class-attachment-importers.php';
        require_once NNC_DEMO_IMPORT_PATH . 'inc/importers/class-customizer-importers.php';
    }

    /**
     * Import Attachments.
     *
     * @throws Exception
     */
    function import_attachments() {

        check_ajax_referer( 'nnc_demo_import_nonce', 'nounce' );

        $theme_demo_id = $_POST['theme_demo_id'];
        $nnc_demo_import = get_transient( 'nnc_demo_importer_'.$theme_demo_id);

        if(!$nnc_demo_import) {
            //import attachments.
            $nnc_attachment_importers =  new Nnc_Attachment_Importers();
            $nnc_attachment_imported  = $nnc_attachment_importers->import($theme_demo_id);

            //handle errors
            if(is_wp_error( $nnc_attachment_imported ) ) {
                http_response_code(500);
                echo json_encode([
                    'status' => $nnc_attachment_imported->get_error_code(),
                    'message' => $nnc_attachment_imported->get_error_message()
                ]);
                exit;
            }

            //set transient data
            set_transient( 'nnc_demo_importer_'.$theme_demo_id, 1);
            set_transient(
                'nnc_demo_importer_map_featured_images_'.$theme_demo_id,
                $nnc_attachment_importers->get_mapped_attachments_old_and_new_ids()
            );
        }

        http_response_code(200);
        echo 'Attachments imported successfully.';
        exit;
    }

    /**
     * Import contents
     */
    function import_contents() {
        check_ajax_referer( 'nnc_demo_import_nonce', 'nounce' );

        $theme_demo_id = $_POST['theme_demo_id'];
        $nnc_demo_import = get_transient( 'nnc_demo_importer_'.$theme_demo_id);

        if($nnc_demo_import == 1) {
            $nnc_content_importers = new Nnc_Content_Importers();

            $nnc_content_imported = $nnc_content_importers->import($theme_demo_id);

            //handle errors
            if(is_wp_error( $nnc_content_imported ) ) {
                http_response_code(500);
                echo json_encode([
                    'status' => $nnc_content_imported->get_error_code(),
                    'message' => $nnc_content_imported->get_error_message()
                ]);
                exit;
            }

            //managing theme location
            if(count($nnc_content_importers->get_all_created_menus()) > 0) {
                $locations = get_theme_mod('nav_menu_locations');

                foreach ($nnc_content_importers->get_all_created_menus() as $created_menu) {
                    $menu = wp_get_nav_menu_object($created_menu);
                    if($created_menu == 'topbar-menu') {
                        $locations['topbar-menu'] = $menu->term_id;
                    } else {
                        $locations['primary'] = $menu->term_id;
                    }
                }
                set_transient(
                    'nnc_demo_importer_nav_menu_location_'.$theme_demo_id,
                    $locations
                );
            }

            set_transient(
                'nnc_demo_importer_map_post_ids_'.$theme_demo_id,
                $nnc_content_importers->get_map_content_old_and_new_ids()
            );
            set_transient(
                'nnc_demo_importer_map_taxonomy_ids_'.$theme_demo_id,
                $nnc_content_importers->get_map_taxonomy_old_and_new_ids()
            );
            set_transient( 'nnc_demo_importer_'.$theme_demo_id, 2);
        }

        http_response_code(200);
        echo 'Content imported successfully.';
        exit;
    }



    /**
     * Import customizer settings.
     */
    function import_customizer() {
        check_ajax_referer( 'nnc_demo_import_nonce', 'nounce' );
        $theme_demo_id = $_POST['theme_demo_id'];
        $nnc_demo_import = get_transient( 'nnc_demo_importer_'.$theme_demo_id);

        if($nnc_demo_import == 2) {

            $nnc_customizer_importers = new Nnc_Customizer_Importers();

            $nnc_customizer_imported = $nnc_customizer_importers->import($theme_demo_id);

            $nav_menu_location = get_transient('nnc_demo_importer_nav_menu_location_'.$theme_demo_id);

            if($nav_menu_location) {
                set_theme_mod( 'nav_menu_locations', $nav_menu_location );
            }

            //handle errors
            if(is_wp_error( $nnc_customizer_imported ) ) {
                http_response_code(500);
                echo json_encode([
                    'status' => $nnc_customizer_imported->get_error_code(),
                    'message' => $nnc_customizer_imported->get_error_message()
                ]);
                exit;
            }

            set_transient('nnc_demo_importer_'.$theme_demo_id, 3);
        }

        http_response_code(200);
        echo 'Customizer imported successfully.';
        exit;
    }

    /**
     * Import widgets.
     */
    function import_widgets() {
        check_ajax_referer( 'nnc_demo_import_nonce', 'nounce' );
        $theme_demo_id = $_POST['theme_demo_id'];
        $nnc_demo_import = get_transient( 'nnc_demo_importer_'.$theme_demo_id);

        if($nnc_demo_import == 3) {

            $nnc_widget_importers = new Nnc_Widget_Importers();

            $nnc_widget_imported = $nnc_widget_importers->import($theme_demo_id);

            //handle errors
            if(is_wp_error( $nnc_widget_imported ) ) {
                http_response_code(500);
                echo json_encode([
                    'status' => $nnc_widget_imported->get_error_code(),
                    'message' => $nnc_widget_imported->get_error_message()
                ]);
                exit;
            }

            set_transient( 'nnc_demo_importer_'.$theme_demo_id, 4);
        }

        //flushing unwanted data.
        delete_transient('nnc_demo_importer_nav_menu_location_'.$theme_demo_id);
        delete_transient('nnc_demo_importer_map_featured_images_'.$theme_demo_id);
        delete_transient('nnc_demo_importer_map_post_ids_'.$theme_demo_id);
        delete_transient('nnc_demo_importer_map_taxonomy_ids_'.$theme_demo_id);

        http_response_code(200);
        echo 'Widgets imported successfully.';
        exit;
    }
}