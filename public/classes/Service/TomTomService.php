<?php


namespace Palasthotel\WordPress\TrafficIncidents\Service;


use Palasthotel\WordPress\TrafficIncidents\Model\TomTomIncidentsRequestArgs;
use Palasthotel\WordPress\TrafficIncidents\Model\TomTomTrafficResponse;
use Palasthotel\WordPress\TrafficIncidents\Model\TomTomTrafficIncidentResponse;

/**
 * @property string baseUrl
 * @property string apiKey
 */
class TomTomService {

	public function __construct($apiKey) {
		$this->apiKey = $apiKey;
	}

	private function getUrl(TomTomIncidentsRequestArgs $args): string {
		$boundingBox = $args->boundingBox;
		$zoom = $args->zoom;
		$modelId = $args->trafficModelId;
		$format = $args->format;
		$version = $args->version;
		$style = $args->style;
		$projection = $args->projection;
		$lang = $args->language;
		$url = "https://api.tomtom.com/traffic/services/$version/incidentDetails/$style/$boundingBox/$zoom/$modelId/$format";
		return "$url?projection=$projection&language=$lang&key=$this->apiKey";
	}

	public function getIncidents(TomTomIncidentsRequestArgs $args){
		$url = $this->getUrl($args);
		$response = wp_remote_get($url);
		if(is_wp_error($response)){
			error_log($response->get_error_message());
			return [];
		}
		$body = wp_remote_retrieve_body($response);
		$json =  json_decode($body);
		if(!isset($json->tm) || !isset($json->tm->poi) || !is_array($json->tm->poi) || !isset($json->tm->{"@id"})){
			error_log("Unknown response: ".$body);
			return [];
		}

		$id = $json->tm->{"@id"};

		$items = array_map(function($json){
			return TomTomTrafficIncidentResponse::from($json);
		}, $json->tm->poi);

		return new TomTomTrafficResponse($id, $items);
	}

}