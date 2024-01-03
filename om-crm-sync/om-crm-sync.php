<?php
/**
 * Plugin Name: OM CRM Sync
 * Plugin URI:  http://www.openminds.com
 * Description: complete functionality for working with CRM's API.
 * Version:     0.1.0
 * Author:      Billy Fischbach, OPEN MINDS
 * Author URI:  https://www.openminds.com
 * License:     GPLv2+
 * Text Domain: omcrm
 */

defined( 'ABSPATH' ) or die();

if( !defined( 'OMCRM_USERNAME' ) || !defined('OMCRM_PASSWORD' ) ){
	return;
}

define( 'OMCRM_PATH',    dirname( __FILE__ ) . '/' );
define( 'OMCRM_INC',     OMCRM_PATH . 'includes/' );

require OMCRM_PATH . '/vendor/autoload.php';

/**
 * Admin -------
 */
require_once( OMCRM_INC . 'admin/setup.php' );
require_once( OMCRM_INC . 'admin/ajax.php' );

/**
 * Automations --------
 */
require_once( OMCRM_INC . 'automations/syncProducts.php' );
require_once( OMCRM_INC . 'automations/syncVendors.php' );

/**
 * Objects --------
 */
require_once( OMCRM_INC . 'objects/HTIProducts.php' );
require_once( OMCRM_INC . 'objects/HTIVendors.php' );

/**
 * Services --------
 */
require_once( OMCRM_INC . 'services/Bootstrap.php' );

/**
 * Setup ---------
 */
require_once( OMCRM_INC . 'setup/cron.php' );


/**
 * WP-CLI --------
 */
if ( defined('WP_CLI') && WP_CLI ) {
	require_once( OMCRM_INC . 'wp-cli/Sync.php' );
}

register_activation_hook( __FILE__, function() {

	do_action('omcrm_setup');

} );

register_deactivation_hook( __FILE__, function() {

	do_action('omcrm_teardown');

} );