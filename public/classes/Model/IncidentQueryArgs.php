<?php


namespace Palasthotel\WordPress\TrafficIncidents\Model;


/**
 * @property int|string post_id
 * @property int magnitudeOfDelay
 * @property int category
 * @property int eventCode
 */
class IncidentQueryArgs {

	private function __construct( $post_id ) {
		$this->post_id = $post_id;
	}

	public static function build( $post_id ) {
		return new static( $post_id );
	}

	public function byCategory($value){
		$this->category = $value;

		return $this;
	}

	public function byMagnitudeOfDelay($value){
		$this->magnitudeOfDelay = $value;

		return $this;
	}

	public function byEventCode($value){
		$this->eventCode = $value;

		return $this;
	}
}