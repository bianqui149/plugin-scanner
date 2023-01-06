<?php

/**
 *  Plugin_Scanner
 *
 * Initialize the plugin.
 */
class Plugin_Scanner_Admin_Settings
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
			self::$instance = new Plugin_Scanner_Admin_Settings;
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
		add_action('admin_menu', [$this, 'add_plugin_options_page']);
		add_action('admin_init', [$this, 'add_plugin_page_init']);
	}

	public function add_plugin_options_page()
	{
		// This page will be under "Settings"
		add_options_page(
			'WPScanner Admin',
			'WPScanner Admin',
			'manage_options',
			'wpscanner-admin',
			array($this, 'create_admin_wps_page')
		);
	}

	/**
	 * Options page callback
	 */
	public function create_admin_wps_page()
	{
		// Set class property
		$this->options = get_option('wps_option_name');
?>
		<div class="wrap">
			<h1>My Settings</h1>
			<form method="post" action="options.php">
				<?php
				// This prints out all hidden setting fields
				settings_fields('wps_option_group');
				do_settings_sections('my-setting-admin');
				submit_button();
				?>
			</form>
		</div>
<?php
	}

	/**
	 * Register and add settings
	 */
	public function add_plugin_page_init()
	{
		register_setting(
			'wps_option_group', 
			'wps_option_name', 
			[ $this, 'sanitize_input_values' ]
		);

		add_settings_section(
			'setting_section_id',
			'WPSCANNER Settings',
			array($this, 'print_section_info'),
			'my-setting-admin'
		);

		add_settings_field(
			'public_key_wps',
			'KEY WPSCANNER', 
			[ $this, 'public_key_wps_callback' ],
			'my-setting-admin',
			'setting_section_id'        
		);

		add_settings_field(
			'plugins_list_wps',
			'List of Plugins',
			[ $this, 'plugins_list_wps_callback' ],
			'my-setting-admin',
			'setting_section_id'
		);
	}

	/**
	 * Sanitize each setting field as needed
	 *
	 * @param array $input Contains all settings fields as array keys
	 */
	public function sanitize_input_values( $input )
	{
		$new_input = array();
		if (isset($input['public_key_wps']))
			$new_input['public_key_wps'] = sanitize_text_field($input['public_key_wps']);

		if (isset($input['plugins_list_wps']))
			$new_input['plugins_list_wps'] = sanitize_text_field($input['plugins_list_wps']);

		return $new_input;
	}

	/** 
	 * Print the Section text
	 */
	public function print_section_info()
	{
		print 'Enter your settings below:';
	}

	/** 
	 * Get the settings option array and print one of its values
	 */
	public function public_key_wps_callback()
	{
		printf(
			'<input type="password" id="public_key_wps" class="regular-text" name="wps_option_name[public_key_wps]" value="%s" />',
			isset($this->options['public_key_wps']) ? esc_attr($this->options['public_key_wps']) : ''
		);
	}

	/** 
	 * Get the settings option array and print one of its values
	 */
	public function plugins_list_wps_callback()
	{
		printf(
			'<input type="text" id="plugins_list_wps" class="regular-text" name="wps_option_name[plugins_list_wps]" value="%s" />',
			isset($this->options['plugins_list_wps']) ? esc_attr($this->options['plugins_list_wps']) : ''
		);
	}

} // End class.
Plugin_Scanner_Admin_Settings::get_instance();
