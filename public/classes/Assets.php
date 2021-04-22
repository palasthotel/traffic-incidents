<?php


namespace Palasthotel\WordPress\TrafficIncidents;


class Assets extends _Component {

	public function onCreate() {
		parent::onCreate();
		add_action('init', [$this, 'init']);
	}

	public function init(){
		$this->registerAPI();
	}

	public function registerAPI(){
		$info = include $this->plugin->path . "/dist/api.asset.php";
		wp_register_script(
			Plugin::HANDLE_API_JS,
			$this->plugin->url . "/dist/api.js",
			$info["dependencies"],
			$info["version"]
		);
		wp_localize_script(
			Plugin::HANDLE_API_JS,
			"TrafficIncidents",
			[
				"rest_namespace" => "/".REST::NAMESPACE,
			]
		);
	}

	public function enqueueAPI(){
		wp_enqueue_script(Plugin::HANDLE_API_JS);
	}

}