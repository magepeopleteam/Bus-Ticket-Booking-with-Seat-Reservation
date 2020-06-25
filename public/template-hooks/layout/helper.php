<?php
function mage_bus_isset($parameter) {
    return isset($_GET[$parameter]) ? strip_tags($_GET[$parameter]) : false;
}

function mage_bus_text($text) {
    _e($text, 'bus-ticket-booking-with-seat-reservation');
}

function mage_bus_label($var, $text) {
    global $wbtmmain;
    echo $wbtmmain->bus_get_option($var, 'label_setting_sec') ? $wbtmmain->bus_get_option($var, 'label_setting_sec') : $text;
}

function mage_route_list($single_bus, $start_route) {
    echo '<ul class="mage_input_select_list">';
    if ($single_bus) {
        if ($start_route) {
            $start_stops = maybe_unserialize(get_post_meta(get_the_id(), 'wbtm_bus_bp_stops', true));
            foreach ($start_stops as $route) {
                echo '<li data-route="' . $route['wbtm_bus_bp_stops_name'] . '"><span class="fa fa-map-marker"></span>' . $route['wbtm_bus_bp_stops_name'] . '</li>';
            }
        } else {
            $end_stops = maybe_unserialize(get_post_meta(get_the_id(), 'wbtm_bus_next_stops', true));
            foreach ($end_stops as $route) {
                echo '<li data-route="' . $route['wbtm_bus_next_stops_name'] . '"><span class="fa fa-map-marker"></span>' . $route['wbtm_bus_next_stops_name'] . '</li>';
            }
        }
    } else {
        $routes = get_terms(array(
            'taxonomy' => 'wbtm_bus_stops',
            'hide_empty' => false,
        ));
        foreach ($routes as $route) {
            echo '<li data-route="' . $route->name . '"><span class="fa fa-map-marker"></span>' . $route->name . '</li>';
        }
    }
    echo '</ul>';
}

function mage_search_bus_query($return) {
    $start = $return ? mage_bus_isset('bus_end_route') : mage_bus_isset('bus_start_route');
    $end = $return ? mage_bus_isset('bus_start_route') : mage_bus_isset('bus_end_route');
    return array(
        'post_type' => array('wbtm_bus'),
        'posts_per_page' => -1,
        'order' => 'ASC',
        'orderby' => 'meta_value',
        'meta_key' => 'wbtm_bus_start_time',
        'meta_query' => array(
            'relation' => 'AND',
            array(
                'key' => 'wbtm_bus_bp_stops',
                'value' => $start,
                'compare' => 'LIKE',
            ),
            array(
                'key' => 'wbtm_bus_next_stops',
                'value' => $end,
                'compare' => 'LIKE',
            ),
        )

    );
}

function mage_bus_title() {
    ?>
    <div class="mage_flex_mediumRadiusTop mage_bus_list_title ">
        <div class="mage_bus_img flexCenter"><h6><?php _e('Image','bus-ticket-booking-with-seat-reservation'); ?></h6></div>
        <div class="mage_bus_info flexEqual flexCenter">
            <div class="flexEqual">
                <h6><?php mage_bus_label('wbtm_bus_name_text', __('Bus','bus-ticket-booking-with-seat-reservation')); ?></h6>
                <h6 class="mage_hidden_xxs"><?php _e('Schedule','bus-ticket-booking-with-seat-reservation'); ?></h6>
            </div>
            <div class="flexEqual flexCenter textCenter">
                <h6 class="mage_hidden_xxs"><?php mage_bus_label('wbtm_type_text', __('Coach Type','bus-ticket-booking-with-seat-reservation')); ?></h6>
                <h6 class="mage_hidden_xs"><?php mage_bus_label('wbtm_fare_text', __('Fare','bus-ticket-booking-with-seat-reservation')); ?></h6>
                <h6 class="mage_hidden_md"><?php mage_bus_label('wbtm_seats_available_text', __('Available','bus-ticket-booking-with-seat-reservation')); ?></h6>
                <h6><?php mage_bus_label('wbtm_view_text', __('Action','bus-ticket-booking-with-seat-reservation')); ?></h6>
            </div>
        </div>
    </div>
    <?php
}

function mage_get_bus_seat_plan_type() {
    $id = get_the_id();
    $seat_cols = get_post_meta($id, 'wbtm_seat_cols', true);
    $seats = get_post_meta($id, 'wbtm_bus_seats_info', true);
    if ($seat_cols && $seat_cols > 0 && is_array($seats) && sizeof($seats) > 0) {
        return (int)$seat_cols;
    } else {
        $current_plan = get_post_meta($id, 'seat_plan', true);
        $bus_meta = get_post_custom($id);
        if (array_key_exists('wbtm_seat_col', $bus_meta)) {
            $seat_col = $bus_meta['wbtm_seat_col'][0];
            $seat_col_arr = explode(",", $seat_col);
            $seat_column = count($seat_col_arr);
        } else {
            $seat_column = 0;
        }
        if ($current_plan) {
            $current_seat_plan = $current_plan;
        } else {
            if ($seat_column == 4) {
                $current_seat_plan = 'seat_plan_1';
            } else {
                $current_seat_plan = 'seat_plan_2';
            }
        }
        return $current_seat_plan;
    }
}

//bus off date check
function mage_bus_off_date_check($return) {
    $start_date = strtotime(get_post_meta(get_the_id(), 'wbtm_od_start', true));
    $end_date = strtotime(get_post_meta(get_the_id(), 'wbtm_od_end', true));
    $date = wbtm_convert_date_to_php(mage_bus_isset($return ? 'r_date' : 'j_date'));

    return (($start_date <= $date) && ($end_date >= $date)) ? false : true;
}

//bus off date check
function mage_bus_off_day_check($return) {
    $current_day = 'offday_' . strtolower(date('D', strtotime($return ? wbtm_convert_date_to_php(mage_bus_isset('r_date')) : wbtm_convert_date_to_php(mage_bus_isset('j_date')))));
    return get_post_meta(get_the_id(), $current_day, true) == 'yes' ? false : true;
}

//bus setting on date
function mage_bus_on_date_setting_check($return) {
    $mage_bus_on_dates = maybe_unserialize(get_post_meta(get_the_id(), 'wbtm_bus_on_dates', true));
    $date =  wbtm_convert_date_to_php(mage_bus_isset($return ? 'r_date' : 'j_date'));
    
    $mage_bus_on = array();
    if (!empty($mage_bus_on_dates) && is_array($mage_bus_on_dates)) {
        foreach ($mage_bus_on_dates as $value) {
            $mage_bus_on[] = $value['wbtm_on_date_name'];
        }
        return in_array($date, $mage_bus_on) ? true : false;
    } else {
        return false;
    }
}

//buffer time check
function mage_buffer_time_check($return) {
    $date = wbtm_convert_date_to_php(mage_bus_isset($return ? 'r_date' : 'j_date'));
    $buffer_time = mage_bus_setting_value('bus_buffer_time', 0);
    $start_time = strtotime($date . ' ' . date('H:i:s', strtotime(mage_bus_time($return, false))));
    $current_time = strtotime(current_time('Y-m-d H:i:s'));
    $dif = round(($start_time - $current_time) / 3600, 1);
    return ($dif >= $buffer_time) ? true : false;
}

//return bus time
function mage_bus_time($return, $dropping) {
    if ($dropping) {
        $start = mage_bus_isset($return ? 'bus_start_route' : 'bus_end_route');
    } else {
        $start = mage_bus_isset($return ? 'bus_end_route' : 'bus_start_route');
    }

    $meta_key = $dropping ? 'wbtm_bus_next_stops' : 'wbtm_bus_bp_stops';
    $array_key = $dropping ? 'wbtm_bus_next_stops_name' : 'wbtm_bus_bp_stops_name';
    $array_value = $dropping ? 'wbtm_bus_next_end_time' : 'wbtm_bus_bp_start_time';
    $array = maybe_unserialize(get_post_meta(get_the_id(), $meta_key, true));
    foreach ($array as $key => $val) {
        if ($val[$array_key] === $start) {
            return $val[$array_value];
        }
    }
    return false;
}

//return setting value
function mage_bus_setting_value($key, $default = null) {
    $settings = get_option('wbtm_bus_settings');
    $val = $settings[$key];
    return $val ? $val : $default;
}

//return check bus on off
function mage_bus_run_on_date($return) {
    if (((mage_bus_off_date_check($return) && mage_bus_off_day_check($return)) || mage_bus_on_date_setting_check($return)) && mage_buffer_time_check($return)) {
        return true;
    }
    return false;

}

//bus type return (ac/non ac)
function mage_bus_type() {
    return get_the_terms(get_the_id(), 'wbtm_bus_cat') ? get_the_terms(get_the_id(), 'wbtm_bus_cat')[0]->name : '';
}

// bus total seat
function mage_bus_total_seat() {
    $bus_id = get_the_id();
    $seat_plan_type = mage_get_bus_seat_plan_type();
    if ($seat_plan_type > 0) {
        $seats_rows = get_post_meta($bus_id, 'wbtm_bus_seats_info', true);
        $seat_col = get_post_meta($bus_id, 'wbtm_seat_cols', true);
        $total_seat = 0;
        foreach ($seats_rows as $seat) {
            for ($i = 1; $i <= $seat_col; $i++) {
                $seat_name = strtolower($seat["seat" . $i]);
                if ($seat_name != 'door' && $seat_name != 'wc' && $seat_name != '') {
                    $total_seat++;
                }
            }
        }
        $seats_dd = get_post_meta($bus_id, 'wbtm_bus_seats_info_dd', true);
        $seat_col_dd = get_post_meta($bus_id, 'wbtm_seat_rows_dd', true);
        if (is_array($seats_dd) && sizeof($seats_dd) > 0) {
            foreach ($seats_dd as $seat) {
                for ($i = 1; $i <= $seat_col_dd; $i++) {
                    $seat_name = $seat["dd_seat" . $i];
                    if ($seat_name != 'door' && $seat_name != 'wc' && $seat_name != '') {
                        $total_seat++;
                    }
                }
            }
        }
        return $total_seat;
    } else {
        $bus_meta = get_post_custom($bus_id);
        $seats_rows = explode(",", $bus_meta['wbtm_seat_row'][0]);
        $seat_col = $bus_meta['wbtm_seat_col'][0];
        $seat_col_arr = explode(",", $seat_col);
        return count($seats_rows) * count($seat_col_arr);
    }
}

//bus available seat
function mage_bus_available_seat($return) {
    return mage_bus_total_seat() - mage_bus_sold_seat($return);
}

//sold seat return
function mage_bus_sold_seat($return) {
    $bus_id = get_the_id();
    $date = $return ?  wbtm_convert_date_to_php(mage_bus_isset('r_date')) :  wbtm_convert_date_to_php(mage_bus_isset('j_date'));
    $args = array(
        'post_type' => 'wbtm_bus_booking',
        'posts_per_page' => -1,
        'meta_query' => array(
            'relation' => 'AND',
            array(
                'relation' => 'AND',
                array(
                    'key' => 'wbtm_journey_date',
                    'value' => $date,
                    'compare' => '='
                ),
                array(
                    'key' => 'wbtm_bus_id',
                    'value' => $bus_id,
                    'compare' => '='
                )
            ),
            array(
                'relation' => 'OR',
                array(
                    'key' => 'wbtm_status',
                    'value' => 1,
                    'compare' => '='
                ),
                array(
                    'key' => 'wbtm_status',
                    'value' => 2,
                    'compare' => '='
                )
            )
        )
    );
    $q = new WP_Query($args);
    return $q->post_count > 0 ? $q->post_count : 0;
}

//seat price
function mage_bus_seat_price($return) {
    $start = $return ? mage_bus_isset('bus_end_route') : mage_bus_isset('bus_start_route');
    $end = $return ? mage_bus_isset('bus_start_route') : mage_bus_isset('bus_end_route');
    $price_arr = maybe_unserialize(get_post_meta(get_the_id(), 'wbtm_bus_prices', true));
    foreach ($price_arr as $key => $val) {
        if ($val['wbtm_bus_bp_price_stop'] === $start && $val['wbtm_bus_dp_price_stop'] === $end) {
            return $val['wbtm_bus_price'];
        }
    }
    return false;
}

// check product in cart
function mage_bus_in_cart($seat_name) {
    $product_id = get_the_id();
    foreach (WC()->cart->get_cart() as $cart_item) {
        if ($cart_item['product_id'] === $product_id) {
            if ($seat_name) {
                foreach ($cart_item['wbtm_seats'] as $item) {
                    if ($item['wbtm_seat_name'] == $seat_name) {
                        return true;
                    }
                }
            } else {
                return true;
            }
        }
    }
    return false;
}

//find seat status
function mage_bus_seat_status($field_name, $return) {
    $date = $return ? wbtm_convert_date_to_php(mage_bus_isset('r_date')) :  wbtm_convert_date_to_php(mage_bus_isset('j_date'));
    $start = $return ? mage_bus_isset('bus_end_route') : mage_bus_isset('bus_start_route');
    $end = $return ? mage_bus_isset('bus_start_route') : mage_bus_isset('bus_end_route');
    $bus_id = get_the_id();
    $args = array(
        'post_type' => 'wbtm_bus_booking',
        'posts_per_page' => -1,
        'meta_query' => array(
            'relation' => 'AND',
            array(
                'relation' => 'AND',
                array(
                    'key' => 'wbtm_seat',
                    'value' => $field_name,
                    'compare' => '='
                ),
                array(
                    'key' => 'wbtm_journey_date',
                    'value' => $date,
                    'compare' => '='
                ),
                array(
                    'key' => 'wbtm_bus_id',
                    'value' => $bus_id,
                    'compare' => '='
                ),
            ),
            array(
                'relation' => 'OR',
                array(
                    'key' => 'wbtm_boarding_point',
                    'value' => $start,
                    'compare' => 'LIKE'
                ),
                array(
                    'key' => 'wbtm_next_stops',
                    'value' => $start,
                    'compare' => 'LIKE'
                ),
                array(
                    'key' => 'wbtm_next_stops',
                    'value' => $end,
                    'compare' => 'LIKE'
                ),
            )
        ),
    );
    $q = new WP_Query($args);
    $booking_id = $q->posts[0]->ID;
    return get_post_meta($booking_id, 'wbtm_status', true) ? get_post_meta($booking_id, 'wbtm_status', true) : 0;
}

//find seat Droping Point
function mage_bus_seat_droping_point($field_name, $point, $return) {
    $date = $return ? wbtm_convert_date_to_php(mage_bus_isset('r_date')) :  wbtm_convert_date_to_php(mage_bus_isset('j_date'));
    $start = $return ? mage_bus_isset('bus_end_route') : mage_bus_isset('bus_start_route');
    $end = $return ? mage_bus_isset('bus_start_route') : mage_bus_isset('bus_end_route');
    $bus_id = get_the_id();
    $args = array(
        'post_type' => 'wbtm_bus_booking',
        'posts_per_page' => -1,
        'meta_query' => array(
            'relation' => 'AND',
            array(
                'relation' => 'AND',
                array(
                    'key' => 'wbtm_seat',
                    'value' => $field_name,
                    'compare' => '='
                ),
                array(
                    'key' => 'wbtm_journey_date',
                    'value' => $date,
                    'compare' => '='
                ),
                array(
                    'key' => 'wbtm_bus_id',
                    'value' => $bus_id,
                    'compare' => '='
                ),
            ),
            array(
                'relation' => 'OR',
                array(
                    'key' => 'wbtm_boarding_point',
                    'value' => $start,
                    'compare' => 'LIKE'
                ),
                array(
                    'key' => 'wbtm_next_stops',
                    'value' => $start,
                    'compare' => 'LIKE'
                ),
                array(
                    'key' => 'wbtm_next_stops',
                    'value' => $end,
                    'compare' => 'LIKE'
                ),
            )
        ),
    );
    $q = new WP_Query($args);
    $booking_id = $q->posts[0]->ID;
    return get_post_meta($booking_id, $point, true) ? get_post_meta($booking_id, $point, true) : 0;
}

// Return Array
function mage_bus_get_all_stopages($post_id) {
    $total_stopage = 0;

    $all_stopage = get_post_meta($post_id, 'wbtm_bus_prices', true);

    if($all_stopage) {

        $input = (is_array($all_stopage) ? $all_stopage : unserialize($all_stopage));
        
        $input = array_column($input, 'wbtm_bus_bp_price_stop');
        $all_stopage= array_unique($input);
        $all_stopage= array_values($all_stopage);

        return $all_stopage;
    }

    return;
}