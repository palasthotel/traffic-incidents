<?php


namespace Palasthotel\WordPress\TrafficIncidents;


use Palasthotel\WordPress\TrafficIncidents\Data\IncidentsDatabase;
use Palasthotel\WordPress\TrafficIncidents\Model\BoundingBox;
use Palasthotel\WordPress\TrafficIncidents\Model\IncidentEntity;
use Palasthotel\WordPress\TrafficIncidents\Model\TomTomIncidentsRequestArgs;
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

	public function getIncidents( $post_id ) {
		return $this->database->getAll( $post_id );
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

		$entities = array_map( function ( $item ) use ( $response, $post_id ) {
			return IncidentEntity::build( $item->id, $response->id, $post_id )
			                     ->description( $item->description )
			                     ->category( $item->category )
			                     ->magnitudeOfDelay( $item->delayMagnitude )
			                     ->start( $item->startDate )
			                     ->end( $item->endDate )
			                     ->intersectionFrom( $item->intersectionFrom )
			                     ->intersectionTo( $item->intersectionTo )
			                     ->delayInSeconds( $item->delayInSeconds )
			                     ->lengthInMeteres( $item->lengthInMeters );
		}, $response->incidents );

		update_post_meta( $post_id, Plugin::POST_META_LAST_TRAFFIC_MODEL_ID, $response->id );

		foreach ( $entities as $entity ) {
			$this->database->save( $entity );
		}
	}

}