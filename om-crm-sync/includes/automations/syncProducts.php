<?php

namespace OM\CRM\Automations;

use OM\CRM\Objects\HTIProducts;

class SyncProducts {

	protected $crm_products = [];
	protected $product_legend = [];
	protected $vendor_legend = [];
	protected $techCategoryLegend = [];
	protected $marketLegend = [];

	public function __construct() {

		$this->buildPostLegend();
		$this->buildTechCategoryLegend();
		$this->buildMarketLegend();

	}

	protected function queryCRMProducts( $recent = true ) {

		$response = ( new HTIProducts )->requestRecent();

		//var_dump($response);

		if( !isset( $response->products ) ){ return; }

		$this->crm_products = $response->products;

	}

	protected function isNew( $crm_id ){

		return !$this->alreadyExists( $crm_id );

	}

	protected function alreadyExists( $crm_id ){

		return isset( $this->product_legend[ intval( $crm_id ) ] );

	}

	protected function post_id( $crm_id ){

		if( $this->isNew( $crm_id ) ){ return 0; }

		return $this->product_legend[ intval( $crm_id ) ];

	}

	protected function buildPostLegend() {

		global $wpdb;

		$meta_rows = $wpdb->get_results(
			"SELECT post_id, meta_value 
					FROM $wpdb->postmeta 
					WHERE meta_key = '_crm_product_id'"
		);

		foreach( $meta_rows as $meta_row ){

			$this->updatePostLegend(
				sanitize_text_field( $meta_row->post_id ),
				sanitize_text_field( $meta_row->meta_value )
			);

		}

	}

	protected function updatePostLegend( $post_id, $product_id ){

		$this->product_legend[ intval( $product_id ) ] = intval( $post_id );

	}

	protected function buildTechCategoryLegend(){

		$tech_categories = get_terms( [ 'taxonomy' => 'tech_category', 'hide_empty' => false ] );

		foreach( $tech_categories as $category ){

			$this->techCategoryLegend[$category->term_id] = $category->slug;

		}

	}

	protected function buildMarketLegend() {

		$markets = get_terms( [ 'taxonomy' => 'market', 'hide_empty' => false ] );

		foreach( $markets as $category ){

			$this->marketLegend[$category->term_id] = $category->slug;

		}

	}

	public function bulkSync( $recent = true ) {

		$this->queryCRMProducts( $recent );

		foreach( $this->crm_products as $crm_product ){

			$this->sync( $crm_product );

		}

	}

	protected function flushTerms( $post_id ){

		wp_delete_object_term_relationships( $post_id, ['market', 'tech_category' ] );

	}

	protected function addTechCategories( $product ) {

		$post_id = $this->post_id( $product->id );

		$matches = array_intersect( $this->techCategoryLegend, $product->taxonomies->tech_category );

		wp_set_post_terms( $post_id, array_keys( $matches ), 'tech_category' );

	}

	protected function addMarkets( $product ) {

		$post_id = $this->post_id( $product->id );

		$matches = array_intersect( $this->marketLegend, $product->taxonomies->market );

		wp_set_post_terms( $post_id, array_keys( $matches ), 'market' );

	}

	protected function sync( $product ){

		$post_args = [
			'post_type' => 'product',
			'post_title' => $product->name,
			'post_status' => $product->is_active? 'publish' : 'trash',
			'meta_input' => [
				'_crm_product_id' => $product->id,
				'owner_id' => $product->vendor_id,
				'is_claimed' => $product->is_claimed? 1 : 0,
				'contact_name' => $product->contact_person,
			]
		];

		if( $this->alreadyExists( $product->id ) ){

			$new_post_id = $this->product_legend[ $product->id ];

			$post_args['ID'] = $new_post_id;

			wp_update_post( $post_args );

		} else {

			$post_args['meta_input']['is_paid'] = 0;

			$new_post_id = wp_insert_post( $post_args );

		}

		$this->updatePostLegend( $new_post_id, $product->id );

		$this->flushTerms( $new_post_id );

		$this->addTechCategories( $product );

		$this->addMarkets( $product );

	}

}