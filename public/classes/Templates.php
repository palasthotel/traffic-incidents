<?php


namespace Palasthotel\WordPress\TrafficIncidents;

use Palasthotel\WordPress\TrafficIncidents\Model\IncidentModel;
use Palasthotel\WordPress\TrafficIncidents\Model\IncidentQueryArgs;

class Templates extends _Component {

	var $sub_dirs = null;

	public function onCreate() {
		parent::onCreate();
		add_action( Plugin::ACTION_THE_CONTENT, [ $this, "render_incident_query" ] );
	}

	public function render_incident_query( IncidentQueryArgs $args ) {
		include $this->get_template_path( Plugin::TEMPLATE_INCIDENTS );
	}

	/**
	 * Look for existing template path
	 * @return string|false
	 */
	function get_template_path( $template ) {

		// theme or child theme
		if ( $overridden_template = locate_template( $this->get_template_dirs( $template ) ) ) {
			return $overridden_template;
		}

		// parent theme
		foreach ( $this->get_template_dirs( $template ) as $path ) {
			if ( is_file( get_template_directory() . "/$path" ) ) {
				return get_template_directory() . "/$path";
			}
		}

		// other plugins
		$paths = apply_filters( Plugin::FILTER_ADD_TEMPLATE_PATHS, array() );
		// add default templates at last position
		$paths[] = $this->plugin->path . 'templates';
		// find templates
		foreach ( $paths as $path ) {
			if ( is_file( "$path/$template" ) ) {
				return "$path/$template";
			}
		}

		// if nothing found...
		return false;
	}

	/**
	 * get array of possible template files in theme
	 *
	 * @param $template
	 *
	 * @return array
	 */
	function get_template_dirs( $template ) {
		$dirs = array(
			Plugin::THEME_FOLDER . "/" . $template,
		);
		foreach ( $this->get_sub_dirs() as $sub ) {
			$dirs[] = $sub . '/' . $template;
		}

		return $dirs;
	}

	/**
	 * paths for locate_template
	 * @return array
	 */
	function get_sub_dirs() {
		if ( $this->sub_dirs == null ) {
			$this->sub_dirs = array();
			$dirs           = array_filter( glob( get_template_directory() . '/' . Plugin::THEME_FOLDER . '/*' ), 'is_dir' );
			foreach ( $dirs as $dir ) {
				$this->sub_dirs[] = str_replace( get_template_directory() . '/', '', $dir );
			}
		}

		return $this->sub_dirs;
	}


}