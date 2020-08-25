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

        // Mage Responsive Form
        var form = jQuery('.mage-responsive-form');
        var form_width = form.width();
        
        if (form_width > 932) {
            form.addClass('mage-form-lg');
        } else {
            form.addClass('mage-form-sm');
        }
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
        let selectUl = $('.mage_input_select_list');
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
            $(this).parents('.mage_input_select').find('.mage_input_select_list').find('li').filter(function () {
                $(this).toggle($(this).attr('data-route').toLowerCase().indexOf(input) > -1);
            });
        },
        click: function () {
            $(this).parents('.mage_input_select').find('.mage_input_select_list').slideDown(200);
            $(this).parents('label').addClass('activeMageSelect');
        },
        blur: function() {
            $(this).parents('label').removeClass('activeMageSelect');
        }
    }, '.mage_input_select input');
    $(document).on({
        click: function () {
            let route = $(this).attr('data-route');
            $(this).parents('.mage_input_select_list').slideUp(200).parents('.mage_input_select').find('input').val(route);
            mage_bus_dropping_point($(this));
            $(this).parents('.mage_input_select_list').siblings('label').removeClass('activeMageSelect');
        }
    }, '.mage_input_select_list li');

    function mage_bus_dropping_point(target) {
        if (target.parents().hasClass('mage_bus_boarding_point')) {
            var boarding_point = target.attr('data-route');
            console.log(boarding_point);
            
            if (boarding_point != undefined) {
                $.ajax({
                    type: 'POST',
                    // url: wbtm_ajax.wbtm_ajaxurl,
                    url: wbtm_ajaxurl,
                    data: { "action": "wbtm_load_dropping_point", "boarding_point": boarding_point },
                    beforeSend: function () {
                        $('#wbtm_dropping_point_inupt').val('');
                        $('#wbtm_dropping_point_list').slideUp(200);
                        $('#wbtm_show_msg').show();
                        $('#wbtm_show_msg').html('<span>Loading..</span>');
                    },
                    success: function (data) {
                        $('#wbtm_show_msg').hide();
                        $('#wbtm_dropping_point_inupt').val('');
                        $('.mage_bus_dropping_point .mage_input_select_list ul').html(data);
                        $('#wbtm_dropping_point_list').slideDown(200);
                        $('#wbtm_dropping_point_list').siblings('label').addClass('activeMageSelect');
                    }
                });
                return false;
            }
        }
    }

    //bus price convert
    function mage_bus_price_convert(price, target,loader) {
        $.ajax({
            type: 'POST',
            // url: wbtm_ajax.wbtm_ajaxurl,
            url: wbtm_ajaxurl,
            data: {"action": "mage_bus_price_convert", "price": price},
            success: function (data) {
                target.html(data);
                if (loader) {
                    defaultLoaderFixedRemove();
                }
            },
            error: function (response) {
                console.log(response);
            }
        });

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
            defaultLoaderFixed();
            mage_seat_selection(target);
        }
    }, '.mage_bus_seat_item');
    $(document).on({
        click: function (e) {
            e.stopPropagation();
            let target = $(this);
            let passengerType = target.attr('data-seat-type');
            if (mage_seat_price_change(target, passengerType)) {
                if (target.parents('.mage_bus_seat_item').hasClass('mage_selected')) {
                    target.parents('.mage_bus_seat_item').trigger('click');
                }
                target.parents('.mage_bus_seat_item').trigger('click');
            }
        }
    }, '.mage_bus_seat_item li');
    $(document).on({
        click: function () {
            let target = $(this);
            let targetParents = target.parents('.mage_bus_item');
            let seatName = target.parents('.mage_bus_selected_seat_item').attr('data-seat-name');
            targetParents.find('.mage_bus_seat_plan [data-seat-name="' + seatName + '"]').trigger('click');
        }
    }, '.mage_bus_seat_unselect');

    function mage_seat_price_change(target, passengerType) {
        let price = target.attr('data-seat-price');
        target.parents('.mage_bus_seat_item').attr('data-price', price).attr('data-passenger-type', passengerType);
        return true;
    }

    function mage_seat_selection(target) {
        console.log('sdkfl')
        let parents = target.parents('.mage_bus_item');
        let seatName = target.attr('data-seat-name');
        let price = target.attr('data-price');
        let passengerType = target.attr('data-passenger-type');
        let busDd = target.attr('data-bus-dd');

        if (target.hasClass('mage_selected')) {
            target.removeClass('mage_selected');
            parents.find('.mage_bus_selected_seat_list [data-seat-name="' + seatName + '"]').slideUp(200, function () {
                $(this).remove();
                mage_bus_price_qty_calculation(parents);
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
            let start = $('input[name="start_stops"]').val();
            let end = $('input[name="end_stops"]').val();
            let bus_id = parents.find('input[name="bus_id"]').val();
            parents.find('.mage_customer_info_area').append(mageCustomerInfoFormBus(parents, seatName, passengerType,busDd)).find('[data-seat-name="' + seatName + '"]').slideDown(200);
            $.ajax({
                type: 'POST',
                // url: wbtm_ajax.wbtm_ajaxurl,
                url: wbtm_ajaxurl,
                data: {"action": "mage_bus_selected_seat_item", "price": price, "seat_name": seatName, "passenger_type": passengerType, "start": start, "end": end, "id": bus_id},
                success: function (data) {
                    parents.find('.mage_bus_selected_seat_list').append(data).slideDown(200);
                    mage_bus_price_qty_calculation(parents);
                },
                error: function (response) {
                    console.log(response);
                }
            });
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
        mage_bus_price_convert(subTotal, parents.find('.mage_bus_sub_total_price'),false);
        parents.find('.mage_customer_info_area input[name="extra_bag_quantity[]"]').each(function (index) {
            bagPerPrice = parseFloat($(this).attr('data-price'));
            bagQty += parseInt($(this).val());
            bagPrice += parseFloat($(this).val()) * bagPerPrice;
        });
        if (qty > 0) {
            parents.find('form.mage_bus_info_form').slideDown(250);
        } else {
            parents.find('form.mage_bus_info_form').slideUp(250);
        }
        if (bagQty > 0) {
            parents.find('.mage_bus_extra_bag_qty').html(bagQty);
            mage_bus_price_convert(bagPerPrice, parents.find('.mage_extra_bag_price'),false);
            mage_bus_price_convert(bagPrice, parents.find('.mage_bus_extra_bag_total_price'),false);
            parents.find('.mage_extra_bag').slideDown(200);
        } else {
            parents.find('.mage_extra_bag').slideUp(200);
        }
        let totalPrice = subTotal + (bagPrice > 0 ? parseFloat(bagPrice) : 0);
        mage_bus_price_convert(totalPrice, parents.find('.mage_bus_total_price'),true);

    }

    function mage_bus_seat_item(seatName, price, passengerType) {
        let item = '<div class="flexEqual mage_bus_selected_seat_item" data-seat-name="' + seatName + '">';
        item += '<h6>' + seatName + '</h6>';
        item += '<h6>' + passengerType + '</h6>';
        item += '<h6>' + price + '</h6>';
        item += '<h6><spn class="fa fa-trash mage_bus_seat_unselect"></spn></h6>';
        item += '</div>';
        return item;
    }

    //customer form
    function mageCustomerInfoFormBus(parent, seatName, passengerType,busDd) {
        let formTitle = parent.find('input[name="mage_bus_title"]').val() + seatName;
        let currentTarget = parent.find('.mage_hidden_customer_info_form');
        if (currentTarget.length > 0) {
            currentTarget.find('input[name="seat_name[]"]').val(seatName);
            currentTarget.find('input[name="passenger_type[]"]').val(passengerType);
            currentTarget.find('input[name="bus_dd[]"]').val(busDd);
            currentTarget.find('.mage_form_list').attr('data-seat-name', seatName).find('.mage_title h5').html(formTitle);
            return currentTarget.html();
        } else {
            return '<input type="hidden" name="bus_dd[]" value="' + busDd + '" /><input type="hidden" name="seat_name[]" value="' + seatName + '" /><input type="hidden" name="passenger_type[]" value="' + passengerType + '" />';
        }

    }

//loader default fixed
    function defaultLoaderFixed() {
        $('body').append('<div class="defaultLoaderFixed"><span></span></div>');
    }

    function defaultLoaderFixedRemove() {
        $('body').find('.defaultLoaderFixed').remove();
    }

})(jQuery);
