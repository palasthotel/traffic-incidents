<?php


namespace Palasthotel\WordPress\TrafficIncidents\Model;

class TomTomTrafficIncidentEventResponse {

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
}