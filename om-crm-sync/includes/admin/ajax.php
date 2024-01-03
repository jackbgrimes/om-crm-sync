<?php

namespace OM\CRM\Admin;

use \League\Csv\Writer;
use function \OM\CRM\Setup\omcrm_reschedule_cron;
use function \OM\CRM\Setup\omcrm_maintenance;

add_action("wp_ajax_omcrm_tools", __NAMESPACE__ . "\\handle");
add_action("wp_ajax_nopriv_omcrm_tools", __NAMESPACE__ . "\\no_response");

function handle(){

	$nonce = sanitize_text_field( $_POST['wp_nonce'] );
	$perform = sanitize_text_field( $_POST['perform'] );

	// Verify Capabilities
	if( !current_user_can( 'manage_options' ) ) {
		wp_die('Error, requires admin access.');
	}

	// Verify Nonce
	if(!isset( $nonce ) || !wp_verify_nonce( $nonce, 'omcrm_admin_tools')) {
		wp_die('Error, Please try again.');
	}

	switch( $perform ){

		case 'sync':
			sync();
			break;

		case 'prune':
			prune();
			break;

		case 'product_report':
			product_report();
			break;

		case 'vendor_report':
			vendor_report();
			break;

	}

	header("Location: ".$_SERVER["HTTP_REFERER"]);

	wp_die();

}

function sync(){

	omcrm_maintenance();
	omcrm_reschedule_cron();

}

function prune(){



}

function product_report(){

	global $wpdb;

	$results = $wpdb->get_results(
		$wpdb->prepare( "
		SELECT
		    meta.meta_value as 'crm_id',
		    post.post_title as 'name',
		    CONCAT( '%s', post.post_name ) as 'url'
		FROM hti_openminds.wp_posts as post
		    JOIN hti_openminds.wp_postmeta AS meta ON meta.post_id = post.ID
		WHERE
		      post.post_type = 'product' AND
		      post.post_status = 'publish' AND
		      meta.meta_key = '_crm_product_id'
      ",
			get_site_url() . '/product/'
		),
		ARRAY_N
	);

	//we create the CSV into memory
	$csv = Writer::createFromFileObject(new \SplTempFileObject());

	//we insert the CSV header
	$csv->insertOne(['crm_id', 'name', 'url']);

	// The PDOStatement Object implements the Traversable Interface
	// that's why Writer::insertAll can directly insert
	// the data into the CSV
	$csv->insertAll($results);

	// Because you are providing the filename you don't have to
	// set the HTTP headers Writer::output can
	// directly set them for you
	// The file is downloadable
	$csv->output('products.csv');

}

function vendor_report(){

	global $wpdb;

	$results = $wpdb->get_results(
		$wpdb->prepare( "
		SELECT
		    meta.meta_value as 'crm_id',
		    post.post_title as 'name',
		    CONCAT( '%s', post.post_name ) as 'url'
		FROM hti_openminds.wp_posts as post
		    JOIN hti_openminds.wp_postmeta AS meta ON meta.post_id = post.ID
		WHERE
		      post.post_type = 'vendor' AND
		      post.post_status = 'publish' AND
		      meta.meta_key = '_crm_vendor_id'
      ",
			get_site_url() . '/vendor/'
		),
		ARRAY_N
	);

	//we create the CSV into memory
	$csv = Writer::createFromFileObject(new \SplTempFileObject());

	//we insert the CSV header
	$csv->insertOne(['crm_id', 'name', 'url']);

	// The PDOStatement Object implements the Traversable Interface
	// that's why Writer::insertAll can directly insert
	// the data into the CSV
	$csv->insertAll($results);

	// Because you are providing the filename you don't have to
	// set the HTTP headers Writer::output can
	// directly set them for you
	// The file is downloadable
	$csv->output('vendors.csv');

}

function no_response() {

	wp_die();

}
