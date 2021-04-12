<?php


namespace Palasthotel\WordPress\TrafficIncidents\Data;

use DateTime;
use Palasthotel\WordPress\TrafficIncidents\Model\IncidentEntity;
use wpdb;

/**
 * @property wpdb wpdb
 * @property string table
 */
class IncidentsDatabase {

	public function __construct() {
		global $wpdb;
		$this->wpdb  = $wpdb;
		$this->table = $wpdb->prefix . "tom_tom_traffic_incidents";
	}

	public function save( IncidentEntity $incident ) {
		$start = $incident->start instanceof DateTime ?
			$incident->start->format("Y-m-d h:i:s") : null;
		$end = $incident->end instanceof DateTime ?
			$incident->end->format("Y-b-d h:i:s") : null;
		$this->wpdb->replace(
			$this->table,
			[
				"incident_id"        => $incident->id,
				"post_id"            => $incident->post_id,
				"description"        => $incident->description,
				"category"           => $incident->category,
				"magnitude_of_delay" => $incident->magnitudeOfDelay,
				"ts_start"           => $start,
				"ts_end"             => $end,
				"intersection_from"  => $incident->intersectionFrom,
				"intersection_to"    => $incident->intersectionTo,
				"delay_in_seconds"   => $incident->delayInSeconds,
				"length_in_meters"   => $incident->lengthInMeters,
			]
		);
	}

	public function getAll( $post_id ) {
		$results = $this->wpdb->get_results(
			$this->wpdb->prepare(
				"SELECT * FROM $this->table WHERE post_id = %d", $post_id
			)
		);

		return array_map( function ( $item ) use ( $post_id ) {
			return IncidentEntity::build( $item->incident_id, $item->post_id )
			                     ->description( $item->description )
			                     ->category( $item->category )
			                     ->magnitudeOfDelay( $item->magnitude_of_delay )
			                     ->start( $item->ts_start )
			                     ->end( $item->ts_end )
			                     ->intersectionFrom( $item->intersection_from )
			                     ->intersectionTo( $item->intersection_to )
			                     ->delayInSeconds( $item->delay_in_seconds )
			                     ->lengthInMeteres( $item->length_in_meters );
		}, $results );
	}

	/**
	 * create tables if they do not exist
	 */
	function createTable() {
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		\dbDelta( "CREATE TABLE IF NOT EXISTS $this->table
			(
			incident_id varchar (190) NOT NULL,
			post_id bigint(20) unsigned NOT NULL,
    		description varchar(190) NOT NULL,  
    		category int(2) NOT NULL,
    		magnitude_of_delay int(2) NOT NULL,
    		ts_start TIMESTAMP NULL,
    		ts_end TIMESTAMP NULL,
    		intersection_from varchar(190),
    		intersection_to varchar(190),
    		delay_in_seconds int(10),
    		length_in_meters int(10),
			primary key (incident_id),
    		key (post_id),
			key (category),
    		key (magnitude_of_delay),
    		key (description),
    		key (intersection_from),
    		key (intersection_to),
    		key (ts_start),
    		key (ts_end),
    		key (delay_in_seconds),
    		key (length_in_meters)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;" );
	}


}