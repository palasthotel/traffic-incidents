<?php


namespace Palasthotel\WordPress\TrafficIncidents\Data;


use Palasthotel\WordPress\TrafficIncidents\_Component;
use Palasthotel\WordPress\TrafficIncidents\Model\BoundingBox;
use Palasthotel\WordPress\TrafficIncidents\Model\IncidentMagnitude;
use Palasthotel\WordPress\TrafficIncidents\Model\IncidentModel;
use Palasthotel\WordPress\TrafficIncidents\Model\IncidentQueryArgs;
use Palasthotel\WordPress\TrafficIncidents\Plugin;

class PostTypeTraffic extends _Component {

	const DEFAULT_NAME = "traffic";

	public function onCreate() {
		parent::onCreate();
		add_action( 'init', function () {
			register_post_type( $this->getName(), $this->getArgs() );
		} );

		add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
		add_action( 'save_post', array( $this, 'save' ) );
		add_filter( 'the_content', array( $this, 'the_content' ) );
	}

	public function the_content( $content ) {

		if ( get_post_type() !== $this->getName() ) {
			return $content;
		}

		$args = IncidentQueryArgs::build( get_the_ID() );
		ob_start();
		do_action( Plugin::ACTION_THE_CONTENT, $args );
		$content .= ob_get_contents();
		ob_end_clean();

		return $content;
	}

	/**
	 * @param string|int $post_id
	 *
	 * @return BoundingBox|null
	 */
	public function getBoundingBox( $post_id ) {
		$bb = get_post_meta( $post_id, Plugin::POST_META_BOUNDING_BOX, true );

		return BoundingBox::parse( $bb );
	}

	/**
	 * @param int|string $post_id
	 * @param BoundingBox|null $bb
	 */
	public function setBoundingBox( $post_id, $bb ) {
		if ( $bb instanceof BoundingBox ) {
			update_post_meta( $post_id, Plugin::POST_META_BOUNDING_BOX, "$bb" );
		} else {
			delete_post_meta( $post_id, Plugin::POST_META_BOUNDING_BOX );
		}
	}

	/**
	 *  register meta box
	 */
	public function add_meta_box() {
		add_meta_box(
			Plugin::DOMAIN . '-meta-box',
			__( 'Traffic incidents area', Plugin::DOMAIN ),
			array( $this, 'render' ),
			$this->getName(),
			"normal",
			"high"
		);
	}

	public function render( $post ) {
		echo "<label>";
		echo __( 'Bounding box', Plugin::DOMAIN ) . "<br/>";
		$name = Plugin::POST_META_BOUNDING_BOX;
		$bb   = $this->getBoundingBox( $post->ID );
		echo "<input type='text' name='$name' value='$bb' style='width: 100%' />";
		echo "</label>";

		$incidents = $this->plugin->repo->queryIncidents( IncidentQueryArgs::build( $post->ID ) );


		echo "<h2>Incidents</h2>";

		echo "<ul>";
		foreach ( $incidents as $incident ) {

			echo "<li style='margin-bottom: 20px;'>";
			echo "<div>";
			echo implode( ",", $incident->events );
			echo "</div>";
			if ( $incident->start ) {
				echo "<div>";
				printf(
					__( "Started: %s", Plugin::DOMAIN ),
					$incident->start->format( "H:i:s" )
				);
				if ( $incident->end ) {
					printf(
						__( " -> will approximatly end: %s", Plugin::DOMAIN ),
						$incident->end->format( "H:i:s" )
					);
				}
				echo "</div>";
			}
			echo "<div>";
			printf(
				__( "From <b>%s</b> to <b>%s</b>", Plugin::DOMAIN ),
				$incident->intersectionFrom,
				$incident->intersectionTo
			);
			echo "</div>";

			if(!empty($incident->getGoodLocations())){
				echo "<ul>";
				foreach ($incident->getGoodLocations() as $location){
					echo "<li>🛣 $location->address </br>Lat: $location->lat, Lng: $location->lng</li>";
				}
				echo "</ul>";
			}

			printf(
				__( "<i>Last update: %s</i>", Plugin::DOMAIN ),
				$incident->modified->format( "Y-m-d H:i" )
			);

			echo "</li>";
		}
		echo "</ul>";
	}

	public function save( $post_id ) {

		if ( ! isset( $_POST[ Plugin::POST_META_BOUNDING_BOX ] ) ) {
			return;
		}

		$this->setBoundingBox(
			$post_id,
			BoundingBox::parse(
				sanitize_text_field( $_POST[ Plugin::POST_META_BOUNDING_BOX ] )
			)
		);
		$this->plugin->repo->fetchIncidents( $post_id );
	}


	public function getName() {
		return apply_filters( Plugin::FILTER_CPT_TRAFFIC_SLUG, static::DEFAULT_NAME );
	}

	public function getArgs() {
		$labels = array(
			'name'                  => __( 'Traffic area', Plugin::DOMAIN ),
			'singular_name'         => __( 'Traffic area', Plugin::DOMAIN ),
			'menu_name'             => __( 'Traffic area', Plugin::DOMAIN ),
			'name_admin_bar'        => __( 'Traffic area', Plugin::DOMAIN ),
			'archives'              => __( 'Traffic area Archives', Plugin::DOMAIN ),
			'attributes'            => __( 'Traffic area Attributes', Plugin::DOMAIN ),
			'parent_item_colon'     => __( 'Parent Item:', Plugin::DOMAIN ),
			'all_items'             => __( 'All Traffic areas', Plugin::DOMAIN ),
			'add_new_item'          => __( 'Add New area', Plugin::DOMAIN ),
			'add_new'               => __( 'New', Plugin::DOMAIN ),
			'new_item'              => __( 'New Area', Plugin::DOMAIN ),
			'edit_item'             => __( 'Edit Traffic area', Plugin::DOMAIN ),
			'update_item'           => __( 'Update Traffic area', Plugin::DOMAIN ),
			'view_item'             => __( 'View Traffic area', Plugin::DOMAIN ),
			'view_items'            => __( 'View Traffic area', Plugin::DOMAIN ),
			'search_items'          => __( 'Search Traffic area', Plugin::DOMAIN ),
			'not_found'             => __( 'Not found', Plugin::DOMAIN ),
			'not_found_in_trash'    => __( 'Not found in Trash', Plugin::DOMAIN ),
			'featured_image'        => __( 'Featured Image', Plugin::DOMAIN ),
			'set_featured_image'    => __( 'Set featured image', Plugin::DOMAIN ),
			'remove_featured_image' => __( 'Remove featured image', Plugin::DOMAIN ),
			'use_featured_image'    => __( 'Use as featured image', Plugin::DOMAIN ),
			'insert_into_item'      => __( 'Insert into item', Plugin::DOMAIN ),
			'uploaded_to_this_item' => __( 'Uploaded to this item', Plugin::DOMAIN ),
			'items_list'            => __( 'Items list', Plugin::DOMAIN ),
			'items_list_navigation' => __( 'Items list navigation', Plugin::DOMAIN ),
			'filter_items_list'     => __( 'Filter items list', Plugin::DOMAIN ),
		);
		$args   = array(
			'label'               => __( 'Traffic area', Plugin::DOMAIN ),
			'description'         => __( 'Traffic incident areas', Plugin::DOMAIN ),
			'labels'              => $labels,
			'supports'            => array(
				'title',
				'revisions',
			),
			'taxonomies'          => array(),
			'hierarchical'        => false,
			'public'              => true,
			'menu_icon'           => 'dashicons-car',
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_rest'        => true,
			'menu_position'       => 5,
			'show_in_admin_bar'   => true,
			'show_in_nav_menus'   => true,
			'can_export'          => true,
			'has_archive'         => true,
			'exclude_from_search' => false,
			'publicly_queryable'  => true,
			'capability_type'     => 'page',
		);

		return apply_filters( Plugin::FILTER_CPT_TRAFFIC_ARGS, $args );
	}


}