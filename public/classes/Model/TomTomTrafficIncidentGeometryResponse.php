<?php


namespace Palasthotel\WordPress\TrafficIncidents\Model;

class TomTomTrafficIncidentGeometryResponse {

	/**
	 * @var string
	 */
	var $type;

	/**
	 * @var array
	 */
	var $coorinates;

	public function __construct(string $type, array $coordinates) {
		$this->type = $type;
		$this->coorinates = $coordinates;
	}
}