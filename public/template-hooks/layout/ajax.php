<?php
add_action('wp_ajax_mage_bus_price_convert', 'mage_bus_price_convert');
add_action('wp_ajax_nopriv_mage_bus_price_convert', 'mage_bus_price_convert');
function mage_bus_price_convert(){
    echo wc_price(strip_tags($_POST['price']));
    die();
}
add_action('wp_ajax_mage_bus_selected_seat_item', 'mage_bus_selected_seat_item');
add_action('wp_ajax_nopriv_mage_bus_selected_seat_item', 'mage_bus_selected_seat_item');
function mage_bus_selected_seat_item(){
    ?>
    <div class="flexEqual mage_bus_selected_seat_item" data-seat-name="<?php echo $_POST['seat_name']; ?>">
        <h6><?php echo $_POST['seat_name']; ?></h6>
        <?php
        if(mage_bus_multiple_passenger_type_check($_POST['id'],$_POST['start'],$_POST['end'])){
            $seat_panel_settings = get_option('wbtm_bus_settings');
            $adult_label = $seat_panel_settings['wbtm_seat_type_adult_label'];
            $child_label = $seat_panel_settings['wbtm_seat_type_child_label'];
            $infant_label = $seat_panel_settings['wbtm_seat_type_infant_label'];
            $special_label = $seat_panel_settings['wbtm_seat_type_special_label'];
            if(1==$_POST['passenger_type']){
                $type=$child_label;
            }elseif(2==$_POST['passenger_type']){
                $type=$infant_label;
            }elseif(3==$_POST['passenger_type']){
                $type=$special_label;
            }else{
                $type=$adult_label;
            }
            echo '<h6>'.$type.'</h6>';
        }
        ?>
        <h6><?php echo wc_price($_POST['price']); ?></h6>
        <h6><span class="fa fa-trash mage_bus_seat_unselect"></span></h6>
    </div>
    <?php
    die();
}