<?php

/**
 * Plugin Name: Traffic Incidents
 * Description:
 * Version: 0.0.1
 * Author: Palasthotel <rezeption@palasthotel.de> (Edward Bock)
 * Author URI: https://palasthotel.de
 * Text Domain: traffic-incidents
 * Domain Path: /languages
 */

namespace Palasthotel\WordPress\TrafficIncidents;

// If this file is called directly, abort.
use Palasthotel\WordPress\TrafficIncidents\Data\PostTypeTraffic;

if ( ! defined( 'ABSPATH' ) ) {
	die;
}


/**
 * @property string path
 * @property string url
 * @property Assets assets
 * @property PostTypeTraffic postTypeTraffic
 * @property Repository repo
 * @property Settings settings
 * @property Schedule schedule
 * @property Templates templates
 * @property REST rest
 */
class Plugin {

	const DOMAIN = "traffic-incidents";

	const OPTION_TOM_TOM_API_KEY = "_tom_tom_api_key";

	const POST_META_LAST_TRAFFIC_MODEL_ID = "_tom_tom_last_traffic_model_id";
	const POST_META_BOUNDING_BOX = "_traffic_incidents_bounding_box";

	const FILTER_CPT_TRAFFIC_SLUG = "traffic_incidents_cpt_traffic_slug";
	const FILTER_CPT_TRAFFIC_ARGS = "traffic_incidents_cpt_traffic_args";

	const FILTER_ADD_TEMPLATE_PATHS = "traffic_incidents_add_template_paths";

	const FILTER_QUERY_INCIDENTS_RESULT = "traffic_incidents_query_result";

	const ACTION_QUERY_INCIDENTS_ARGS = "traffic_incidents_query_args";
	const ACTION_THE_CONTENT = "traffic_incidents_the_content";

	const THEME_FOLDER = "plugin-parts";
	const TEMPLATE_INCIDENTS = "traffic-incidents.php";

	const SCHEDULE_FETCH_TRAFFIC_UPDATE = "traffic_incidents_fetch_update";

	const HANDLE_API_JS = "traffic_incidents_api_js";

	private function __construct() {

		/**
		 * load translations
		 */
		load_plugin_textdomain(
			Plugin::DOMAIN,
			false,
			dirname( plugin_basename( __FILE__ ) ) . '/languages'
		);

		/**
		 * Base paths
		 */
		$this->path = plugin_dir_path( __FILE__ );
		$this->url  = plugin_dir_url( __FILE__ );

		require_once dirname( __FILE__ ) . "/vendor/autoload.php";

		$this->assets          = new Assets( $this );
		$this->postTypeTraffic = new PostTypeTraffic( $this );
		$this->repo            = new Repository( $this );
		$this->settings        = new Settings( $this );
		$this->schedule        = new Schedule( $this );
		$this->templates       = new Templates( $this );
		$this->rest            = new REST( $this );

		// for regeneration of permalinks after plugin activation/deactivation
		register_activation_hook( __FILE__, array( $this, "activation" ) );
		register_deactivation_hook( __FILE__, array( $this, "deactivation" ) );

		if ( WP_DEBUG ) {
			$this->repo->database->createTable();
		}

	}

	/**
	 * on plugin activation
	 */
	function activation() {
		$this->repo->database->createTable();
		$this->schedule->schedule();
	}

	/**
	 * on plugin deactivation
	 */
	function deactivation() {
	}

	private static $instance;

	public static function instance(): Plugin {
		if ( null == static::$instance ) {
			static::$instance = new static();
		}

		return static::$instance;
	}
}

Plugin::instance();