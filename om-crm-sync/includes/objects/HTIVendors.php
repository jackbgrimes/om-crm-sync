<?php

namespace OM\CRM\Objects;

use OM\CRM\Services\Bootstrap;

class HTIVendors {

	protected $service;
	protected $version = 1;
	protected $endpoint = 'hti/vendor';

	public function __construct() {

		$this->service = new Bootstrap( $this->version );

	}

	public function request() {

		return $this->service->get( $this->endpoint );
		echo "BILLY";

	}

	public function single( $vendor_id ){}

	public function singleByPostID( $post_id ){}
	

}