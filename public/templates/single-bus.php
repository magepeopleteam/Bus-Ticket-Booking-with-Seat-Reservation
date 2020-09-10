<?php
get_header();
the_post();
$id = get_the_id();
$values = get_post_custom($id);
?>
    <div class="mage mage_single_bus_search_page">
        <?php do_action('woocommerce_before_single_product'); ?>
        <div class="post-content-wrap">
        <?php echo the_content();?>
        </div>
        <div class="mage_default">
            <div class="flexEqual">
                <div class="mage_xs_full"><?php the_post_thumbnail('full'); ?></div>
                <div class="ml_25 mage_xs_full">
                    <div class="mage_default_bDot">
                        <h4><?php the_title(); ?><small>( <?php echo $values['wbtm_bus_no'][0]; ?> )</small></h4>
                        <h6 class="mar_t_xs"><strong><?php _e('Bus Type :', 'bus-ticket-booking-with-seat-reservation'); ?></strong><?php echo mage_bus_type(); ?></h6>
                        <h6 class="mar_t_xs"><strong><?php _e('Passenger Capacity :', 'bus-ticket-booking-with-seat-reservation'); ?></strong><?php echo mage_bus_total_seat(); ?></h6>
                        <?php if (mage_bus_run_on_date(false) && isset($_GET['bus_start_route']) && ($_GET['bus_end_route']) && ($_GET['j_date'])) { ?>
                            <h6 class="mar_t_xs">
                                <span><?php _e('Fare :', 'bus-ticket-booking-with-seat-reservation'); ?></span>
                                <strong><?php echo wc_price(mage_bus_seat_price($id,mage_bus_isset('bus_start_route'), mage_bus_isset('bus_end_route'),false)); ?></strong>/
                                <span><?php _e('Seat', 'bus-ticket-booking-with-seat-reservation'); ?></span>
                            </h6>
                        <?php } ?>
                    </div>
                    <div class="flexEqual_mar_t mage_bus_drop_board">
                        <div class="mage_default_bDot">
                            <h5><?php _e('Boarding Point', 'bus-ticket-booking-with-seat-reservation'); ?></h5>
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
                            <h5><?php _e('Dropping Point', 'bus-ticket-booking-with-seat-reservation'); ?></h5>
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

            $global_target = $wbtmmain->bus_get_option('search_target_page', 'label_setting_sec') ? get_post_field('post_name', $wbtmmain->bus_get_option('search_target_page', 'label_setting_sec')) : 'bus-search-list';
            if(isset($params)) {
                $target = $params['search-page'] ? $params['search-page'] : $global_target;
            } else {
                $target = $global_target;
            }

            mage_bus_search_form_only(true, $target); ?>
        </div>
        <?php
        //  if (mage_bus_run_on_date(false) && isset($_GET['bus_start_route']) && ($_GET['bus_end_route']) && ($_GET['j_date'])) { 
         if (isset($_GET['bus_start_route']) && ($_GET['bus_end_route']) && ($_GET['j_date'])) { 
            
            $start = $_GET['bus_start_route'];
            $end = $_GET['bus_end_route'];
            $j_date = $_GET['j_date'];
            $j_date = mage_convert_date_format($j_date, 'Y-m-d');

            $check_has_price = mage_bus_seat_price($id, $start, $end, false);
            // Buffer Time Calculation
            $bus_bp_array = get_post_meta($id, 'wbtm_bus_bp_stops', true);
            $bus_bp_array = unserialize($bus_bp_array);
            $bp_time = $wbtmmain->wbtm_get_bus_start_time($start, $bus_bp_array);
            $is_buffer = $wbtmmain->wbtm_buffer_time_check($bp_time, date('Y-m-d', strtotime($j_date)));
            // Buffer Time Calculation END

            $single_offday = false;
            $has_bus = false;

            // Is Bus Show or not
            if( $is_buffer == 'yes' ) {
                // Operational on day
                $on_date = mage_bus_on_date($id, $j_date);
                
                if( $on_date == 'no' ) {
                    // Operational off day
                    $offday_start = get_post_meta($id, 'wbtm_od_start', true);
                    $offday_end = get_post_meta($id, 'wbtm_od_end', true);

                    // Day Off
                    $day_off = mage_check_search_day_off($id, $j_date);
                    
                    if($offday_start != null) {
                        $offday_start = date('Y-m-d', strtotime($offday_start));
                        if($offday_end != null) {
                            $offday_end = date('Y-m-d', strtotime($offday_end));
                            $single_offday = false;
                        } else {
                            $offday_end = '';
                            $single_offday = true;
                        }
                    } else {
                        $offday_start = '';
                        $offday_end = '';
                        $single_offday = true;
                    }


                    if($single_offday) { // Only single date
                        if( $j_date != $offday_start && $day_off === false ) {
                            $has_bus = true;
                        }
                    } else { // Date range
                        if( ($j_date >= $offday_start) && ($j_date <= $offday_end) ) {
                            // Bus off
                        } else {
                            if(!$day_off) {
                                $has_bus = true;
                            }
                        }
                    }
                    // Operational off day END
                } elseif( $on_date == 'yes' ) {
                    mage_bus_search_item($return, $id);
                    $has_bus = true;
                } else {
                    // echo 'no bus';
                }
            }

        ?>
            <div class="mage_bus_item <?php echo mage_bus_in_cart(false) ? 'mage_bus_in_cart' : ''; ?>">
                <?php

                if ($has_bus && $check_has_price) {

                    mage_next_date_suggestion(false, true);
                    mage_bus_route_title(false);
                    mage_bus_item_seat_details(false);

                } else {
                    echo '<div class="wbtm-warnig">';
                    _e('This bus available only in the particular date. :) ', 'bus-ticket-booking-with-seat-reservation');
                    echo '</div>';
                }
                ?>
            </div>
        <?php } ?>
    </div>


<?php
get_footer();