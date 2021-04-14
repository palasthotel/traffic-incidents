<?php


namespace Palasthotel\WordPress\TrafficIncidents\Model;


class IncidentEventModel {
	/**
	 * @var int
	 */
	var $code;

	/**
	 * @var string
	 */
	var $description;

	public function __construct(int $code, string $description) {
		$this->code = $code;
		$this->description = $description;
	}

	public function __toString() {
		return $this->description;
	}


}