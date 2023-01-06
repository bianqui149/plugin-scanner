<?php

/**
 *  Plugin_Scanner_Endpoint
 *
 * Initialize the plugin.
 */
class Plugin_Scanner_Endpoint
{

	/**
	 * Instance of the class; False if not instantiated yet.
	 *
	 * @var boolean
	 */
	private static $instance = false;

	/**
	 * Instantiates the Singleton if not already done and return it.
	 *
	 * @return obj  Instance of this class; false on failure
	 */
	public static function get_instance()
	{
		if (!self::$instance) {
			self::$instance = new Plugin_Scanner_Endpoint;
		}
		return self::$instance;
	}

	/**
	 * Environment (DEV|QA|PRD)
	 *
	 * @var string
	 */
	public $env_name = '';

	/**
	 * Endpoint
	 *
	 * @var string
	 */
	public $endpoint_wpscan = 'https://wpscan.com/api/v3/plugins/';

	/**
	 * Hash
	 *
	 * @var string
	 */
	public $hash = ENV['hash_code'];


	/**
	 * Construct the class instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		add_action( 'rest_api_init', [ $this, 'rest_api_wps' ] );
	}


	/**
	 * Define the rest api
	 *
	 * @return void
	 */
	public function rest_api_wps()
	{
		register_rest_route( '/security/v1', '/list', array(
			'methods'  => 'GET',
			'callback' => [ $this, 'get_latest_security_data' ]
		) );
	}

	/**
	 * Define the rest api
	 *
	 * @return void
	 */
	public function get_latest_security_data( $data )
	{
		$secret_key = $data->get_param( 'sp' );
		if( isset($secret_key) || $secret_key !== $this->hash ){
			$response = array(
				'status'  => 401,
				'message' => 'Unauthorized'
			);
		}
		$storage_info = get_option( 'wp_request_option_data' );
        if ( isset( $storage_info ) ) {
            $response = array(
				'status'  => 410,
				'message' => 'Gone'
			);
        }
		if ( $storage_info && $secret_key === $this->hash) {
			$response = array(
				'status'  => 200,
				'message' => json_decode( $storage_info )
			);
		}
		return new WP_REST_Response( $response );
	}

} // End class.
Plugin_Scanner_Endpoint::get_instance();
