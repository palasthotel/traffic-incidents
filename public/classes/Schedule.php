<?php


namespace Palasthotel\WordPress\TrafficIncidents;


use Palasthotel\WordPress\TrafficIncidents\Model\IncidentQueryArgs;

class Schedule extends _Component {

	private const TRANSIENT_KEY = "_traffic_incidents_is_fetching";

	public function onCreate() {
		parent::onCreate();
		add_action( 'admin_init', [ $this, 'schedule' ] );
		add_action( Plugin::SCHEDULE_FETCH_TRAFFIC_UPDATE, [ $this, 'run' ] );
	}

	private function setRunning( bool $is ) {
		if ( $is ) {
			set_transient( self::TRANSIENT_KEY, "1", MINUTE_IN_SECONDS * 10 );
		} else {
			delete_transient( self::TRANSIENT_KEY );
		}

	}

	private function isRunning() {
		return get_transient( self::TRANSIENT_KEY ) === "1";
	}

	public function schedule() {
		if ( ! wp_next_scheduled( Plugin::SCHEDULE_FETCH_TRAFFIC_UPDATE ) ) {
			wp_schedule_event( time(), 'hourly', Plugin::SCHEDULE_FETCH_TRAFFIC_UPDATE );
		}
	}

	public function run() {

		if ( $this->isRunning() ) {
			return;
		}

		$this->setRunning( true );

		$posts = $this->plugin->repo->getPosts();

		foreach ( $posts as $post ) {
			$this->plugin->log->add( "Fetch for post $post->ID" );
			$this->plugin->repo->fetchIncidents( $post->ID );

			$incidents = $this->plugin->repo->queryIncidents( IncidentQueryArgs::build( $post->ID ) );
			$counter   = 0;
			foreach ( $incidents as $incident ) {
				$location = $incident->getStartLocation();
				if ( null === $location || ! $location->isEmpty() ) {
					continue;
				}
				$counter ++;
				$this->plugin->repo->fetchLocation( $location );

			}
			$count = count( $incidents );
			$this->plugin->log->add( "Fetched locations for $counter of $count incidents" );
		}

		$this->setRunning( false );

	}

}