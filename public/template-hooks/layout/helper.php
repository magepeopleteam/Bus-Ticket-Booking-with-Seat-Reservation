<?php
function mage_bus_isset($parameter) {
    return isset($_GET[$parameter]) ? strip_tags($_GET[$parameter]) : false;
}

function mage_bus_translate($parameter) {
    return isset($_GET[$parameter]) ? strip_tags($_GET[$parameter]) : false;
}

function mage_bus_text($text) {
    _e($text, 'bus-ticket-booking-with-seat-reservation');
}

function mage_bus_label($var, $text) {
    global $wbtmmain;
    echo $wbtmmain->bus_get_option($var, 'label_setting_sec') ? $wbtmmain->bus_get_option($var, 'label_setting_sec') : $text;
}

// check search day is off?
function mage_check_search_day_off($id, $j_date) {

    $db_day_prefix = 'offday_';
    if( $j_date ) {
        $j_date_day = strtolower(date('D', strtotime($j_date)));
        $get_day = get_post_meta( $id, $db_day_prefix.$j_date_day, true );
        $get_day = ($get_day != null) ? strtolower($get_day) : null;

        if($get_day == 'yes') {
            return true;
        } else {
            return false;
        }
    } else {
        return null;
    }
    
}

// convert date formate
// function mage_convert_date_format($date, $format) {
//     $wp_date_format = get_option('date_format');
//     if(strpos($wp_date_format, ' ') || strpos($wp_date_format, ',')) {
//         $wp_date_format = 'Y-m-d';
//     } else {
//         $wp_date_format = str_replace('/', '-', $wp_date_format);
//     }
    
//     $myDateTime = date_create_from_format($wp_date_format, $date);
//     $final = date_format($myDateTime, 'Y-m-d');
//     return $final;
// }

// convert date formate
function mage_convert_date_format($date, $format) {
    $setting_format = get_option('date_format');

    if(!$date) {
        return null;
    }
    
    if( preg_match('/\s/',$setting_format) ) {

        return date($format, strtotime($date));

    } else {
        $setting_format__dashed = str_replace('/', '-', $setting_format);
        $setting_format__dashed = str_replace('.', '-', $setting_format__dashed);

        $dash_date = str_replace('/', '-', $date);
        $dash_date = str_replace('.', '-', $dash_date);
        // echo $setting_format__dashed.'<br>';
        // echo $dash_date.'<br>';
        $date_f = DateTime::createFromFormat($setting_format__dashed , $dash_date);
        if($date_f) {
            $res = $date_f->format($format);
            return $res;
        } else {
            return null;
        }
        
    }
}

// check bus on Date
function mage_bus_on_date($id, $j_date) {
    if($j_date) {
        $is_on_date = 'no';
        $on_dates = get_post_meta( $id, 'wbtm_bus_on_dates', true );
        if($on_dates) {
            $is_on_date = 'has';
            $on_dates = unserialize($on_dates);
            $on_dates = array_column($on_dates, 'wbtm_on_date_name');

            foreach($on_dates as $date) {
                $date = date('Y-m-d', strtotime($date));
                if( $j_date == $date ) {
                    $is_on_date = 'yes';
                break;
                }
            }

        }

        return $is_on_date;

    } else {
        return null;
    }
}

function mage_route_list($single_bus, $start_route) {
    echo '<div class="mage_input_select_list"><ul>';
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
    echo '</ul></div>';
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
        <div class="mage_bus_img flexCenter"><h6><?php mage_bus_label('wbtm_image_text', __('Image', 'bus-ticket-booking-with-seat-reservation'));        
        ?></h6></div>
        <div class="mage_bus_info flexEqual flexCenter">
            <div class="flexEqual">
                <h6><?php mage_bus_label('wbtm_bus_name_text', __('Bus', 'bus-ticket-booking-with-seat-reservation')); ?></h6>
                <h6 class="mage_hidden_xxs"><?php  mage_bus_label('wbtm_schedule_text', __('Schedule', 'bus-ticket-booking-with-seat-reservation'));  ?></h6>
            </div>
            <div class="flexEqual flexCenter textCenter">
                <h6 class="mage_hidden_xxs"><?php mage_bus_label('wbtm_type_text', __('Coach Type', 'bus-ticket-booking-with-seat-reservation')); ?></h6>
                <h6 class="mage_hidden_xs"><?php mage_bus_label('wbtm_fare_text', __('Fare', 'bus-ticket-booking-with-seat-reservation')); ?></h6>
                <h6 class="mage_hidden_md"><?php mage_bus_label('wbtm_seats_available_text', __('Available', 'bus-ticket-booking-with-seat-reservation')); ?></h6>
                <h6><?php mage_bus_label('wbtm_view_text', __('Action', 'bus-ticket-booking-with-seat-reservation')); ?></h6>
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
    $date = wbtm_convert_date_to_php(mage_bus_isset($return ? 'r_date' : 'j_date'));

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
                    $seat_name = isset($seat["dd_seat" . $i]) ? $seat["dd_seat" . $i] : '';
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
    $date = $return ? wbtm_convert_date_to_php(mage_bus_isset('r_date')) : wbtm_convert_date_to_php(mage_bus_isset('j_date'));
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
function mage_bus_seat_price($bus_id,$start, $end, $dd, $seat_type = null) {
    $price_arr = maybe_unserialize(get_post_meta($bus_id, 'wbtm_bus_prices', true));
    
    // Check this route has price if not, return
    if(!empty($price_arr) && is_array($price_arr)) {
        // $price_arr = array_values($price_arr);
        foreach($price_arr as $value) {
            if( ($value['wbtm_bus_bp_price_stop'] == $start) && ($value['wbtm_bus_dp_price_stop'] == $end) && ($value['wbtm_bus_price'] == 0 || $value['wbtm_bus_price'] == null) ) {
                return false;
            }
        }
    } else {
        return false;
    }
    
    $seat_dd_increase = (int)get_post_meta($bus_id, 'wbtm_seat_dd_price_parcent', true);
    $dd_price_increase = ($dd && $seat_dd_increase) ? $seat_dd_increase : 0;
    foreach ($price_arr as $key => $val) {
        if ($val['wbtm_bus_bp_price_stop'] === $start && $val['wbtm_bus_dp_price_stop'] === $end) {
            if (1 == $seat_type) {
                $price = $val['wbtm_bus_child_price'] + ($val['wbtm_bus_child_price'] * $dd_price_increase / 100);
            } elseif (2 == $seat_type) {
                $price = $val['wbtm_bus_infant_price'] + ($val['wbtm_bus_infant_price'] * $dd_price_increase / 100);
            } elseif (3 == $seat_type) {
                $price = $val['wbtm_bus_special_price'] + ($val['wbtm_bus_special_price'] * $dd_price_increase / 100);
            } else {
                $price = $val['wbtm_bus_price'] + ($val['wbtm_bus_price'] * $dd_price_increase / 100);
            }
            return $price;
        }
    }
    return false;
}

function mage_bus_passenger_type($return, $dd) {
    $id = get_the_id();
    $start = $return ? mage_bus_isset('bus_end_route') : mage_bus_isset('bus_start_route');
    $end = $return ? mage_bus_isset('bus_start_route') : mage_bus_isset('bus_end_route');
    $price_arr = maybe_unserialize(get_post_meta($id, 'wbtm_bus_prices', true));
    $seat_panel_settings = get_option('wbtm_bus_settings');
    $adult_label = $seat_panel_settings['wbtm_seat_type_adult_label'];
    $child_label = $seat_panel_settings['wbtm_seat_type_child_label'];
    $infant_label = $seat_panel_settings['wbtm_seat_type_infant_label'];
    $special_label = $seat_panel_settings['wbtm_seat_type_special_label'];
    foreach ($price_arr as $key => $val) {
        if ($val['wbtm_bus_bp_price_stop'] === $start && $val['wbtm_bus_dp_price_stop'] === $end) {
            if (mage_bus_multiple_passenger_type_check($id, $start, $end)) {
                $dd_price_increase = 0;
                if ($dd) {
                    $seat_dd_increase = (int)get_post_meta($id, 'wbtm_seat_dd_price_parcent', true);
                    $dd_price_increase = $seat_dd_increase ? $seat_dd_increase : 0;
                }
                ?>
                <div class="passenger_type_list">
                    <ul>
                        <?php
                        if ($val['wbtm_bus_price'] > 0) {
                            $price = $val['wbtm_bus_price'] + ($val['wbtm_bus_price'] * $dd_price_increase / 100);
                            echo '<li data-seat-price="' . $price . '" data-seat-type="0" data-seat-label="'. $adult_label .'">' . $adult_label.' ' . wc_price($price) . __('/Seat', 'bus-ticket-booking-with-seat-reservation') . '</li>';
                        }
                        if ($val['wbtm_bus_child_price'] > 0) {
                            $price = $val['wbtm_bus_child_price'] + ($val['wbtm_bus_child_price'] * $dd_price_increase / 100);
                            echo '<li data-seat-price="' . $price . '" data-seat-type="1" data-seat-label="'. $child_label .'">' . $child_label.' ' . wc_price($price) . __('/Seat', 'bus-ticket-booking-with-seat-reservation') . '</li>';
                        }
                        if ($val['wbtm_bus_infant_price'] > 0) {
                            $price = $val['wbtm_bus_infant_price'] + ($val['wbtm_bus_infant_price'] * $dd_price_increase / 100);
                            echo '<li data-seat-price="' . $price . '" data-seat-type="2" data-seat-label="'. $infant_label .'">' . $infant_label .' '. wc_price($price) . __('/Seat', 'bus-ticket-booking-with-seat-reservation') . '</li>';
                        }
                        if ($val['wbtm_bus_special_price'] > 0) {
                            $price = $val['wbtm_bus_special_price'] + ($val['wbtm_bus_special_price'] * $dd_price_increase / 100);
                            echo '<li data-seat-price="' . $price . '" data-seat-type="3" data-seat-label="'. $special_label .'">' . $special_label.' ' . wc_price($price) . __('/Seat', 'bus-ticket-booking-with-seat-reservation') . '</li>';
                        }
                        ?>
                    </ul>
                </div>
                <?php
            }
        }
    }
}

function mage_bus_passenger_type_admin($return, $dd) {
    global $wbtmmain;
    $id = get_the_id();
    $start = $return ? mage_bus_isset('bus_end_route') : mage_bus_isset('bus_start_route');
    $end = $return ? mage_bus_isset('bus_start_route') : mage_bus_isset('bus_end_route');
    $price_arr = maybe_unserialize(get_post_meta($id, 'wbtm_bus_prices', true));
    $seat_panel_settings = get_option('wbtm_bus_settings');
    $adult_label = $seat_panel_settings['wbtm_seat_type_adult_label'];
    $child_label = $seat_panel_settings['wbtm_seat_type_child_label'];
    $infant_label = $seat_panel_settings['wbtm_seat_type_infant_label'];
    $special_label = $seat_panel_settings['wbtm_seat_type_special_label'];
    $rdate               = isset( $_GET['j_date'] ) ? sanitize_text_field($_GET['j_date']) : date('Y-m-d');
    $uid = get_the_id().$wbtmmain->wbtm_make_id($rdate);
    foreach ($price_arr as $key => $val) {
        if ($val['wbtm_bus_bp_price_stop'] === $start && $val['wbtm_bus_dp_price_stop'] === $end) {
            if (mage_bus_multiple_passenger_type_check($id, $start, $end)) {
                $dd_price_increase = 0;
                if ($dd) {
                    $seat_dd_increase = (int)get_post_meta($id, 'wbtm_seat_dd_price_parcent', true);
                    $dd_price_increase = $seat_dd_increase ? $seat_dd_increase : 0;
                }
                ?>
                <div class="<?php echo 'admin_'.$uid; ?> admin_passenger_type_list">
                    <ul>
                        <?php
                        if ($val['wbtm_bus_price'] > 0) {
                            $price = $val['wbtm_bus_price'] + ( $dd_price_increase != 0 ? ($val['wbtm_bus_price'] * $dd_price_increase / 100) : 0 );
                            echo '<li data-seat-price="' . $price . '" data-seat-type="0" data-seat-label="'. $adult_label .'">' . $adult_label.' ' . wc_price($price) . __('/ Seat', 'bus-ticket-booking-with-seat-reservation') . '</li>';
                        }
                        if ($val['wbtm_bus_child_price'] > 0) {
                            $price = $val['wbtm_bus_child_price'] + ( $dd_price_increase != 0 ? ($val['wbtm_bus_child_price'] * $dd_price_increase / 100) : 0 );
                            echo '<li data-seat-price="' . $price . '" data-seat-type="1" data-seat-label="'. $child_label .'">' . $child_label.' ' . wc_price($price) . __('/ Seat', 'bus-ticket-booking-with-seat-reservation') . '</li>';
                        }
                        if ($val['wbtm_bus_infant_price'] > 0) {
                            $price = $val['wbtm_bus_infant_price'] + ( $dd_price_increase != 0 ? ($val['wbtm_bus_infant_price'] * $dd_price_increase / 100) : 0 );
                            echo '<li data-seat-price="' . $price . '" data-seat-type="2" data-seat-label="'. $infant_label .'">' . $infant_label .' '. wc_price($price) . __('/ Seat', 'bus-ticket-booking-with-seat-reservation') . '</li>';
                        }
                        if ($val['wbtm_bus_special_price'] > 0) {
                            $price = $val['wbtm_bus_special_price'] + ( $dd_price_increase != 0 ? ($val['wbtm_bus_special_price'] * $dd_price_increase / 100) : 0 );
                            echo '<li data-seat-price="' . $price . '" data-seat-type="3" data-seat-label="'. $special_label .'">' . $special_label.' ' . wc_price($price) . __('/ Seat', 'bus-ticket-booking-with-seat-reservation') . '</li>';
                        }
                        ?>
                    </ul>
                </div>
                <?php
            }
        }
    }
}

function mage_bus_multiple_passenger_type_check($id, $start, $end) {
    $price_arr = maybe_unserialize(get_post_meta($id, 'wbtm_bus_prices', true));
    foreach ($price_arr as $key => $val) {
        if ($val['wbtm_bus_bp_price_stop'] === $start && $val['wbtm_bus_dp_price_stop'] === $end) {
            if ($val['wbtm_bus_price'] && ($val['wbtm_bus_child_price'] || $val['wbtm_bus_infant_price'] || $val['wbtm_bus_special_price'])) {
                return true;
            }
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
    $date = $return ? wbtm_convert_date_to_php(mage_bus_isset('r_date')) : wbtm_convert_date_to_php(mage_bus_isset('j_date'));
    $start = $return ? mage_bus_isset('bus_end_route') : mage_bus_isset('bus_start_route');
    $end = $return ? mage_bus_isset('bus_start_route') : mage_bus_isset('bus_end_route');
    $bus_id = get_the_id();
    $args = array(
        'post_type' => 'wbtm_bus_booking',
        'posts_per_page' => 1,
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
    $booking_id = ( isset($q->posts[0]) ? $q->posts[0]->ID : null );
    // return $booking_id;
    return get_post_meta($booking_id, 'wbtm_status', true) ? get_post_meta($booking_id, 'wbtm_status', true) : 0;
}

// Get seat Booking Data
function get_seat_booking_data($seat_name, $return) {
    if(!$seat_name) {
        return false;
    }
    $date = $return ? wbtm_convert_date_to_php(mage_bus_isset('r_date')) : wbtm_convert_date_to_php(mage_bus_isset('j_date'));
    $start = $return ? mage_bus_isset('bus_end_route') : mage_bus_isset('bus_start_route');
    $end = $return ? mage_bus_isset('bus_start_route') : mage_bus_isset('bus_end_route');
    $bus_id = get_the_id();
    $args = array(
        'post_type' => 'wbtm_bus_booking',
        'posts_per_page' => 1,
        'meta_query' => array(
            'relation' => 'AND',
            array(
                'relation' => 'AND',
                array(
                    'key' => 'wbtm_seat',
                    'value' => $seat_name,
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
    $booking_id = ( isset($q->posts[0]) ? $q->posts[0]->ID : null );
    // return $booking_id;
    if($booking_id) {
        $data = array(
            'status'            => get_post_meta($booking_id, 'wbtm_status', true),
            'boarding_point'    => get_post_meta($booking_id, 'wbtm_boarding_point', true),
            'dropping_point'    => get_post_meta($booking_id, 'wbtm_droping_point', true),
        );
        return $data;
    } else {
        return false;
    }
}

//find seat Droping Point
function mage_bus_seat_droping_point($field_name, $point, $return) {
    $date = $return ? wbtm_convert_date_to_php(mage_bus_isset('r_date')) : wbtm_convert_date_to_php(mage_bus_isset('j_date'));
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
    // $booking_id = $q->posts[0]->ID;
    $booking_id = ( isset($q->posts[0]) ? $q->posts[0]->ID : null );
    return get_post_meta($booking_id, $point, true) ? get_post_meta($booking_id, $point, true) : 0;
}

// Return Array
function mage_bus_get_all_stopages($post_id) {
    $total_stopage = 0;

    $all_stopage = get_post_meta($post_id, 'wbtm_bus_prices', true);

    if ($all_stopage) {

        $input = (is_array($all_stopage) ? $all_stopage : unserialize($all_stopage));

        $input = array_column($input, 'wbtm_bus_bp_price_stop');
        $all_stopage = array_unique($input);
        $all_stopage = array_values($all_stopage);

        return $all_stopage;
    }

    return;
}

function mage_bus_get_option($option, $section, $default = '') {
    $options = get_option($section);

    if (isset($options[$option])) {
        return $options[$option];
    }

    return $default;
}