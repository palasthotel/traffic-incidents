<?php


namespace Palasthotel\WordPress\TrafficIncidents;


use Palasthotel\WordPress\TrafficIncidents\Model\IncidentQueryArgs;
use WP_REST_Request;

class REST extends _Component {

	const NAMESPACE = "traffic-incidents/v1";

	public function onCreate() {
		parent::onCreate();
		add_action( 'rest_api_init', [ $this, 'init' ] );
	}

	public function init() {

		register_rest_route(
			static::NAMESPACE,
			'/incidents/(?P<id>\d+)',
			array(
				'methods'             => "GET",
				'callback'            => array( $this, 'query' ),
				'permission_callback' => '__return_true',
				'args'                => [
					"id" => array(
						'required'          => true,
						'validate_callback' => function ( $param, $request, $key ) {
							$isNumeric = is_numeric( $param );
							$isPostType = get_post_type( $param ) === $this->plugin->postTypeTraffic->getName();
							return $isNumeric && $isPostType;
						},
					),
				]
			)
		);
	}

	public function query( WP_REST_Request $request ) {
		$post_id = $request->get_param("id");
		return $this->plugin->repo->queryIncidents(IncidentQueryArgs::build($post_id));
	}

}