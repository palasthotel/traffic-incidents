<?php


namespace Palasthotel\WordPress\TrafficIncidents\Model;

/**
 * @property null|string locality
 * @property null|string place
 * @property null|string region
 * @property null|string address
 * @property null|string postcode
 * @property null|string country
 */
class MapBoxLocationResponse {
	public function __construct() {
		$this->locality = null;
		$this->place = null;
		$this->region = null;
		$this->address = null;
		$this->postcode = null;
		$this->country = null;
	}

	function isEmpty(){
		return !$this->locality &&
		       !$this->place &&
		       !$this->region &&
		       !$this->address &&
		       !$this->postcode &&
		       !$this->country;
	}
}