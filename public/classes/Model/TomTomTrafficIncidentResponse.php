<?php


namespace Palasthotel\WordPress\TrafficIncidents\Model;


/**
 * @property  string $id
 * @property  int $category
 * @property  int $delayMagnitude
 * @property  string $description
 * @property string $startDate
 * @property string $endDate
 * @property string $intersectionFrom
 * @property string $intersectionTo
 * @property int $lengthInMeters
 * @property int $delayInSeconds
 */
class TomTomTrafficIncidentResponse {

	const IC_UNKNOWN = 0;
	const IC_ACCIDENT = 1;
	const IC_FOG = 2;
	const IC_DANGEROUS_CONDITIONS = 3;
	const IC_RAIN = 4;
	const IC_ICE = 5;
	const IC_JAM = 6;
	const IC_LANE_CLOSED = 7;
	const IC_ROAD_CLOSED = 8;
	const IC_ROAD_WORKS = 9;
	const IC_WIND = 10;
	const IC_FLOODING = 11;
	const IC_DETOUR = 12;
	const IC_CLUSTER = 13;
	const IC_BROKEN_DOWN_VEHICLE = 14;

	// https://developer.tomtom.com/traffic-api/traffic-api-documentation-traffic-incidents/incident-details#response-data
	public static function from( $json ) {
		$incident                   = new TomTomTrafficIncidentResponse();
		$incident->id               = $json->id;
		$incident->category         = $json->ic;
		$incident->delayMagnitude   = $json->ty;
		$incident->description      = $json->d;
		$incident->startDate        = $json->sd;
		$incident->endDate          = $json->ed ?? null;
		$incident->intersectionFrom = $json->f;
		$incident->intersectionTo   = $json->t;
		$incident->lengthInMeters   = $json->l;
		$incident->delayInSeconds   = $json->dl ?? null;

		return $incident;
	}
}