<?php


namespace Palasthotel\WordPress\TrafficIncidents\Data;


use Palasthotel\WordPress\TrafficIncidents\Plugin;

class Update extends \Palasthotel\WordPress\TrafficIncidents\Components\Update {

	function getVersion(): int {
		return 1;
	}

	private function getVersionOptionKey(){
		return Plugin::DOMAIN."_db_version";
	}

	function getCurrentVersion(): int {
		return intval(get_option($this->getVersionOptionKey(), 0));
	}

	function setCurrentVersion( int $version ) {
		update_option($this->getVersionOptionKey(), $version);
	}

	function update_1(){
		Plugin::instance()->repo->database->createTable();
	}

}