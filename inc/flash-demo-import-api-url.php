<?php

defined( 'ABSPATH' ) || exit;

/**
 * Get import customizer url.
 *
 * @param $theme_demo_id
 * @return string
 */
function fdi_get_import_customizer_url($theme_demo_id) {
    $fdi_main_url = FLASH_DEMO_IMPORT_THIRD_PARTY_API_URL . 'theme-demos/' . $theme_demo_id . '/customizer';

    return $fdi_main_url . fdi_license_url_query();
}

/**
 * Get import widgets url.
 *
 * @param $theme_demo_id
 * @return string
 */
function fdi_get_import_options_url($theme_demo_id) {
    $fdi_main_url = FLASH_DEMO_IMPORT_THIRD_PARTY_API_URL . 'theme-demos/' . $theme_demo_id . '/options';

    return $fdi_main_url . fdi_license_url_query();
}

/**
 * Get import content Url.
 *
 * @param $theme_demo_id
 * @return string
 */
function fdi_get_import_contents_url($theme_demo_id) {
    $fdi_main_url = FLASH_DEMO_IMPORT_THIRD_PARTY_API_URL . 'theme-demos/' . $theme_demo_id . '/contents';

    return $fdi_main_url . fdi_license_url_query();
}

/**
 * Get import attachments url
 *
 * @param $theme_demo_id
 * @param string $url_params
 * @return string
 */
function fdi_get_import_attachments_url($theme_demo_id, $url_params = '') {

    $fdi_main_url = FLASH_DEMO_IMPORT_THIRD_PARTY_API_URL . 'theme-demos/' . $theme_demo_id . '/attachments' ;

    if($url_params != '') {
        $fdi_main_url .= $url_params;
    }

    return $fdi_main_url . fdi_license_url_query($url_params != ''?'&':'?');
}

/**
 * Get theme demos url
 *
 * @return string
 */
function fdi_get_theme_demos_url() {
    $theme_name = fdi_base64_url_encode(
        fdi_get_current_theme_domain()
    );

    return FLASH_DEMO_IMPORT_THIRD_PARTY_API_URL . 'themes/' . $theme_name;
}



/**
 * license url query
 *
 * @param string $url_pattern
 * @return string
 */
function fdi_license_url_query($url_pattern = '?') {

    $theme_data = get_option(  fdi_get_current_theme_domain() . '-license-settings' );

    if((isset($theme_data['email']) && $theme_data['email'] != '') &&
        (isset($theme_data['license_key']) && $theme_data['license_key'] != '')) {

        return $url_pattern.'license_email=' . sanitize_email($theme_data['email']) . '&license_key=' . sanitize_title($theme_data['license_key']);
    }

    return '';
}