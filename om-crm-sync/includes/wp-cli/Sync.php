<?php

namespace OM\CRM\WPCLI;

if( !defined( 'ABSPATH' ) ) {
	exit;
}

use \OM\CRM\Automations\SyncVendors;
use \OM\CRM\Automations\SyncProducts;
use \WP_CLI;
use \WP_CLI_COMMAND;

WP_CLI::add_command('crm_sync', __NAMESPACE__ . '\Sync');

class Sync extends WP_CLI_COMMAND {

	/**
	 *  Syncs products & vendors from the CRM
	 *
	 *  [--flush]
	 *  : determines whether to sync all or only recently updated variants
	 */
	public function all( $args, $assoc_args ){

		WP_CLI::log( 'Performing Step 1 of 2 | Sync Vendors' );
		$this->syncVendors();

		WP_CLI::log( 'Performing Step 2 of 2 | Sync Products' );
		$this->syncProducts( $args, $assoc_args );

		WP_CLI::success('Sync Complete.');

	}

	/**
	 *  Syncs only products from the CRM
	 *
	 *  [--flush]
	 *  : determines whether to sync all or only recently updated variants
	 */
	public function syncProducts( $args, $assoc_args ) {

		$recent = !isset( $assoc_args['flush'] );

		( new SyncProducts )->bulkSync( $recent );
		
		WP_CLI::success( 'Finished Syncing Products.');

	}

	/**
	 *  Syncs only vendors from the CRM
	 */
	public function syncVendors() {

		( new SyncVendors )->bulkSync();

		WP_CLI::success( 'Finished Syncing Vendors.');

	}

}