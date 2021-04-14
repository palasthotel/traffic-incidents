<?php


namespace Palasthotel\WordPress\TrafficIncidents\Model;


use DateTime;
use Exception;

/**
 * @property string $id
 * @property int $category
 * @property int $delayMagnitude
 * @property null|DateTime $startTime
 * @property null|DateTime $endTime
 * @property string $intersectionFrom
 * @property string $intersectionTo
 * @property int $lengthInMeters
 * @property int $delayInSeconds
 * @property TomTomTrafficIncidentEventResponse[] $events
 * @property string[] $roadNumbers
 */
class TomTomTrafficIncidentResponse {

	// https://developer.tomtom.com/traffic-api/traffic-api-documentation-traffic-incidents/incident-details#response-data
	public static function from( $json ) {
		$incident                 = new TomTomTrafficIncidentResponse();
		$incident->id             = $json->id;
		$incident->category       = $json->iconCategory;
		$incident->delayMagnitude = $json->magnitudeOfDelay;

		$incident->events = [];
		foreach ( $json->events as $event ) {
			$incident->events[] = new TomTomTrafficIncidentEventResponse( $event->code, $event->description );
		}
		$incident->roadNumbers = $json->roadNumbers;

		$incident->startTime = $json->startTime;
		if ( isset( $json->startTime ) ) {
			try {
				// date in UTC
				$incident->startTime = new DateTime( $json->startTime );
			} catch ( Exception $e ) {
			}
		}
		$incident->endTime = null;
		if ( isset( $json->endTime ) ) {
			try {
				// date in UTC
				$incident->endTime = new DateTime( $json->endTime );
			} catch ( Exception $e ) {
			}
		}
		$incident->intersectionFrom = $json->from;
		$incident->intersectionTo   = $json->to;
		$incident->lengthInMeters   = intval( $json->length );
		$incident->delayInSeconds   = $json->delay;

		return $incident;
	}
}