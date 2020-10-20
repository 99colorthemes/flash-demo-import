<?php

defined( 'ABSPATH' ) || exit;

/**
 * Class to import customizer
 *
 * Class Nnc_Customizer_Importers
 */
class Nnc_Customizer_Importers {

    /**
     * Import customizer
     *
     * @param $theme_demo_id
     * @return WP_Error
     */
    public function import($theme_demo_id)
    {
        global $wp_customize;

        $nnc_demo_import_api_calls = new Nnc_Demo_Import_Api_Calls(
            nnc_get_import_customizer_url($theme_demo_id)
        );

        //api call fail
        if($nnc_demo_import_api_calls->has_error()) {
            return $nnc_demo_import_api_calls->get_error();
        }

        if ($nnc_demo_import_api_calls->is_success()) {
            $customizer = $nnc_demo_import_api_calls->fetch_data();

            if (isset($customizer['data'])) {

                foreach ( $customizer['data'] as $key => $val ) {
                    if($key == 'show_on_front' || $key == 'page_on_front'){
                        update_option($key, $this->filter_value($theme_demo_id, $key, $val) );
                    } else {
                        do_action( 'customize_save_' . $key, $wp_customize );
                        set_theme_mod( $key, $this->filter_value($theme_demo_id, $key, $val) );
                    }
                }
            }
        }
    }

    public function filter_value($theme_demo_id, $key, $value) {

        if (stripos($key, "page") !== false) {
            $mapped_post_ids = get_transient('nnc_demo_importer_map_post_ids_'.$theme_demo_id);


            return $this->get_value_by_mapped_ids($mapped_post_ids, $value);
        }

        if (stripos($key, "category") !== false) {
            $mapped_taxanomy_ids = get_transient('nnc_demo_importer_map_taxonomy_ids_'.$theme_demo_id);

            return $this->get_value_by_mapped_ids($mapped_taxanomy_ids, $value);
        }

        return $value;

    }

    public function get_value_by_mapped_ids($mapped_ids, $value) {

        $old_ids = explode(',', $value);
        $original_ids = array();
        foreach ($old_ids as $old_id) {
            if($mapped_ids[$old_id]) {
                $original_ids[] = $mapped_ids[$old_id];
            } else {
                $original_ids[] = $old_id;
            }

        }

        return implode(',', $original_ids);
    }

}
