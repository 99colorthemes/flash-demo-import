<?php

defined( 'ABSPATH' ) || exit;

/**
 * Get import customizer url.
 *
 * @param $theme_demo_id
 * @return string
 */
function nnc_get_import_customizer_url($theme_demo_id) {
    return NNC_DEMO_IMPORT_THIRD_PARTY_API_URL . 'theme-demos/' . $theme_demo_id . '/customizer';
}

/**
 * Get import widgets url.
 *
 * @param $theme_demo_id
 * @return string
 */
function nnc_get_import_widgets_url($theme_demo_id) {
    return NNC_DEMO_IMPORT_THIRD_PARTY_API_URL . 'theme-demos/' . $theme_demo_id . '/widgets';
}

/**
 * Get import content Url.
 *
 * @param $theme_demo_id
 * @return string
 */
function nnc_get_import_contents_url($theme_demo_id) {
    return NNC_DEMO_IMPORT_THIRD_PARTY_API_URL . 'theme-demos/' . $theme_demo_id . '/contents';
}

/**
 * Get import attachments url
 *
 * @param $theme_demo_id
 * @return string
 */
function nnc_get_import_attachments_url($theme_demo_id) {
    return NNC_DEMO_IMPORT_THIRD_PARTY_API_URL . 'theme-demos/' . $theme_demo_id . '/attachments';
}

/**
 * Get theme demos url
 *
 * @return string
 */
function nnc_get_theme_demos_url() {
    $theme_name = nnc_demo_import_base64_url_encode(
        nnc_demo_import_get_current_theme_domain()
    );

    return NNC_DEMO_IMPORT_THIRD_PARTY_API_URL . 'themes/' . $theme_name;
}
