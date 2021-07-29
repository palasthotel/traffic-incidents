<?php

namespace Palasthotel\WordPress\TrafficIncidents\Utils;

use Palasthotel\WordPress\TrafficIncidents\Model\IncidentLocation;

class Mapper {

	public static function rowToLocation($row): IncidentLocation{
		$location = new IncidentLocation($row->lat, $row->lng, $row->id);
		$location->region($row->region);
		$location->place($row->place);
		$location->locality($row->locality);
		$location->country($row->country);
		$location->postcode($row->postcode);
		$location->address($row->address);
		return $location;
	}

	public static function locationToRow(IncidentLocation $location): array {
		return [
			"lat" => $location->lat,
			"lng" => $location->lng,
			"region" => $location->region,
			"locality" => $location->locality,
			"place" => $location->place,
			"address" => $location->address,
			"postcode" => $location->postcode,
			"country" => $location->country
		];
	}

}