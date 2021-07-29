<?php


namespace Palasthotel\WordPress\TrafficIncidents\Model;


/**
 * @property  string lat
 * @property  string lng
 * @property null|string locality
 * @property null|string place
 * @property null|string region
 * @property null|string address
 * @property null|string postcode
 * @property null|string country
 * @property int|null id
 */
class IncidentLocation {

	public function __construct($lat, $lng, $id = null) {
		$this->id = $id;
		$this->lat = $lat;
		$this->lng = $lng;
		$this->locality = null;
		$this->place = null;
		$this->region = null;
		$this->address = null;
		$this->postcode = null;
		$this->country = null;
	}

	public function id($value): self{
		$this->id = $value;
		return $this;
	}

	public function locality($value){
		$this->locality = $value;

		return $this;
	}

	public function place($value){
		$this->place = $value;

		return $this;
	}

	public function region($value){
		$this->region = $value;

		return $this;
	}

	public function address($value){
		$this->address = $value;
		return $this;
	}

	public function postcode($value){
		$this->postcode = $value;

		return $this;
	}

	public function country($value){
		$this->country = $value;

		return $this;
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