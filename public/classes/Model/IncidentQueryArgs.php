<?php


namespace Palasthotel\WordPress\TrafficIncidents\Model;


/**
 * @property int|string post_id
 */
class IncidentQueryArgs {

	private function __construct( $post_id ) {
		$this->post_id = $post_id;
	}

	public static function build( $post_id ) {
		return new static( $post_id );
	}

}