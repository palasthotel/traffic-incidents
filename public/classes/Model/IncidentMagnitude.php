<?php


namespace Palasthotel\WordPress\TrafficIncidents\Model;


class IncidentMagnitude {
	const UNKNOWN = 0;
	const MINOR = 1;
	const MODERATE = 2;
	const MAJOR = 3;
	const UNDEFINED = 4; // road closed or other indefinite delays
}