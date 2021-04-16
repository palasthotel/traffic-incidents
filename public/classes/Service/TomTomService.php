<?php


namespace Palasthotel\WordPress\TrafficIncidents\Service;


use Palasthotel\WordPress\TrafficIncidents\Model\TomTomIncidentsRequestArgs;
use Palasthotel\WordPress\TrafficIncidents\Model\TomTomTrafficIncidentResponse;
use Palasthotel\WordPress\TrafficIncidents\Model\TomTomTrafficResponse;

/**
 * @property string baseUrl
 * @property string apiKey
 */
class TomTomService {

	public function __construct( $apiKey ) {
		$this->apiKey = $apiKey;
	}

	private function getUrl( TomTomIncidentsRequestArgs $args ): string {
		$boundingBox = urlencode( $args->boundingBox );
		$lang        = $args->language;
		$fields      = urlencode( $args->fields );
		$url         = "https://api.tomtom.com/traffic/services/5/incidentDetails";
		$query       = "bbox=$boundingBox&fields=$fields&language=$lang&key=$this->apiKey";

		return "$url?$query";
	}

	public function getIncidents( TomTomIncidentsRequestArgs $args ) {

		if(empty($this->apiKey)){
			error_log("TomTom API-Key missing.");

			return false;
		}

		$url      = $this->getUrl( $args );
		$response = wp_remote_get( $url );
		if ( is_wp_error( $response ) ) {
			error_log( $response->get_error_message() );

			return false;
		}
		$body = wp_remote_retrieve_body( $response );
		$json = json_decode( $body );
		if ( ! isset( $json->incidents ) || ! is_array( $json->incidents ) ) {
			error_log( "Unknown response: " . $body );

			return false;
		}

		$id = wp_remote_retrieve_header( $response, "TrafficModelID" );

		$items = array_map( function ( $incidentJson ) {
			return TomTomTrafficIncidentResponse::from( $incidentJson->properties );
		}, $json->incidents );

		return new TomTomTrafficResponse( $id, $items );
	}

}