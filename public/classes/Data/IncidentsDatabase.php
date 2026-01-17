<?php


namespace Palasthotel\WordPress\TrafficIncidents\Data;

use DateTime;
use Palasthotel\WordPress\TrafficIncidents\Model\IncidentEventModel;
use Palasthotel\WordPress\TrafficIncidents\Model\IncidentLocation;
use Palasthotel\WordPress\TrafficIncidents\Model\IncidentModel;
use Palasthotel\WordPress\TrafficIncidents\Model\IncidentQueryArgs;
use Palasthotel\WordPress\TrafficIncidents\Utils\Mapper;

class IncidentsDatabase {

    /** @var wpdb */
    public $wpdb;
    public string $table;
    public string $tableEvents;
    public string $tableIncidentEvents;
    public string $tableLocations;
    public string $tableIncidentLocations;

	const DATE_TIME_FORMAT = "Y-m-d h:i:s";

	public function __construct() {
		global $wpdb;
		$this->wpdb                   = $wpdb;
		$this->table                  = $wpdb->prefix . "tom_tom_traffic_incidents";
		$this->tableEvents            = $wpdb->prefix . "tom_tom_traffic_events";
		$this->tableIncidentEvents    = $wpdb->prefix . "tom_tom_traffic_incident_events";
		$this->tableLocations         = $wpdb->prefix . "tom_tom_locations";
		$this->tableIncidentLocations = $wpdb->prefix . "tom_tom_incident_locations";
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
			if ( ! is_numeric( $relationId ) || intval( $relationId ) <= 0 ) {
				$relationId = $this->wpdb->insert(
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

		foreach ($incident->locations as $location){
			$this->wpdb->replace(
				$this->tableIncidentLocations,
				[
					"location_id" => $location->id,
					"incident_id" => $incident->id,
				]
			);
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

	/**
	 * @param IncidentLocation $location
	 *
	 * @return bool|int
	 */
	public function saveLocation( IncidentLocation $location ){
		$success = $this->wpdb->insert(
			$this->tableLocations,
			Mapper::locationToRow($location),
		);
		return $success ? $this->wpdb->insert_id : false;
	}

	public function updateLocation( IncidentLocation $location ){
		$row = Mapper::locationToRow($location);
		unset($row["id"]);
		unset($row["lat"]);
		unset($row["lng"]);
		return $this->wpdb->update(
			$this->tableLocations,
			$row,
			[
				"id" => $location->id,
			]
		);
	}

	/**
	 * @param string $lat
	 * @param string $lng
	 *
	 * @return IncidentLocation|null
	 */
	public function getLocation(string $lat, string $lng){
		$row = $this->wpdb->get_row(
			$this->wpdb->prepare(
				"SELECT * FROM $this->tableLocations WHERE lat = %s AND lng = %s", $lat, $lng
			)
		);
		return $row !== null ? Mapper::rowToLocation($row) : null;
	}

	public function query( IncidentQueryArgs $args ) {

		$conditions = [];
		if ( isset( $args->magnitudeOfDelay ) ) {
			$conditions[] = $this->wpdb->prepare(
				"magnitude_of_delay = %d",
				$args->magnitudeOfDelay
			);
		}
		if ( isset( $args->category ) ) {
			$conditions[] = $this->wpdb->prepare(
				"category = %d",
				$args->category
			);
		}
		if ( isset( $args->eventCode ) ) {
			$conditions[] = $this->wpdb->prepare(
				"i.id IN (SELECT incident_id FROM $this->tableIncidentEvents WHERE event_id IN (SELECT id FROM $this->tableEvents WHERE code = %d))",
				$args->eventCode
			);
		}

		$where = "";
		if ( count( $conditions ) > 0 ) {
			$where = " AND " . implode( " AND ", $conditions );
		}

		$results = $this->wpdb->get_results(
			$this->wpdb->prepare(
				"SELECT *	FROM $this->table as i 
    					LEFT JOIN $this->tableIncidentEvents as ie on (i.id = ie.incident_id)
						LEFT JOIN $this->tableEvents as e on (e.id = ie.event_id)
    					WHERE 
    					      post_id = %d AND i.ts_modified > (SELECT DATE_SUB(max(ts_modified), INTERVAL 20 SECOND) FROM $this->table)
							  $where
						ORDER BY ie.incident_id DESC, e.code ASC 
    					", $args->post_id
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
				                         ->modified( $item->ts_modified )
				                         ->intersectionFrom( $item->intersection_from )
				                         ->intersectionTo( $item->intersection_to )
				                         ->delayInSeconds( $item->delay_in_seconds )
				                         ->lengthInMeters( $item->length_in_meters )
				                         ->roadNumbers( json_decode( $item->road_numbers ) );

				$incidents[ $item->incident_id ] = $incident;
			} else {
				$incident = $incidents[ $item->incident_id ];
			}

			if ( $item->code != null && is_string( $item->description ) ) {
				$events   = $incident->events;
				$events[] = new IncidentEventModel( intval( $item->code ), $item->description );
				$incident->events( $events );
			}
		}

		$incidentIds = array_keys($incidents);
		$ids = implode(', ', array_map(function($id){
			return "'$id'";
		}, $incidentIds));

		$results = $this->wpdb->get_results(
			"SELECT * FROM $this->tableLocations as loc
				LEFT JOIN $this->tableIncidentLocations as iloc ON (loc.id = iloc.location_id)
				WHERE iloc.incident_id IN ( {$ids} ) ORDER BY iloc.id ASC
				"
		);

		foreach ($results as $row){
			if(!isset($incidents[$row->incident_id])) {
				continue;
			}
			if($incidents[$row->incident_id] instanceof IncidentModel){
				$row->id = $row->location_id;
				$incidents[$row->incident_id]->locations[] = Mapper::rowToLocation($row);
			}
		}

		return array_values( $incidents );
	}

	/**
	 * create tables if they do not exist
	 */
	function createTable() {
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		\dbDelta( "CREATE TABLE IF NOT EXISTS $this->tableEvents
			(
			id bigint(20) unsigned auto_increment NOT NULL,
			code int(4) NOT NULL,
			description varchar (190) NOT NULL,
			primary key (id),
    		unique key (code, description),
    		key (code),
    		key (description)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;" );

		\dbDelta( "CREATE TABLE IF NOT EXISTS $this->tableLocations
			(
			id bigint(20) unsigned auto_increment NOT NULL,
			lat varchar (25) NOT NULL,
			lng varchar (25) NOT NULL,
    		address varchar (190) DEFAULT NULL,
    		postcode varchar (190) DEFAULT NULL,
    		locality varchar (190) DEFAULT NULL,
    		place varchar(190) DEFAULT NULL,
    		region varchar(190) DEFAULT NULL,
    		country varchar (190) DEFAULT NULL,
    		primary key (id),
			unique key (lat, lng)
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
			event_id bigint(20) unsigned NOT NULL,
    		incident_id varchar(190) NOT NULL,
			primary key (event_id, incident_id),
    		key (event_id),
    		key (incident_id),
    		FOREIGN KEY (event_id) REFERENCES $this->tableEvents (id) ON UPDATE CASCADE ON DELETE CASCADE,
    		FOREIGN KEY (incident_id) REFERENCES $this->table (id) ON UPDATE CASCADE ON DELETE CASCADE 
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;" );

		\dbDelta( "CREATE TABLE IF NOT EXISTS $this->tableIncidentLocations
			(
		    id bigint(20) unsigned auto_increment NOT NULL,
		    location_id  bigint(20) unsigned NOT NULL,
    		incident_id varchar(190) NOT NULL,
			primary key (id),
    		key (location_id),
    		key (incident_id),
    		unique key (location_id, incident_id),    
    		FOREIGN KEY (location_id) REFERENCES $this->tableLocations (id) ON UPDATE CASCADE ON DELETE CASCADE,
    		FOREIGN KEY (incident_id) REFERENCES $this->table (id) ON UPDATE CASCADE ON DELETE CASCADE 
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;" );

	}


}
