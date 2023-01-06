<?php
/**
 * Plugin Name: Plugin Scanner
 * Plugin URI: https://github.com/bianqui149/plugin-scanner
 * Description: This plugin checks the vulnerabilities of the other pluginss.
 * Version: 0.1
 * Author: Julian Bianqui.
 * Author URI: https://github.com/bianqui149/
 *
 * @package plugin-scanner
 * @version 0.1
 * @author Julian Bianqui <bianquijulian@gmail.com>
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'WP_PLUGIN_SCANNER' ) ) {
	define( 'WP_PLUGIN_SCANNER', plugin_dir_path( __FILE__ ) );
}

require_once( WP_PLUGIN_SCANNER . '/private-scripts/class-plugin-scanner-admin-settings.php' );
require_once( WP_PLUGIN_SCANNER . '/private-scripts/class-plugin-scanner-endpoint.php' );
require_once( WP_PLUGIN_SCANNER . '/private-scripts/class-plugin-scanner-cronjob.php' );

register_deactivation_hook( __FILE__, 'my_deactivation' );
  
function my_deactivation() {
    wp_clear_scheduled_hook( 'wpscanner_results' );
}