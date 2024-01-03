<?php

namespace OM\CRM\Services;

class Bootstrap {

	protected $apiRoot = 'https://bootstrap.openminds.com/api';
	protected $version = 1;
	protected $username = OMCRM_USERNAME;
	protected $password = OMCRM_PASSWORD;

	/**
	 * OpenMinds constructor.
	 *
	 * @param null $version
	 */
	public function __construct( $version = null ) {

		if( !is_null( $version ) ){

			$this->version = intval( $version );

		}

	}

	/**
	 * @param $endpoint
	 *
	 * @return string
	 */
	protected function restURL( $endpoint ){

		return sprintf(
			'%s/v%d/%s',
			$this->apiRoot,
			$this->version,
			$endpoint
		);

	}

	protected function authCreds() {

		return sprintf( 'Basic %s',
			base64_encode("$this->username:$this->password")
		);
		


	}

	protected function assembleArgs( $args = [] ){

		$defaultArgs = [

			'headers' => [

				'Authorization' => $this->authCreds()

			]

		];

		return array_merge( $defaultArgs, $args );

	}

	public function get( $endpoint, $args = [] ){

		try {

			$response = wp_remote_request( $this->restURL( $endpoint ), $this->assembleArgs( $args ) );

			if( !is_array( $response ) ){

				return false;

			}

			return json_decode( $response['body'] );

		} catch( Exception $e ){

			return false;

		}

	}

}