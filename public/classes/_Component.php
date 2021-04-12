<?php


namespace Palasthotel\WordPress\TrafficIncidents;

/**
 * @property Plugin plugin
 */
abstract class _Component {

	public function __construct(Plugin $plugin) {
		$this->plugin = $plugin;
		$this->onCreate();
	}

	public function onCreate(){

	}
}
