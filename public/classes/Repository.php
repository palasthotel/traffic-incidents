<?php


namespace Palasthotel\WordPress\TrafficIncidents;


use Palasthotel\WordPress\TrafficIncidents\Data\IncidentsDatabase;
use Palasthotel\WordPress\TrafficIncidents\Model\BoundingBox;
use Palasthotel\WordPress\TrafficIncidents\Model\IncidentEntity;
use Palasthotel\WordPress\TrafficIncidents\Model\TomTomIncidentRequestArgs;
use Palasthotel\WordPress\TrafficIncidents\Service\TomTomTrafficIncidents;

/**
 * @property TomTomTrafficIncidents service
 * @property IncidentsDatabase database
 */
class Repository extends _Component {

	public function onCreate() {
		parent::onCreate();
		$this->service  = new TomTomTrafficIncidents(
			Settings::getTomTomApiKey()
		);
		$this->database = new IncidentsDatabase();
	}

	public function getIncidents( $post_id ) {
		return $this->database->getAll( $post_id );
	}

	public function fetchIncidents( $post_id ) {
		$bb = $this->plugin->postTypeTraffic->getBoundingBox( $post_id );
		if ( ! ( $bb instanceof BoundingBox ) ) {
			return [];
		}

		$response = $this->service->getIncidents( TomTomIncidentRequestArgs::build( $bb ) );

		$entities = array_map( function ( $item ) use ( $post_id ) {
			return IncidentEntity::build( $item->id, $post_id )
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

		foreach ( $entities as $entity ) {
			$this->database->save( $entity );
		}
	}

}