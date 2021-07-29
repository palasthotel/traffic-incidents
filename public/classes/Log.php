<?php


namespace Palasthotel\WordPress\TrafficIncidents;


class Log extends _Component {
	/**
	 * @var null|\CronLogger\Log
	 */
	private $log;

	public function onCreate() {
		parent::onCreate();
		add_action("cron_logger_init", function(\CronLogger\Plugin $logger){
			$this->log = $logger->log;
		});
	}

	public function add($message){
		if(class_exists('\CronLogger\Log') && $this->log instanceof \CronLogger\Log){
			$this->log->addInfo($message);
		} else {
			error_log($message);
		}
	}
}