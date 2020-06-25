<?php
get_header();
the_post();
$id = get_the_id();
$values = get_post_custom($id);
?>
    <div class="mage mage_single_bus_search_page">
        <?php  do_action('woocommerce_before_single_product'); ?>
        <div class="mage_default">
            <div class="flexEqual">
                <div class="mage_xs_full"><?php the_post_thumbnail('full'); ?></div>
                <div class="ml_25 mage_xs_full">
                    <div class="mage_default_bDot">
                        <h4><?php the_title(); ?><small>( <?php echo $values['wbtm_bus_no'][0]; ?> )</small></h4>
                        <h6 class="mar_t_xs"><strong><?php _e('Bus Type :','bus-ticket-booking-with-seat-reservation'); ?></strong><?php echo mage_bus_type(); ?></h6>
                        <h6 class="mar_t_xs"><strong><?php _e('Passenger Capacity :','bus-ticket-booking-with-seat-reservation'); ?></strong><?php echo mage_bus_total_seat(); ?></h6>
                        <h6 class="mar_t_xs">
                            <span><?php _e('Fare :','bus-ticket-booking-with-seat-reservation'); ?></span>
                            <strong><?php echo get_woocommerce_currency_symbol() . mage_bus_seat_price(false); ?></strong>/
                            <span><?php _e('Seat','bus-ticket-booking-with-seat-reservation'); ?></span>
                        </h6>
                    </div>
                    <div class="flexEqual_mar_t mage_bus_drop_board">
                        <div class="mage_default_bDot">
                            <h5><?php _e('Boarding Point','bus-ticket-booking-with-seat-reservation'); ?></h5>
                            <ul class="mage_list mar_t_xs">
                                <?php
                                $start_stops = maybe_unserialize(get_post_meta(get_the_id(), 'wbtm_bus_bp_stops', true));
                                foreach ($start_stops as $route) {
                                    echo '<li><span class="fa fa-map-marker"></span>' . $route['wbtm_bus_bp_stops_name'] . '</li>';
                                }
                                ?>
                            </ul>
                        </div>
                        <div class="mage_default_bDot">
                            <h5><?php _e('Dropping Point','bus-ticket-booking-with-seat-reservation'); ?></h5>
                            <ul class="mage_list mar_t_xs">
                                <?php
                                $end_stops = maybe_unserialize(get_post_meta(get_the_id(), 'wbtm_bus_next_stops', true));
                                foreach ($end_stops as $route) {
                                    echo '<li><span class="fa fa-map-marker"></span>' . $route['wbtm_bus_next_stops_name'] . '</li>';
                                }
                                ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="mage_default mage_form_inline">

            <?php 
            
            $global_target   = $wbtmmain->bus_get_option('search_target_page', 'label_setting_sec') ? get_post_field( 'post_name', $wbtmmain->bus_get_option('search_target_page', 'label_setting_sec')) : 'bus-search-list' ;
            $target          = $params['search-page'] ? $params['search-page'] : $global_target;
    
            mage_bus_search_form_only(true,$target); ?>
        </div>
        <?php if (mage_bus_run_on_date(false) && isset($_GET['bus_start_route']) && ($_GET['bus_end_route']) && ($_GET['j_date'])) { ?>
            <div class="mage_bus_item <?php echo mage_bus_in_cart(false) ? 'mage_bus_in_cart' : ''; ?>">
                <?php
                $wbtm_bus_on_dates = get_post_meta(get_the_id(),'wbtm_bus_on_dates',true) ? maybe_unserialize(get_post_meta(get_the_id(),'wbtm_bus_on_dates',true)) : array(); 

                if(is_array($wbtm_bus_on_dates) && sizeof($wbtm_bus_on_dates) > 0){


                    $pday 	= 		array();
                    foreach($wbtm_bus_on_dates as $_wbtm_bus_on_dates){
                        $pday[] = date('Y-m-d',strtotime($_wbtm_bus_on_dates['wbtm_on_date_name']));
                    }

                    $search_date = date('Y-m-d',strtotime($_GET['j_date']));



                    if(in_array($search_date, $pday)){
                        mage_next_date_suggestion(false, true);
                        mage_bus_route_title(false);
                        mage_bus_item_seat_details(false);
                    }else{
                        echo '<div class="wbtm-warnig">';
                            _e('Uhu! No Cheating, This bus available only in the particular date. :) ','bus-ticket-booking-with-seat-reservation');
                        echo '</div>';
                    }

                }else{
                    mage_next_date_suggestion(false, true);
                    mage_bus_route_title(false);
                    mage_bus_item_seat_details(false);
                }
                ?>
            </div>
        <?php } ?>
    </div>


<?php
get_footer();