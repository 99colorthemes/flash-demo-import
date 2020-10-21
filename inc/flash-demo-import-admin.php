<?php

defined( 'ABSPATH' ) || exit;

/**
 * Flash demo importing functions
 *
 * Class Flash_Demo_Import_Admin
 */
class Flash_Demo_Import_Admin {

    function __construct()
    {
        $this->load_dependencies();
    }

    /**
     * load dependencies
     */
    function load_dependencies() {
        require_once FLASH_DEMO_IMPORT_PATH . 'inc/importers/class-attachment-importers.php';
        require_once FLASH_DEMO_IMPORT_PATH . 'inc/importers/class-content-importers.php';
        require_once FLASH_DEMO_IMPORT_PATH . 'inc/importers/class-customizer-and-widget-importers.php';
    }

    /**
     * Import Attachments.
     *
     * @throws Exception
     */
    function import_attachments() {

        check_ajax_referer( 'flash_demo_import_nonce', 'nounce' );

        $theme_demo_id = intval($_POST['theme_demo_id']);

        //import attachments.
        $fdi_attachment_importers =  new Fdi_Attachment_Importers();
        $fdi_attachment_imported  = $fdi_attachment_importers->import($theme_demo_id);

        //handle errors
        if(is_wp_error( $fdi_attachment_imported ) ) {
            http_response_code(500);
            echo json_encode([
                'status' => $fdi_attachment_imported->get_error_code(),
                'message' => $fdi_attachment_imported->get_error_message()
            ]);
            exit;
        }

        //set transient data
        $flash_demo_import_map_featured_images = get_transient('flash_demo_import_map_featured_images_'.$theme_demo_id);
        $fdi_mapped_attachments_old_and_new_ids = $fdi_attachment_importers->get_mapped_attachments_old_and_new_ids();
        if($flash_demo_import_map_featured_images) {
            $fdi_mapped_attachments_old_and_new_ids = array_merge(
                $flash_demo_import_map_featured_images,
                $fdi_mapped_attachments_old_and_new_ids
            );
        }

        set_transient('flash_demo_import_map_featured_images_'.$theme_demo_id, $fdi_mapped_attachments_old_and_new_ids);

        http_response_code(200);
        echo esc_html__('Attachments imported successfully.', 'flash-demo-import');
        exit;
    }

    /**
     * Import contents
     */
    function import_contents() {
        check_ajax_referer( 'flash_demo_import_nonce', 'nounce' );

        $theme_demo_id = intval($_POST['theme_demo_id']);

        $fdi_content_importers = new Fdi_Content_Importers();

        $fdi_content_imported = $fdi_content_importers->import($theme_demo_id);

        //handle errors
        if(is_wp_error( $fdi_content_imported ) ) {
            http_response_code(500);
            echo json_encode([
                'status' => $fdi_content_imported->get_error_code(),
                'message' => $fdi_content_imported->get_error_message()
            ]);
            exit;
        }

        //managing theme location
        if(count($fdi_content_importers->get_all_created_menus()) > 0) {
            $locations = get_theme_mod('nav_menu_locations');

            foreach ($fdi_content_importers->get_all_created_menus() as $created_menu) {
                $menu = wp_get_nav_menu_object($created_menu);
                if($created_menu == 'topbar-menu') {
                    $locations['topbar-menu'] = intval($menu->term_id);
                } else {
                    $locations['primary'] = intval($menu->term_id);
                }
            }
            set_transient('flash_demo_import_nav_menu_location_'.$theme_demo_id, $locations);
        }

        set_transient(
            'flash_demo_import_map_post_ids_'.$theme_demo_id,
            $fdi_content_importers->get_map_content_old_and_new_ids()
        );
        set_transient(
            'flash_demo_import_map_taxonomy_ids_'.$theme_demo_id,
            $fdi_content_importers->get_map_taxonomy_old_and_new_ids()
        );

        http_response_code(200);
        echo esc_html__('Content imported successfully.', 'flash-demo-import');
        exit;
    }

    /**
     * Import customer and widgets.
     */
    function import_customizer_and_widgets() {
        check_ajax_referer( 'flash_demo_import_nonce', 'nounce' );

        $theme_demo_id = intval($_POST['theme_demo_id']);

        $fdi_widget_importers = new Fdi_Customizer_And_Widget_Importers();

        $fdi_widget_imported = $fdi_widget_importers->import($theme_demo_id);

        //handle errors
        if(is_wp_error( $fdi_widget_imported ) ) {
            http_response_code(500);
            echo json_encode([
                'status' => $fdi_widget_imported->get_error_code(),
                'message' => $fdi_widget_imported->get_error_message()
            ]);
            exit;
        }

        $this->data_import_complete($theme_demo_id);

        http_response_code(200);
        echo esc_html__('Widgets imported successfully.', 'flash-demo-import');
        exit;
    }

    /**
     * Data import complete
     *
     * @param $theme_demo_id
     */
    public function data_import_complete($theme_demo_id) {

        // updating nav locations.
        $nav_menu_location = get_transient('flash_demo_import_nav_menu_location_'.$theme_demo_id);
        if($nav_menu_location) {
            set_theme_mod( 'nav_menu_locations', $nav_menu_location );
        }

        //flushing unwanted data.
        delete_transient('flash_demo_import_map_post_ids_'.$theme_demo_id);
        delete_transient('flash_demo_import_map_taxonomy_ids_'.$theme_demo_id);
        delete_transient('flash_demo_import_nav_menu_location_'.$theme_demo_id);
        delete_transient('flash_demo_import_map_featured_images_'.$theme_demo_id);

        set_transient( 'flash_demo_import_complete'.$theme_demo_id, true);
    }
}