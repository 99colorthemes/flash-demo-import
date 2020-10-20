<?php

defined( 'ABSPATH' ) || exit;

/**
 * Base64 Url encode
 *
 * @param $input
 * @return string
 */
function nnc_demo_import_base64_url_encode($input) {
    return strtr(base64_encode($input), '+/=', '._-');
}

/**
 * nnc get current theme author
 *
 * @return array|false|string
 */
function nnc_demo_import_get_current_theme_name(){
    return wp_get_theme()->get( 'Name' );
}

/**
 * Nnc get current theme slug
 *
 * @return string
 */
function nnc_demo_import_get_current_theme_domain() {
    return wp_get_theme()->get('TextDomain');
}