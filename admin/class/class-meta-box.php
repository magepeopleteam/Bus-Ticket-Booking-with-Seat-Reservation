<?php
if ( ! defined('ABSPATH')) exit;  // if direct access

class WBTMMetaBox{

    public function __construct(){
        $this->meta_boxs();
        add_action( 'add_meta_boxes', array($this,'wbtm_bus_meta_box_add' ));
        add_action('save_post',array($this,'wbtm_bus_seat_panels_meta_save'));
        add_action( 'admin_menu' , array($this,'wbtm_remove_post_custom_fields'));
    }

    public function wbtm_bus_meta_box_add(){
        add_meta_box( 'wbtm-bus-ticket-type', '<span class="dashicons dashicons-id" style="color: #0071a1;"></span>Bus Ticket Panel', array($this,'wbtm_bus_ticket_type'), 'wbtm_bus', 'normal', 'high' );
    }

    function wbtm_remove_post_custom_fields() {
        // remove_meta_box( 'tagsdiv-wbtm_seat' , 'wbtm_bus' , 'side' );
        remove_meta_box( 'wbtm_seat_typediv' , 'wbtm_bus' , 'side' );
        remove_meta_box( 'wbtm_bus_stopsdiv' , 'wbtm_bus' , 'side' );
        remove_meta_box( 'wbtm_bus_routediv' , 'wbtm_bus' , 'side' );
      }





   public function wbtm_bus_ticket_type() {
        global $post,$wbtmmain;
        $values           = get_post_custom( $post->ID );
        // $mep_bus_seat     = get_post_meta($post->ID, 'wbtm_bus_seat', true);
      ?>
      <style type="text/css">
      div#webmenu_msdd {
          width: 250px!important;
      }
      table#repeatable-fieldset-seat-one tr td input, table#repeatable-fieldset-seat-one-dd tr td input {
          width: auto;
          min-width: 20px;
          max-width: 60px;
      }
      </style>
      <table style="width: 100%;margin: 30px auto;">
        <tr>
          <th><?php _e('Driver Seat Position','bus-ticket-booking-with-seat-reservation'); ?></th>
          <td>
            <?php
                if(array_key_exists('driver_seat_position', $values)){
                    $position = $values['driver_seat_position'][0];
                }else{
                    $position = 'left';
                }
                $wbtmmain->wbtm_get_driver_position($position);
            ?>
          </td>
          <td>
            <label><?php _e('Total Seat Columns','bus-ticket-booking-with-seat-reservation'); ?>
              <input type="number" value='<?php if(array_key_exists('wbtm_seat_cols', $values)){ echo $values['wbtm_seat_cols'][0]; } ?>' name="seat_col" id='seat_col' style="width: 70px;" pattern="[1-9]*" inputmode="numeric"  min="0" max="">
            </label>
          </td>
          <td>
            <label><?php _e('Total Seat Rows','bus-ticket-booking-with-seat-reservation'); ?>
              <input type="number" value='<?php if(array_key_exists('wbtm_seat_rows', $values)){ echo $values['wbtm_seat_rows'][0]; } ?>' name="seat_rows" id='seat_rows' style="width: 70px;" pattern="[1-9]*" inputmode="numeric"  min="0" max="">
            </label>
          </td>
          <td><button id="create_seat_plan"><span class="dashicons dashicons-plus"></span><?php _e('Create Seat Plan','bus-ticket-booking-with-seat-reservation'); ?></button></td>
        </tr>
      </table>
      <div id="seat_result">
        <?php
        if(array_key_exists('wbtm_bus_seats_info', $values)){
        $old        = $values['wbtm_bus_seats_info'][0];
        $seatrows   = $values['wbtm_seat_rows'][0];
        $seatcols   = $values['wbtm_seat_cols'][0];
        $seats      = unserialize($old);
      // if($old){
        ?>
      <script type="text/javascript">
        jQuery(document).ready(function( $ ){
          $( '#add-seat-row' ).on('click', function() {
            var row = $( '.empty-row-seat.screen-reader-text' ).clone(true);
            row.removeClass( 'empty-row-seat screen-reader-text' );
            row.insertBefore( '#repeatable-fieldset-seat-one tbody>tr:last' );
            var qtt = parseInt($('#seat_rows').val(), 10);
            $('#seat_rows').val(qtt+1);
            return false;
          });
          $( '.remove-seat-row' ).on('click', function() {
            $(this).parents('tr').remove();
            var qtt = parseInt($('#seat_rows').val(), 10);
            $('#seat_rows').val(qtt-1);
            return false;
          });
        });
      </script>
      <table id="repeatable-fieldset-seat-one" width="100%">
      <tbody>
      <?php
      // echo '<pre>';
      // print_r($seats);
      // echo '</pre>';

      foreach ($seats as $_seats) {
      ?>
          <tr>
            <?php
            for ($x=1; $x <=$seatcols; $x++){
                $text_field_name = "seat" . $x;
                $seat_type_name = "seat_types" . $x;
                ?>
                <td align="center">
                  <input type="text" value="<?php echo $_seats[$text_field_name]; ?>" name="<?php echo $text_field_name; ?>[]"  class="text">
                <?php wbtm_get_seat_type_list($seat_type_name,$post->ID); ?>
                
                </td>
                <?php
            }
            ?>
              <td align="center"><a class="button remove-seat-row" href="#"><span class="dashicons dashicons-trash" style="margin-top: 3px;color: red;"></span><?php _e('Remove','bus-ticket-booking-with-seat-reservation'); ?></a>
                  <input type="hidden" name="bus_seat_panels[]">
                </td>
          </tr>
            <?php } ?>
          <!-- empty hidden one for jQuery -->
          <tr class="empty-row-seat screen-reader-text">
            <?php
            for ($row = 1; $row <= $seatcols; $row++) {
              $seat_type_name = "seat_types" . $row;
            ?>
              <td align="center">
                <input type="text" value="" name="seat<?php echo $row; ?>[]"  class="text">
                <?php wbtm_get_seat_type_list($seat_type_name); ?>
              </td>
            <?php } ?>
              <td align="center"><a class="button remove-seat-row" href="#"><span class="dashicons dashicons-trash" style="margin-top: 3px;color: red;"></span><?php _e('Remove','bus-ticket-booking-with-seat-reservation'); ?></a><input type="hidden" name="bus_seat_panels[]"></td>
          </tr>
        </tbody>
      </table>
      <p><div id="add-seat-row" class="button"><i class="fas fa-plus-square"></i> <?php _e(' Add New Seat Row','bus-ticket-booking-with-seat-reservation'); ?></div></p>
      <?php } ?>
      </div>







      <script type="text/javascript">
        jQuery( "#create_seat_plan" ).click(function(e) {
           e.preventDefault();
           // alert('Yes');
              seat_col        = jQuery("#seat_col").val().trim();
              seat_row        = jQuery("#seat_rows").val().trim();
              jQuery.ajax({
                type: 'POST',
                url:wbtm_ajax.wbtm_ajaxurl,
                data: {"action": "wbtm_seat_plan", "seat_col":seat_col, "seat_row":seat_row},
              beforeSend: function(){
                  jQuery('#seat_result').html('<span class=search-text style="display:block;background:#ddd:color:#000:font-weight:bold;text-align:center">Creating Seat Plan...</span>');
                      },
              success: function(data)
                  {
                    jQuery('#seat_result').html(data);
                  }
                });
               return false;
            });

      </script>


<!-- Double Decker Seat Plan Here -->


<h1><?php _e('Seat Plan For Upper Deck','bus-ticket-booking-with-seat-reservation') ?></h1>


<table style="width: 100%;margin: 30px auto;">
        <tr>
          <td>
            <label><?php _e('Total Seat Columns','bus-ticket-booking-with-seat-reservation'); ?>
              <input type="number" value='<?php if(array_key_exists('wbtm_seat_cols_dd', $values)){ echo $values['wbtm_seat_cols_dd'][0]; } ?>' name="seat_col_dd" id='seat_col_dd' style="width: 70px;" pattern="[1-9]*" inputmode="numeric"  min="0" max="">
            </label>
          </td>
          <td>
            <label><?php _e('Total Seat Rows','bus-ticket-booking-with-seat-reservation'); ?>
              <input type="number" value='<?php if(array_key_exists('wbtm_seat_rows_dd', $values)){ echo $values['wbtm_seat_rows_dd'][0]; } ?>' name="seat_rows_dd" id='seat_rows_dd' style="width: 70px;" pattern="[1-9]*" inputmode="numeric"  min="0" max="">
            </label>
          </td>
          <td>
            <label><?php _e('Price Increase','bus-ticket-booking-with-seat-reservation'); ?>
              <input type="number" value='<?php if(array_key_exists('wbtm_seat_dd_price_parcent', $values)){ echo $values['wbtm_seat_dd_price_parcent'][0]; } ?>' name="wbtm_seat_dd_price_parcent" id='wbtm_seat_dd_price_parcent' style="width: 70px;" pattern="[1-9]*" inputmode="numeric"  min="0" max="">%
              <?php //_e('Please enter a Parcent (%) amount of price increase for this Seat panel. Suppouse you want to increase 10% increase price for this panel just put 10 here.','') ?>
            </label>
          </td>
          <td><button id="create_seat_plan_dd"><span class="dashicons dashicons-plus"></span><?php _e('Create Seat Plan','bus-ticket-booking-with-seat-reservation'); ?></button></td>
        </tr>
      </table>
      <div id="seat_result_dd">
      <?php
        if(array_key_exists('wbtm_bus_seats_info_dd', $values)){
        $old        = $values['wbtm_bus_seats_info_dd'][0];
        $seatrows   = $values['wbtm_seat_rows_dd'][0];
        $seatcols   = $values['wbtm_seat_cols_dd'][0];
        $seats      = unserialize($old);
        // print_r($seats);
      // if($old){
        ?>
      <script type="text/javascript">
        jQuery(document).ready(function( $ ){
          $( '#add-seat-row-dd' ).on('click', function() {
            var row = $( '.empty-row-seat-dd.screen-reader-text' ).clone(true);
            row.removeClass( 'empty-row-seat-dd screen-reader-text' );
            row.insertBefore( '#repeatable-fieldset-seat-one-dd tbody>tr:last' );
            var qtt = parseInt($('#seat_rows_dd').val(), 10);
            $('#seat_rows_dd').val(qtt+1);
            return false;
          });
          $( '.remove-seat-row-dd' ).on('click', function() {
            $(this).parents('tr').remove();
            var qtt = parseInt($('#seat_rows_dd').val(), 10);
            $('#seat_rows_dd').val(qtt-1);
            return false;
          });
        });
      </script>
      <table id="repeatable-fieldset-seat-one-dd" width="100%">
      <tbody>
      <?php
      if(is_array($seats) && sizeof($seats) > 0){
      foreach ($seats as $_seats) {
      ?>
          <tr>
            <?php
            for ($x=1; $x <=$seatcols; $x++){
                $text_field_name = "dd_seat" . $x;
                ?>
                <td align="center"><input type="text" value="<?php echo $_seats[$text_field_name]; ?>" name="<?php echo $text_field_name; ?>[]"  class="text"></td>
                <?php
            }
            ?>
                <td align="center"><a class="button remove-seat-row-dd" href="#"><?php _e('Remove','bus-ticket-booking-with-seat-reservation'); ?></a>
                  <input type="hidden" name="bus_seat_panels_dd[]">
                </td>
          </tr>
            <?php } } ?>
          <!-- empty hidden one for jQuery -->
          <tr class="empty-row-seat-dd screen-reader-text">
            <?php
            for ($row = 1; $row <= $seatcols; $row++) {
            ?>
              <td align="center"><input type="text" value="" name="seat<?php echo $row; ?>[]"  class="text"></td>
            <?php } ?>
              <td align="center"><a class="button remove-seat-row-dd" href="#"><?php _e('Remove','bus-ticket-booking-with-seat-reservation'); ?></a><input type="hidden" name="bus_seat_panels_dd[]"></td>
          </tr>
        </tbody>
      </table>
      <p><div id="add-seat-row-dd" class="button"><i class="fas fa-plus-square"></i> <?php _e('Add New Seat Row','bus-ticket-booking-with-seat-reservation'); ?></div></p>
      <?php } ?>
      </div>






      <script type="text/javascript">
            jQuery( "#create_seat_plan_dd" ).click(function(e) {
           e.preventDefault();
           // alert('Yes');
              seat_col        = jQuery("#seat_col_dd").val().trim();
              seat_row        = jQuery("#seat_rows_dd").val().trim();
              jQuery.ajax({
                type: 'POST',
                url:wbtm_ajax.wbtm_ajaxurl,
                data: {"action": "wbtm_seat_plan_dd", "seat_col":seat_col, "seat_row":seat_row},
              beforeSend: function(){
                  jQuery('#seat_result_dd').html('<span class=search-text style="display:block;background:#ddd:color:#000:font-weight:bold;text-align:center">Creating Seat Plan...</span>');
                      },
              success: function(data)
                  {
                    jQuery('#seat_result_dd').html(data);
                  }
                });
               return false;
            });
</script>



<?php
}



function wbtm_bus_seat_panels_meta_save($post_id){
    global $post,$wbtmmain;
    if($post){
    $pid = $post->ID;
    if ($post->post_type != 'wbtm_bus'){
        return;
    }


    $seat_col             = strip_tags($_POST['seat_col']);
    $seat_row             = strip_tags($_POST['seat_rows']);
    $old = get_post_meta($post_id, 'wbtm_bus_seats_info', true);
    $new = array();
    $bus_seat_panels   = $_POST['bus_seat_panels'];
    $count             = count( $bus_seat_panels )-2;
    for ( $r = 0; $r <= $count; $r++ ) {
        for ($x=1; $x <= $seat_col; $x++ ){
            $text_field_name = "seat" . $x;
            $seat_type_name = "seat_types" . $x;
              $new[$r][$text_field_name] = stripslashes( strip_tags($_POST[$text_field_name][$r] ));
              //$new[$r][$seat_type_name] = implode(',',$_POST[$seat_type_name][$r] );
        }
    }

    $bus_start_time = $wbtmmain->get_bus_start_time($post_id);
    update_post_meta( $post_id, 'wbtm_bus_start_time', $bus_start_time );

  if (!empty($new) && $new != $old )
    update_post_meta( $post_id, 'wbtm_bus_seats_info', $new );
  elseif ( empty($new) && $old )
    delete_post_meta( $post_id, 'wbtm_bus_seats_info', $old );

// maybe_unserialize()

// Save Double Deacker Seat Data

$seat_col_dd             = strip_tags($_POST['seat_col_dd']);
$seat_row_dd             = strip_tags($_POST['seat_rows_dd']);
$wbtm_seat_dd_price_parcent             = strip_tags($_POST['wbtm_seat_dd_price_parcent']);
$old = get_post_meta($post_id, 'wbtm_bus_seats_info_dd', true);
$new = array();
$bus_seat_panels_dd   = $_POST['bus_seat_panels_dd'] ? $_POST['bus_seat_panels_dd'] : array();
$count             = count( $bus_seat_panels_dd )-2;
for ( $r = 0; $r <= $count; $r++ ) {
    for ($x=1; $x <= $seat_col_dd; $x++ ){
        $text_field_name = "dd_seat" . $x;
          $new[$r][$text_field_name] = stripslashes( strip_tags($_POST[$text_field_name][$r] ));
    }
}
if (!empty($new) && $new != $old )
update_post_meta( $post_id, 'wbtm_bus_seats_info_dd', $new );
elseif ( empty($new) && $old )
delete_post_meta( $post_id, 'wbtm_bus_seats_info_dd', $old );

update_post_meta( $pid, 'wbtm_seat_cols_dd', $seat_col_dd);
update_post_meta( $pid, 'wbtm_seat_rows_dd', $seat_row_dd);
update_post_meta( $pid, 'wbtm_seat_dd_price_parcent', $wbtm_seat_dd_price_parcent);








  $update_seat_col      = update_post_meta( $pid, 'wbtm_seat_cols', $seat_col);
  update_post_meta( $pid, '_price', 0);
  $update_seat_row      = update_post_meta( $pid, 'wbtm_seat_rows', $seat_row);
  $driver_seat_position  = strip_tags($_POST['driver_seat_position']);
  $update_wbtm_driver_seat_position     = update_post_meta( $pid, 'driver_seat_position', $driver_seat_position);
  $update_seat_stock_status         = update_post_meta( $pid, '_sold_individually', 'yes');
}
}


    public function meta_boxs(){
        global $wbtmmain, $wbtmcore;

            $bus_panel_routing = array(
                'page_nav' 	=> __( '<i class="fas fa-cog"></i> Nav Title 2', 'bus-ticket-booking-with-seat-reservation' ),
                'priority' => 10,
                'sections' => array(
                    'section_2' => array(
                        'title' 	=> 	__('','bus-ticket-booking-with-seat-reservation'),
                        'description' 	=> __('','bus-ticket-booking-with-seat-reservation'),
                        'options' 	=> array(
                            array(
                                'id'		=> 'wbtm_bus_bp_stops',
                                'title'		=> __('Boarding Point','bus-ticket-booking-with-seat-reservation'),
                                'details'	=> __('Please select Boarding point and time ','bus-ticket-booking-with-seat-reservation'),
                                'collapsible'=>true,
                                'type'		=> 'repeatable',
                                'btn_text'  => 'Add New Boarding Point',
                                'title_field' => 'wbtm_bus_bp_stops_name',
                                'fields'    => array(
                                     array(
                                         'type'=>'select',
                                         'default'=>'option_1',
                                         'item_id'=>'wbtm_bus_bp_stops_name',
                                         'name'=>'Stops Name',
                                         'args'=> 'TAXN_%wbtm_bus_stops%'
                                        ),
                                        array(
                                            'type'=>'time',
                                            'default'=>'',
                                            'item_id'=>'wbtm_bus_bp_start_time',
                                            'name'=>'Time'
                                        ),
                                ),
                            ),
                            array(
                                'id'		=> 'wbtm_bus_next_stops',
                                'title'		=> __('Dropping Point','bus-ticket-booking-with-seat-reservation'),
                                'details'	=> __('Please Select Dropping point and time ','bus-ticket-booking-with-seat-reservation'),
                                'collapsible'=>true,
                                'type'		=> 'repeatable',
                                'btn_text'	=> 'Add New Dropping Point',
                                'title_field' => 'wbtm_bus_next_stops_name',
                                'fields'    => array(
                                     array(
                                         'type'=>'select',
                                         'default'=>'option_1',
                                         'item_id'=>'wbtm_bus_next_stops_name',
                                         'name'=>'Stops Name',
                                         'args'=> 'TAXN_%wbtm_bus_stops%'
                                        ),
                                        array(
                                            'type'=>'time',
                                            'default'=>'',
                                            'item_id'=>'wbtm_bus_next_end_time',
                                            'name'=>'Time'
                                        ),
                                ),
                            )
                        )
                    ),

                ),
            );
            $bus_pricing = array(
                'page_nav' 	=> __( '<i class="fas fa-cog"></i> Nav Title 2', 'bus-ticket-booking-with-seat-reservation' ),
                'priority' => 10,
                'sections' => array(
                    'section_2' => array(
                        'title' 	=> 	__('','bus-ticket-booking-with-seat-reservation'),
                        'description' 	=> __('','bus-ticket-booking-with-seat-reservation'),
                        'options' 	=> array(
                          array(
                            'id'    => 'wbtm_available_seat_type',
                            'title'    => __('Available Seat Type For this bus','text-domain'),
                            'details'  => __('Please select what type of seats are availabe in this bus','text-domain'),
                            'type'    => 'select2',
                            'default'    => '',
                            'multiple'    => true,
                            'args'    => array(
                              'adult' => wbtm_get_seat_type_label('adult','Adult'),
                              'child' => wbtm_get_seat_type_label('child','Child'),
                              'infant' => wbtm_get_seat_type_label('infant','Infant'),
                              'special' => wbtm_get_seat_type_label('special','Special')
                            )
                        ),



                            array(
                                'id'		=> 'wbtm_bus_prices',
                                'title'		=> __('Bus Pricing','bus-ticket-booking-with-seat-reservation'),
                                'details'	=> __('Please Select Bus Boarding & Dropping Stops and price','bus-ticket-booking-with-seat-reservation'),
                                'collapsible'=>true,
                                'type'		=> 'repeatable',
                                'btn_text'  => 'Add New Price',
                                'title_field' => 'wbtm_bus_bp_price_stop/wbtm_bus_dp_price_stop',
                                'fields'    => array(
                                     array(
                                         'type'=>'select',
                                        //  'default'=>'option_1',
                                         'item_id'=>'wbtm_bus_bp_price_stop',
                                         'name'=>'Start Stop Name',
                                         'args'=> 'TAXN_%wbtm_bus_stops%'
                                        ),
                                     array(
                                         'type'=>'select',
                                         'default'=>'option_1',
                                         'item_id'=>'wbtm_bus_dp_price_stop',
                                         'name'=>'End Stop Name',
                                         'args'=> 'TAXN_%wbtm_bus_stops%'
                                        ),                                  
                                     array(
                                            'type'=>'text',
                                            'default'=>'',
                                            'item_id'=>'wbtm_bus_price',
                                            'name'=> wbtm_get_seat_type_label('adult','Adult').' Price'
                                        ),
                                     array(
                                            'type'=>'text',
                                            'default'=>'',
                                            'item_id'=>'wbtm_bus_child_price',
                                            'name'=> wbtm_get_seat_type_label('child','Child').' Price'
                                        ),
                                     array(
                                            'type'=>'text',
                                            'default'=>'',
                                            'item_id'=>'wbtm_bus_infant_price',
                                            'name'=> wbtm_get_seat_type_label('infant','Infant').' Price'
                                        ),
                                     array(
                                            'type'=>'text',
                                            'default'=>'',
                                            'item_id'=>'wbtm_bus_special_price',
                                            'name'=> wbtm_get_seat_type_label('special','Special').' Price'
                                        ),
                                ),
                            ),

                        )
                    ),

                ),
            );
            $bus_information = array(
                'page_nav' 	=> __( '<i class="fas fa-cog"></i> Nav Title 2', 'bus-ticket-booking-with-seat-reservation' ),
                'priority' => 10,
                'sections' => array(
                    'section_2' => array(
                        'title' 	=> 	__('','bus-ticket-booking-with-seat-reservation'),
                        'description' 	=> __('','bus-ticket-booking-with-seat-reservation'),
                        'options' 	=> array(

                            array(
                                'id'		    => 'wbtm_bus_no',
                                'title'		    => __('Coach No','bus-ticket-booking-with-seat-reservation'),
                                'details'	    => __('Please enter coach no here','bus-ticket-booking-with-seat-reservation'),
                                'type'		    => 'text',
                                'placeholder'   => __('Coach No','bus-ticket-booking-with-seat-reservation'),
                            ),

                            array(
                                'id'		    => 'wbtm_total_seat',
                                'title'		    => __('Total Seat','bus-ticket-booking-with-seat-reservation'),
                                'details'	    => __('Please enter Total Seat here','bus-ticket-booking-with-seat-reservation'),
                                'type'		    => 'text',
                                'placeholder'   => __('Total Seat','bus-ticket-booking-with-seat-reservation'),
                            ),

                        )
                    ),

                ),
            );
            $bus_off_day_information = array(
                'page_nav' 	=> __( '<i class="fas fa-cog"></i> Nav Title 2', 'bus-ticket-booking-with-seat-reservation' ),
                'priority' => 10,
                'sections' => array(
                    'section_2' => array(
                        'title' 	=> 	__('','bus-ticket-booking-with-seat-reservation'),
                        'description' 	=> __('','bus-ticket-booking-with-seat-reservation'),
                        'options' 	=> array(
                            array(
                                'id'		    => 'wbtm_od_start',
                                'title'		    => __('Offday Start Date','bus-ticket-booking-with-seat-reservation'),
                                'details'	    => __('Please enter Offday Start Date here','bus-ticket-booking-with-seat-reservation'),
                                'type'		    => 'datepicker',
                                'placeholder'   => __('Offday Start Date','bus-ticket-booking-with-seat-reservation'),
                            ),
                            array(
                                'id'		    => 'wbtm_od_end',
                                'title'		    => __('Offday End date','bus-ticket-booking-with-seat-reservation'),
                                'details'	    => __('Please enter Offday End date here','bus-ticket-booking-with-seat-reservation'),
                                'type'		    => 'datepicker',
                                'placeholder'   => __('Offday End date','bus-ticket-booking-with-seat-reservation'),
                            ),
                            array(
                                'id'		=> 'offday_sun',
                                'type'		    => 'checkbox',
                                'args'		=> array(
                                    'yes'	=> __('Sunday','bus-ticket-booking-with-seat-reservation'),
                                ),
                            ),
                            array(
                                'id'		=> 'offday_mon',
                                'type'		    => 'checkbox',
                                'args'		=> array(
                                    'yes'	=> __('Monday','bus-ticket-booking-with-seat-reservation'),
                                ),
                            ),
                            array(
                                'id'		=> 'offday_tue',
                                'type'		    => 'checkbox',
                                'args'		=> array(
                                    'yes'	=> __('Tuesday','bus-ticket-booking-with-seat-reservation'),
                                ),
                            ),
                            array(
                                'id'		=> 'offday_wed',
                                'type'		    => 'checkbox',
                                'args'		=> array(
                                    'yes'	=> __('Wednesday','bus-ticket-booking-with-seat-reservation'),
                                ),
                            ),
                            array(
                                'id'		=> 'offday_thu',
                                'type'		    => 'checkbox',
                                'args'		=> array(
                                    'yes'	=>  __('Thursday','bus-ticket-booking-with-seat-reservation'),
                                ),
                            ),
                            array(
                                'id'		=> 'offday_fri',
                                'type'		    => 'checkbox',
                                'args'		=> array(
                                    'yes'	=> __('Friday','bus-ticket-booking-with-seat-reservation'),
                                ),
                            ),
                            array(
                                'id'		=> 'offday_sat',
                                'type'		    => 'checkbox',
                                'args'		=> array(
                                    'yes'	=> 'Saturday'
                                ),
                            ),
                        )
                    ),

                ),
            );


            $bus_on_day = array(
                'page_nav' 	=> __( '<i class="fas fa-cog"></i> Nav Title 2', 'bus-ticket-booking-with-seat-reservation' ),
                'priority' => 10,
                'sections' => array(
                    'section_2' => array(
                        'title' 	=> 	__('','bus-ticket-booking-with-seat-reservation'),
                        'description' 	=> __('','bus-ticket-booking-with-seat-reservation'),
                        'options' 	=> array(
                            array(
                                'id'		=> 'wbtm_bus_on_dates',
                                'title'		=> __('Add Particular On Date','bus-ticket-booking-with-seat-reservation'),
                                'details'	=> __('Please Enter Add Particular On Date, Operational Offday setting will not work if you set bus in particular date here.

                                ','bus-ticket-booking-with-seat-reservation'),
                                'collapsible'=>true,
                                'type'		=> 'repeatable',
                                'title_field' => 'wbtm_on_date_name',
                                'fields'    => array(
                                     array(
                                         'type'=>'date',
                                         'default'=>'option_1',
                                         'item_id'=>'wbtm_on_date_name',
                                         'name'=>'Start Stop Name',
                                        )
                                ),
                            ),

                        )
                    ),

                ),
            );
            $route_args = array(
                'meta_box_id'               => 'bus_meta_boxes_route',
                'meta_box_title'            => __( 'Bus Routing Information', 'bus-ticket-booking-with-seat-reservation' ),
                //'callback'       => '_meta_box_callback',
                'screen'                    => array( 'wbtm_bus'),
                'context'                   => 'normal', // 'normal', 'side', and 'advanced'
                'priority'                  => 'high', // 'high', 'low'
                'callback_args'             => array(),
                'nav_position'              => 'none', // right, top, left, none
                'item_name'                 => "MagePeople",
                'item_version'              => "2.0",
                'panels' 	        => array(
                    'bus_panel_routing'        => $bus_panel_routing
                ),
            );
            $price_args = array(
                'meta_box_id'               => 'bus_meta_boxes_pricing',
                'meta_box_title'            => __( 'Bus Pricing Information', 'bus-ticket-booking-with-seat-reservation' ),
                //'callback'       => '_meta_box_callback',
                'screen'                    => array( 'wbtm_bus'),
                'context'                   => 'normal', // 'normal', 'side', and 'advanced'
                'priority'                  => 'high', // 'high', 'low'
                'callback_args'             => array(),
                'nav_position'              => 'none', // right, top, left, none
                'item_name'                 => "MagePeople",
                'item_version'              => "2.0",
                'panels' 	        => array(
                    'bus_pricing'              => $bus_pricing
                ),
            );
            $info_args = array(
                'meta_box_id'               => 'bus_meta_boxes_info',
                'meta_box_title'            => __( 'Bus Information', 'bus-ticket-booking-with-seat-reservation' ),
                //'callback'       => '_meta_box_callback',
                'screen'                    => array( 'wbtm_bus'),
                'context'                   => 'normal', // 'normal', 'side', and 'advanced'
                'priority'                  => 'high', // 'high', 'low'
                'callback_args'             => array(),
                'nav_position'              => 'none', // right, top, left, none
                'item_name'                 => "MagePeople",
                'item_version'              => "2.0",
                'panels' 	        => array(
                    'bus_information'          => $bus_information
                ),
            );
            $oday_args = array(
                'meta_box_id'               => 'bus_meta_boxes_offday',
                'meta_box_title'            => __( 'Operational offday settings', 'bus-ticket-booking-with-seat-reservation' ),
                //'callback'       => '_meta_box_callback',
                'screen'                    => array( 'wbtm_bus'),
                'context'                   => 'normal', // 'normal', 'side', and 'advanced'
                'priority'                  => 'high', // 'high', 'low'
                'callback_args'             => array(),
                'nav_position'              => 'none', // right, top, left, none
                'item_name'                 => "MagePeople",
                'item_version'              => "2.0",
                'panels' 	                => array(
                    'bus_off_day_information' => $bus_off_day_information
                ),
            );
            $onday_args = array(
                'meta_box_id'               => 'bus_meta_boxes_onday',
                'meta_box_title'            => __( 'Operational Onday settings', 'bus-ticket-booking-with-seat-reservation' ),
                //'callback'       => '_meta_box_callback',
                'screen'                    => array( 'wbtm_bus'),
                'context'                   => 'normal', // 'normal', 'side', and 'advanced'
                'priority'                  => 'high', // 'high', 'low'
                'callback_args'             => array(),
                'nav_position'              => 'none', // right, top, left, none
                'item_name'                 => "MagePeople",
                'item_version'              => "2.0",
                'panels' 	                => array(
                    'bus_on_day' => $bus_on_day

                ),
            );

            $BusRoute = new AddMetaBox( $route_args );
            $BusPrice = new AddMetaBox( $price_args );
            $BusInfo = new AddMetaBox( $info_args );
            $BusoDay = new AddMetaBox( $oday_args );
            $BusoNDay = new AddMetaBox( $onday_args );
    }
}
new WBTMMetaBox();
