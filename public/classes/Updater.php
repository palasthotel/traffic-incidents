<?php


namespace Palasthotel\WordPress\TrafficIncidents;


use Palasthotel\WordPress\TrafficIncidents\Components\Component;
use Palasthotel\WordPress\TrafficIncidents\Data\Update;

class Updater extends Component {

	public function onCreate() {
		parent::onCreate();
		add_action('init', function(){
			(new Update())->checkUpdates();
		});
	}

}