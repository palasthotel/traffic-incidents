<?php


namespace Palasthotel\WordPress\TrafficIncidents\Model;


use DateTime;
use DateTimeZone;
use Exception;

/**
 * @property string id
 * @property int traffic_model_id
 * @property int post_id
 * @property string incident_id
 * @property IncidentEventModel[] events
 * @property int category
 * @property int magnitudeOfDelay
 * @property null|DateTime start
 * @property null|DateTime end
 * @property string intersectionFrom
 * @property string intersectionTo
 * @property int delayInSeconds
 * @property int lengthInMeters
 * @property DateTime modified
 * @property string[] roadNumbers
 */
class IncidentModel {

	private $timezone;

	private function __construct( $incident_id, $traffic_model_id, $post_id ) {
		$this->id               = $incident_id;
		$this->traffic_model_id = $traffic_model_id;
		$this->post_id          = $post_id;

		$this->timezone = new DateTimeZone( wp_timezone_string() );
		$this->modified = new DateTime();
		$this->modified->setTimezone( $this->timezone );

		$this->events           = [];
		$this->category         = 0;
		$this->magnitudeOfDelay = 0;
		$this->start            = null;
		$this->end              = null;
		$this->intersectionFrom = "";
		$this->intersectionTo   = "";
		$this->delayInSeconds   = 0;
		$this->lengthInMeters   = 0;
		$this->roadNumbers      = [];

	}

	public static function build( $incident_id, $traffic_model_id, $post_id ) {
		return new static( $incident_id, $traffic_model_id, $post_id );
	}

	/**
	 * @param IncidentEventModel[] $values
	 */
	public function events( array $values ): self {
		$this->events = $values;

		return $this;
	}

	public function category( int $value ): self {
		$this->category = $value;

		return $this;
	}

	public function magnitudeOfDelay( int $value ): self {
		$this->magnitudeOfDelay = $value;

		return $this;
	}

	public function start( $datetimeOrUTCString ): self {

		$this->start = $this->getDateTimeFrom( $datetimeOrUTCString );

		return $this;
	}

	public function end( $datetimeOrUTCString ): self {

		$this->end = $this->getDateTimeFrom( $datetimeOrUTCString );

		return $this;
	}

	public function modified( $datetimeOrUTCString ): self {
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

	public function intersectionFrom( string $value ): self {
		$this->intersectionFrom = $value;

		return $this;
	}

	public function intersectionTo( string $value ): self {
		$this->intersectionTo = $value;

		return $this;
	}

	public function delayInSeconds( int $value ): self {
		$this->delayInSeconds = $value;

		return $this;
	}

	public function lengthInMeters( int $value ): self {
		$this->lengthInMeters = $value;

		return $this;
	}

	public function roadNumbers( array $values ): self {
		$this->roadNumbers = $values;

		return $this;
	}


}