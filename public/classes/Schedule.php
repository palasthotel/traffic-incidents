<?php


namespace Palasthotel\WordPress\TrafficIncidents;


class Schedule extends _Component {

	public function onCreate() {
		parent::onCreate();
		add_action('admin_init', [$this, 'schedule']);
		add_action(Plugin::SCHEDULE_FETCH_TRAFFIC_UPDATE, [$this, 'run']);
	}

	public function schedule(){
		if(!wp_next_scheduled(Plugin::SCHEDULE_FETCH_TRAFFIC_UPDATE)){
			wp_schedule_event(time(), 'hourly', Plugin::SCHEDULE_FETCH_TRAFFIC_UPDATE);
		}
	}

	public function run(){
		$posts = $this->plugin->repo->getPosts();
		foreach ($posts as $post){
			$this->plugin->repo->fetchIncidents($post->ID);
		}
	}

}