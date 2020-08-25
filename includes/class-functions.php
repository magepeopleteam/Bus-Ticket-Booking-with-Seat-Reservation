<?php
if (!defined('ABSPATH')) {
    die;
} // Cannot access pages directly.

/**
 * @since      1.0.0
 * @package    WBTM_Plugin
 * @subpackage WBTM_Plugin/includes
 * @author     MagePeople team <magepeopleteam@gmail.com>
 */
class WBTM_Plugin_Functions
{

    protected $loader;
    protected $plugin_name;
    protected $version;

    public function __construct()
    {
        $this->add_hooks();
        add_filter('mage_wc_products', array($this, 'add_cpt_to_wc_product'), 10, 1);
    }

    private function add_hooks()
    {
        add_action('plugins_loaded', array($this, 'load_plugin_textdomain'));
        add_action('wp_ajax_wbtm_seat_plan', array($this, 'wbtm_seat_plan'));
        add_action('wp_ajax_nopriv_wbtm_seat_plan', array($this, 'wbtm_seat_plan'));
        add_action('woocommerce_order_status_changed', array($this, 'wbtm_bus_ticket_seat_management'), 10, 4);
        add_action('wp_ajax_wbtm_seat_plan_dd', array($this, 'wbtm_seat_plan_dd'));
        add_action('wp_ajax_nopriv_wbtm_seat_plan_dd', array($this, 'wbtm_seat_plan_dd'));
        add_action('woocommerce_checkout_order_processed', array($this, 'bus_order_processed'), 10);
    }

    public function load_plugin_textdomain()
    {
        load_plugin_textdomain(
            'bus-ticket-booking-with-seat-reservation',
            false,
            dirname(dirname(plugin_basename(__FILE__))) . '/languages/'
        );
    }

    public function wbtm_get_driver_position($current_plan)
    {
        ?>
        <select name="driver_seat_position">
            <option <?php if ($current_plan == 'driver_left') {
                echo 'Selected';
            } ?> value="driver_left"><?php _e('Left', 'bus-ticket-booking-with-seat-reservation'); ?></option>
            <option <?php if ($current_plan == 'driver_right') {
                echo 'Selected';
            } ?> value="driver_right"><?php _e('Right', 'bus-ticket-booking-with-seat-reservation'); ?></option>
            <?php do_action('wbtm_after_driver_position_dd'); ?>
        </select>
        <?php
    }


    public function wbtm_seat_plan()
    {
        $seat_col = strip_tags($_POST['seat_col']);
        $seat_row = strip_tags($_POST['seat_row']);
        ?>

        <div>
            <script type="text/javascript">
                jQuery(document).ready(function ($) {
                    $('#add-seat-row').on('click', function () {
                        var row = $('.empty-row-seat.screen-reader-text').clone(true);
                        row.removeClass('empty-row-seat screen-reader-text');
                        row.insertBefore('#repeatable-fieldset-seat-one tbody>tr:last');
                        var qtt = parseInt($('#seat_rows').val(), 10);
                        $('#seat_rows').val(qtt + 1);
                        return false;
                    });
                    $('.remove-seat-row').on('click', function () {
                        $(this).parents('tr').remove();
                        var qtt = parseInt($('#seat_rows').val(), 10);
                        $('#seat_rows').val(qtt - 1);
                        return false;
                    });
                });
            </script>
            <table id="repeatable-fieldset-seat-one" width="100%">
                <tbody>
                <?php
                for ($x = 1; $x <= $seat_row; $x++) {
                    ?>
                    <tr>
                        <?php
                        for ($row = 1; $row <= $seat_col; $row++) {
                            $seat_type_name = "seat_types" . $row;
                            ?>
                            <td align="center">
                                <input type="text" value="" name="seat<?php echo $row; ?>[]" class="text">
                                <?php wbtm_get_seat_type_list($seat_type_name); ?>
                            </td>
                        <?php } ?>
                        <td align="center"><a class="button remove-seat-row"
                                              href="#"><?php _e('Remove', 'bus-ticket-booking-with-seat-reservation'); ?></a>
                            <input type="hidden" name="bus_seat_panels[]">
                        </td>
                    </tr>
                    <?php
                }
                ?>
                <!-- empty hidden one for jQuery -->
                <tr class="empty-row-seat screen-reader-text">
                    <?php
                    for ($row = 1; $row <= $seat_col; $row++) {
                        $seat_type_name = "seat_types" . $row;
                        ?>
                        <td align="center">
                            <input type="text" value="" name="seat<?php echo $row; ?>[]" class="text">
                            <?php wbtm_get_seat_type_list($seat_type_name); ?>


                        </td>
                    <?php } ?>
                    <td align="center"><a class="button remove-seat-row"
                                          href="#"><?php _e('Remove', 'bus-ticket-booking-with-seat-reservation'); ?></a><input
                                type="hidden" name="bus_seat_panels[]"></td>
                </tr>
                </tbody>
            </table>
            <p><a id="add-seat-row" class="add-seat-row-btn"
                  href="#"><i class="fas fa-plus"></i></a></p>
        </div>
        <?php
        die();
    }

    public function wbtm_seat_plan_dd()
    {
        $seat_col = strip_tags($_POST['seat_col']);
        $seat_row = strip_tags($_POST['seat_row']);
        ?>

        <div>
            <script type="text/javascript">
                jQuery(document).ready(function ($) {
                    $('#add-seat-row-dd').on('click', function () {
                        var row = $('.empty-row-seat-dd.screen-reader-text').clone(true);
                        row.removeClass('empty-row-seat-dd screen-reader-text');
                        row.insertBefore('#repeatable-fieldset-seat-one-dd tbody>tr:last');
                        var qtt = parseInt($('#seat_rows_dd').val(), 10);
                        $('#seat_rows_dd').val(qtt + 1);
                        return false;
                    });
                    $('.remove-seat-row-dd').on('click', function () {
                        $(this).parents('tr').remove();
                        var qtt = parseInt($('#seat_rows_dd').val(), 10);
                        $('#seat_rows_dd').val(qtt - 1);
                        return false;
                    });
                });
            </script>
            <table id="repeatable-fieldset-seat-one-dd" width="100%">
                <tbody>
                <?php
                for ($x = 1; $x <= $seat_row; $x++) {
                    ?>
                    <tr>
                        <?php
                        for ($row = 1; $row <= $seat_col; $row++) {
                            ?>
                            <td align="center"><input type="text" value="" name="dd_seat<?php echo $row; ?>[]"
                                                      class="text"></td>
                        <?php } ?>
                        <td align="center"><a class="button remove-seat-row-dd"
                                              href="#"><?php _e('Remove', 'bus-ticket-booking-with-seat-reservation'); ?></a>
                            <input type="hidden" name="bus_seat_panels_dd[]">
                        </td>
                    </tr>
                    <?php
                }
                ?>
                <!-- empty hidden one for jQuery -->
                <tr class="empty-row-seat-dd screen-reader-text">
                    <?php
                    for ($row = 1; $row <= $seat_col; $row++) {
                        ?>
                        <td align="center"><input type="text" value="" name="dd_seat<?php echo $row; ?>[]" class="text">
                        </td>
                    <?php } ?>
                    <td align="center"><a class="button remove-seat-row-dd"
                                          href="#"><?php _e('Remove', 'bus-ticket-booking-with-seat-reservation'); ?></a><input
                                type="hidden" name="bus_seat_panels_dd[]"></td>
                </tr>
                </tbody>
            </table>
            <p><a id="add-seat-row-dd" class="add-seat-row-btn" href="#"><i class="fas fa-plus"></i></a></p>
        </div>
        <?php
        die();
    }

// Get Bus Settings Optiins Data
    public function bus_get_option($meta_key, $setting_name = '', $default = null)
    {
        $get_settings = get_option('wbtm_bus_settings');
        $get_val = isset($get_settings[$meta_key]) ? $get_settings[$meta_key] : '';
        $output = $get_val ? $get_val : $default;
        return $output;
    }

    public function wbtm_bus_seat_plan_dd($start, $date)
    {

        wbtm_seat_global($start, $date, 'dd');

    }

// Getting all the bus stops name from a stop name
    public function wbtm_get_all_stops_after_this($bus_id, $val, $end)
    {
        //echo $end;


        $end_s = array($val);
        //Getting All boarding points
        $boarding_points = maybe_unserialize(get_post_meta($bus_id, 'wbtm_bus_bp_stops', true));
        $all_bp_stops = array();
        foreach ($boarding_points as $_boarding_points) {
            $all_bp_stops[] = $_boarding_points['wbtm_bus_bp_stops_name'];
        }
        $pos2 = array_search($end, $all_bp_stops);
        // if (sizeof($pos2) > 0) {
        if ($pos2 != '') {
            unset($all_bp_stops[$pos2]);
        }
        // print_r($all_bp_stops);
        // echo '<br/>';
        //Gettings Stops Name Before Droping Stops
        $start_stops = maybe_unserialize(get_post_meta($bus_id, 'wbtm_bus_next_stops', true));
        $all_stops = array();
        if (is_array($start_stops) && sizeof($start_stops) > 0) {
            foreach ($start_stops as $_start_stops) {
                $all_stops[] = $_start_stops['wbtm_bus_next_stops_name'];
            }
        }
        $full_array = $all_stops;
        $mkey = array_search($end, $full_array);
        $newarray = array_slice($full_array, $mkey, count($full_array), true);
        // return $newarray;
        $myArrayInit = $full_array; //<-- Your actual array
        $offsetKey = $mkey; //<--- The offset you need to grab
        //Lets do the code....
        $n = array_keys($myArrayInit); //<---- Grab all the keys of your actual array and put in another array
        $count = array_search($offsetKey, $n); //<--- Returns the position of the offset from this array using search
        $new_arr = array_slice($myArrayInit, 0, $count + 1, true);//<--- Slice it with the 0 index as start and position+1 as the length parameter.
        $pos2 = array_search($end, $new_arr);
        // if (sizeof($pos2) > 0) {
        if ($pos2 != '') {
            unset($new_arr[$pos2]);
        }

        $res = array_merge($all_bp_stops, $new_arr);
        return $res;

        // print_r();
    }

// Adding Custom Post to WC Prodct Data Filter.
    public function add_cpt_to_wc_product($data)
    {
        $WBTM_cpt = array('wbtm_bus');
        return array_merge($data, $WBTM_cpt);
    }

// make page id
    public function wbtm_make_id($val)
    {
        return str_replace("-", "", $val);

    }

// create bus js 
    public function wbtm_seat_booking_js($id, $fare)
    {
        $fare = isset($fare) ? $fare : 0;
        $upper_price_percent = (int)get_post_meta(get_the_ID(), 'wbtm_seat_dd_price_parcent', true);
        ?>
        <script>
            jQuery(document).ready(function ($) {

                $('#bus-booking-btn<?php echo $id; ?>').hide();

                $(document).on('remove_selection<?php echo $id; ?>', function (e, seatNumber) {

                    $('#selected_list<?php echo $id; ?>_' + seatNumber).remove();
                    $('#seat<?php echo $id; ?>_' + seatNumber).removeClass('seat<?php echo $id; ?>_booked');
                    $('#seat<?php echo $id; ?>_' + seatNumber).removeClass('seat_booked');

                    wbt_calculate_total();
                    wbt_update_passenger_form();
                })

                $(document).on('click', '.seat<?php echo $id; ?>_booked', function () {
                    // $( document.body ).trigger( 'remove_selection<?php echo $id; ?>', [ $(this).data("seat") ] );
                })

                $(document).on('click', '.remove-seat-row<?php echo $id; ?>', function () {
                    $(document.body).trigger('remove_selection<?php echo $id; ?>', [$(this).data("seat")]);
                });

                jQuery('#start_stops<?php echo $id; ?>').on('change', function () {
                    var start_time = jQuery(this).find(':selected').data('start');
                    jQuery('#user_start_time<?php echo $id; ?>').val(start_time);
                    ;
                });


                jQuery(".seat<?php echo $id; ?>_blank").on('click', function () {

                    if (jQuery(this).hasClass('seat<?php echo $id; ?>_booked')) {

                        jQuery(document.body).trigger('remove_selection<?php echo $id; ?>', [jQuery(this).data("seat")]);
                        return;
                    }

                    jQuery(this).addClass('seat<?php echo $id; ?>_booked');
                    jQuery(this).addClass('seat_booked');

                    var seat<?php echo $id; ?>_name = jQuery(this).data("seat");
                    var seat<?php echo $id; ?>_class = jQuery(this).data("sclass");
                    
                    var seat_pos = jQuery(this).data("seat-pos");
                    if( seat_pos == 'upper' ) {
                        var fare = <?php echo $fare + ( $upper_price_percent != 0 ? (($fare * $upper_price_percent) / 100) : 0 ); ?>;
                    } else {
                        var fare = <?php echo $fare; ?>;
                    }
                    
                    var foo = "<tr class='seat_selected_price' id='selected_list<?php echo $id; ?>_" + seat<?php echo $id; ?>_name + "'><td align=center><input type='hidden' name='passenger_label[]' value='Adult'/>" + "<input type='hidden' name='passenger_type[]' value='0'/>" + "<input type='hidden' name='seat_name[]' value='" + seat<?php echo $id; ?>_name + "'/>" + seat<?php echo $id; ?>_name + "</td><td align=center>Adult</td><td align=center><input class='seat_fare' type='hidden' name='seat_fare[]' value=" + fare + "><input type='hidden' name='bus_fare<?php echo $id; ?>' value=" + fare + "><?php echo get_woocommerce_currency_symbol(); ?>" + fare + "</td><td align=center><a class='button remove-seat-row<?php echo $id; ?>' data-seat='" + seat<?php echo $id; ?>_name + "'>X</a></td></tr>";

                    jQuery(foo).insertAfter('.list_head<?php echo $id; ?>');

                    var total_fare = jQuery('.bus_fare<?php echo $id; ?>').val();
                    var rowCount = jQuery('.selected-seat-list<?php echo $id; ?> tr').length - 2;
//                  var totalFare = (rowCount * fare);
//                     
                    var totalFare = 0;
                    jQuery('.selected-seat-table tbody tr').each(function() {
                        if( $(this).hasClass('seat_selected_price') ) {
                            totalFare = totalFare + parseFloat($(this).find('.seat_fare').val());
                        }
                    });

                    jQuery('#total_seat<?php echo $id; ?>_booked').html(rowCount);
                    jQuery('#tq<?php echo $id; ?>').val(rowCount);
                    jQuery('#totalFare<?php echo $id; ?>').html("<?php echo get_woocommerce_currency_symbol(); ?>" + totalFare);
                    jQuery('#tfi<?php echo $id; ?>').val("<?php echo get_woocommerce_currency_symbol(); ?>" + totalFare);
                    if (totalFare > 0) {
                        jQuery('#bus-booking-btn<?php echo $id; ?>').show();

                    }
                    // alert(totalFare);
                    wbt_update_passenger_form();
                });

                // *******Admin Ticket Purchase*******
                
                jQuery('.admin_<?php echo $id; ?> li').on('click', function() {
                    var $this = jQuery(this);
                    var parent = $this.parents('.admin_<?php echo $id; ?>').siblings('.seat<?php echo $id; ?>_blank');
                    var price = $this.attr('data-seat-price');
                    var label = $this.attr('data-seat-label');
                    var passenger_type = $this.attr('data-seat-type');

                    if (parent.hasClass('seat<?php echo $id; ?>_booked')) {
                        jQuery(document.body).trigger('remove_selection<?php echo $id; ?>', [parent.data("seat")]);
                    }

                    parent.addClass('seat<?php echo $id; ?>_booked');
                    parent.addClass('seat_booked');

                    console.log('hkdkf');
                    var seat<?php echo $id; ?>_name = parent.data("seat");
                    var seat<?php echo $id; ?>_class = parent.data("sclass");
                    var fare = price;
                    var foo = "<tr class='seat_selected_price' id='selected_list<?php echo $id; ?>_" + seat<?php echo $id; ?>_name + "'><td align=center><input type='hidden' name='passenger_label[]' value='" + label + "'/>" + "<input type='hidden' name='passenger_type[]' value='" + passenger_type + "'/>" + "<input type='hidden' name='seat_name[]' value='" + seat<?php echo $id; ?>_name + "'/>" + seat<?php echo $id; ?>_name + "</td><td align=center>" + label + "</td><td align=center><input class='seat_fare' type='hidden' name='seat_fare[]' value=" + fare + "><input type='hidden' name='bus_fare<?php echo $id; ?>' value=" + fare + "><?php echo get_woocommerce_currency_symbol(); ?>" + fare + "</td><td align=center><a class='button remove-seat-row<?php echo $id; ?>' data-seat='" + seat<?php echo $id; ?>_name + "'>X</a></td></tr>";

                    jQuery(foo).insertAfter('.list_head<?php echo $id; ?>');

                    var total_fare = jQuery('.bus_fare<?php echo $id; ?>').val();
                    var rowCount = jQuery('.selected-seat-list<?php echo $id; ?> tr').length - 2;

                    var totalFare = 0;
                    jQuery('.selected-seat-table tbody tr').each(function() {
                        // totalFare = totalFare + parseFloat($(this).find('.seat_selected_price').val());
                        if( $(this).hasClass('seat_selected_price') ) {
                            totalFare = totalFare + parseFloat($(this).find('.seat_fare').val());
                        }
                    });
                    
                    jQuery('#total_seat<?php echo $id; ?>_booked').html(rowCount);
                    jQuery('#tq<?php echo $id; ?>').val(rowCount);
                    jQuery('#totalFare<?php echo $id; ?>').html("<?php echo get_woocommerce_currency_symbol(); ?>" + totalFare.toFixed(2));
                    jQuery('#tfi<?php echo $id; ?>').val("<?php echo get_woocommerce_currency_symbol(); ?>" + totalFare.toFixed(2));
                    if (totalFare > 0) {
                        jQuery('#bus-booking-btn<?php echo $id; ?>').show();

                    }

                    wbt_update_passenger_form();
                    
                });
                // ******Admin Ticket Purchase********

                function wbt_calculate_total() {

                    var fare = <?php echo $fare; ?>;
                    var rowCount = jQuery('.selected-seat-list<?php echo $id; ?> tr').length - 2;

                    var totalFare = 0;
                    jQuery('.selected-seat-table tbody tr').each(function() {
                        if( $(this).hasClass('seat_selected_price') ) {
                            totalFare = totalFare + parseFloat($(this).find('.seat_fare').val());
                        }
                    })

                    jQuery('#total_seat<?php echo $id; ?>_booked').html(rowCount);
                    jQuery('#tq<?php echo $id; ?>').val(rowCount);
                    jQuery('#totalFare<?php echo $id; ?>').html("<?php echo get_woocommerce_currency_symbol(); ?>" + totalFare.toFixed(2));
                    jQuery('#tfi<?php echo $id; ?>').val(totalFare.toFixed(2));
                    if (totalFare == 0) {
                        jQuery('#bus-booking-btn<?php echo $id; ?>').hide();
                    }
                    // alert(totalFare);
                }

                function wbt_update_passenger_form() {

                    var input = jQuery('#tq<?php echo $id; ?>').val() || 0;
                    var children = jQuery('#divParent<?php echo $id; ?> > div').length || 0;

                    if (input < children) {
                        jQuery('#divParent<?php echo $id; ?>').empty();
                        children = 0;
                    }

                    for (var i = children + 1; i <= input; i++) {

                        jQuery('#divParent<?php echo $id; ?>').append(
                            jQuery('<div/>')
                                .attr("id", "newDiv" + i)
                                .html("<?php do_action('wbtm_reg_fields'); ?>")
                        );
                    }
                }

                jQuery("#view_panel_<?php echo $id; ?>").click(function () {
                    jQuery("#admin-bus-details<?php echo $id; ?>").slideToggle("slow", function () {
                        // Animation complete.
                    });
                });
            });
        </script>
        <?php
    }


    public function wbtm_bus_seat_plan($current_plan, $start, $date)
    {
        $global_plan = get_post_meta(get_the_id(), 'wbtm_bus_seats_info', true);
        if (!empty($global_plan)) {
            wbtm_seat_global($start, $date);
        } else {
            if ($current_plan == 'seat_plan_1') {
                wbtm_seat_plan_1($start, $date);
            }
            if ($current_plan == 'seat_plan_2') {
                wbtm_seat_plan_2($start, $date);
            }
            if ($current_plan == 'seat_plan_3') {
                wbtm_seat_plan_3($start, $date);
            }
        }


    }

    public function wbtm_get_this_bus_seat_plan()
    {
        $current_plan = get_post_meta(get_the_id(), 'seat_plan', true);
        $bus_meta = get_post_custom(get_the_id());
        if (array_key_exists('wbtm_seat_col', $bus_meta)) {
            $seat_col = $bus_meta['wbtm_seat_col'][0];
            $seat_col_arr = explode(",", $seat_col);
            $seat_column = count($seat_col_arr);
        } else {
            $seat_col = array();
            $seat_column = 0;
        }

        if (array_key_exists('wbtm_seat_row', $bus_meta)) {
            $seat_row = $bus_meta['wbtm_seat_row'][0];
            $seat_row_arr = explode(",", $seat_row);
        } else {
            $seat_row = array();
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


    function wbtm_get_bus_start_time($start, $array)
    {
        // print_r($array);
        foreach ($array as $key => $val) {
            if ($val['wbtm_bus_bp_stops_name'] === $start) {
                return $val['wbtm_bus_bp_start_time'];
                // return $key;
            }
        }
        return null;
    }

    public function wbtm_get_bus_end_time($end, $array)
    {
        foreach ($array as $key => $val) {
            if ($val['wbtm_bus_next_stops_name'] === $end) {
                return $val['wbtm_bus_next_end_time'];
                // return $key;
            }
        }
        return null;
    }

    public function wbtm_buffer_time_check($bp_time, $date)
    {
        // Get the buffer time set by user
        $bus_buffer_time = $this->bus_get_option('bus_buffer_time', 'general_setting_sec', 0);
        // Convert bus start time into date format
        $bus_start_time = date('H:i:s', strtotime($bp_time));
        // Make bus search date & bus start time as date format
        $start_bus = $date . ' ' . $bus_start_time;


        // $diff = round((strtotime($start_bus) - strtotime(current_time('Y-m-d H:i:s'))) / 3600, 1); // In Hour
        $diff = round((strtotime($start_bus) - strtotime(current_time('Y-m-d H:i:s'))) / 60, 1); // In Minute

        if ($diff >= $bus_buffer_time) {
            return 'yes';
        } else {
            return 'no';
        }
    }

    public function wbtm_get_seat_status($seat, $date, $bus_id, $start, $end)
    {


        $args = array(
            'post_type' => 'wbtm_bus_booking',
            'posts_per_page' => -1,
            'meta_query' => array(
                'relation' => 'AND',
                array(
                    'relation' => 'AND',
                    array(
                        'key' => 'wbtm_seat',
                        'value' => $seat,
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
        $booking_status = get_post_meta($booking_id, 'wbtm_status', true);
        return $booking_status;
    }

    public function get_bus_start_time($bus_id)
    {
        $start_stop_array = get_post_meta($bus_id, 'wbtm_bus_bp_stops', true) ? maybe_unserialize(get_post_meta($bus_id, 'wbtm_bus_bp_stops', true)) : array();
        $c = 1;
        $start_time = '';
        if (sizeof($start_stop_array) > 0) {
            foreach ($start_stop_array as $stops) {
                if ($c == 1) {
                    $start_time = $stops['wbtm_bus_bp_start_time'];
                }
                # code...
                $c++;
            }
        }
        return $start_time;
    }


    // get bus price
    public function wbtm_get_bus_price($start, $end, $array, $seat_type = '')
    {
        foreach ($array as $key => $val) {
            if ($val['wbtm_bus_bp_price_stop'] === $start && $val['wbtm_bus_dp_price_stop'] === $end) {
                //echo '<pre>';print_r($seat_type);echo '</pre>';die();
                if ('1' == $seat_type) {
                    $price = $val['wbtm_bus_child_price'];
                } elseif ('2' == $seat_type) {
                    $price = $val['wbtm_bus_infant_price'];
                } elseif ('3' == $seat_type) {
                    $price = $val['wbtm_bus_special_price'];
                } else {
                    $price = $val['wbtm_bus_price'];
                }
                return $price;
            }
        }
        return null;
    }

    public function wbtm_check_od_in_range($start_date, $end_date, $j_date)
    {
        // Convert to timestamp
        $start_ts = strtotime($start_date);
        $end_ts = strtotime($end_date);
        $user_ts = strtotime($j_date);

        // Check that user date is between start & end
        if (($user_ts >= $start_ts) && ($user_ts <= $end_ts)) {
            return 'yes';
        } else {
            return 'no';
        }
    }

    public function wbtm_array_strip($string, $allowed_tags = NULL)
    {
        if (is_array($string)) {
            foreach ($string as $k => $v) {
                $string[$k] = $this->wbtm_array_strip($v, $allowed_tags);
            }
            return $string;
        }
        return strip_tags($string, $allowed_tags);
    }

    public function wbtm_get_seat_cehck_before_order($seat, $date, $bus_id, $start)
    {
        global $wpdb;
        $total_booking_id = 0;
        $total_booking = 0;
        foreach ($seat as $_seat) {
            $args = array(
                'post_type' => 'wbtm_bus_booking',
                'posts_per_page' => -1,
                'meta_query' => array(
                    'relation' => 'AND',
                    array(
                        'relation' => 'AND',
                        array(
                            'key' => 'wbtm_seat',
                            'value' => $_seat,
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
                            'key' => 'wbtm_next_stops',
                            'value' => $start,
                            'compare' => 'LIKE'
                        ),
                        array(
                            'key' => 'wbtm_next_stops',
                            'value' => $start,
                            'compare' => 'LIKE'
                        ),
                    )
                ),
            );

            $q = new WP_Query($args);
            $total_booking_id = $q->post_count + $total_booking;

        }

        return $total_booking_id;
    }

    public function wbtm_get_seat_cehck_before_place_order($seat, $date, $bus_id, $start)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . "wbtm_bus_booking_list";
        $total_booking_id = 0;
        $total_booking = 0;

        foreach ($seat as $_seat) {

            $_seat = $_seat['wbtm_seat_name'];


            $args = array(
                'post_type' => 'wbtm_bus_booking',
                'posts_per_page' => -1,
                'meta_query' => array(
                    'relation' => 'AND',
                    array(
                        'relation' => 'AND',
                        array(
                            'key' => 'wbtm_seat',
                            'value' => $_seat,
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
                            'key' => 'wbtm_next_stops',
                            'value' => $start,
                            'compare' => 'LIKE'
                        ),
                        array(
                            'key' => 'wbtm_next_stops',
                            'value' => $start,
                            'compare' => 'LIKE'
                        ),
                    )
                ),
            );
            $q = new WP_Query($args);
            $total_booking_id = $q->post_count + $total_booking;
        }
        return $total_booking_id;
    }

    public function wbtm_get_order_meta($item_id, $key)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . "woocommerce_order_itemmeta";
        $sql = 'SELECT meta_value FROM ' . $table_name . ' WHERE order_item_id =' . $item_id . ' AND meta_key="' . $key . '"';
        $results = $wpdb->get_results($sql) or die(mysql_error());
        foreach ($results as $result) {
            $value = $result->meta_value;
        }
        return $value;
    }

    public function wbtm_get_order_seat_check($bus_id, $order_id, $seat, $bus_start, $date)
    {
        $args = array(
            'post_type' => 'wbtm_bus_booking',
            'posts_per_page' => -1,
            'meta_query' => array(
                'relation' => 'AND',
                array(
                    'key' => 'wbtm_seat',
                    'value' => $seat,
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
                array(
                    'key' => 'wbtm_bus_start',
                    'value' => $bus_start,
                    'compare' => '='
                ),
                array(
                    'key' => 'wbtm_order_id',
                    'value' => $order_id,
                    'compare' => '='
                )
            )
        );
        $q = new WP_Query($args);
        $total_booking_id = $q->post_count;
        return $total_booking_id;
    }

    public function update_bus_seat_status($order_id, $bus_id, $status)
    {
        $args = array(
            'post_type' => 'wbtm_bus_booking',
            'posts_per_page' => -1,
            'meta_query' => array(
                'relation' => 'AND',
                array(
                    'key' => 'wbtm_bus_id',
                    'value' => $bus_id,
                    'compare' => '='
                ),
                array(
                    'key' => 'wbtm_order_id',
                    'value' => $order_id,
                    'compare' => '='
                )
            )
        );
        $q = new WP_Query($args);
        foreach ($q->posts as $bus) {
            # code...
            update_post_meta($bus->ID, 'wbtm_status', $status);
        }
    }


    public function create_bus_passenger($order_id, $bus_id, $user_id, $start, $next_stops, $end, $b_time, $j_time, $_seats, $fare, $j_date, $add_datetime, $user_name, $user_email, $passenger_type, $passenger_type_num, $user_phone, $user_gender, $user_address, $wbtm_extra_bag_qty, $usr_inf, $counter, $status)
    {

        $add_datetime = date("Y-m-d h:i:s");
        $name = '#' . $order_id . get_the_title($bus_id);
        $new_post = array(
            'post_title' => $name,
            'post_content' => '',
            'post_category' => array(),
            'tags_input' => array(),
            'post_status' => 'publish',
            'post_type' => 'wbtm_bus_booking'
        );

        //SAVE THE POST
        $pid = wp_insert_post($new_post);
        update_post_meta($pid, 'wbtm_order_id', $order_id);
        update_post_meta($pid, 'wbtm_bus_id', $bus_id);
        update_post_meta($pid, 'wbtm_user_id', $user_id);
        update_post_meta($pid, 'wbtm_boarding_point', $start);
        update_post_meta($pid, 'wbtm_next_stops', $next_stops);
        update_post_meta($pid, 'wbtm_droping_point', $end);
        update_post_meta($pid, 'wbtm_bus_start', $b_time);
        update_post_meta($pid, 'wbtm_user_start', $j_time);
        update_post_meta($pid, 'wbtm_seat', $_seats);
        update_post_meta($pid, 'wbtm_bus_fare', $fare);
        update_post_meta($pid, 'wbtm_journey_date', $j_date);
        update_post_meta($pid, 'wbtm_booking_date', $add_datetime);
        update_post_meta($pid, 'wbtm_status', $status);
        update_post_meta($pid, 'wbtm_ticket_status', 1);
        update_post_meta($pid, 'wbtm_user_name', $user_name);
        update_post_meta($pid, 'wbtm_user_email', $user_email);
        update_post_meta($pid, 'wbtm_user_phone', $user_phone);
        update_post_meta($pid, 'wbtm_user_gender', $user_gender);
        update_post_meta($pid, 'wbtm_user_address', $user_address);
        update_post_meta($pid, 'wbtm_user_extra_bag', $wbtm_extra_bag_qty);
        update_post_meta($pid, 'wbtm_passenger_type', $passenger_type);
        update_post_meta($pid, 'wbtm_passenger_type_num', $passenger_type_num);

        $reg_form_arr = unserialize(get_post_meta($bus_id, 'attendee_reg_form', true));

        if (is_array($reg_form_arr) && sizeof($reg_form_arr) > 0) {
            foreach ($reg_form_arr as $builder) {
                update_post_meta($pid, $builder['field_id'], $usr_inf[$counter][$builder['field_id']]);

            }
        }
    }

    public function bus_order_processed($order_id)
    {
        // Getting an instance of the order object
        $order = wc_get_order($order_id);
        $order_meta = get_post_meta($order_id);
        
        # Iterating through each order items (WC_Order_Item_Product objects in WC 3+)
        foreach ($order->get_items() as $item_id => $item_values) {
            $product_id = $item_values->get_product_id();
            $item_data = $item_values->get_data();
            $product_id = $item_data['product_id'];
            $item_quantity = $item_values->get_quantity();
            $product = get_page_by_title($item_data['name'], OBJECT, 'wbtm_bus');
            $event_name = $item_data['name'];
            $event_id = $product->ID;
            $item_id = $item_id;
            $wbtm_bus_id = $this->wbtm_get_order_meta($item_id, '_wbtm_bus_id');
            if (get_post_type($wbtm_bus_id) == 'wbtm_bus') {

                $user_id = $order_meta['_customer_user'][0];
                $bus_id = $this->wbtm_get_order_meta($item_id, '_bus_id');
                $user_info_arr = $this->wbtm_get_order_meta($item_id, '_wbtm_passenger_info');
                $user_single_info_arr = maybe_unserialize($this->wbtm_get_order_meta($item_id, '_wbtm_single_passenger_info'));
                $seat = $this->wbtm_get_order_meta($item_id, 'Seats');
                $start = $this->wbtm_get_order_meta($item_id, 'Start');
                $end = $this->wbtm_get_order_meta($item_id, 'End');
                $j_date = $this->wbtm_get_order_meta($item_id, 'Date');
                $j_time = $this->wbtm_get_order_meta($item_id, 'Time');
                $bus_id = $this->wbtm_get_order_meta($item_id, '_bus_id');
                $b_time = $this->wbtm_get_order_meta($item_id, '_btime');
                $extra_bag = $this->wbtm_get_order_meta($item_id, 'Extra Bag');
                $wbtm_tp = $this->wbtm_get_order_meta($item_id, '_wbtm_tp');
                $seats = explode(",", $seat);
                $usr_inf = unserialize($user_info_arr);

                
                
                $counter = 0;
                $next_stops = maybe_serialize($this->wbtm_get_all_stops_after_this($bus_id, $start, $end));
                $price_arr = maybe_unserialize(get_post_meta($bus_id, 'wbtm_bus_prices', true));
                $extra_bag_price = get_post_meta($bus_id, 'wbtm_extra_bag_price', true) ? get_post_meta($bus_id, 'wbtm_extra_bag_price', true) : 0;
                // $fare = $this->wbtm_get_bus_price($start, $end, $price_arr);

                foreach ($seats as $_seats) {
                    if (!empty($_seats)) {

                        // $fare = $this->wbtm_get_bus_price($start, $end, $price_arr, $usr_inf[$counter]['wbtm_passenger_type_num']);
                        $fare = $usr_inf[$counter]['wbtm_seat_fare'];

                        if (is_array($user_single_info_arr) && sizeof($user_single_info_arr) > 0) {
                            $user_name = isset($usr_inf[$counter]['wbtm_user_name']) ? $usr_inf[$counter]['wbtm_user_name'] : '';

                            $passenger_type = isset($usr_inf[$counter]['wbtm_passenger_type']) ? $usr_inf[$counter]['wbtm_passenger_type'] : '';

                            $passenger_type_num = isset($usr_inf[$counter]['wbtm_passenger_type_num']) ? $usr_inf[$counter]['wbtm_passenger_type_num'] : '';

                            $user_email = isset($usr_inf[$counter]['wbtm_user_email']) ? $usr_inf[$counter]['wbtm_user_email'] : '';

                            $user_phone = isset($usr_inf[$counter]['wbtm_user_phone']) ? $usr_inf[$counter]['wbtm_user_phone'] : '';

                            $user_address = isset($usr_inf[$counter]['wbtm_user_address']) ? $usr_inf[$counter]['wbtm_user_address'] : '';
                            $user_gender = isset($usr_inf[$counter]['wbtm_user_gender']) ? $usr_inf[$counter]['wbtm_user_gender'] : '';
                        } else {
                            $user_name = $order_meta['_billing_first_name'][0] . ' ' . $order_meta['_billing_last_name'][0];
                            $passenger_type = isset($usr_inf[0]['wbtm_passenger_type']) ? $usr_inf[0]['wbtm_passenger_type'] : '';
                            $passenger_type_num = isset($usr_inf[0]['wbtm_passenger_type_num']) ? $usr_inf[0]['wbtm_passenger_type_num'] : '';
                            $user_email = $order_meta['_billing_email'][0];
                            $user_phone = $order_meta['_billing_phone'][0];
                            $user_address = $order_meta['_billing_address_1'][0];
                            $user_gender = '';
                        }


                        if (isset($usr_inf[$counter]['wbtm_extra_bag_qty'])) {
                            $wbtm_extra_bag_qty = $usr_inf[$counter]['wbtm_extra_bag_qty'];
                            // $fare               = $fare + ($extra_bag_price * $wbtm_extra_bag_qty);
                        } else {
                            $wbtm_extra_bag_qty = 0;
                        }

                        $add_datetime = date("Y-m-d h:i:s");
                        $this->create_bus_passenger($order_id, $bus_id, $user_id, $start, $next_stops, $end, $b_time, $j_time, $_seats, $fare, $j_date, $add_datetime, $user_name, $user_email, $passenger_type, $passenger_type_num, $user_phone, $user_gender, $user_address, $wbtm_extra_bag_qty, $usr_inf, $counter, 0);

                    }
                    $counter++;
                }
            }
        }

    }


    public function wbtm_bus_ticket_seat_management($order_id, $from_status, $to_status, $order)
    {
        global $wpdb;
        // Getting an instance of the order object
        $order = wc_get_order($order_id);
        $order_meta = get_post_meta($order_id);

        # Iterating through each order items (WC_Order_Item_Product objects in WC 3+)
        foreach ($order->get_items() as $item_id => $item_values) {
            $item_id = $item_id;
            $wbtm_bus_id = $this->wbtm_get_order_meta($item_id, '_wbtm_bus_id');
            if (get_post_type($wbtm_bus_id) == 'wbtm_bus') {
                $bus_id = $this->wbtm_get_order_meta($item_id, '_bus_id');
                if ($order->has_status('processing') || $order->has_status('pending') || $order->has_status('on-hold')) {
                    $status = 1;
                    $this->update_bus_seat_status($order_id, $bus_id, $status);
                }
                if ($order->has_status('cancelled') || $order->has_status('refunded')) {
                    $status = 3;
                    $this->update_bus_seat_status($order_id, $bus_id, $status);
                }
                if ($order->has_status('completed')) {
                    $status = 2;
                    $this->update_bus_seat_status($order_id, $bus_id, $status);
                }
            }
        }
    }

    function wbtm_get_bus_route_list($name, $value = '')
    {

        global $post;
        if ($post) {
            $values = get_post_custom($post->ID);
        } else {
            $values = '';
        }


        if (is_array($values) && array_key_exists($name, $values)) {
            $seat_name = $name;
            $type_name = $values[$seat_name][0];
        } else {
            $type_name = '';
        }
        $terms = get_terms(array(
            // 'taxonomy' => 'wbtm_bus_route',
            'taxonomy' => 'wbtm_bus_stops',
            'hide_empty' => false,
        ));
        if (!empty($terms) && !is_wp_error($terms)) : ob_start(); ?>
            <select name="<?php echo $name; ?>" class='seat_type' required>
                <option value=""><?php _e('Please Select', 'bus-ticket-booking-with-seat-reservation'); ?></option>
                <?php foreach ($terms as $term) :
                    $selected = $type_name == $term->name ? 'selected' : '';
                    if (!empty($value)) $selected = $term->name == $value ? 'selected' : '';
                    printf('<option %s value="%s">%s</option>', $selected, $term->name, $term->name);
                endforeach; ?>
            </select>
        <?php endif;
        return ob_get_clean();
    }

    function wbtm_bus_search_fileds($start, $end, $date, $r_date)
    {
        global $wbtmpublic, $start, $end, $date, $r_date;
        ob_start();
        $wbtmpublic->wbtm_template_part('bus-search-form-fields');
        $content = ob_get_clean();
        echo $content;
    }


    function wbtm_get_available_seat($bus_id, $date)
    {
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
        $total_booking_id = $q->post_count;
        return $total_booking_id;
    }


    function wbtm_find_product_in_cart($id)
    {
        $product_id = $id;
        $in_cart = false;
        foreach (WC()->cart->get_cart() as $cart_item) {
            $product_in_cart = $cart_item['product_id'];
            if ($product_in_cart === $product_id) $in_cart = true;
        }

        if ($in_cart) {
            return 'into-cart';
        } else {
            return 'not-in-cart';
        }
    }

}

global $wbtmmain;
$wbtmmain = new WBTM_Plugin_Functions();


function wbtm_get_seat_type_list($name, $bus_id = '')
{
    ob_start();
    ?>
    <!-- <div class='field-select2-wrapper'>
<select name="<?php echo $name; ?>[]" id="" class="select2" multiple>
    <option value="adult"><?php echo wbtm_get_seat_type_label('adult', 'Adult'); ?></option>
    <option value="child"><?php echo wbtm_get_seat_type_label('child', 'Child'); ?></option>
    <option value="infant"><?php echo wbtm_get_seat_type_label('infant', 'Infant'); ?></option>
    <option value="special"><?php echo wbtm_get_seat_type_label('special', 'Special'); ?></option>
    <?php do_action('wbtm_seat_type_list_init'); ?>
</select>
</div> -->
    <?php
    //echo ob_get_clean();

    echo '';
}

function wbtm_get_seat_type_label($key, $default)
{
    global $wbtmmain;
    $metakey = "wbtm_seat_type_" . $key . "_label";
    return $wbtmmain->bus_get_option($metakey, '', $default);
}


/**
 * The magical Datetime Function, Just call this function where you want display date or time, Pass the date or time and the format this will be return the date or time in the current wordpress saved datetime format and according the timezone.
 */
function get_wbtm_datetime($date, $type)
{
    $date_format = get_option('date_format');
    $time_format = get_option('time_format');
    $wpdatesettings = $date_format . '  ' . $time_format;
    $timezone = wp_timezone_string();
    $timestamp = strtotime($date . ' ' . $timezone);

    if ($type == 'date') {
        return wp_date($date_format, $timestamp);
    }
    if ($type == 'date-time') {
        return wp_date($wpdatesettings, $timestamp);
    }
    if ($type == 'date-text') {

        return wp_date($date_format, $timestamp);
    }

    if ($type == 'date-time-text') {
        return wp_date($wpdatesettings, $timestamp, wp_timezone());
    }
    if ($type == 'time') {
        return wp_date($time_format, $timestamp, wp_timezone());
    }

    if ($type == 'day') {
        return wp_date('d', $timestamp);
    }
    if ($type == 'month') {
        return wp_date('M', $timestamp);
    }
}


function wbtm_convert_datepicker_dateformat()
{
    $date_format = get_option('date_format');
    // return $date_format;
    // $php_d   = array('F', 'j', 'Y', 'm','d','D','M','y');
    // $js_d   = array('d', 'M', 'yy','mm','dd','tt','mm','yy');
    $dformat = str_replace('d', 'dd', $date_format);
    $dformat = str_replace('m', 'mm', $dformat);
    $dformat = str_replace('Y', 'yy', $dformat);

    if ($date_format == 'Y-m-d' || $date_format == 'm/d/Y' || $date_format == 'd/m/Y' || $date_format == 'Y/d/m' || $date_format == 'Y-d-m') {
        return str_replace('/', '-', $dformat);
    } else {
        return 'yy-mm-dd';
    }
}


function wbtm_convert_date_to_php($date)
{

    $date_format = get_option('date_format');
    if ($date_format == 'Y-m-d' || $date_format == 'm/d/Y' || $date_format == 'm/d/Y') {
        if ($date_format == 'd/m/Y') {
            $date = str_replace('/', '-', $date);
        }
    }
    return date('Y-m-d', strtotime($date));
}


/**
 * This Function will modify the journey date input box if there is any settings for particular on date, Then only particular on date will be enable in the datepicker calendar.
 * */
add_action('wp_footer', 'wbtm_journey_date_js');
// add_action('wbtm_search_form_end','wbtm_journey_date_js');
function wbtm_journey_date_js()
{
    global $post;
    ob_start();
    ?>
    <script>
        jQuery(function () {
            <?php
            if(is_single()){

            $wbtm_bus_on_dates = get_post_meta($post->ID, 'wbtm_bus_on_dates', true) ? maybe_unserialize(get_post_meta($post->ID, 'wbtm_bus_on_dates', true)) : array();

            if(is_array($wbtm_bus_on_dates) && sizeof($wbtm_bus_on_dates) > 0){
            $pday = array();
            foreach ($wbtm_bus_on_dates as $_wbtm_bus_on_dates) {
                $pday[] = '"' . date('d-m-Y', strtotime($_wbtm_bus_on_dates['wbtm_on_date_name'])) . '"';
            }
            $particular_date = implode(',', $pday);
            ?>

            var enableDays = [<?php echo $particular_date; ?>];

            function enableAllTheseDays(date) {
                var sdate = jQuery.datepicker.formatDate('dd-mm-yy', date)
                if (jQuery.inArray(sdate, enableDays) != -1) {
                    return [true];
                }
                return [false];
            }

            jQuery('#j_date').datepicker({
                dateFormat: '<?php echo wbtm_convert_datepicker_dateformat(); ?>',
                minDate: 0,
                beforeShowDay: enableAllTheseDays
            });

            <?php }else{ ?>

            jQuery("#j_date").datepicker({
                dateFormat: "<?php echo wbtm_convert_datepicker_dateformat(); ?>",
                minDate: 0
            });


            <?php } }else{ ?>

            jQuery("#j_date").datepicker({
                dateFormat: "<?php echo wbtm_convert_datepicker_dateformat(); ?>",
                minDate: 0
            });

            <?php } ?>

            jQuery("#r_date").datepicker({
                dateFormat: "<?php echo wbtm_convert_datepicker_dateformat(); ?>"
            });

        })


        jQuery('#wbtm_boarding_point').on('change', function () {
            var boarding_point = jQuery(this).val();
            jQuery.ajax({
                type: 'POST',
                url: ajax.ajaxurl,
                data: {"action": "wbtm_load_dropping_point", "boarding_point": boarding_point},
                beforeSend: function () {
                    jQuery('#wbtm_show_notice').html('<span class=search-text style="display:block;background:#ddd:color:#000:font-weight:bold;text-align:center">Time List Loading..</span>');
                },
                success: function (data) {
                    jQuery('#wbtm_dropping_ponit_sec').html(data);
                }
            });
            return false;

        });
    </script>
    <?php
    echo ob_get_clean();
}


function wbtm_get_busstop_name($id)
{
    $category = get_term_by('id', $id, 'wbtm_bus_stops');
    return $category->name;
}

add_action('wp_ajax_wbtm_load_dropping_point', 'wbtm_load_dropping_point');
add_action('wp_ajax_nopriv_wbtm_load_dropping_point', 'wbtm_load_dropping_point');
function wbtm_load_dropping_point()
{
    $boardingPoint = strip_tags($_POST['boarding_point']);
    $category = get_term_by('name', $boardingPoint, 'wbtm_bus_stops');
    $allStopArr = get_terms(array(
        'taxonomy' => 'wbtm_bus_stops',
        'hide_empty' => false
    ));
    $dropingarray = get_term_meta($category->term_id, 'wbtm_bus_routes_name_list', true) ? maybe_unserialize(get_term_meta($category->term_id, 'wbtm_bus_routes_name_list', true)) : array();
    if (sizeof($dropingarray) > 0) {
        foreach ($dropingarray as $dp) {
            $name = $dp['wbtm_bus_routes_name'];
            echo "<li data-route='$name'><span class='fa fa-map-marker'></span>$name</li>";
        }
    } else {
        foreach ($allStopArr as $dp) {
            $name = $dp->name;
            echo "<li data-route='$name'><span class='fa fa-map-marker'></span>$name</li>";
        }
    }
    die();
}


// add_action('wbtm_after_search_result_section','wbtm_search_result_list_script');

function wbtm_search_result_list_script()
{
    ob_start();
    ?>
    <script>
        jQuery('#mage_bus_search_button').on('click', function () {

            var bus_start_route = jQuery('#wbtm_starting_point_inupt').val();
            var bus_end_route = jQuery('#wbtm_dropping_point_inupt').val();
            var j_date = jQuery('#j_date').val();
            var r_date = jQuery('#r_date').val();
            jQuery.ajax({
                type: 'GET',
                url: wbtm_ajax.wbtm_ajaxurl,
                data: {
                    "action": "wbtm_ajax_search_bus",
                    "bus_start_route": bus_start_route,
                    "bus_end_route": bus_end_route,
                    "j_date": j_date,
                    "r_date": r_date
                },
                beforeSend: function () {
                    jQuery('#wbtm_search_result_section').html('<span class=wbtm-loading-animation><img src="<?php echo WBTM_PLUGIN_URL . 'public/images/'; ?>loading.gif"</span>');
                },
                success: function (data) {
                    jQuery('#wbtm_search_result_section').html(data);
                }
            });
            return false;


        });


        jQuery('.wbtm_next_day_search').on('click', function () {

            var bus_start_route = jQuery(this).data('sroute');
            var bus_end_route = jQuery(this).data('eroute');
            var j_date = jQuery(this).data('jdate');
            var r_date = jQuery(this).data('rdate');
            jQuery.ajax({
                type: 'GET',
                url: wbtm_ajax.wbtm_ajaxurl,
                data: {
                    "action": "wbtm_ajax_search_bus_tab",
                    "bus_start_route": bus_start_route,
                    "bus_end_route": bus_end_route,
                    "j_date": j_date,
                    "r_date": r_date
                },
                beforeSend: function () {
                    jQuery('.wbtm_search_part').html('<span class=wbtm-loading-animation><img src="<?php echo WBTM_PLUGIN_URL . 'public/images/'; ?>loading.gif"</span>');
                },
                success: function (data) {
                    jQuery('.wbtm_search_part').html(data);
                    jQuery('#j_date').val(j_date);


                }
            });
            return false;


        });


    </script>
    <?php
    echo ob_get_clean();
}

add_action('wp_ajax_wbtm_ajax_search_bus', 'wbtm_ajax_search_bus');
add_action('wp_ajax_nopriv_wbtm_ajax_search_bus', 'wbtm_ajax_search_bus');
function wbtm_ajax_search_bus()
{
    echo '<div class="mage_ajax_search_result">';
    if (isset($_GET['bus_start_route']) && ($_GET['bus_end_route']) && ($_GET['j_date'])) {
        mage_next_date_suggestion(false, false);
        echo '<div class="wbtm_search_part">';
        mage_bus_route_title(false);
        mage_bus_search_list(false);
        echo '</div>';
    }
    if (isset($_GET['bus_start_route']) && ($_GET['bus_end_route']) && ($_GET['r_date'])) {
        mage_next_date_suggestion(false, false);
        echo '<div class="wbtm_search_part">';
        mage_bus_route_title(true);
        mage_bus_search_list(true);
        echo '</div>';
    }
    echo '</div>';

    die();
}


add_action('wp_ajax_wbtm_ajax_search_bus_tab', 'wbtm_ajax_search_bus_tab');
add_action('wp_ajax_nopriv_wbtm_ajax_search_bus_tab', 'wbtm_ajax_search_bus_tab');
function wbtm_ajax_search_bus_tab()
{
    echo '<div class="mage_ajax_search_result">';
    if (isset($_GET['bus_start_route']) && ($_GET['bus_end_route']) && ($_GET['j_date'])) {

        echo '<div class="wbtm_search_part">';
        mage_bus_route_title(false);
        mage_bus_search_list(false);
        echo '</div>';
    }
    if (isset($_GET['bus_start_route']) && ($_GET['bus_end_route']) && ($_GET['r_date'])) {

        echo '<div class="wbtm_search_part">';
        mage_bus_route_title(true);
        mage_bus_search_list(true);
        echo '</div>';
    }
    echo '</div>';

    die();
}


function wbtm_bus_target_page_filter_rewrite_rule()
{
    add_rewrite_rule(
        '^bus-search-list/?$',
        'index.php?bussearchlist=busSearchDefault&pagename=bus-search-list',
        'top'
    );
}

add_action('init', 'wbtm_bus_target_page_filter_rewrite_rule');

function wbtm_bus_target_page_query_var($vars)
{
    $vars[''] = 'bussearchlist';
    return $vars;
}

add_filter('query_vars', 'wbtm_bus_target_page_query_var');

function wbtm_wbtm_bus_target_page_template_chooser($template)
{
    global $wp_query;
    $plugin_path = plugin_dir_path(__DIR__);
    $template_name = $plugin_path . 'public/templates/bus-search-list.php';
    //echo get_query_var( 'bussearchlist' );
    if (get_query_var('bussearchlist')) {
        $template = $template_name;
    }
    return $template;
}

add_filter('template_include', 'wbtm_wbtm_bus_target_page_template_chooser');


// Function for create hidden product for bus
function wbtm_create_hidden_event_product($post_id, $title)
{
    $new_post = array(
        'post_title' => $title,
        'post_content' => '',
        'post_name' => uniqid(),
        'post_category' => array(),
        'tags_input' => array(),
        'post_status' => 'publish',
        'post_type' => 'product'
    );


    $pid = wp_insert_post($new_post);

    update_post_meta($post_id, 'link_wc_product', $pid);
    update_post_meta($pid, 'link_wbtm_bus', $post_id);
    update_post_meta($pid, '_price', 0.01);

    update_post_meta($pid, '_sold_individually', 'yes');
    update_post_meta($pid, '_virtual', 'yes');
    $terms = array('exclude-from-catalog', 'exclude-from-search');
    wp_set_object_terms($pid, $terms, 'product_visibility');
    update_post_meta($post_id, 'check_if_run_once', true);
}


function wbtm_on_post_publish($post_id, $post, $update)
{
    if ($post->post_type == 'wbtm_bus' && $post->post_status == 'publish' && empty(get_post_meta($post_id, 'check_if_run_once'))) {

        // ADD THE FORM INPUT TO $new_post ARRAY
        $new_post = array(
            'post_title' => $post->post_title,
            'post_content' => '',
            'post_name' => uniqid(),
            'post_category' => array(),  // Usable for custom taxonomies too
            'tags_input' => array(),
            'post_status' => 'publish', // Choose: publish, preview, future, draft, etc.
            'post_type' => 'product'  //'post',page' or use a custom post type if you want to
        );
        //SAVE THE POST
        $pid = wp_insert_post($new_post);
        $product_type = mep_get_option('mep_event_product_type', 'general_setting_sec', 'yes');
        update_post_meta($post_id, 'link_wc_product', $pid);
        update_post_meta($pid, 'link_wbtm_bus', $post_id);
        update_post_meta($pid, '_price', 0.01);
        update_post_meta($pid, '_sold_individually', 'yes');
        update_post_meta($pid, '_virtual', $product_type);
        $terms = array('exclude-from-catalog', 'exclude-from-search');
        wp_set_object_terms($pid, $terms, 'product_visibility');
        update_post_meta($post_id, 'check_if_run_once', true);
    }
}

add_action('wp_insert_post', 'wbtm_on_post_publish', 10, 3);

function wbtm_count_hidden_wc_product($event_id)
{
    $args = array(
        'post_type' => 'product',
        'posts_per_page' => -1,
        'meta_query' => array(
            array(
                'key' => 'link_wbtm_bus',
                'value' => $event_id,
                'compare' => '='
            )
        )
    );
    $loop = new WP_Query($args);
    return $loop->post_count;
}


add_action('save_post', 'wbtm_wc_link_product_on_save', 99, 1);
function wbtm_wc_link_product_on_save($post_id)
{

    if (get_post_type($post_id) == 'wbtm_bus') {

        //   if ( ! isset( $_POST['mep_event_reg_btn_nonce'] ) ||
        //   ! wp_verify_nonce( $_POST['mep_event_reg_btn_nonce'], 'mep_event_reg_btn_nonce' ) )
        //     return;

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
            return;

        if (!current_user_can('edit_post', $post_id))
            return;
        $event_name = get_the_title($post_id);

        if (wbtm_count_hidden_wc_product($post_id) == 0 || empty(get_post_meta($post_id, 'link_wc_product', true))) {
            wbtm_create_hidden_event_product($post_id, $event_name);
        }

        $product_id = get_post_meta($post_id, 'link_wc_product', true) ? get_post_meta($post_id, 'link_wc_product', true) : $post_id;
        set_post_thumbnail($product_id, get_post_thumbnail_id($post_id));
        wp_publish_post($product_id);

        // $product_type               = mep_get_option('mep_event_product_type', 'general_setting_sec','yes');

        $_tax_status = isset($_POST['_tax_status']) ? strip_tags($_POST['_tax_status']) : 'none';
        $_tax_class = isset($_POST['_tax_class']) ? strip_tags($_POST['_tax_class']) : '';

        update_post_meta($product_id, '_tax_status', $_tax_status);
        update_post_meta($product_id, '_tax_class', $_tax_class);
        update_post_meta($product_id, '_stock_status', 'instock');
        update_post_meta($product_id, '_manage_stock', 'no');
        update_post_meta($product_id, '_virtual', 'yes');
        update_post_meta($product_id, '_sold_individually', 'yes');


        // Update post
        $my_post = array(
            'ID' => $product_id,
            'post_title' => $event_name, // new title
            'post_name' => uniqid()// do your thing here
        );

        // unhook this function so it doesn't loop infinitely
        remove_action('save_post', 'wbtm_wc_link_product_on_save');
        // update the post, which calls save_post again
        wp_update_post($my_post);
        // re-hook this function
        add_action('save_post', 'wbtm_wc_link_product_on_save');
        // Update the post into the database


    }

}


add_action('parse_query', 'wbtm_product_tags_sorting_query');
function wbtm_product_tags_sorting_query($query)
{
    global $pagenow;

    $taxonomy = 'product_visibility';

    $q_vars = &$query->query_vars;

    if ($pagenow == 'edit.php' && isset($q_vars['post_type']) && $q_vars['post_type'] == 'product') {


        $tax_query = array([
            'taxonomy' => 'product_visibility',
            'field' => 'slug',
            'terms' => 'exclude-from-catalog',
            'operator' => 'NOT IN',
        ]);
        $query->set('tax_query', $tax_query);
    }

}


function wbtm_find_product_in_cart()
{
    $product_id = get_the_id();

    $jdate = $_GET['j_date'];
    $start = $_GET['bus_start_route'];
    $end = $_GET['bus_end_route'];

    $cart = WC()->cart->get_cart();
    foreach ($cart as $cart_item) {
        if ($cart_item['wbtm_bus_id'] == $product_id && $cart_item['wbtm_start_stops'] == $start && $cart_item['wbtm_end_stops'] == $end && $cart_item['wbtm_journey_date'] == $jdate) {

            return 'mage_bus_in_cart';
        }
    }
    return null;
}


function wbtm_find_seat_in_cart($seat_name)
{
    $product_id = get_the_id();
    $cart = WC()->cart->get_cart();
    $jdate = $_GET['j_date'];
    $start = $_GET['bus_start_route'];
    $end = $_GET['bus_end_route'];
    foreach ($cart as $cart_item) {
        if ($cart_item['wbtm_bus_id'] == $product_id && $cart_item['wbtm_start_stops'] == $start && $cart_item['wbtm_end_stops'] == $end && $cart_item['wbtm_journey_date'] == $jdate) {
            foreach ($cart_item['wbtm_seats'] as $item) {
                if ($item['wbtm_seat_name'] == $seat_name) {
                    return true;
                }
            }
        }
    }
    return null;
}

add_action( 'woocommerce_after_order_itemmeta', 'mage_show_passenger_info_in_order_details', 10, 3 );
function mage_show_passenger_info_in_order_details( $item_id, $item, $_product ) {
    ?>
<style type="text/css">
  .th__title{
    text-transform: capitalize;
    display: inline-block;
    min-width: 140px;
    font-weight: 700
  }
ul.mage_passenger_list {
    border: 1px solid #ddd;
    padding: 20px;
    margin-bottom: 20px;
    width: 100%;
    border-radius: 3px;
}
ul.mage_passenger_list li {
    border-bottom: 1px dashed #ddd;
    padding: 5px 0 10px;
    color:#888;
}
ul.mage_passenger_list li h3 {
    padding: 0;
    margin: 0;
    color: #555;
} 
</style>
<?php

    $passenger_data = wc_get_order_item_meta( $item_id, '_wbtm_passenger_info', true );
    if($passenger_data){
        $event_id = wc_get_order_item_meta($item_id,'event_id',true);
        $counter = 1;
        if(!empty($passenger_data)){
            foreach ($passenger_data as $key => $value) {
                echo '<ul class="mage_passenger_list">';
                echo "<li><h3>".__('Passenger', 'bus-ticket-booking-with-seat-reservation').": $counter</h3></li>";
                echo "<li><span class='th__title'>".__('Name', 'bus-ticket-booking-with-seat-reservation').":</span> $value[wbtm_user_name]</li>";
                echo "<li><span class='th__title'>".__('Phone', 'bus-ticket-booking-with-seat-reservation').":</span> $value[wbtm_user_phone]</li>";
                echo "<li><span class='th__title'>".__('Seat', 'bus-ticket-booking-with-seat-reservation').":</span> $value[wbtm_seat_name]</li>";
                echo "<li><span class='th__title'>".__('Seat Type', 'bus-ticket-booking-with-seat-reservation').":</span> $value[wbtm_passenger_type]</li>";
                echo "<li><span class='th__title'>".__('Seat Fear', 'bus-ticket-booking-with-seat-reservation').":</span> ".wc_price($value['wbtm_seat_fare'])."</li>";
                
                echo '</ul>';
                $counter++;
            }
        }
    }
    
}





// Ajax Issue
add_action('wp_head','wbtm_ajax_url',5);
add_action('admin_head','wbtm_ajax_url',5);
function wbtm_ajax_url(){
    ?>
<script type="text/javascript">
    var wbtm_ajaxurl = "<?php echo admin_url('admin-ajax.php'); ?>";
</script>
    <?php
}


add_action('rest_api_init', 'wbtm_bus_cunstom_fields_to_rest_init');
if (!function_exists('wbtm_bus_cunstom_fields_to_rest_init')) {
    function wbtm_bus_cunstom_fields_to_rest_init()
    {
        register_rest_field('wbtm_bus', 'bus_informations', array(
            'get_callback'    => 'wbtm_get_bus_custom_meta_for_api',
            'schema'          => null,
        ));
    }
}
if (!function_exists('wbtm_get_bus_custom_meta_for_api')) {
    function wbtm_get_bus_custom_meta_for_api($object)
    {
        $post_id = $object['id'];
        $post_meta = get_post_meta( $post_id );
        $post_image = get_post_thumbnail_id( $post_id );      
        $post_meta["bus_feature_image"] = wp_get_attachment_image_src($post_image,'full')[0];
        return $post_meta;
    }
}