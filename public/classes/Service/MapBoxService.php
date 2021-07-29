<?php


namespace Palasthotel\WordPress\TrafficIncidents\Service;


use Palasthotel\WordPress\TrafficIncidents\Model\MapBoxLocationResponse;

class MapBoxService {
	/**
	 * @var string
	 */
	private $accessToken;

	const BASE_URL = "https://api.mapbox.com/geocoding/v5/mapbox.places/";

	public function __construct(string $accessToken) {
		$this->accessToken = $accessToken;
	}

	/**
	 * @param $lat
	 * @param $lng
	 *
	 * @return MapBoxLocationResponse|\WP_Error
	 */
	public function getLocation($lat, $lng){
		$url = self::BASE_URL."$lng,$lat.json?access_token={$this->accessToken}&language=de";
		$response = wp_remote_get( $url );

		if($response instanceof \WP_Error){
			return $response;
		}

		$json = json_decode( $response['body'] );

		if(!isset($json->features) || !is_array($json->features)){
			return new \WP_Error(500,"Could not find features array in response");
		}
		$features = $json->features;

		$responseObj = new MapBoxLocationResponse();
		$responseObj->locality = $this->getFeatureOrNull($features, "locality");
		$responseObj->region = $this->getFeatureOrNull($features, "region");
		$responseObj->place = $this->getFeatureOrNull($features, "place");
		$responseObj->address = $this->getFeatureOrNull($features, "address");
		$responseObj->postcode = $this->getFeatureOrNull($features, "postcode");
		$responseObj->country = $this->getFeatureOrNull($features, "country");

		return $responseObj;
	}

	private function getFeatureOrNull($featuresArr, $feature){
		$found = array_values(array_filter($featuresArr, function($item) use ( $feature ) {
			return isset($item->place_type) &&
			       is_array($item->place_type) &&
			       !empty($item->place_type) &&
			       $item->place_type[0] === $feature;
		}));
		return !empty($found) ? $found[0]->place_name : null;
	}


}