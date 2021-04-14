<?php


namespace Palasthotel\WordPress\TrafficIncidents\Data;

use DateTime;
use Palasthotel\WordPress\TrafficIncidents\Model\IncidentEventModel;
use Palasthotel\WordPress\TrafficIncidents\Model\IncidentModel;
use wpdb;

/**
 * @property wpdb wpdb
 * @property string table
 * @property string now
 * @property string tableEvents
 * @property string tableIncidentEvents
 */
class IncidentsDatabase {

	const DATE_TIME_FORMAT = "Y-m-d h:i:s";

	public function __construct() {
		global $wpdb;
		$this->wpdb                = $wpdb;
		$this->table               = $wpdb->prefix . "tom_tom_traffic_incidents";
		$this->tableEvents         = $wpdb->prefix . "tom_tom_traffic_events";
		$this->tableIncidentEvents = $wpdb->prefix . "tom_tom_traffic_incident_events";
		$this->now                 = ( new DateTime() )->format( self::DATE_TIME_FORMAT );
	}

	public function save( IncidentModel $incident ) {
		$start = null;
		if ( $incident->start instanceof DateTime ) {
			$start = new DateTime();
			$start->setTimestamp( $incident->start->getTimestamp() );
			$start = $start->format( self::DATE_TIME_FORMAT );
		}
		$end = null;
		if ( $incident->end instanceof DateTime ) {
			$end = new DateTime();
			$end->setTimestamp( $incident->end->getTimestamp() );
			$end = $end->format( self::DATE_TIME_FORMAT );
		}
		$result = $this->wpdb->replace(
			$this->table,
			[
				"id"                 => $incident->id,
				"traffic_model_id"   => $incident->traffic_model_id,
				"post_id"            => $incident->post_id,
				"category"           => $incident->category,
				"magnitude_of_delay" => $incident->magnitudeOfDelay,
				"ts_modified"        => $this->now,
				"ts_start"           => $start,
				"ts_end"             => $end,
				"intersection_from"  => $incident->intersectionFrom,
				"intersection_to"    => $incident->intersectionTo,
				"delay_in_seconds"   => $incident->delayInSeconds,
				"length_in_meters"   => $incident->lengthInMeters,
				"road_numbers"       => json_encode( $incident->roadNumbers ),
			]
		);

		$relationsSQL   = [];
		$relationsValue = [];
		foreach ( $incident->events as $event ) {
			$relationId = $this->wpdb->get_var(
				$this->wpdb->prepare(
					"SELECT id FROM $this->tableEvents WHERE code = %d AND description = %s",
					[ $event->code, $event->description ]
				)
			);
			if ( $relationId ) {
				$eventRelations[] = "( $relationId, )";
			} else {
				$eventRelations[] = $this->wpdb->insert(
					$this->tableEvents,
					[
						"code"        => $event->code,
						"description" => $event->description,
					]
				);
			}

			$relationsSQL[]   = "(%s, %d)";
			$relationsValue[] = $incident->id;
			$relationsValue[] = intval( $relationId );
		}

		// cleanup old events
		$this->wpdb->query(
			$this->wpdb->prepare(
				"DELETE FROM $this->tableIncidentEvents WHERE incident_id = %s",
				[ $incident->id ]
			)
		);

		// connect events
		$this->wpdb->query(
			$this->wpdb->prepare(
				"INSERT INTO $this->tableIncidentEvents (incident_id, event_id) VALUES " . implode( ",", $relationsSQL ),
				$relationsValue,
			)
		);

	}

	public function getCurrent( $post_id ) {

		$results = $this->wpdb->get_results(
			$this->wpdb->prepare(
				"SELECT *	FROM $this->table as i 
    					LEFT JOIN $this->tableIncidentEvents as ie on (i.id = ie.incident_id)
						LEFT JOIN $this->tableEvents as e on (e.id = ie.event_id)
    					WHERE post_id = %d AND i.ts_modified = (SELECT ts_modified FROM $this->table ORDER BY ts_modified DESC LIMIT 1)
						ORDER BY ie.incident_id DESC, e.code ASC 
    					", $post_id
			)
		);

		/**
		 * @var IncidentModel[] $incidents
		 */
		$incidents = [];
		foreach ( $results as $item ) {
			if ( ! isset( $incidents[ $item->incident_id ] ) ) {

				$incident = IncidentModel::build( $item->incident_id, $item->traffic_model_id, $item->post_id )
				                         ->category( $item->category )
				                         ->magnitudeOfDelay( $item->magnitude_of_delay )
				                         ->start( $item->ts_start )
				                         ->end( $item->ts_end )
				                         ->intersectionFrom( $item->intersection_from )
				                         ->intersectionTo( $item->intersection_to )
				                         ->delayInSeconds( $item->delay_in_seconds )
				                         ->lengthInMeters( $item->length_in_meters )
				                         ->roadNumbers( json_decode( $item->road_numbers ) );

				$incidents[ $item->incident_id ] = $incident;
			} else {
				$incident = $incidents[ $item->incident_id ];
			}

			if($item->code != null && is_string($item->description) ){
				$events   = $incident->events;
				$events[] = new IncidentEventModel( intval($item->code), $item->description );
				$incident->events( $events );
			}
		}

		return array_values($incidents);
	}

	/**
	 * create tables if they do not exist
	 */
	function createTable() {
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		\dbDelta( "CREATE TABLE IF NOT EXISTS $this->tableEvents
			(
			id int(10) unsigned auto_increment NOT NULL,
			code int(4) NOT NULL,
			description varchar (190) NOT NULL,
			primary key (id),
    		unique key (code, description),
    		key (code),
    		key (description)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;" );

		\dbDelta( "CREATE TABLE IF NOT EXISTS $this->table
			(
			id varchar (190) NOT NULL,
			post_id bigint(20) unsigned NOT NULL,
    		traffic_model_id int(11) NOT NULL, 
    		category int(2) NOT NULL,
    		magnitude_of_delay int(2) NOT NULL,
    		ts_modified TIMESTAMP NOT NULL,
    		ts_start TIMESTAMP NULL,
    		ts_end TIMESTAMP NULL,
    		intersection_from varchar(190),
    		intersection_to varchar(190),
    		delay_in_seconds int(10),
    		length_in_meters int(10),
    		road_numbers text NOT NULL, 
			primary key (id),
    		key (traffic_model_id),
    		key (post_id),
			key (category),
    		key (magnitude_of_delay),
    		key (intersection_from),
    		key (intersection_to),
    		key (ts_modified),
    		key (ts_start),
    		key (ts_end),
    		key (delay_in_seconds),
    		key (length_in_meters)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;" );

		\dbDelta( "CREATE TABLE IF NOT EXISTS $this->tableIncidentEvents
			(
			event_id int(10) unsigned NOT NULL,
    		incident_id varchar(190) NOT NULL,
			primary key (event_id, incident_id),
    		key (event_id),
    		key (incident_id),
    		FOREIGN KEY (event_id) REFERENCES $this->tableEvents (id) ON UPDATE CASCADE ON DELETE CASCADE,
    		FOREIGN KEY (incident_id) REFERENCES $this->table (id) ON UPDATE CASCADE ON DELETE CASCADE 
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;" );

	}


}