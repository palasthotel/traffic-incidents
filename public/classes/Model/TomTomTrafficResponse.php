<?php

namespace Palasthotel\WordPress\TrafficIncidents\Model;

/**
 * @property TomTomTrafficIncidentResponse[] incidents
 * @property int id
 */
class TomTomTrafficResponse {
	public function __construct( $id, $incidents ) {
		$this->id        = $id;
		$this->incidents = $incidents;
	}
}