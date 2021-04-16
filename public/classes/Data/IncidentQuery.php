<?php


namespace Palasthotel\WordPress\TrafficIncidents\Data;


use Palasthotel\WordPress\TrafficIncidents\Model\IncidentModel;
use Palasthotel\WordPress\TrafficIncidents\Model\IncidentQueryArgs;
use Palasthotel\WordPress\TrafficIncidents\Plugin;

/**
 * @property IncidentModel[] incidents
 */
class IncidentQuery {

	private $pointer = 0;

	public function __construct( IncidentQueryArgs $args ) {
		$this->incidents = Plugin::instance()->repo->queryIncidents( $args );
	}

	public function haveIncidents(): bool {
		return $this->pointer < count( $this->incidents );
	}

	public function count(): int {
		return count($this->incidents);
	}

	/**
	 * @return IncidentModel|null
	 */
	public function nextIncident() {
		if ( isset( $this->incidents[ $this->pointer ] ) ) {
			return $this->incidents[ $this->pointer ++ ];
		}

		return null;
	}
}