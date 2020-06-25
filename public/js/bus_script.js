(function ($) {
    'use strict';

    jQuery(document).ready(function ($) {
        // jQuery("#r_date").datepicker({
        //     dateFormat: "yy-mm-dd",
        //     minDate: 0
        // });
        jQuery("#ja_date").datepicker({
            dateFormat: "yy-mm-dd"
        });
        jQuery(".the_select select").select2();
        jQuery("#boarding_point, #drp_point").select2();
    });
    //one way return
    $(document).on({
        click: function () {
            $('.mage_return_date').slideUp(300).removeClass('mage_hidden').find('input').val('');
        }
    }, '#one_way');
    $(document).on({
        click: function () {
            $('.mage_return_date').slideDown(300).removeClass('mage_hidden');
        }
    }, '#return');
    //qty inc dec
    $(document).on({
        click: function () {
            let target = $(this).siblings('input');
            let value = (parseInt(target.val()) - 1) > 0 ? (parseInt(target.val()) - 1) : 0;
            mageTicketQty(target, value);
        }
    }, '.mage_qty_dec');
    $(document).on({
        click: function () {
            let target = $(this).siblings('input');
            let value = parseInt(target.val()) + 1;
            mageTicketQty(target, value);
        }
    }, '.mage_qty_inc');
    $(document).on({
        keyup: function () {
            let target = $(this);
            let value = parseInt(target.val());
            mageTicketQty(target, value);
        }
    }, '.mage_form_qty input.mage_form_control');

    function mageTicketQty(target, value) {
        let minSeat = parseInt(target.attr('min'));
        let maxSeat = parseInt(target.attr('max'));
        target.siblings('.mage_qty_inc , .mage_qty_dec').removeClass('mage_disabled');
        if (value < minSeat || isNaN(value) || value === 0) {
            value = minSeat;
            target.siblings('.mage_qty_dec').addClass('mage_disabled');
        }
        if (value > maxSeat) {
            value = maxSeat;
            target.siblings('.mage_qty_inc').addClass('mage_disabled');
        }
        target.val(value);
        if (target.parents().hasClass('mage_bus_item')) {
            mage_bus_price_qty_calculation(target.parents('.mage_bus_item'));
        }
    }

    // input use drop down selector
    $(document).on('click', function (event) {
        let selectUl = $('ul.mage_input_select_list');
        if (!$(event.target).parents().hasClass('mage_input_select') && selectUl.is(':visible')) {
            let target = $('.mage_input_select input');
            target.each(function (index) {
                let input = $(this).val().toLowerCase();
                let flag = 0;
                $(this).parents('.mage_input_select').find('li').filter(function () {
                    if ($(this).attr('data-route').toLowerCase() === input) {
                        flag = 1;
                        mage_bus_dropping_point(selectUl);
                    }
                });
                if (flag < 1) {
                    $(this).val('');
                }
            });
            selectUl.slideUp(200);
        }
    });
    $(document).on({
        keyup: function () {
            let input = $(this).val().toLowerCase();
            $(this).parents('.mage_input_select').find('ul.mage_input_select_list').find('li').filter(function () {
                $(this).toggle($(this).attr('data-route').toLowerCase().indexOf(input) > -1);
            });
        },
        click: function () {
            $(this).parents('.mage_input_select').find('ul.mage_input_select_list').slideDown(200);
        }
    }, '.mage_input_select input');
    $(document).on({
        click: function () {
            let route = $(this).attr('data-route');
            $(this).parents('ul.mage_input_select_list').slideUp(200).parents('.mage_input_select').find('input').val(route);
            mage_bus_dropping_point($(this));
        }
    }, 'ul.mage_input_select_list li');
    
    function mage_bus_dropping_point(target){
        if(target.parents().hasClass('mage_bus_boarding_point')){                        
            var boarding_point =target.attr('data-route');               
              $.ajax({
                type: 'POST',
                url:wbtm_ajax.wbtm_ajaxurl,
                data: {"action": "wbtm_load_dropping_point", "boarding_point":boarding_point},
                    beforeSend: function(){
                            $('#wbtm_dropping_point_inupt').val('');   
                            $('#wbtm_dropping_point_list').slideUp(200);    
                            $('#wbtm_show_msg').show();
                            $('#wbtm_show_msg').html('<span>Loading..</span>');
                    },
                    success: function(data){   
                            $('#wbtm_show_msg').hide();
                            $('#wbtm_dropping_point_inupt').val('');                           
                            $('.mage_bus_dropping_point .mage_input_select_list').html(data);  
                            $('#wbtm_dropping_point_list').slideDown(200);                         
                    }
                });
               return false;                                                
        }
    }
    
    //bus details toggle
    $(document).on({
        click: function () {
            let target = $(this).parents('.mage_bus_item').find('.mage_bus_seat_details');
            if (target.is(':visible')) {
                target.slideUp(300);
            } else {
                $('.mage_bus_item').find('.mage_bus_seat_details').slideUp(300);
                target.slideDown(300);
            }
        }
    }, '.mage_bus_details_toggle');
    //bus seat selected price,qty calculation,extra price
    $(document).on({
        click: function () {
            let target = $(this);
            let parents = target.parents('.mage_bus_item');
            $.when(mage_seat_selection(target)).then(function () {
                mage_bus_price_qty_calculation(parents);
            });
        }
    }, '.mage_bus_seat_item');
    $(document).on({
        click: function () {
            let target = $(this);
            let targetParents = target.parents('.mage_bus_item');
            let seatName = target.parents('.mage_bus_selected_seat_item').attr('data-seat-name');
            targetParents.find('.mage_bus_seat_plan [data-seat-name="' + seatName + '"]').trigger('click');
        }
    }, '.mage_bus_seat_unselect');

    function mage_seat_selection(target) {
        let parents = target.parents('.mage_bus_item');
        let seatName = target.attr('data-seat-name');
        let currency = parents.find('input[name="mage_currency"]').val();
        let price = currency + parseFloat(target.attr('data-price')).toFixed(2);

        if (target.hasClass('mage_selected')) {
            target.removeClass('mage_selected');
            parents.find('.mage_bus_selected_seat_list [data-seat-name="' + seatName + '"]').slideUp(200, function () {
                $(this).remove();
            });
            if (parents.find('.mage_customer_info_area [data-seat-name="' + seatName + '"]').length > 0) {
                parents.find('.mage_customer_info_area [data-seat-name="' + seatName + '"]').slideUp(200, function () {
                    $(this).remove();
                });

            } else {
                parents.find('.mage_customer_info_area input[value="' + seatName + '"]').remove();
            }
        } else {
            target.addClass('mage_selected');
            parents.find('.mage_bus_selected_seat_list').append(mage_bus_seat_item(seatName, price)).slideDown(200);
            parents.find('.mage_customer_info_area').append(mageCustomerInfoFormBus(parents, seatName)).find('[data-seat-name="' + seatName + '"]').slideDown(200);
        }
    }

    //price qty calculation function
    function mage_bus_price_qty_calculation(parents) {
        let qty = 0;
        let subTotal = 0;
        let bagQty = 0;
        let bagPrice = 0;
        let bagPerPrice = 0;
        parents.find('.mage_bus_seat_item.mage_selected').each(function (index) {
            subTotal += parseFloat($(this).attr('data-price'));
            qty++;
        });


        parents.find('.mage_bus_total_qty').html(qty);
        parents.find('.mage_bus_sub_total_price').html(subTotal.toFixed(2));
        parents.find('.mage_customer_info_area input[name="extra_bag_quantity[]"]').each(function (index) {
            bagPerPrice = parseFloat($(this).attr('data-price'));
            bagQty += parseInt($(this).val());
            bagPrice += parseFloat($(this).val()) * bagPerPrice;
        });
        let totalPrice = subTotal + (bagPrice > 0 ? parseFloat(bagPrice) : 0);
        parents.find('.mage_bus_total_price').html(totalPrice.toFixed(2));
        if (qty > 0) {
            parents.find('form.mage_bus_info_form').slideDown(250);
        } else {
            parents.find('form.mage_bus_info_form').slideUp(250);
        }
        if (bagQty > 0) {
            parents.find('.mage_bus_extra_bag_qty').html(bagQty);
            parents.find('.mage_extra_bag_price').html(bagPerPrice.toFixed(2));
            parents.find('.mage_bus_extra_bag_total_price').html(bagPrice.toFixed(2));
            parents.find('.mage_extra_bag').slideDown(200);
        } else {
            parents.find('.mage_extra_bag').slideUp(200);
        }
    }

    function mage_bus_seat_item(seatName, price) {
        let item = '<div class="flexEqual mage_bus_selected_seat_item" data-seat-name="' + seatName + '">';
        item += '<h6>' + seatName + '</h6>';
        item += '<h6>' + price + '</h6>';
        item += '<h6><spn class="fa fa-trash mage_bus_seat_unselect"></spn></h6>';
        item += '</div>';
        return item;
    }

    //customer form
    function mageCustomerInfoFormBus(parent, seatName) {
        let formTitle = parent.find('input[name="mage_bus_title"]').val() + seatName;
        let currentTarget = parent.find('.mage_hidden_customer_info_form');
        if (currentTarget.length > 0) {
            currentTarget.find('input[name="seat_name[]"]').val(seatName);
            currentTarget.find('.mage_form_list').attr('data-seat-name', seatName).find('.mage_title h5').html(formTitle);
            return currentTarget.html();
        } else {
            return '<input type="hidden" name="seat_name[]" value="' + seatName + '" />';
        }

    }


})(jQuery);
