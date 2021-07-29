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
 * @property TomTomTrafficIncidentGeometryResponse $geometry
 */
class TomTomTrafficIncidentResponse {

	// https://developer.tomtom.com/traffic-api/traffic-api-documentation-traffic-incidents/incident-details#response-data
	public static function from( $json ) {
		$properties = $json->properties;
		$geometry = $json->geometry;
		$incident                 = new TomTomTrafficIncidentResponse();
		$incident->id             = $properties->id;
		$incident->category       = $properties->iconCategory;
		$incident->delayMagnitude = $properties->magnitudeOfDelay;

		$incident->geometry = new TomTomTrafficIncidentGeometryResponse(
			$geometry->type,
			$geometry->coordinates,
		);

		$incident->events = [];
		foreach ( $properties->events as $event ) {
			$incident->events[] = new TomTomTrafficIncidentEventResponse( $event->code, $event->description );
		}
		$incident->roadNumbers = $properties->roadNumbers;

		$incident->startTime = $properties->startTime;
		if ( isset( $properties->startTime ) ) {
			try {
				// date in UTC
				$incident->startTime = new DateTime( $properties->startTime );
			} catch ( Exception $e ) {
				error_log($e->getMessage());
			}
		}
		$incident->endTime = null;
		if ( isset( $properties->endTime ) ) {
			try {
				// date in UTC
				$incident->endTime = new DateTime( $properties->endTime );
			} catch ( Exception $e ) {
				error_log($e->getMessage());
			}
		}
		$incident->intersectionFrom = $properties->from;
		$incident->intersectionTo   = $properties->to;
		$incident->lengthInMeters   = intval( $properties->length );
		$incident->delayInSeconds   = $properties->delay;

		return $incident;
	}
}