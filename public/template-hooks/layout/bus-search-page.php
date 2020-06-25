<?php
$mage_bus_total_seats_availabel = 0;
function mage_bus_search_page() {
    global $wbtmmain;
    $global_target   = $wbtmmain->bus_get_option('search_target_page', 'label_setting_sec') ? get_post_field( 'post_name', $wbtmmain->bus_get_option('search_target_page', 'label_setting_sec')) : 'bus-search-list' ;
    echo '<div class="mage">';
    mage_bus_search_form($global_target);
echo "<div id='wbtm_search_result_section'>";
if (isset($_GET['bus_start_route']) && ($_GET['bus_end_route']) && ($_GET['j_date'])) {
    mage_next_date_suggestion(false, false);
    
	echo '<div class="wbtm_search_part">';
		mage_bus_route_title(false);
        mage_bus_search_list(false);
        
    echo '</div>';
    

}
if (isset($_GET['bus_start_route']) && ($_GET['bus_end_route']) && ($_GET['r_date'])) {
	mage_next_date_suggestion(true, false);
	echo '<div class="wbtm_search_part">';
		mage_bus_route_title(true);
		mage_bus_search_list(true);
	echo '</div>';
}
    echo '</div></div>';
}





//bus search list
function mage_bus_search_list($return) {
    $bus_list       = mage_search_bus_query($return);
    $bus_list_loop = new WP_Query($bus_list);

    echo '<div class="mar_t mage_bus_lists">'; 
    mage_bus_title();
    while ($bus_list_loop->have_posts()) {
        $bus_list_loop->the_post();
        $id         = get_the_id();
            mage_bus_search_item($return, $id);
    }
    echo '<div class="mediumRadiusBottom mage_bus_list_title "></div>';
    echo '</div>';
    wp_reset_query();
}

function mage_bus_search_item($return, $id) {
    $time_format        = get_option('time_format');

    $start              = $return ? mage_bus_isset('bus_end_route') : mage_bus_isset('bus_start_route');
    $end                = $return ? mage_bus_isset('bus_start_route') : mage_bus_isset('bus_end_route');
   // $in_cart = wbtm_find_product_in_cart();
    $seat_price         = mage_bus_seat_price($return);
    $values             = get_post_custom($id);
    $start_time         = get_wbtm_datetime(mage_bus_time($return, false),'time');
    $end_time           = get_wbtm_datetime(mage_bus_time($return, true),'time');
    $cart_class = wbtm_find_product_in_cart();

    

    if (mage_bus_run_on_date($return)) {
        ?>
        <div class="mage_bus_item <?php echo $cart_class;  ?>">
            <div class="mage_flex">
                <div class="mage_bus_img flexCenter"><?php the_post_thumbnail('thumb'); ?></div>
                <div class="mage_bus_info flexEqual_flexCenter">
                    <div class="flexEqual_flexCenter">
                        <h6>
                            <strong class="dBlock_mar_zero"><?php the_title(); ?></strong>
                            <small class="dBlock"><?php echo $values['wbtm_bus_no'][0]; ?></small>
                            <?php
                            if ($cart_class) {
                                echo '<span class="dBlock_mar_t_xs"><span class="fa fa-shopping-cart"></span>';
                                _e('Already Added in cart !','bus-ticket-booking-with-seat-reservation');
                                echo '</span>';
                            }
                            ?>
                        </h6>
                        <div class="mage_hidden_xxs">
                            <h6>
                                <span class="fa fa-angle-double-right"></span>
                                <span><?php echo $start; ?> ( <?php echo $start_time; ?> )</span>
                            </h6>
                            <h6>
                                <span class="fa fa-stop"></span>
                                <span><?php echo $end; ?> ( <?php echo $end_time; ?> )</span>
                            </h6>
                        </div>
                    </div>
                    <div class="flexEqual_flexCenter_textCenter">
                        <h6 class="mage_hidden_xxs"><?php echo mage_bus_type(); ?></h6>
                        <h6 class="mage_hidden_xs"><strong><?php echo get_woocommerce_currency_symbol() . $seat_price; ?></strong>/<span><?php _e('Seat','bus-ticket-booking-with-seat-reservation'); ?></h6>
                        <h6 class="mage_hidden_md"><?php echo mage_bus_available_seat($return) . ' / ' . mage_bus_total_seat(); ?></h6>
                        <button type="button" class="mage_button_xs mage_bus_details_toggle"><?php mage_bus_label('wbtm_view_seats_text', __('View Seats','bus-ticket-booking-with-seat-reservation')); ?></button>
                    </div>
                </div>
            </div>
            <?php mage_bus_item_seat_details($return); ?>
        </div>
        <?php
    }
}

function mage_bus_item_seat_details($return) {
    $date_format        = get_option('date_format');
    $time_format        = get_option('time_format');

    $start              = $return ? mage_bus_isset('bus_end_route') : mage_bus_isset('bus_start_route');
    $end                = $return ? mage_bus_isset('bus_start_route') : mage_bus_isset('bus_end_route');
    $date           = $return ? mage_bus_isset('r_date') : mage_bus_isset('j_date');
    $start_time         = get_wbtm_datetime(mage_bus_time($return, false),'time');
    $end_time           = get_wbtm_datetime(mage_bus_time($return, true),'time');
    $date = wbtm_convert_date_to_php($date);
    $seat_price = mage_bus_seat_price($return);;
    ?>
    <div class="mage_bus_seat_details">
        <div class="mage_flex_justifyBetween">
            <?php
            $seat_plan_type = mage_get_bus_seat_plan_type();
            if ($seat_plan_type > 0) {
                $bus_width = $seat_plan_type * 45;
            } else {
                $bus_width = 250;
            }
            
            mage_bus_seat_plan($seat_plan_type, $bus_width, $seat_price, $return);
            global $mage_bus_total_seats_availabel;
            ?>
            <div class="mage_bus_customer_sec mage_default" style="width: calc(100% - 20px - <?php echo $bus_width; ?>px);">
                <div class="flexEqual">
                    <div class="mage_bus_details_short">
                        <h6>
                            
                            <span class='wbtm-details-page-list-label'><span class="fa fa-map-marker"></span><?php _e('Boarding:','bus-ticket-booking-with-seat-reservation'); ?></span>
                            <?php echo $start; ?> ( <?php echo $start_time; ?> )
                        </h6>
                        <h6 class="mar_t_xs">
                           
                            <span class='wbtm-details-page-list-label'> <span class="fa fa-map-marker"></span><?php _e('Dropping:','bus-ticket-booking-with-seat-reservation'); ?></span>
                            <?php echo $end; ?> ( <?php echo $end_time; ?> )
                        </h6>
                        <h6 class="mar_t_xs">
                            <span class='wbtm-details-page-list-label'><i class="fa fa-bus" aria-hidden="true"></i>
                            <?php _e('Coach Type:','bus-ticket-booking-with-seat-reservation'); ?></span>
                            <?php echo mage_bus_type(); ?>
                        </h6>
                        <h6 class="mar_t_xs">
                            <span class='wbtm-details-page-list-label'><i class="fa fa-calendar" aria-hidden="true"></i>
                            <?php _e('Date:','bus-ticket-booking-with-seat-reservation'); ?></span>
                            <?php echo get_wbtm_datetime($date,'date'); ?>
                        </h6>
                        <h6 class="mar_t_xs">
                            <span class='wbtm-details-page-list-label'><i class="fa fa-clock-o" aria-hidden="true"></i>
                            <?php _e('Start Time:','bus-ticket-booking-with-seat-reservation'); ?></span>
                            <?php echo $start_time; ?>
                        </h6>
                        <h6 class="mar_t_xs">
                            <span class='wbtm-details-page-list-label'>
                                <i class="fa fa-money" aria-hidden="true"></i>
                                <?php _e('Fare:','bus-ticket-booking-with-seat-reservation'); ?></span>
                           <?php echo get_woocommerce_currency_symbol() . $seat_price; ?>/
                            <span><?php _e('Seat','bus-ticket-booking-with-seat-reservation'); ?></span>
                        </h6>
                        <h6 class="mar_t_xs wbtm-details-page-list-total-avl-seat">
                            <strong><?php echo $mage_bus_total_seats_availabel //mage_bus_available_seat($return); ?></strong>
                            <span><?php _e('Seat Available','bus-ticket-booking-with-seat-reservation'); ?></span>
                        </h6>
                    </div>
                    <div class="textCenter mage_bus_seat_list">
                        <div class="flexEqual mage_bus_selected_list">
                            <h6><strong><?php mage_bus_label('wbtm_seat_no_text', __('Seat No','bus-ticket-booking-with-seat-reservation')); ?></strong></h6>
                            <h6><strong><?php mage_bus_label('wbtm_fare_text', __('Fare','bus-ticket-booking-with-seat-reservation')); ?></strong></h6>
                            <h6><strong><?php mage_bus_label('wbtm_remove_text', __('Remove','bus-ticket-booking-with-seat-reservation')); ?></strong></h6>
                        </div>
                        <div class="mage_bus_selected_seat_list">
                        </div>
                        <div class="mage_bus_selected_list mage_bus_sub_total padding">
                            <h5>
                                <strong><?php _e('Sub Total :','bus-ticket-booking-with-seat-reservation'); ?></strong>
                                <span class="mage_bus_total_qty">0</span>x
                                <span><?php echo get_woocommerce_currency_symbol() . $seat_price; ?></span>=
                                <?php echo get_woocommerce_currency_symbol(); ?><strong class="mage_bus_sub_total_price">0</strong>
                            </h5>
                            <div class="mage_extra_bag">
                                <h5>
                                    <strong><?php _e('Extra Bag :','bus-ticket-booking-with-seat-reservation'); ?></strong>
                                    <span class="mage_bus_extra_bag_qty">0</span>x
                                    <span><?php echo get_woocommerce_currency_symbol(); ?><span class="mage_extra_bag_price">0</span></span>=
                                    <?php echo get_woocommerce_currency_symbol(); ?><strong class="mage_bus_extra_bag_total_price">0</strong>
                                </h5>
                            </div>
                        </div>
                    </div>
                </div>
                <form class="mage_form mage_bus_info_form" action="" method="post">
                    <input type="hidden" name='journey_date' value='<?php echo $date; ?>'/>
                    <input type="hidden" name='start_stops' value="<?php echo $start; ?>"/>
                    <input type='hidden' name='end_stops' value='<?php echo $end; ?>'/>
                    <input type="hidden" name="user_start_time" value="<?php echo $start_time; ?>"/>
                    <input type="hidden" name="bus_start_time" value="<?php echo $start_time; ?>"/>
                    <input type="hidden" name="bus_id" value="<?php echo get_the_id(); ?>"/>
                    <input type="hidden" name='total_seat' value="0"/>
                    <div class="mage_customer_info_area">
                        <?php echo has_action('mage_bus_hidden_customer_info_form') ? '<input type="hidden" name="custom_reg_user" value="yes" />' : '<input type="hidden" name="custom_reg_user" value="no" />' ?>
                    </div>
                    <div class="flexEqual flexCenter textCenter_mar_t">
                        <h4>
                            <strong><?php _e('Total :','bus-ticket-booking-with-seat-reservation'); ?></strong>
                            <?php echo get_woocommerce_currency_symbol(); ?><strong class="mage_bus_total_price">0</strong>
                        </h4>
                        <button class="mage_button" type="submit" name="add-to-cart" value="<?php echo get_post_meta(get_the_id(),'link_wc_product',true); //echo esc_attr(get_the_id()); ?>" class="single_add_to_cart_button">
                            <?php mage_bus_label('wbtm_book_now_text', __('Book Now','bus-ticket-booking-with-seat-reservation')); ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php do_action('mage_bus_hidden_customer_info_form'); ?>
    <input type="hidden" name="mage_currency" value="<?php echo get_woocommerce_currency_symbol(); ?>"/>
    <input type="hidden" name="mage_bus_title" value="<?php _e('Passenger Info seat : ','bus-ticket-booking-with-seat-reservation') ?>"/>
    <?php
}

//bus seat plan
function mage_bus_seat_plan($seat_plan_type, $bus_width, $price, $return) {
    global $mage_bus_total_seats_availabel;
    $current_driver_position = get_post_meta(get_the_id(), 'driver_seat_position', true);
    $seat_panel_settings = get_option('wbtm_bus_settings');
    $driver_image = $seat_panel_settings['diriver_image'] ? wp_get_attachment_url($seat_panel_settings['diriver_image'], 'full') : WBTM_PLUGIN_URL . '/public/images/driver-default.png';

    $all_stopages_name = mage_bus_get_all_stopages(get_the_id());
    ?>
    <div class="mage_bus_seat_plan" style="width: <?php echo $bus_width; ?>px;">
        <div class="mage_default_pad_xs">
            <div class="flexEqual">
                <div class="padding"><img class="<?php echo ($current_driver_position == 'driver_left') ? 'mageLeft' : 'mageRight'; ?>" src="<?php echo $driver_image; ?>" alt=""></div>
            </div>
            <?php
            $mage_bus_total_seats_availabel = mage_bus_total_seat();
            if ($seat_plan_type > 0) {
                $seats_rows = get_post_meta(get_the_id(), 'wbtm_bus_seats_info', true);
                $seat_col = get_post_meta(get_the_id(), 'wbtm_seat_cols', true);
                foreach ($seats_rows as $seat) {
                    echo '<div class="flexEqual mage_bus_seat">';
                    for ($i = 1; $i <= $seat_col; $i++) {
                        $seat_name = $seat["seat" . $i];
                        $mage_bus_total_seats_availabel = mage_bus_seat($seat_plan_type, $seat_name, $price, $return, 0, $all_stopages_name, $mage_bus_total_seats_availabel);
                    }
                    echo '</div>';
                }
            } elseif ($seat_plan_type == 'seat_plan_1' || $seat_plan_type == 'seat_plan_2' || $seat_plan_type == 'seat_plan_3') {
                $bus_meta = get_post_custom(get_the_id());
                $seats_rows = explode(",", $bus_meta['wbtm_seat_row'][0]);
                $seat_col = $bus_meta['wbtm_seat_col'][0];
                $seat_col_arr = explode(",", $seat_col);
                foreach ($seats_rows as $seat) {
                    echo '<div class="flexEqual mage_bus_seat">';
                    foreach ($seat_col_arr as $seat_col) {
                        $seat_name = $seat . $seat_col;
                        mage_bus_seat($seat_plan_type, $seat_name, $price, $return, $seat_col, $all_stopages_name);
                    }
                    echo '</div>';
                }
            } else {
                echo 'Please update Your Seat Plan !';
            }
            ?>
        </div>
        <?php
        $seats_dd = get_post_meta(get_the_id(), 'wbtm_bus_seats_info_dd', true);
        $seat_col_dd = get_post_meta(get_the_id(), 'wbtm_seat_rows_dd', true);

        

        if (is_array($seats_dd) && sizeof($seats_dd) > 0) {
            echo '<div class="mage_default_pad_xs_mar_t">';
            foreach ($seats_dd as $seat) {
                echo '<div class="flexEqual mage_bus_seat">';
                for ($i = 1; $i <= $seat_col_dd; $i++) {
                    $seat_name = $seat["dd_seat" . $i];
                    mage_bus_seat($seat_plan_type, $seat_name, $price, $return, 0, $all_stopages_name);
                }
                echo '</div>';
            }
            echo '</div>';
        }
        ?>
    </div>
    <?php
}

//bus seat place
function mage_bus_seat($seat_plan_type, $seat_name, $price, $return, $seat_col, $all_stopages_name, $mage_bus_total_seats_availabel) {

    if (strtolower($seat_name) == 'door') {
        echo '<div></div>';
    } elseif (strtolower($seat_name) == 'wc') {
        echo '<div></div>';
    } elseif ($seat_name == '') {
        echo '<div></div>';
    } else {
        $seat_status = mage_bus_seat_status($seat_name, $return);

        // Partial Route
        $seat_boarding_point = mage_bus_seat_droping_point($seat_name, 'wbtm_boarding_point', $return);
        $seat_droping_point = mage_bus_seat_droping_point($seat_name, 'wbtm_droping_point', $return);

        $get_search_start_position = (int)array_search($_GET['bus_start_route'], $all_stopages_name);
        $get_search_droping_position = (int)array_search($_GET['bus_end_route'], $all_stopages_name);

        $get_seat_boarding_position = (int)array_search($seat_boarding_point, $all_stopages_name);
        $get_seat_droping_position = (int)array_search($seat_droping_point, $all_stopages_name);


        if(!$get_seat_droping_position || empty($get_seat_droping_position)) {
            $get_seat_droping_position = count($all_stopages_name);
        }
        if(!$get_search_droping_position || empty($get_search_droping_position)) {
            $get_search_droping_position = count($all_stopages_name);
        }

        $partial_route_condition = false;
        if( $get_seat_droping_position <= $get_search_start_position ) {
            $partial_route_condition = false;
        } else {
            if( $get_seat_boarding_position >= $get_search_droping_position ) {
                $partial_route_condition = false;
            } else {
                $partial_route_condition = true;
            }
        }
        // Partial Route END

        if (wbtm_find_seat_in_cart($seat_name)) {
            ?>
            <div class="flex_justifyCenter mage_seat_in_cart" title="<?php _e('Already Added in cart !','bus-ticket-booking-with-seat-reservation'); ?>">
                <span class="mage_bus_seat_icon"><?php echo $seat_name; ?><span class="bus_handle"></span></span>
            </div>
            <?php
        } elseif ( $seat_status == 1 && $partial_route_condition === true ) {
            $mage_bus_total_seats_availabel--; // for seat available
            ?>
            <div class="flex_justifyCenter mage_seat_booked" title="<?php _e('Already Booked By another !','bus-ticket-booking-with-seat-reservation'); ?>">
                <span class="mage_bus_seat_icon"><?php echo $seat_name; ?><span class="bus_handle"></span></span>
            </div>
            <?php
        } elseif ( $seat_status == 2 && $partial_route_condition === true ) {
            $mage_bus_total_seats_availabel--; // for seat available
            ?>
            <div class="flex_justifyCenter mage_seat_confirmed" title="<?php _e('Already Sold By another !','bus-ticket-booking-with-seat-reservation'); ?>">
                <span class="mage_bus_seat_icon"><?php echo $seat_name; ?><span class="bus_handle"></span></span>
            </div>
            <?php
        } else {
            ?>
            <div class="flex_justifyCenter mage_bus_seat_item" data-price="<?php echo $price; ?>" data-seat-name="<?php echo $seat_name; ?>">
                <span class="mage_bus_seat_icon"><?php echo $seat_name; ?><span class="bus_handle"></span></span>
            </div>
            <?php
        }
        if (($seat_plan_type == 'seat_plan_1' && $seat_col == 2) || ($seat_plan_type == 'seat_plan_2' && $seat_col == 1) || ($seat_plan_type == 'seat_plan_3' && $seat_col == 2)) {
            echo '<div></div>';
        }
    }

    return $mage_bus_total_seats_availabel;
}

//next 6  date suggestion
function mage_next_date_suggestion($return, $single_bus) {
    $date = $return ? mage_bus_isset('r_date') : mage_bus_isset('j_date');
    $date = wbtm_convert_date_to_php($date);
    if ($date) {
        $tab_date = isset($_GET['tab_date']) ? $_GET['tab_date'] : wbtm_convert_date_to_php(mage_bus_isset('j_date'));
        $tab_date_r = isset($_GET['tab_date_r']) ? $_GET['tab_date_r'] : wbtm_convert_date_to_php(mage_bus_isset('r_date'));
        $next_date = $return ? $tab_date_r : $tab_date;
        ?>
        <div class="mage_default_xs mt_25">
            <ul class="mage_list_inline flexEqual mage_next_date">
                <?php
                for ($i = 0; $i < 6; $i++) {
                    ?>
                    <li class="<?php echo $date == $next_date ? 'mage_active' : ''; ?>">
                        <a href="<?php echo $single_bus ? '' : get_site_url() . '/bus-search-list/'; ?>?bus_start_route=<?php echo strip_tags($_GET['bus_start_route']); ?>&bus_end_route=<?php echo strip_tags($_GET['bus_end_route']); ?>&j_date=<?php echo $return ? strip_tags($_GET['j_date']) : $next_date; ?>&r_date=<?php echo $return ? $next_date : strip_tags($_GET['r_date']); ?>&bus-r=<?php echo strip_tags($_GET['bus-r']); ?>&tab_date=<?php echo $tab_date; ?>&tab_date_r=<?php echo $tab_date_r; ?>" data-sroute='<?php echo strip_tags($_GET['bus_start_route']); ?>' data-eroute='<?php echo strip_tags($_GET['bus_end_route']); ?>' data-jdate='<?php echo $return ? strip_tags($_GET['j_date']) : $next_date; ?>' data-rdate='<?php echo $return ? $next_date : strip_tags($_GET['r_date']); ?>' class='wbtm_next_day_search'>
                            <?php echo get_wbtm_datetime($next_date,'date-text') ?>
                        </a>
                    </li>
                    <?php
                    $next_date = date('Y-m-d', strtotime($next_date . ' +1 day'));
                }
                ?>
            </ul>
        </div>
        <?php
    }
}

// bus list title
function mage_bus_route_title($return) {
    $start      = $return ? mage_bus_isset('bus_end_route') : mage_bus_isset('bus_start_route');
    $end        = $return ? mage_bus_isset('bus_start_route') : mage_bus_isset('bus_end_route');
    $date       = $return ? date('D, d M Y', strtotime(wbtm_convert_date_to_php(mage_bus_isset('r_date')))) : date('D, d M Y', strtotime(wbtm_convert_date_to_php(mage_bus_isset('j_date'))));
    ?>
    <div class="bgLight_mar_t_textCenter_radius_pad_xs_justifyAround mage_title">
        <h4>
            <strong>
                <span><?php echo $start; ?></span>
                <span class="fa fa-long-arrow-right"></span>
                <span><?php echo $end; ?></span>
            </strong>
        </h4>
        <h4><strong><?php echo get_wbtm_datetime($date,'date-text'); ?></strong></h4>
    </div>
    <?php
}