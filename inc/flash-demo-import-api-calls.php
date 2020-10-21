<?php
defined( 'ABSPATH' ) || exit;

/**
 * Class Flash_Demo_Import_Api_Calls
 */
class Flash_Demo_Import_Api_Calls {

    private $is_error = false;

    private $request = null;

    private $response_code = 200;

    public function __construct($url)
    {
        $request = wp_remote_get($url);

        $this->request = $request;
        $this->handle_errors($request);
    }

    /**
     * Handle errors
     *
     * @param $request
     */
    public function handle_errors($request)
    {
        if( is_wp_error( $request )) {
            $this->is_error = true;
            $this->response_code = $request->get_error_code();
            exit;
        }

        $this->response_code = wp_remote_retrieve_response_code( $request );
        if($this->response_code !== 200) {
            $this->is_error = true;
        }
    }

    /**
     * Does api request send error.
     *
     * @return bool
     */
    public function has_error()
    {
        return $this->is_error;
    }

    /**
     * Is the response success
     *
     * @return bool
     */
    public function is_success()
    {
        return !$this->is_error;
    }

    /**
     * get Response code
     *
     * @return int
     */
    public function get_response_code()
    {
        return $this->response_code;
    }

    /**
     * Get error object.
     *
     * @return WP_Error
     */
    public function get_error() {
        if($this->response_code === 403) {
            return new WP_Error(
                'forbidden_api_call',
                __( 'Please buy a premium theme and add license key to import the premium demo.', 'flash-demo-import' )
            );
        }
        return new WP_Error(
            'import_failed',
            __( 'Failed to import.', 'flash-demo-import' )
        );
    }

    /**
     * Fetch data
     *
     * @return mixed
     */
    public function fetch_data() {

        $body = wp_remote_retrieve_body( $this->request );

        $data = json_decode( $body, true);

        return $data;
    }
}

