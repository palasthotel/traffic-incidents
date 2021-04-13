<?php


namespace Palasthotel\WordPress\TrafficIncidents\Model;


use DateTime;
use DateTimeZone;
use Exception;

/**
 * @property  string id
 * @property int traffic_model_id
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
 * @property DateTime modified
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

	private $timezone;


	private function __construct( $incident_id, $traffic_model_id, $post_id ) {
		$this->id               = $incident_id;
		$this->traffic_model_id = $traffic_model_id;
		$this->post_id          = $post_id;

		$this->timezone = new DateTimeZone( wp_timezone_string() );
		$this->modified = new DateTime();
		$this->modified->setTimezone( $this->timezone );
	}

	public static function build( $incident_id, $traffic_model_id, $post_id ) {
		return new static( $incident_id, $traffic_model_id, $post_id );
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

	public function start( $datetimeOrUTCString ) {

		$this->start = $this->getDateTimeFrom( $datetimeOrUTCString );

		return $this;
	}

	public function end( $datetimeOrUTCString ) {

		$this->end = $this->getDateTimeFrom( $datetimeOrUTCString );

		return $this;
	}

	public function modified( $datetimeOrUTCString ) {
		$this->modified = $this->getDateTimeFrom( $datetimeOrUTCString );

		return $this;
	}

	private function getDateTimeFrom( $datetimeOrUTCString ) {
		if ( $datetimeOrUTCString instanceof DateTime ) {
			$d = new DateTime();
			$d->setTimestamp( $datetimeOrUTCString->getTimestamp() );
			$d->setTimezone( $this->timezone );

			return $d;
		} else if ( is_string( $datetimeOrUTCString ) ) {
			try {
				$d = new DateTime( $datetimeOrUTCString );
				$d->setTimezone( $this->timezone );

				return $d;
			} catch ( Exception $e ) {
			}
		}

		return null;
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