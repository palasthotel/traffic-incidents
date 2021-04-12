<?php


namespace Palasthotel\WordPress\TrafficIncidents\Model;


use DateTime;

/**
 * @property  string id
 * @property  int post_id
 * @property  string incident_id
 * @property string description
 * @property int category
 * @property int magnitudeOfDelay
 * @property null|DateTime start
 * @property null|DateTime end
 * @property string intersectionFrom
 * @property string intersectionTo
 * @property int delayInSeconds
 * @property int lengthInMeters
 */
class IncidentEntity {

	var $description = "";
	var $category = 0;
	var $magnitudeOfDelay = 0;
	var $start = null;
	var $end = null;
	var $intersectionFrom = "";
	var $intersectionTo = "";
	var $delayInSeconds = 0;
	var $lengthInMeters = 0;

	public function __construct( $incident_id, $post_id ) {
		$this->id      = $incident_id;
		$this->post_id = $post_id;
	}

	public static function build( $incident_id, $post_id ) {
		return new static( $incident_id, $post_id );
	}

	public function description( string $value ) {
		$this->description = $value;

		return $this;
	}

	public function category( $value ) {
		$this->category = $value;

		return $this;
	}

	public function magnitudeOfDelay( $value ) {
		$this->magnitudeOfDelay = $value;

		return $this;
	}

	public function start( $datetime ) {
		try {
			$this->start = new DateTime( $datetime );
		} catch ( \Exception $e ) {
			$this->start = null;
		}

		return $this;
	}

	public function end( $datetime ) {
		try {
			$this->end = new DateTime( $datetime );
		} catch ( \Exception $e ) {
			$this->end = null;
		}

		return $this;
	}

	public function intersectionFrom( $value ) {
		$this->intersectionFrom = $value;

		return $this;
	}

	public function intersectionTo( $value ) {
		$this->intersectionTo = $value;

		return $this;
	}

	public function delayInSeconds( $value ) {
		$this->delayInSeconds = $value;

		return $this;
	}

	public function lengthInMeteres( $value ) {
		$this->lengthInMeters = $value;

		return $this;
	}


}