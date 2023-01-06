<?php


/**
 *  Plugin_Scanner_Cronjob
 *
 * Initialize the plugin.
 */
class Plugin_Scanner_Cronjob
{

	private static $instance = false;

	/**
	 * Instantiates the Singleton if not already done and return it.
	 *
	 * @return obj  Instance of this class; false on failure
	 */
	public static function get_instance()
	{
		if (!self::$instance) {
			self::$instance = new Plugin_Scanner_Cronjob;
		}
		return self::$instance;
	}

	/**
	 * Construct the class instance.
	 *
	 * @return void
	 */
	public function __construct()
	{

        if ( ! wp_next_scheduled( 'wpscanner_results' ) ) {
		    wp_schedule_event( time(), 'weekly', 'wpscanner_results' );
        }
        add_action( 'wpscanner_results', [ $this, 'wp_scanner_function_request' ]);
        add_filter('cron_schedules', [ $this, 'cron_schedules_custom'] );
		
	}

    /**
     * It takes the public key from the plugin's settings, and then uses it to make a request to the
     * plugin's server.
     * The array of plugins that have a vulnerability score greater than or equal to the score
     * set by the user.
     *  
     * @return void
     */
    public function wp_scanner_function_request() {
        $data       = get_option( 'wps_option_name' );
        $plugins    = '';
        $storage_db = [];
        if ( ! isset( $data['public_key_wps'] ) ) {
            return;
        }
        if ( ! isset( $data['plugins_list_wps'] ) ) {
            $plugins = $this->get_list_of_plugins();
        } else {
            $plugins = $this->get_storage_data_from_plugin();
        }
		foreach($plugins as $plugin){
			$request = $this->get_request_handle( $data['public_key_wps'], $plugin[0] );
			if ( $request > $plugin[1] ) {
				array_push(
					$storage_db,
					$plugin
				);
			}
		}
		if( get_option('wp_request_option_data') ) {
			update_option('wp_request_option_data', json_encode( $storage_db ) );
		} else {
			add_option( 'wp_request_option_data', json_encode( $storage_db ) );
		}
    }

    /**
     * It gets the data from the database, splits it into an array, then loops through the array and
     * compares it to the data from the database
     * 
     * @return An array of arrays.
     */
    public function get_storage_data_from_plugin(){
        $data         = get_option( 'wps_option_name' );
		$storage_data = explode(',',$data['plugins_list_wps']);
		$versions     = [];
		$all_plugins  = get_plugins();
		foreach ( $storage_data as $slug_plugin ) {
			foreach ( $all_plugins as $plugin ) {
				if ( $plugin['TextDomain'] === $slug_plugin ) {
					array_push( 
						$versions,
						array(
							$plugin['TextDomain'],
							floatval($plugin['Version'])
						),
					);
				}
			}
		}
        return $versions;
    }

    /**
     * It returns an array of all the plugins installed on the site, except for the ones that start
     * with `wp-plugin-`
     * 
     * @return An array of arrays. Each array contains the name of the plugin and the version number.
     */
    public function get_list_of_plugins() {
        $list             = get_plugins();
		$plugins_filtered = [];
		foreach ( $list as $plugin ) {
			
			array_push( 
				$plugins_filtered, 
				array(
					$plugin['TextDomain'],
					floatval( $plugin['Version'] ) 
					)
				);
			
		}
		return $plugins_filtered;
    }

    /**
     * It adds a new schedule to the list of schedules that WordPress uses for cron jobs
     * 
     * @param schedules This is an array of schedules and their respective intervals.
     * 
     * @return the array of schedules.
     */
    public function cron_schedules_custom( $schedules ) {
        $schedules['weekly'] = array(
            'interval' => 604800,
            'display'  => __( 'Once Weekly' )
        );
        return $schedules;
    }

    /**
     * It gets the latest version of a plugin from the wpscan API.
     * 
     * @param credentials Your API key
     * @param plugin_slug The plugin slug, which is the plugin name in lowercase with dashes instead of
     * spaces.
     * 
     * @return The version number of the latest version of the plugin.
     */
    public function get_request_handle ( $credentials, $plugin_slug ) {
        $curl = curl_init();
		curl_setopt_array($curl, array(
		CURLOPT_URL            => 'https://wpscan.com/api/v3/plugins/' . $plugin_slug,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING       => '',
		CURLOPT_MAXREDIRS      => 10,
		CURLOPT_TIMEOUT        => 0,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST  => 'GET',
		CURLOPT_HTTPHEADER     => array(
			'Authorization: Token token=' . $credentials
		  ),
		));
		$response      = curl_exec($curl);
		curl_close($curl);
		$plugin_result = json_decode( $response, TRUE );
        if ( array_key_exists( $plugin_slug, $plugin_result ) ) {
		    $fixed = floatval( end( $plugin_result[$plugin_slug]["vulnerabilities"] )["fixed_in"] );
        }
		return $fixed;
    }

} // End class.
Plugin_Scanner_Cronjob::get_instance();
