<?php

namespace OM\CRM\Objects;

use OM\CRM\Services\Bootstrap;

class HTIProducts {

	protected $service;
	protected $version = 1;
	protected $endpoint = 'hti/product';

	public function __construct() {

		$this->service = new Bootstrap( $this->version );

	}

	public function requestRecent() {

		return $this->service->get( $this->endpoint . "?updated_after=".date('Y-m-d', strtotime( '-1 week' ) ) );

	}

	public function requestAll() {

		return $this->service->get( $this->endpoint );

	}

	public function single( $product_id ){}

	public function singleByPostID( $post_id ){}

}