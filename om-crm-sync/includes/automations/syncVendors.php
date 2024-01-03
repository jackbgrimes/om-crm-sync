<?php

namespace OM\CRM\Automations;

use OM\CRM\Objects\HTIVendors;

class SyncVendors {

	protected $crm_vendors = [];
	protected $vendor_legend = [];

	public function __construct() {

		$this->buildLegend();

	}

	protected function queryCRMVendors() {

		$response = ( new HTIVendors )->request();

		if( !isset( $response->vendors ) ){ return $response; }

		$this->crm_vendors = $response->vendors;

		//echo $response;

	}

	protected function isNew( $crm_id ){

		return !$this->alreadyExists( $crm_id );

	}

	protected function alreadyExists( $crm_id ){

		return isset( $this->vendor_legend[ $crm_id ] );

	}

	protected function buildLegend() {

		global $wpdb;

		$meta_rows = $wpdb->get_results( "SELECT post_id, meta_value FROM $wpdb->postmeta WHERE meta_key = '_crm_vendor_id'");

		foreach( $meta_rows as $meta_row ){

			$vendor_id = intval( sanitize_text_field( $meta_row->meta_value ) );

			$this->vendor_legend[$vendor_id] = intval( $meta_row->post_id );

		}

	}

	public function bulkSync() {

		$this->queryCRMVendors();

		foreach( $this->crm_vendors as $crm_vendor ){

			$this->sync( $crm_vendor );

		}

	}

	protected function sync( $vendor ){

		$post_args = [
			'post_type' => 'vendor',
			'post_title' => $vendor->name,
			'post_status' => 'publish',
			'meta_input' => [
				'_crm_vendor_id' => $vendor->id,
				'city' => $vendor->city,
				'state' => $vendor->state
			]
		];

		if( $this->alreadyExists( $vendor->id ) ){

			$post_args['ID'] = $this->vendor_legend[ $vendor->id ];

		}

		wp_insert_post( $post_args );

	}

	public function bulkPrune() {
		$this->queryCRMVendors();

		foreach( $this->crm_vendors as $crm_vendor ){

			$this->prune( $crm_vendor );

		}

	}

	protected function prune( $vendor ){

		$post_args = [
			'post_status' => 'trash'
		];

		if( !$this->alreadyExists( $vendor->id ) ){

		}

	}

}