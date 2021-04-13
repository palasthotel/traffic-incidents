<?php


namespace Palasthotel\WordPress\TrafficIncidents;


class Settings extends _Component {
	public function onCreate() {
		parent::onCreate();
		add_action( 'admin_init', function () {

			add_settings_section(
				'traffic-incidents-settings',
				'Traffic Incidents',
				function () {
					echo "<span id='" . Plugin::DOMAIN . "'></span>";
				},
				'general'
			);

			register_setting(
				'general',
				Plugin::OPTION_TOM_TOM_API_KEY
			);
			add_settings_field(
				Plugin::OPTION_TOM_TOM_API_KEY,
				__( 'TomTom Api Key', Plugin::DOMAIN ),
				array( $this, 'render_tom_tom_api_key' ),
				'general',
				'traffic-incidents-settings'
			);
		} );
	}

	public static function getTomTomApiKey() {
		return get_option( Plugin::OPTION_TOM_TOM_API_KEY, "" );
	}

	/**
	 * render the setting field
	 */
	public function render_tom_tom_api_key() {
		$val = Settings::getTomTomApiKey();
		echo "<input type='text' value='$val' name='" . Plugin::OPTION_TOM_TOM_API_KEY . "' />";
	}
}