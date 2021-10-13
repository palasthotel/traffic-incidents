<?php

use Palasthotel\WordPress\TrafficIncidents\Model\IncidentQueryArgs;
use Palasthotel\WordPress\TrafficIncidents\Plugin;

function traffic_incidents_plugin(){
	return Plugin::instance();
}

function traffic_incidents_get_incidents_count($post_id){
	return count(traffic_incidents_plugin()->repo->queryIncidents(
		IncidentQueryArgs::build($post_id)
	));
}