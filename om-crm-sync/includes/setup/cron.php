<?php

namespace OM\CRM\Setup;

use \OM\CRM\Automations\SyncVendors;
use \OM\CRM\Automations\SyncProducts;

add_action('omcrm_setup', __NAMESPACE__ . '\omcrm_schedule_activation');
add_action('omcrm_teardown', __NAMESPACE__ . '\omcrm_schedule_deactivation');
add_action('omcrm_daily', __NAMESPACE__ . '\omcrm_maintenance' );

// On activation, schedule a daily process of renewing the oauth credentials
function omcrm_schedule_activation() {
	if (! wp_next_scheduled ( 'omcrm_daily' )) {
		wp_schedule_event( time(), 'daily', 'omcrm_daily');
	}
}

function omcrm_reschedule_cron() {
	omcrm_schedule_deactivation();
	omcrm_schedule_activation();
}

// On deactivation, remove scheduled maintenance events
function omcrm_schedule_deactivation() {
	wp_clear_scheduled_hook( 'omcrm_daily' );
}

function omcrm_maintenance() {

	( new SyncVendors )->bulkSync();

	( new SyncProducts )->bulkSync();

}
