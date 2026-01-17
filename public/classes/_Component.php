<?php


namespace Palasthotel\WordPress\TrafficIncidents;

abstract class _Component {

    public Plugin $plugin;

	public function __construct(Plugin $plugin) {
		$this->plugin = $plugin;
		$this->onCreate();
	}

	public function onCreate(){

	}
}
