<?php


namespace Palasthotel\WordPress\TrafficIncidents;


use Palasthotel\WordPress\TrafficIncidents\Data\IncidentsDatabase;
use Palasthotel\WordPress\TrafficIncidents\Model\BoundingBox;
use Palasthotel\WordPress\TrafficIncidents\Model\IncidentEventModel;
use Palasthotel\WordPress\TrafficIncidents\Model\IncidentLocation;
use Palasthotel\WordPress\TrafficIncidents\Model\IncidentModel;
use Palasthotel\WordPress\TrafficIncidents\Model\IncidentQueryArgs;
use Palasthotel\WordPress\TrafficIncidents\Model\TomTomIncidentsRequestArgs;
use Palasthotel\WordPress\TrafficIncidents\Model\TomTomTrafficResponse;
use Palasthotel\WordPress\TrafficIncidents\Service\MapBoxService;
use Palasthotel\WordPress\TrafficIncidents\Service\TomTomService;

/**
 * @property TomTomService service
 * @property IncidentsDatabase database
 * @property MapBoxService mapBoxService
 */
class Repository extends _Component {

	public function onCreate() {
		parent::onCreate();
		$this->service  = new TomTomService(
			Settings::getTomTomApiKey()
		);
		$this->mapBoxService = new MapBoxService(
			Settings::getMapBoxApiKey()
		);
		$this->database = new IncidentsDatabase();
	}

	/**
	 * @param IncidentQueryArgs $args
	 *
	 * @return IncidentModel[]
	 */
	public function queryIncidents( IncidentQueryArgs $args ) {
		do_action( Plugin::ACTION_QUERY_INCIDENTS_ARGS, $args);
		$result = $this->database->query( $args );
		return array_values(apply_filters(Plugin::FILTER_QUERY_INCIDENTS_RESULT, $result, $args));
	}

	public function getPosts( $queryArgs = [] ) {
		return get_posts(
			array_merge(
				[
					"post_type" => $this->plugin->postTypeTraffic->getName(),
				],
				$queryArgs
			)
		);
	}

	public function fetchIncidents( $post_id ) {
		$bb = $this->plugin->postTypeTraffic->getBoundingBox( $post_id );
		if ( ! ( $bb instanceof BoundingBox ) ) {
			return;
		}

		$args           = TomTomIncidentsRequestArgs::build( $bb );
		$response = $this->service->getIncidents( $args );

		if(!($response instanceof TomTomTrafficResponse)) return;

		$count = count($response->incidents);
		$this->plugin->log->add("Got $count incidents for traffic model $response->id");

		$entities = array_map( function ( $item ) use ( $response, $post_id ) {
			return IncidentModel::build( $item->id, $response->id, $post_id )
			                    ->events( array_map(function($event){
			                    	return new IncidentEventModel($event->code, $event->description);
			                    }, $item->events) )
			                    ->category( $item->category )
			                    ->magnitudeOfDelay( $item->delayMagnitude )
			                    ->start( $item->startTime )
			                    ->end( $item->endTime )
			                    ->intersectionFrom( $item->intersectionFrom )
			                    ->intersectionTo( $item->intersectionTo )
			                    ->delayInSeconds( $item->delayInSeconds )
			                    ->lengthInMeters( $item->lengthInMeters )
								->roadNumbers($item->roadNumbers)
								->locations(array_map(function($item){

									$lat = $item[1]."";
									$lng = $item[0]."";

									$location = $this->database->getLocation($lat, $lng);

									if($location instanceof IncidentLocation){
										return $location;
									}

									$location = new IncidentLocation($lat, $lng);
									$id = $this->database->saveLocation($location);
									$location->id($id);
									return $location;
								}, $item->geometry->coorinates));
		}, $response->incidents );

		update_post_meta( $post_id, Plugin::POST_META_LAST_TRAFFIC_MODEL_ID, $response->id );

		foreach ( $entities as $entity ) {
			$this->database->save( $entity );
			do_action(Plugin::ACTION_AFTER_INCIDENT_SAVED, $entity);
		}
	}

	public function fetchLocation(IncidentLocation $location){
		$response = $this->mapBoxService->getLocation($location->lat, $location->lng);
		if($response instanceof \WP_Error){
			return $response;
		}
		if($response->isEmpty()){
			return false;
		}

		$location->locality($response->locality);
		$location->place($response->place);
		$location->region($response->region);
		$location->address($response->address);
		$location->postcode($response->postcode);
		$location->country($response->country);

		return $this->database->updateLocation($location);
	}

}