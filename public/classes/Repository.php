<?php


namespace Palasthotel\WordPress\TrafficIncidents;


use Palasthotel\WordPress\TrafficIncidents\Data\IncidentsDatabase;
use Palasthotel\WordPress\TrafficIncidents\Model\BoundingBox;
use Palasthotel\WordPress\TrafficIncidents\Model\IncidentEventModel;
use Palasthotel\WordPress\TrafficIncidents\Model\IncidentModel;
use Palasthotel\WordPress\TrafficIncidents\Model\IncidentQueryArgs;
use Palasthotel\WordPress\TrafficIncidents\Model\TomTomIncidentsRequestArgs;
use Palasthotel\WordPress\TrafficIncidents\Model\TomTomTrafficResponse;
use Palasthotel\WordPress\TrafficIncidents\Service\TomTomService;

/**
 * @property TomTomService service
 * @property IncidentsDatabase database
 */
class Repository extends _Component {

	public function onCreate() {
		parent::onCreate();
		$this->service  = new TomTomService(
			Settings::getTomTomApiKey()
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
		return apply_filters(Plugin::FILTER_QUERY_INCIDENTS_RESULT, $result, $args);
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

		$trafficModelId = intval( get_post_meta( $post_id, Plugin::POST_META_LAST_TRAFFIC_MODEL_ID, true ) );
		$args           = TomTomIncidentsRequestArgs::build( $bb );
		if ( $trafficModelId > 0 ) {
			$args->trafficModelId( $trafficModelId );
		}
		$response = $this->service->getIncidents( $args );

		if(!($response instanceof TomTomTrafficResponse)) return;

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
								->roadNumbers($item->roadNumbers);
		}, $response->incidents );

		update_post_meta( $post_id, Plugin::POST_META_LAST_TRAFFIC_MODEL_ID, $response->id );

		foreach ( $entities as $entity ) {
			$this->database->save( $entity );
		}
	}

}