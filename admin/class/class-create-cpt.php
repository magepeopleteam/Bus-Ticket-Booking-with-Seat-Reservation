<?php 
if ( ! defined( 'ABSPATH' ) ) { die; } // Cannot access pages directly.
class WBTM_Cpt{
	
	public function __construct(){
		add_action( 'init', array($this, 'register_cpt' ));
	}


	public function register_cpt(){
		$labels = array(
			'name'                  => _x( 'Bus', 'bus-ticket-booking-with-seat-reservation' ),
			'singular_name'         => _x( 'Bus', 'bus-ticket-booking-with-seat-reservation' ),
			'menu_name'             => __( 'Bus', 'bus-ticket-booking-with-seat-reservation' ),
			'name_admin_bar'        => __( 'Bus', 'bus-ticket-booking-with-seat-reservation' ),
			'archives'              => __( 'Bus List', 'bus-ticket-booking-with-seat-reservation' ),
			'attributes'            => __( 'Bus List', 'bus-ticket-booking-with-seat-reservation' ),
			'parent_item_colon'     => __( 'Bus Item:', 'bus-ticket-booking-with-seat-reservation' ),
			'all_items'             => __( 'All Bus', 'bus-ticket-booking-with-seat-reservation' ),
			'add_new_item'          => __( 'Add New Bus', 'bus-ticket-booking-with-seat-reservation' ),
			'add_new'               => __( 'Add New Bus', 'bus-ticket-booking-with-seat-reservation' ),
			'new_item'              => __( 'New Bus', 'bus-ticket-booking-with-seat-reservation' ),
			'edit_item'             => __( 'Edit Bus', 'bus-ticket-booking-with-seat-reservation' ),
			'update_item'           => __( 'Update Bus', 'bus-ticket-booking-with-seat-reservation' ),
			'view_item'             => __( 'View Bus', 'bus-ticket-booking-with-seat-reservation' ),
			'view_items'            => __( 'View Bus', 'bus-ticket-booking-with-seat-reservation' ),
			'search_items'          => __( 'Search Bus', 'bus-ticket-booking-with-seat-reservation' ),
			'not_found'             => __( 'Bus Not found', 'bus-ticket-booking-with-seat-reservation' ),
			'not_found_in_trash'    => __( 'Bus Not found in Trash', 'bus-ticket-booking-with-seat-reservation' ),
			'featured_image'        => __( 'Bus Feature Image', 'bus-ticket-booking-with-seat-reservation' ),
			'set_featured_image'    => __( 'Set Bus featured image', 'bus-ticket-booking-with-seat-reservation' ),
			'remove_featured_image' => __( 'Remove Bus featured image', 'bus-ticket-booking-with-seat-reservation' ),
			'use_featured_image'    => __( 'Use as Bus featured image', 'bus-ticket-booking-with-seat-reservation' ),
			'insert_into_item'      => __( 'Insert into Bus', 'bus-ticket-booking-with-seat-reservation' ),
			'uploaded_to_this_item' => __( 'Uploaded to this Bus', 'bus-ticket-booking-with-seat-reservation' ),
			'items_list'            => __( 'Bus list', 'bus-ticket-booking-with-seat-reservation' ),
			'items_list_navigation' => __( 'Bus list navigation', 'bus-ticket-booking-with-seat-reservation' ),
			'filter_items_list'     => __( 'Filter Bus list', 'bus-ticket-booking-with-seat-reservation' ),
		);
	
	    $args = array(
	        'public'                => true,
	        'labels'                => $labels,
	        'menu_icon'             => 'dashicons-slides',
	        'supports'              => array('title','editor','thumbnail'),
			'rewrite'               => array('slug' => 'bus'),
			'show_in_rest'          => true,
			'rest_base'             => 'wbtm_bus',			

	    );
	   	 register_post_type( 'wbtm_bus', $args );

	}

}
new WBTM_Cpt();