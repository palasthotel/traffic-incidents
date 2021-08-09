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
		return defined('TOM_TOM_API_KEY') ? TOM_TOM_API_KEY : get_option( Plugin::OPTION_TOM_TOM_API_KEY, "" );
	}

	/**
	 * render the setting field
	 */
	public function render_tom_tom_api_key() {
		$val = Settings::getTomTomApiKey();
		$isConstant = defined('TOM_TOM_API_KEY');
		$readonly = $isConstant ? "readonly='readonly'" : "";
		echo "<input type='text' $readonly value='$val' name='" . Plugin::OPTION_TOM_TOM_API_KEY . "' class='regular-text' />";
		if($isConstant){
			$text = __("Api key is defined as a constant in code.", Plugin::DOMAIN);
			echo "<p class='description'>$text</p>";
		}
	}


	public static function getMapBoxApiKey(){
		return defined('MAPBOX_API_TOKEN') ? MAPBOX_API_TOKEN : "";
	}
}