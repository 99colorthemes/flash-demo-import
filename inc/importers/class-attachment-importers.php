<?php

defined( 'ABSPATH' ) || exit;

/**
 * Class to import demo attachments
 *
 * Class Nnc_Attachment_Importers
 */
class Nnc_Attachment_Importers {

    private $map_attachment_old_and_new_ids = [];

    /**
     * Import attachments.
     *
     * @param $theme_demo_id
     * @return int|WP_Error
     */
    public function import($theme_demo_id) {
        //api calls
        $nnc_demo_import_api_calls = new Nnc_Demo_Import_Api_Calls(
            nnc_get_import_attachments_url($theme_demo_id)
        );

        //api call fail
        if($nnc_demo_import_api_calls->has_error()) {
            return $nnc_demo_import_api_calls->get_error();
        }

        //api call success
        if ($nnc_demo_import_api_calls->is_success()) {

            $attachments = $nnc_demo_import_api_calls->fetch_data();

            if (isset($attachments['data'])) {

                foreach ($attachments['data'] as $attachment) {

                    $content = json_decode($attachment['content'], true);
                    $attachment = get_page_by_title($content['post_title'], OBJECT, 'attachment');

                    //if attachment is already uploaded
                    if($attachment) {
                        $this->map_attachment_old_and_new_ids[$content['post_id']] = $attachment->ID;
                        continue;
                    }

                    $upload = $this->fetch_remote_file( $content );

                    if ( is_wp_error( $upload ) ) {
                        return $upload;
                    }

                    $filesize = filesize( $upload['file'] );

                    //ignore if file size is 0
                    if ( 0 === $filesize ) {
                        unlink( $upload['file'] );
                        continue;
                    }


                    $info = wp_check_filetype( $upload['file'] );
                    //ignore if file type invalid
                    if ( ! $info ) {
                       continue;
                    }

                    $post['post_title'] = $content['post_title'];
                    $post['post_mime_type'] = $info['type'];

                    $post_id = wp_insert_attachment( $post, $upload['file'] );

                    //ignore if attachment failed to insert
                    if ( is_wp_error( $post_id ) ) {
                        continue;
                    }

                    $content_metadata = wp_generate_attachment_metadata( $post_id, $upload['file'] );

                    wp_update_attachment_metadata( $post_id, $content_metadata );

                    $this->map_attachment_old_and_new_ids[$content['post_id']] = $post_id;
                }
            }
        }
    }

    /**
     * Get Mapped attachments old and new ids
     *
     * @return array
     */
    public function get_mapped_attachments_old_and_new_ids() {
        return $this->map_attachment_old_and_new_ids;
    }

    /**
     * Fetch remote file
     *
     * @param $url
     * @param $post
     * @return array|WP_Error
     */
    function fetch_remote_file( $post ) {
        $url = ! empty( $post['post_content'] ) ? $post['post_content'] : $post['guid'];

        $file_name = basename( $url );
        $upload = wp_upload_bits( $file_name, 0, '', $post['post_date'] );

        if ( $upload['error'] ) {
            return new WP_Error( 'upload_dir_error', $upload['error'] );
        }

        $response = wp_remote_get( $url, array(
            'stream' => true,
            'timeout' => 60,
            'filename' => $upload['file'],
        ) );

        if ( is_wp_error( $response ) ) {
            unlink( $upload['file'] );
            //return $response;
        }

        $code = (int) wp_remote_retrieve_response_code( $response );

        if ( $code !== 200 ) {
            unlink( $upload['file'] );
            return new WP_Error('import_failed', __( 'Failed to import.', 'nnc-demo-importer' ));
        }

        return $upload;
    }

}
