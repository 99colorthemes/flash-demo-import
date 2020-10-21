<?php

defined( 'ABSPATH' ) || exit;

/**
 * Base64 Url encode
 *
 * @param $input
 * @return string
 */
function fdi_base64_url_encode($input) {
    return strtr(base64_encode($input), '+/=', '._-');
}

/**
 * get current theme author
 *
 * @return array|false|string
 */
function fdi_get_current_theme_name(){
    return wp_get_theme()->get( 'Name' );
}

/**
 *  get current theme slug
 *
 * @return string
 */
function fdi_get_current_theme_domain() {
    return wp_get_theme()->get('TextDomain');
}