<?php

defined( 'ABSPATH' ) || exit;

/**
 * Class to import post, page demo data.
 *
 * Class Fdi_Content_Importers
 */
class Fdi_Content_Importers {

    private $map_content_old_and_new_ids = [];
    private $map_taxonomy_old_and_new_ids = [];
    private $all_created_menus = [];

    /**
     * Import content
     *
     * @param $theme_demo_id
     * @return WP_Error
     */
    public function import($theme_demo_id)
    {
        $flash_demo_import_api_calls = new Flash_Demo_Import_Api_Calls(
            fdi_get_import_contents_url($theme_demo_id)
        );

        //api call fail
        if($flash_demo_import_api_calls->has_error()) {
            return $flash_demo_import_api_calls->get_error();
        }

        if ($flash_demo_import_api_calls->is_success()) {

            $contents = $flash_demo_import_api_calls->fetch_data();

            if (isset($contents['data'])) {

                if(isset($contents['data']['post'])) {
                    $this->save_content($contents['data']['post'], 'post', $theme_demo_id);
                }

                if(isset($contents['data']['page'])) {
                    $this->save_content($contents['data']['page'], 'page', $theme_demo_id);
                }

                if(isset($contents['data']['nav_menu_items'])) {
                    $this->save_content($contents['data']['nav_menu_items'], 'nav_menu_item', $theme_demo_id);
                }
            }
        }

    }

    public function get_all_created_menus() {
        return $this->all_created_menus;
    }



    /**
     * Save content
     *
     * @param $posts
     * @param $post_type
     * @param $theme_demo_id
     */
    public function save_content($posts, $post_type, $theme_demo_id) {

        $flash_demo_import_feature_images = get_transient( 'flash_demo_import_map_featured_images_'.$theme_demo_id);

        foreach ($posts as $post) {
            $content = json_decode($post['content'], true);
            $original_id = isset($content['post_id'])? (int) $content['post_id']: 0;
            $content['post_author'] = (int) get_current_user_id();


            $content_exists = $this->post_exists( $content );
            if ( $content_exists  && $post_type != 'nav_menu_item') {
                $this->map_content_old_and_new_ids[$original_id] = $content_exists;
                continue;
            }

            // Whitelist to just the keys we allow
            $contentdata = array(
                'import_id' => $content['post_id'],
            );
            $allowed = array(
                'post_author'    => true,
                'post_date'      => true,
                'post_date_gmt'  => true,
                'post_content'   => true,
                'post_excerpt'   => true,
                'post_title'     => true,
                'post_status'    => true,
                'post_name'      => true,
                'comment_status' => true,
                'ping_status'    => true,
                'guid'           => true,
                'post_parent'    => true,
                'menu_order'     => true,
                'post_type'      => true,
                'post_password'  => true,
            );



            foreach ( $content as $key => $value ) {
                if ( ! isset( $allowed[ $key ] ) ) {
                    continue;
                }

                $contentdata[ $key ] = $content[ $key ];
            }

            $contentdata['post_type'] = $post_type;


            $taxonomies_ids = $this->prepare_taxonomies_ids($post_type, $content);
            $content_id = wp_insert_post( $contentdata, true );

            //update taxonomies
            if(count($taxonomies_ids) > 0) {
                foreach ( $taxonomies_ids as $tax => $ids ) {
                    wp_set_post_terms( $content_id, $ids, $tax );
                }
            }


            //store meta data
            $this->store_meta_data($content_id, $content);

            //update thumbnail ids
            if(isset($content['meta']['_thumbnail_id'])) {
                $old_thumbnail_id = $content['meta']['_thumbnail_id'];

                if(isset($flash_demo_import_feature_images[$old_thumbnail_id])  &&
                    $flash_demo_import_feature_images[$old_thumbnail_id] != $old_thumbnail_id) {
                    update_post_meta( $content_id, '_thumbnail_id', intval($flash_demo_import_feature_images[$old_thumbnail_id]));
                }
            }

            if($post_type == 'nav_menu_item') {
                $this->update_post_menu_item_object_id($content_id, $content);
            }

            $this->map_content_old_and_new_ids[$original_id] = intval($content_id);

            do_action( 'wp_import_insert_post', intval($content_id), intval($original_id), $contentdata, $content );

        }
    }

    /**
     * Update menu item object id
     *
     * @param $post_id
     */
    protected function update_post_menu_item_object_id( $post_id, $content) {
        $item_type = get_post_meta( $post_id, '_menu_item_type', true );
        $original_object_id = get_post_meta( $post_id, '_menu_item_object_id', true );
        $object_id = null;

        switch ( $item_type ) {
            case 'taxonomy':
                if ( isset( $this->map_taxonomy_old_and_new_ids[ $original_object_id ] ) ) {
                    $object_id = $this->map_taxonomy_old_and_new_ids[ $original_object_id ];
                } else {
                    add_post_meta( $post_id, '_wxr_import_menu_item', wp_slash( $original_object_id ) );
                }
                break;

            case 'post_type':
                if ( isset( $this->map_content_old_and_new_ids[ $original_object_id ] ) ) {
                    $object_id = $this->map_content_old_and_new_ids[ $original_object_id ];
                } else {
                    add_post_meta( $post_id, '_wxr_import_menu_item', wp_slash( $original_object_id ) );
                }
                break;

            case 'custom':
                $object_id = $post_id;
                break;
        }

        if ( empty( $object_id ) ) {
            return;
        }

        update_post_meta( $post_id, '_menu_item_object_id', wp_slash( $object_id ) );

        $menu_id = '';
        if(isset($content['terms']['nav_menu'][0]['name'])) {
            $nav_menu = $content['terms']['nav_menu'][0];
            $menu = wp_get_nav_menu_object(sanitize_text_field($nav_menu['name']));
            if( !$menu ) {
                $menu_id = wp_create_nav_menu(sanitize_text_field($nav_menu['name']));
                $this->all_created_menus[] = sanitize_title($nav_menu['slug']);

            } else {
                $menu_id = $menu->term_id;
            }
        }

        if($menu_id) {
            wp_set_object_terms( $post_id, array( intval($menu_id) ), 'nav_menu' );
        }
    }

    /**
     * Store Meta data.
     *
     * @param $post_id
     * @param $content
     */
    public function store_meta_data($post_id, $content) {
        if(isset($content['meta'])) {
            foreach ($content['meta'] as $meta_key => $meta_value) {
                $menu_value = get_post_meta( $post_id, $meta_key, true );
                $value = maybe_unserialize( $meta_value );

                if($menu_value) {
                    update_post_meta( $post_id, wp_slash( $meta_key ), wp_slash_strings_only( $value ) );
                } else {
                    add_post_meta( $post_id, wp_slash( $meta_key ), wp_slash_strings_only( $value ) );
                    do_action( 'import_post_meta', $post_id, wp_slash( $meta_key ), $value );
                }
            }
        }
    }


    /**
     * Prepare taxonomies ids
     *
     * @param $post_type
     * @param $content
     * @return array
     */
    public function prepare_taxonomies_ids($post_type, $content)
    {
        $taxonomies_ids = array();
        if($post_type == 'post') {
            if ( ! empty( $content['terms'] ) ) {
                foreach ( $content['terms'] as $term_slug => $terms ) {
                    foreach ( $terms as $term ) {
                        $taxonomy = $term['taxonomy'];
                        if ( taxonomy_exists( $taxonomy ) ) {
                            $term_exists = term_exists( $term['slug'], $taxonomy );

                            $term_id = is_array( $term_exists ) ? $term_exists['term_id'] : $term_exists;

                            if ( ! $term_id ) {
                                $term_id_tax_id = wp_insert_term(sanitize_text_field($term['name']), $taxonomy, $term );
                                if ( is_wp_error( $term_id_tax_id ) ) {
                                    continue;
                                }

                                $term_id = $term_id_tax_id['term_id'];
                            }

                            $taxonomies_ids[ $taxonomy ][] = intval( $term_id );

                            $this->map_taxonomy_old_and_new_ids[$term['term_taxonomy_id']] = intval($term_id);
                        }
                    }
                }
            }
        }

        return $taxonomies_ids;
    }

    /**
     * Post exists.
     *
     * @param $data
     * @return int
     */
    public function post_exists( $data ) {

        $exists = post_exists(sanitize_text_field($data['post_title']), $data['post_content'], $data['post_date']);

        return $exists;
    }


    public function get_map_content_old_and_new_ids() {
        return $this->map_content_old_and_new_ids;
    }

    public function get_map_taxonomy_old_and_new_ids() {
        return $this->map_taxonomy_old_and_new_ids;
    }


}
