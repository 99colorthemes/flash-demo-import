<?php

defined( 'ABSPATH' ) || exit;

/**
 * Class to import customizer and widgets demo data.
 *
 * Class Fdi_Customizer_And_Widget_Importers
 */
class Fdi_Customizer_And_Widget_Importers {

    /**
     * Available widgets
     *
     * @var array
     */
    private $available_widgets = [];

    /**
     * Fdi_Customizer_And_Widget_Importers constructor.
     */
    public function __construct()
    {
        $this->available_widgets = $this->get_available_widgets();
    }

    /**
     * Import widgets.
     *
     * @param $theme_demo_id
     * @return WP_Error
     */
    public function import($theme_demo_id)
    {
        $flash_demo_import_api_calls = new Flash_Demo_Import_Api_Calls(
            fdi_get_import_options_url($theme_demo_id)
        );

        //api call fail
        if($flash_demo_import_api_calls->has_error()) {
            return $flash_demo_import_api_calls->get_error();
        }

        if ($flash_demo_import_api_calls->is_success()) {

            $sidebars_with_widgets = $flash_demo_import_api_calls->fetch_data();

            $this->import_widgets($sidebars_with_widgets);

            $this->import_customizer($sidebars_with_widgets, $theme_demo_id);
        }
    }

    private function import_widgets($sidebars_with_widgets) {
        global $wp_registered_sidebars;

        if (isset($sidebars_with_widgets['widgets'])) {

            foreach ($sidebars_with_widgets['widgets'] as $sidebar_id => $widget_siderbar) {

                //default set widgets to inactive
                $use_sidebar_id = 'wp_inactive_widgets';

                //if the widgets has sidebar assign it.
                if (isset($wp_registered_sidebars[$sidebar_id])) {
                    $use_sidebar_id = $sidebar_id;
                }

                foreach ($widget_siderbar as $widget_id => $widget) {

                    $base_widget_id = preg_replace('/-[0-9]+$/', '', $widget_id);

                    $is_widget_appropriate_to_save = $this->is_widget_appropriate_to_save($widget, $base_widget_id, $use_sidebar_id);

                    if(!$is_widget_appropriate_to_save) {
                        continue;
                    }

                    $single_widget_instances = get_option('widget_' . $base_widget_id);
                    $single_widget_instances = !empty($single_widget_instances) ? $single_widget_instances : array('_multiwidget' => 1);
                    $single_widget_instances[] = $widget; // Add it.

                    end($single_widget_instances);
                    //give you the key of last widget
                    $new_instance_id_number = key($single_widget_instances);

                    if ( '0' === strval( $new_instance_id_number ) ) {
                        $new_instance_id_number                           = 1;
                        $single_widget_instances[ $new_instance_id_number ] = $single_widget_instances[0];
                        unset( $single_widget_instances[0] );
                    }

                    $this->save_widget($base_widget_id, $single_widget_instances);
                    $this->save_widget_to_sidebar($base_widget_id, $new_instance_id_number, $use_sidebar_id);
                }
            }
        }
    }


    public function import_customizer($sidebars_with_widgets, $theme_demo_id)
    {
        global $wp_customize;

        if (isset($sidebars_with_widgets['customizer'])) {

            foreach ( $sidebars_with_widgets['customizer'] as $key => $val ) {
                if($key == 'show_on_front' || $key == 'page_on_front'){
                    update_option($key, $this->filter_value($theme_demo_id, $key, $val) );
                } else {
                    do_action( 'customize_save_' . $key, $wp_customize );
                    set_theme_mod( $key, $this->filter_value($theme_demo_id, $key, $val) );
                }
            }
        }
    }

    public function filter_value($theme_demo_id, $key, $value) {

        if (stripos($key, "page") !== false) {
            $mapped_post_ids = get_transient('flash_demo_import_map_post_ids_'.$theme_demo_id);


            return $this->get_value_by_mapped_ids($mapped_post_ids, $value);
        }

        if (stripos($key, "category") !== false) {
            $mapped_taxanomy_ids = get_transient('flash_demo_import_map_taxonomy_ids_'.$theme_demo_id);

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


    /**
     * Get widgets with saved values.
     *
     * @return array
     */
    function get_widgets_with_saved_value() {
        $widget_instances = [];
        foreach ($this->available_widgets as $widget_data) {
            $widget_instances[$widget_data['id_base']] = get_option('widget_' . $widget_data['id_base']);
        }

        return $widget_instances;
    }

    /**
     * get available widgets
     *
     * @return array
     */
    function get_available_widgets() {
        global $wp_registered_widget_controls;
        $widget_controls   = $wp_registered_widget_controls;

        $available_widgets = array();

        foreach ( $widget_controls as $widget ) {
            if ( ! empty( $widget['id_base'] ) && ! isset( $available_widgets[ $widget['id_base'] ] ) ) {
                $available_widgets[ $widget['id_base'] ]['id_base'] = $widget['id_base'];
                $available_widgets[ $widget['id_base'] ]['name']    = $widget['name'];
            }
        }

        return $available_widgets;
    }

    /**
     * Is same setting widgets available.
     *
     * @param $widget
     * @param $base_widget_id
     * @param $use_sidebar_id
     * @param $widget_instances
     * @return bool
     */
    function is_same_setting_widget_available($widget, $base_widget_id, $use_sidebar_id, $widget_instances) {

        if (isset( $widget_instances[ $base_widget_id ] ) ) {
            // Get existing widgets in this sidebar.
            $sidebars_widgets = get_option( 'sidebars_widgets' );
            $sidebar_widgets  = isset( $sidebars_widgets[ $use_sidebar_id ] ) ? $sidebars_widgets[ $use_sidebar_id ] : array(); // Check Inactive if that's where will go.

            // Loop widgets with ID base.
            $single_widget_instances = ! empty( $widget_instances[ $base_widget_id ] ) ? $widget_instances[ $base_widget_id ] : array();
            foreach ( $single_widget_instances as $check_id => $check_widget ) {
                // Is widget in same sidebar and has identical settings?
                if ( in_array( "$base_widget_id-$check_id", $sidebar_widgets ) && (array) $widget == $check_widget ) {
                    return true;
                }
            }
        }

        return false;

    }

    /**
     * Is widget appropriate to save.
     *
     * @param $widget
     * @param $base_widget_id
     * @param $use_sidebar_id
     * @return bool
     */
    function is_widget_appropriate_to_save($widget, $base_widget_id, $use_sidebar_id) {
        // If widget is not available escape this part.
        if (!isset($this->available_widgets[$base_widget_id])) {
            return false;
        }

        $widget_instances = $this->get_widgets_with_saved_value();

        // If widget with same settings already exist escape saving part.
        if ($this->is_same_setting_widget_available($widget, $base_widget_id, $use_sidebar_id, $widget_instances)) {
            return false;
        }

        return true;
    }

    /**
     * Save widget.
     *
     * @param $base_widget_id
     * @param $single_widget_instances
     */
    function save_widget($base_widget_id, $single_widget_instances) {

        //move multiwidget to end array.
        if ( isset( $single_widget_instances['_multiwidget'] ) ) {
            $multiwidget = $single_widget_instances['_multiwidget'];
            unset( $single_widget_instances['_multiwidget'] );
            $single_widget_instances['_multiwidget'] = $multiwidget;
        }

        // Update option with new widget.
        update_option( 'widget_' . $base_widget_id, $single_widget_instances );
    }

    /**
     * Save widget to sidebar
     *
     * @param $base_widget_id
     * @param $new_instance_id_number
     * @param $use_sidebar_id
     */
    function save_widget_to_sidebar($base_widget_id, $new_instance_id_number, $use_sidebar_id) {
        $sidebars_widgets = get_option( 'sidebars_widgets' );
        if ( ! $sidebars_widgets ) {
            $sidebars_widgets = array();
        }

        $new_instance_id = $base_widget_id . '-' . $new_instance_id_number;
        $sidebars_widgets[ $use_sidebar_id ][] = $new_instance_id;
        update_option( 'sidebars_widgets', $sidebars_widgets );
    }

}
