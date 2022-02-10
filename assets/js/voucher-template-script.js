jQuery(document).ready(function($) {
    var voucherTemplate = jQuery('#giftvoucher-template'),
        voucher_template_id = jQuery('#voucher-id'),
        voucher_extra_charges = jQuery('#voucher-extra-charges'),
        voucher_price_value = jQuery('#giftvoucher-template #voucher_price_value'),
        voucher_gift_to = jQuery('#giftvoucher-template #voucher_gift_to'),
        voucher_gift_from = jQuery('#giftvoucher-template #voucher_gift_from'),
        voucher_description = jQuery('#giftvoucher-template #voucher_description'),
        voucher_your_email = jQuery('#giftvoucher-template #voucher_your_email'),
        voucher_recipient_email = jQuery('#giftvoucher-template #voucher_recipient_email');
    voucher_shipping_first = jQuery('#giftvoucher-template #voucher_shipping_first');
    voucher_shipping_last = jQuery('#giftvoucher-template #voucher_shipping_last');
    voucher_shipping_address = jQuery('#giftvoucher-template #voucher_shipping_address');
    voucher_shipping_postcode = jQuery('#giftvoucher-template #voucher_shipping_postcode');
    payment_gateway = jQuery('#giftvoucher-template #payment_gateway');
    voucher_couponcode = jQuery('#giftvoucher-template #voucher-couponcode');
    number_slider = jQuery('#giftvoucher-template #number_giftcard_sl').val();
    buying_for = jQuery('#giftvoucher-template #buying_for').val();
    var step_template = voucherTemplate.find('.giftvoucher-step.active .step-group').data('step');
    //show step
    changeStepVoucher(step_template);

    function sliderVoucherTemplate(number_slider) {
        var number = parseInt(number_slider);
        if (jQuery('#slider-giftvoucher-template .slider-voucher-template').hasClass('slick-initialized')) {
            jQuery('#slider-giftvoucher-template .slider-voucher-template').slick('destroy');
        }
        jQuery('#slider-giftvoucher-template .slider-voucher-template').not('.slick-initialized').slick({
            infinite: true,
            slidesToShow: number,
            slidesToScroll: 1,
            dots: false,
            arrows: true,
            //adaptiveHeight: true,
            prevArrow: "<div class='prev-slider'><i class='fa fa-angle-left'></i></div>",
            nextArrow: "<div class='next-slider'><i class='fa fa-angle-right'></i></div>",
            responsive: [{
                    breakpoint: 1024,
                    settings: {
                        slidesToShow: 2,
                        slidesToScroll: 1,
                        infinite: true,
                    }
                },
                {
                    breakpoint: 736,
                    settings: {
                        slidesToShow: 1,
                        slidesToScroll: 1
                    }
                }
            ]
        });
    };
    // slider voucher ajax
    jQuery(document).on('click', '#giftvoucher-template .format-category-voucher .layout-type', function(event) {
        if (!jQuery(this).hasClass('active')) {
            var dataType = jQuery(this).data('type');
            var dataCategory = voucherTemplate.find('.category-nav-item.active .category-voucher-item').data('category-id');
            var data = {
                action: 'voucher_slider_template',
                dataType: dataType,
                dataCategory: dataCategory
            };
            voucherTemplate.find('.format-category-voucher .layout-type').removeClass('active');
            jQuery(this).addClass('active');
            jQuery.ajax({
                url: frontend_ajax_object.ajaxurl,
                type: "POST",
                data: data,
                beforeSend: function(results) {
                    voucherTemplate.find('.voucher-content-step').addClass('loading');
                },
                success: function(results) {
                    if (results) {
                        voucherTemplate.find('#slider-giftvoucher-template').html(results);
                        // if (dataType == 'landscape') {
                        //     sliderVoucherTemplate();
                        // } else {
                        sliderVoucherTemplate(number_slider);
                        //}
                        voucherTemplate.find('.voucher-content-step').removeClass('loading');
                    }

                },
                error: function() {
                    voucherTemplate.find('.voucher-content-step').removeClass('loading');
                    alert(frontend_ajax_object.error_occur);
                }
            });

        } else {
            return false;
        }
    });

    // filter category slider voucher ajax
    jQuery(document).on('click', '#giftvoucher-template .voucher-category-selection-wrap .category-voucher-item', function(event) {
        if (!jQuery(this).hasClass('active')) {
            var dataType = voucherTemplate.find('.format-category-voucher .layout-type.active').data('type');
            var dataCategory = jQuery(this).data('category-id');
            var data = {
                action: 'voucher_slider_template',
                dataType: dataType,
                dataCategory: dataCategory
            };
            voucherTemplate.find('.voucher-category-selection-wrap .category-nav-item').removeClass('active');
            jQuery(this).parent('.category-nav-item').addClass('active');
            jQuery.ajax({
                url: frontend_ajax_object.ajaxurl,
                type: "POST",
                data: data,
                beforeSend: function(results) {
                    voucherTemplate.find('.voucher-content-step').addClass('loading');
                },
                success: function(results) {
                    if (results) {
                        voucherTemplate.find('#slider-giftvoucher-template').html(results);
                        // if (dataType == 'landscape') {
                        //     sliderVoucherTemplate();
                        // } else {
                        sliderVoucherTemplate(number_slider);
                        //}
                        voucherTemplate.find('.voucher-content-step').removeClass('loading');
                    }

                },
                error: function() {
                    voucherTemplate.find('.voucher-content-step').removeClass('loading');
                    alert(frontend_ajax_object.error_occur);
                }
            });
        } else {
            return false;
        }
    });
    //jQuery select template gift card
    jQuery(document).on('click', '#giftvoucher-template .item-voucher-template .layout-button', function(event) {
        $('#voucher-next-step').removeAttr('target');
        var voucher_id = jQuery(this).parents('.layout-overlay').data('post_id');

        var step_voucher = voucherTemplate.find('.giftvoucher-step.active .step-group').data('step');
        var data = {
            action: 'ajax_select_voucher_template',
            voucher_id: voucher_id
        }
        jQuery.ajax({
            url: frontend_ajax_object.ajaxurl,
            type: "POST",
            data: data,
            beforeSend: function(results) {
                voucherTemplate.find('.voucher-content-step').addClass('loading');
            },
            success: function(results) {

                var data = JSON.parse(results);
                imagesGiftCard = data.url;
                currency = data.currency;
                giftto = data.giftto;
                giftfrom = data.giftfrom;
                date_of = data.date_of;
                company_logo_voucher = data.company_logo;
                company_name = data.company_name;
                email = data.email;
                website = data.web;
                expiryDate = data.expiryDate;
                notice = data.leftside_notice;
                counpon_label = data.counpon;
                json = data.json;
                changeStepVoucher(step_voucher + 1);
                if ($('#coupon_code_length').length) {
                    var numberlenght = $('#coupon_code_length').val()
                    var stringlenght = '1';
                    var stringlenght1 = '9';
                    for (var i = 0; i < numberlenght - 1; i++) {
                        stringlenght += 0
                        stringlenght1 += 0
                    }
                    stringlenght = parseFloat(stringlenght);
                    stringlenght1 = parseFloat(stringlenght1);
                    var couponcode = Math.floor(stringlenght + Math.random() * stringlenght1);
                }
                voucherTemplate.find('#voucher-id').val(voucher_id);
                voucherTemplate.find('#voucher-couponcode').val(couponcode);
                voucherTemplate.find('#template_giftcard_container').append('<img id="template_giftcard_img" src="' + imagesGiftCard + '"/>');
                setTimeout(function(e) {
                    showTemplateGiftCard(json, imagesGiftCard, currency, giftto, giftfrom, date_of, email, website, company_logo_voucher, company_name, expiryDate, notice, counpon_label, parseInt(step_voucher + 1))
                    fitStageIntoParentContainer()
                    jQuery('#show-preview-gift-card').trigger('click');
                    voucherTemplate.find('.voucher-content-step').removeClass('loading');
                    // var getwidthstage2 = jQuery("#template_giftcard_container_2 .konvajs-content canvas").attr("width");
                    // var getheightstage2 = jQuery("#template_giftcard_container_2 .konvajs-content canvas").attr("height");
                    // jQuery("#template_giftcard_container_2").attr("data-width", getwidthstage2);
                    // jQuery("#template_giftcard_container_2").attr("data-height", getheightstage2);

                }, 100);
                // auto draw font
                setTimeout(function(e) {
                    document.getElementById("wpgv_click_draw_auto").click();
                }, 1000);
            },
            error: function() {
                voucherTemplate.find('.voucher-content-step').removeClass('loading');
                alert(frontend_ajax_object.error_occur);
            }
        });
    });
    // click auto draw font
    jQuery("#wpgv_click_draw_auto").click(function() {
        if (typeof stage1 != "undefined") {
            setTimeout(function() {
                stage1.draw();
                stage2.draw();
            }, 700)
            setTimeout(function() {
                stage1.draw();
                stage2.draw();
            }, 1500)
        }

    });
    // function next step payment voucher-continue-step
    jQuery(document).on('click', '#giftvoucher-template #voucher-continue-step .voucher-next-step', function(event) {

        var step = $(this).data('next-step'),
            result = wpgv_validateitemform(step);
        scrollTopGiftCard();
        if (!result) return;
        changeStepVoucher(step);

    });
    // function next step payment voucher-prev-step
    jQuery(document).on('click', '#giftvoucher-template #voucher-continue-step .voucher-prev-step', function(event) {
        var step = $(this).data('prev-step');
        changeStepVoucher(step);
        scrollTopGiftCard();
    });
    // function change buying for
    jQuery(document).on('click', '#giftvoucher-template .buying-options .option-select', function(event) {
        var buying_for = jQuery(this).data('value');
        voucherTemplate.find('.option-select').removeClass('active');
        jQuery(this).addClass('active');
        voucherTemplate.find('#buying_for').val(buying_for);
        showhiddenBuyingFor(buying_for); //show hidden buying forr
        //showhiddenBuyingFor1(buying_for);//show hidden buying forr
    });
    // function change shipping
    jQuery(document).on('click', '#giftvoucher-template .choose-shipping-template .shipping-type', function(event) {
        var typeShipping = jQuery(this).data('type');
        var buying_for = jQuery('#giftvoucher-template #buying_for').val();
        voucherTemplate.find('.shipping-type').removeClass('active');
        jQuery(this).addClass('active');
        var totalPrice = parseFloat(parseFloat(voucher_price_value.val()) + parseFloat(voucher_extra_charges.val()));

        if (typeShipping == 'shipping_as_email') {
            if (buying_for != 'yourself') {
                jQuery(voucher_recipient_email).parent('.voucher-template-input').show();
            }
            voucherTemplate.find('.wrap-shipping-info-voucher').hide();
            voucherTemplate.find('.order-info-shipping').hide();
        } else {
            jQuery(voucher_recipient_email).parent('.voucher-template-input').hide();
            voucherTemplate.find('.wrap-shipping-info-voucher').show();
            voucherTemplate.find('.order-info-shipping').css('display', 'flex');
            voucherTemplate.find('.wrap-shipping-info-voucher .shipping-method').find('label:nth-child(1) input[name=shipping_method]').prop("checked", true);
            var priceShipping = voucherTemplate.find('input[name=shipping_method]:checked').closest('label').data('value');
            voucherTemplate.find('.currency-price-shipping').html(priceShipping);
            totalPrice = parseFloat(priceShipping) + parseFloat(totalPrice);
        }
        voucherTemplate.find('.price-voucher-total .price-total').html(totalPrice); // add total price
        return false;
    });

    function changeStepVoucher(step) {
        voucherTemplate.find('#setup-voucher-template').addClass('loading');
        var dataType = voucherTemplate.find('.format-category-voucher .layout-type.active').data('type'); // template lanscape or portail
        var typeShipping = voucherTemplate.find('.choose-shipping-template .shipping-type.active').data('type'); // check shipping email/post
        var buying_for = jQuery('#giftvoucher-template #buying_for').val();
        if (typeof typeShipping == 'undefined') {
            typeShipping = 'shipping_as_email';
        }
        voucherTemplate.find('.giftvoucher-step').removeClass('active').removeClass('passed'); // remove active in step
        voucherTemplate.find('.giftvoucher-step .step-group').removeClass('enable_click').addClass('disable_click');
        voucherTemplate.find('.wrapper-infomation-voucher-template').hide();
        //show slider       
        if (step == 1) {
            voucherTemplate.find('.voucher-content-step').addClass('loading');
            voucherTemplate.find('.voucher-content-step').hide(); //hide slider; 
            voucherTemplate.find('#select-temp').parent('.giftvoucher-step').addClass('active');
            voucherTemplate.find('#select-temp').removeClass('disable_click').addClass('enable_click');
            voucherTemplate.find('.wrap-format-category-voucher').css('display', 'flex');; //hide format slider;
            voucherTemplate.find('#slider-giftvoucher').show();
            voucherTemplate.find('#voucher-continue-step').removeClass('show'); //hide prev-next-step ;  
            voucherTemplate.find('.progress .progress-bar').css('width', '25%'); //add width progress
            // if (voucherTemplate.find('.format-category-voucher .active').data('type') == 'portrait') {
            //     sliderVoucherTemplate();
            // } else {
            sliderVoucherTemplate(number_slider);
            //}
            //remove canvas
            showTemplateGiftCard('', '', '', '', '', '', '', '', '', '', '', '', '', step)
            setTimeout(function() {
                voucherTemplate.find('.voucher-content-step').removeClass('loading');
            }, 1000);
        } else if (step == 2) {
            //show select template voucher
            voucherTemplate.find('#select-temp').parent('.giftvoucher-step').addClass('passed');
            voucherTemplate.find('#select-temp').removeClass('disable_click').addClass('enable_click');
            voucherTemplate.find('#select-per').parent('.giftvoucher-step').addClass('active');
            voucherTemplate.find('#select-per').removeClass('disable_click').addClass('enable_click');
            voucherTemplate.find('.wrap-setup-voucher-template').addClass('template-voucher-' + dataType);
            voucherTemplate.find('.wrap-format-category-voucher').hide(); //hide format slider;  
            voucherTemplate.find('.voucher-content-step').hide(); //hide slider; 
            voucherTemplate.find('#setup-voucher-template').show(); //show step 2;   
            voucherTemplate.find('#voucher-continue-step').addClass('show'); //show prev-next-step ; 
            voucherTemplate.find('.progress .progress-bar').css('width', '50%'); //add width progress    
            voucherTemplate.find('#content-setup-voucher-template').show(); //show Setup your gift card 
            // add count step
            voucherTemplate.find('#voucher-continue-step').find('.voucher-prev-step').data('prev-step', parseInt(step - 1));
            voucherTemplate.find('#voucher-continue-step').find('.voucher-next-step').data('next-step', parseInt(step + 1));
            voucherTemplate.find('.voucher-preview-pdf').hide(); // hide button preview
            voucherTemplate.find('#payment-voucher-template').hide(); // hide button payment
            voucherTemplate.find('.voucher-next-step').show(); // show button payment 
            voucherTemplate.find('#dataVoucher').val('');
            voucherTemplate.find('.order-voucher-details .acceptVoucherTerms').hide();
        } else if (step == 3) {
            //show step set up gift voucher
            var orderDetails = voucherTemplate.find('.order-voucher-details');
            voucherTemplate.find('#setup-shopping-payment-wrap').show().append(orderDetails); //show Setup your gift card
            voucherTemplate.find('.order-voucher-details .acceptVoucherTerms').hide();
            voucherTemplate.find('#select-temp').parent('.giftvoucher-step').addClass('passed');
            voucherTemplate.find('#select-temp').removeClass('disable_click').addClass('enable_click');
            voucherTemplate.find('#select-per').parent('.giftvoucher-step').addClass('passed');
            voucherTemplate.find('#select-per').removeClass('disable_click').addClass('enable_click');
            voucherTemplate.find('#select-payment').parent('.giftvoucher-step').addClass('active');
            voucherTemplate.find('#select-payment').removeClass('disable_click').addClass('enable_click');
            voucherTemplate.find('.progress .progress-bar').css('width', '75%'); //add width progressvar             
            // add count step
            voucherTemplate.find('#voucher-continue-step').find('.voucher-prev-step').data('prev-step', parseInt(step - 1));
            voucherTemplate.find('#voucher-continue-step').find('.voucher-next-step').data('next-step', parseInt(step + 1));
            voucherTemplate.find('.order-detail-voucher-template .price-voucher .currency-price-value').html(voucher_price_value.val());
            voucherTemplate.find('.order-detail-voucher-template .order-info-name .order-your-name').html(voucher_gift_from.val());
            //total price
            var totalPrice = parseFloat(parseFloat(voucher_price_value.val()) + parseFloat(voucher_extra_charges.val()));
            if (typeShipping == 'shipping_as_email') {
                voucherTemplate.find('.order-info-shipping').hide()
            } else {
                voucherTemplate.find('.order-info-shipping').css('display', 'flex');
                var priceShipping = voucherTemplate.find('input[name=shipping_method]:checked').closest('label').data('value');
                voucherTemplate.find('.currency-price-shipping').html(priceShipping);
                totalPrice = parseFloat(priceShipping) + parseFloat(totalPrice);
            }
            voucherTemplate.find('.voucher-preview-pdf').hide(); // hide button preview
            voucherTemplate.find('#payment-voucher-template').hide(); // hide button payment
            voucherTemplate.find('.voucher-next-step').show(); // show button payment
            voucherTemplate.find('.order-detail-voucher-template .price-voucher-total .price-total').html(totalPrice);
            voucherTemplate.find('#dataVoucher').val('');
        } else if (step == 4) {
            // show step overview and payment
            var orderDetails = voucherTemplate.find('.order-voucher-details');
            voucherTemplate.find('#order-voucher-details-overview').show().append(orderDetails); //add show section overview 
            voucherTemplate.find('.order-voucher-details .acceptVoucherTerms').show();
            voucherTemplate.find('#select-temp').parent('.giftvoucher-step').addClass('passed');
            voucherTemplate.find('#select-temp').removeClass('disable_click').addClass('enable_click');
            voucherTemplate.find('#select-per').parent('.giftvoucher-step').addClass('passed');
            voucherTemplate.find('#select-per').removeClass('disable_click').addClass('enable_click');
            voucherTemplate.find('#select-payment').parent('.giftvoucher-step').addClass('passed');
            voucherTemplate.find('#select-payment').removeClass('disable_click').addClass('enable_click');
            voucherTemplate.find('#select-overview').parent('.giftvoucher-step').addClass('active');
            voucherTemplate.find('#select-overview').removeClass('disable_click').addClass('enable_click');
            voucherTemplate.find('.progress .progress-bar').css('width', '100%'); //add width progress            
            voucherTemplate.find('#voucher-continue-step').find('.voucher-prev-step').data('prev-step', parseInt(step - 1));
            voucherTemplate.find('.voucher-preview-pdf').show(); // show button preview
            voucherTemplate.find('#payment-voucher-template').show(); // show button payment
            voucherTemplate.find('.voucher-next-step').hide(); // hide button payment
            voucherTemplate.find('.value-price-voucher .price').html(voucher_price_value.val());
            voucherTemplate.find('.value-you-email .email').html(voucher_your_email.val());
            voucherTemplate.find('.value-payment-method-voucher .payment-method-voucher').html(payment_gateway.find('option:selected').text());
            var dataString = wpgv_formdata();
            //check show/hide data overview 
            if (typeShipping == 'shipping_as_email') {
                if (buying_for != 'yourself') {
                    voucherTemplate.find('.order-voucher-recipient-email').css('display', 'flex');
                    voucherTemplate.find('.value-recipient-email .recipient-email').html(voucher_recipient_email.val());
                } else {
                    voucherTemplate.find('.order-voucher-recipient-email').hide();
                }
                voucherTemplate.find('.order-voucher-full-name').hide();
                voucherTemplate.find('.order-voucher-address').hide();
                voucherTemplate.find('.order-voucher-postcode').hide();
                voucherTemplate.find('.value-shipping-voucher').html(frontend_ajax_object.via_email);
                dataString = dataString + '&shipping=' + wpgv_b64EncodeUnicode(typeShipping) + '&shippingMethod=' + '&pay_method=' + wpgv_b64EncodeUnicode(payment_gateway.val()) + '&shipping_email=' + wpgv_b64EncodeUnicode(voucher_recipient_email.val())
            } else {
                voucherTemplate.find('.order-voucher-recipient-email').hide();
                voucherTemplate.find('.order-voucher-full-name').css('display', 'flex');
                voucherTemplate.find('.order-voucher-full-name .value-full-name .full-name').html(voucher_shipping_first.val() + ' ' + voucher_shipping_last.val());
                voucherTemplate.find('.order-voucher-address .value-address-voucher .address-voucher').html(voucher_shipping_address.val());
                if (!voucher_shipping_postcode.val()) {
                    voucherTemplate.find('.order-voucher-postcode').css('display', 'flex');
                    voucherTemplate.find('.order-voucher-full-name .value-full-name .full-name').html(voucher_shipping_postcode.val());
                } else {
                    voucherTemplate.find('.order-voucher-postcode').hide();
                }
                voucherTemplate.find('.order-voucher-address').css('display', 'flex');
                voucherTemplate.find('.value-shipping-voucher').html(frontend_ajax_object.via_post);
                dataString = dataString + '&shipping=' + wpgv_b64EncodeUnicode(typeShipping) + '&pay_method=' + wpgv_b64EncodeUnicode(payment_gateway.val()) + '&fisrtName=' + wpgv_b64EncodeUnicode(voucher_shipping_first.val()) + '&lastName=' + wpgv_b64EncodeUnicode(voucher_shipping_last.val()) + '&address=' + wpgv_b64EncodeUnicode(voucher_shipping_address.val()) + '&postcode=' + wpgv_b64EncodeUnicode(voucher_shipping_postcode.val()) + '&shipping_method=' + wpgv_b64EncodeUnicode(voucherTemplate.find('input[name=shipping_method]:checked').val())
            }
            voucherTemplate.find('#dataVoucher').val(dataString); // get data voucher setup                  
        }
        jQuery('#voucher-template-name-step').find('.choose-show-title').html(voucherTemplate.find('.giftvoucher-step.active .step-label').text());
        jQuery('#voucher-template-name-step').find('.number-step').html(step);
        setTimeout(function() {
            voucherTemplate.find('#setup-voucher-template').removeClass('loading');
        }, 1000);
    }

    // change shipping method
    voucherTemplate.find('input[name="shipping_method"]').change(function() {
        var shippingPrice = jQuery(this).closest('label').data('value');
        voucherTemplate.find('input[name="shipping_method"]').prop("checked", false);
        jQuery(this).prop("checked", true);
        var totalPrice = parseFloat(parseFloat(voucher_price_value.val()) + parseFloat(voucher_extra_charges.val()));
        totalPrice = parseFloat(shippingPrice) + totalPrice;
        voucherTemplate.find('.order-detail-voucher-template .price-voucher-total .price-total').html(totalPrice);
        voucherTemplate.find('.order-detail-voucher-template .price-voucher-shipping .currency-price-shipping').html(shippingPrice);
        return false;
    });
    // function validate form voucher
    function wpgv_validateitemform($step) {
        $status = 0;
        buying_for = jQuery('#giftvoucher-template #buying_for').val();

        if ($step == '3') {

            var get_value_price = voucher_price_value.val();
            var get_min_price = voucher_price_value.attr("min-value");
            if (voucher_price_value.val() && voucher_price_value.val() > 0) {

                if (parseInt(get_value_price) < parseInt(get_min_price)) {
                    $status = 0;
                    voucher_price_value.closest('.voucher-template-input').find('.error-input').html(frontend_ajax_object.min_value + get_min_price);
                    voucher_price_value.closest('.voucher-template-input').find('.error-input').show();
                } else {
                    $status = 1;
                    voucher_price_value.closest('.voucher-template-input').find('.error-input').html(frontend_ajax_object.required);
                    voucher_price_value.closest('.voucher-template-input').find('.error-input').hide();
                }
            } else {
                if (parseInt(get_value_price) < 0) {
                    $status = 0;
                    voucher_price_value.closest('.voucher-template-input').find('.error-input').html(frontend_ajax_object.min_value + get_min_price);
                    voucher_price_value.closest('.voucher-template-input').find('.error-input').show();
                } else {
                    $status = 0;
                    voucher_price_value.closest('.voucher-template-input').find('.error-input').html(frontend_ajax_object.required);
                    voucher_price_value.closest('.voucher-template-input').find('.error-input').show();
                }
            }
            if (buying_for == 'someone_else') {
                if (voucher_gift_to.val()) {
                    //$status = 1;
                    voucher_gift_to.closest('.voucher-template-input').find('.error-input').hide();
                } else {
                    $status = 0;
                    voucher_gift_to.closest('.voucher-template-input').find('.error-input').show();
                }
            }
            if (voucher_gift_from.val()) {
                //$status = 1;
                voucher_gift_from.closest('.voucher-template-input').find('.error-input').hide();
            } else {
                $status = 0;
                voucher_gift_from.closest('.voucher-template-input').find('.error-input').show();
            }
        } else if ($step == '4') {
            //check validate 
            var typeShipping = voucherTemplate.find('.choose-shipping-template .shipping-type.active').data('type');
            if (typeof typeShipping == 'undefined') {
                typeShipping = 'shipping_as_email';
            }
            if (typeShipping == 'shipping_as_email') {
                if (voucher_your_email.val() && wpgv_validateEmail(voucher_your_email.val())) {
                    $status = 1;
                    voucher_your_email.closest('.voucher-template-input').find('.error-input').hide();
                } else {
                    $status = 0;
                    voucher_your_email.closest('.voucher-template-input').find('.error-input').show();
                }
                if (buying_for == 'someone_else') {
                    if (voucher_recipient_email.val() && wpgv_validateEmail(voucher_recipient_email.val())) {
                        voucher_recipient_email.closest('.voucher-template-input').find('.error-input').hide();
                    } else {
                        $status = 0;
                        voucher_recipient_email.closest('.voucher-template-input').find('.error-input').show();
                    }
                }
            } else if (typeShipping == 'shipping_as_post') {
                if (voucher_your_email.val() && wpgv_validateEmail(voucher_your_email.val())) {
                    $status = 1;
                    voucher_your_email.closest('.voucher-template-input').find('.error-input').hide();
                } else {
                    $status = 0;
                    voucher_your_email.closest('.voucher-template-input').find('.error-input').show();
                }
                if (voucher_shipping_first.val()) {
                    voucher_shipping_first.closest('.voucher-template-input').find('.error-input').hide();
                } else {
                    $status = 0;
                    voucher_shipping_first.closest('.voucher-template-input').find('.error-input').show();
                }
                if (voucher_shipping_last.val()) {
                    voucher_shipping_last.closest('.voucher-template-input').find('.error-input').hide();
                } else {
                    $status = 0;
                    voucher_shipping_last.closest('.voucher-template-input').find('.error-input').show();
                }
                if (voucher_shipping_address.val()) {
                    voucher_shipping_address.closest('.voucher-template-input').find('.error-input').hide();
                } else {
                    $status = 0;
                    voucher_shipping_address.closest('.voucher-template-input').find('.error-input').show();
                }
            }

        }
        return $status;
    }
    // function validate email
    function wpgv_validateEmail($email) {
        var reg = /^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/;

        if (reg.test($email) == false) {
            return false;
        }
        return true;
    }
    //check messege
    jQuery('#giftvoucher-template #voucher_description').on('keydown', function(e) {
        newLines = jQuery(this).val().split("\n").length;
        jQuery('#giftvoucher-template .voucher-template-input .maxchar').html(frontend_ajax_object.total_character + ": " + (this.value.length));
        if ((e.keyCode == 13 && newLines >= 3) || (e.keyCode != 8 && this.value.length > 250)) {
            return false;
        }
    });
    //check length gift from
    jQuery('#giftvoucher-template #voucher_gift_from').on('keydown', function(e) {
        if (e.keyCode != 8 && this.value.length > 20 && e.keyCode != 46) {
            return false;
        }
    });
    //check length gift to
    jQuery('#giftvoucher-template #voucher_gift_to').on('keydown', function(e) {
        if (e.keyCode != 8 && this.value.length > 20 && e.keyCode != 46) {
            return false;
        }
    });
    //check length gift to
    jQuery('#giftvoucher-template #voucher_price_value').on('keydown', function(e) {
        if (e.keyCode != 8 && this.value.length > 5 && e.keyCode != 46 && parseFloat(this.value) != 0) {
            return false;
        }
    });
    // function data giftvoucher-template
    function wpgv_formdata() {
        var idVoucher = wpgv_b64EncodeUnicode(voucher_template_id.val()),
            priceExtraCharges = wpgv_b64EncodeUnicode(parseFloat(voucher_extra_charges.val())),
            couponcode = wpgv_b64EncodeUnicode(parseFloat(voucher_couponcode.val())),
            priceVoucher = wpgv_b64EncodeUnicode(parseFloat(voucher_price_value.val())),
            giftEmail = wpgv_b64EncodeUnicode(voucher_your_email.val());
        typeGiftCard = wpgv_b64EncodeUnicode(voucherTemplate.find('.format-category-voucher .active').data('type'));
        buying_for = jQuery('#giftvoucher-template #buying_for').val();
        if (jQuery('html').is(':lang(el)')) {
            giftTo = voucher_gift_to.val(),
                giftFor = voucher_gift_from.val();
            message = voucher_description.val();
        } else {
            giftTo = wpgv_b64EncodeUnicode(voucher_gift_to.val()),
                giftFor = wpgv_b64EncodeUnicode(voucher_gift_from.val());
            message = wpgv_b64EncodeUnicode(voucher_description.val());
        }
        return '&idVoucher=' + idVoucher + '&priceExtraCharges=' + priceExtraCharges + '&priceVoucher=' + priceVoucher + '&for=' + giftFor + '&from=' + giftTo + '&message=' + message + '&email=' + giftEmail + '&couponcode=' + couponcode + '&typeGiftCard=' + typeGiftCard + '&buying_for=' + wpgv_b64EncodeUnicode(buying_for);
    }

    function wpgv_b64EncodeUnicode(str) {
        return btoa(encodeURIComponent(str).replace(/%([0-9A-F]{2})/g, function(match, p1) {
            return String.fromCharCode(parseInt(p1, 16))
        }))
    }
    // function click nav step
    jQuery(document).on('click', '#giftvoucher-template .giftvoucher-step.passed', function(event) {
        var step = $(this).find('.step-group').data('step');
        changeStepVoucher(step);
    });
    // payment 
    jQuery(document).on('click', '#payment-voucher-template', function(event) {
        var checkacceptVoucherTerms = $('input[name=acceptVoucherTerms]:checked').is(':checked');
        if (checkacceptVoucherTerms == true) {
            voucherTemplate.find('.order-voucher-details .acceptVoucherTerms .error-input').hide();
            var dataURL = stage2.toDataURL();
            addImagesGiftCard(dataURL);
            var data = voucherTemplate.find('#dataVoucher').val();
            var dataURL = voucherTemplate.find('#show-preview-gift-card').attr('href');
            voucherTemplate.find('#setup-voucher-template').addClass('loading');
            $.ajax({
                url: frontend_ajax_object.ajaxurl,
                type: "POST",
                data: 'action=wpgv_save_gift_card' + data + '&urlImage=' + wpgv_b64EncodeUnicode(dataURL),

                success: function(a) {
                    window.location = a;
                    voucherTemplate.find('#setup-voucher-template').removeClass('loading');
                },
                error: function() {
                    alert(frontend_ajax_object.error_occur);
                    voucherTemplate.find('#setup-voucher-template').removeClass('loading');
                }
            });
            return false;
        } else {
            voucherTemplate.find('.order-voucher-details .acceptVoucherTerms .error-input').show();
        }

    });
    var stage1 = null;
    var stage2 = null;
    var stageWidth = 0;
    var stageHeight = 0;
    var scale = 0;
    var containerWidth = 0;
    //function canvas
    function showTemplateGiftCard(json, urlImage, currency, giftto, giftfrom, date_label, email, website, company_logo_voucher, company_name, expiryDate, notice, counpon, step) {
        if (step != 1) {
            if (json != null || urlImage != null || currency != null || giftto != null || email != null || date_label != null || website != null || company_logo_voucher != null || company_name != null || expiryDate != null || notice != null || counpon != null) {

                var arr_number = [1, 2];
                var stages = [];

                arr_number.forEach(function(number) {
                    stage = Konva.Node.create(json, 'template_giftcard_container_' + number);
                    stageWidth = stage.getAttr('width');
                    stageHeight = stage.getAttr('height');

                    stage.find('#giftcard_bg').forEach(imageNode => {
                        let nativeImage = new window.Image();
                        nativeImage.onload = () => {
                            imageNode.image(nativeImage);
                            imageNode.width(stageWidth);
                            imageNode.height(stageHeight);
                            imageNode.getLayer().batchDraw();
                        }
                        nativeImage.src = urlImage;
                    });
                    if (typeof stage.find('#giftcard_logo')[0] !== 'undefined') {
                        stage.find('#giftcard_logo').forEach(imageNode => {

                            let nativeImage = new window.Image();
                            nativeImage.onload = () => {
                                imageNode.image(nativeImage);
                                let maxWidth = 40; // Max width for the image
                                var maxHeight = 40; // Max height for the image
                                var ratio = 0;
                                var width = nativeImage.width;
                                var height = nativeImage.height;

                                if (width > maxWidth) {
                                    ratio = maxWidth / width;
                                    height = height * ratio; // Reset height to match scaled image
                                    width = width * ratio; // Reset width to match scaled image
                                }
                                // Check if current height is larger than max
                                if (height > maxHeight) {
                                    ratio = maxHeight / height; // get ratio for scaling image
                                    width = width * ratio; // Reset width to match scaled image
                                    height = height * ratio; // Reset height to match scaled image
                                }
                                imageNode.width(width);
                                imageNode.height(height);
                                imageNode.getLayer().batchDraw();
                            }
                            nativeImage.src = company_logo_voucher;

                        });
                    }
                    stages.push(stage);
                });
                stages.forEach(function(stage, k) {
                    if (k == 0) {
                        stage1 = stage;
                    } else {
                        stage2 = stage;
                    }
                })

                stages.forEach(function(stage) {
                    if (typeof stage.find('#gift_title_first')[0] !== 'undefined') {
                        var gift_title_first = stage.find('#gift_title_first')[0];
                        gift_title_first.draggable(false);
                        stage.draw();
                    }
                    if (typeof stage.find('#gift_title_last')[0] !== 'undefined') {
                        var gift_title_last = stage.find('#gift_title_last')[0];
                        gift_title_last.draggable(false);
                        stage.draw();
                    }
                    if (typeof stage.find('#gift_title_end')[0] !== 'undefined') {
                        var gift_title_end = stage.find('#gift_title_end')[0];
                        gift_title_end.draggable(false);
                        stage.draw();
                    }
                    if (typeof stage.find('#giftcard_logo')[0] !== 'undefined') {
                        var giftcard_logo = stage.find('#giftcard_logo')[0];
                        giftcard_logo.draggable(false);
                        stage.draw();
                    }
                    if (typeof stage.find('#gift_title_first')[0] !== 'undefined') {

                        var giftcard_name_voucher = stage.find('#gift_title_first')[0];
                        giftcard_name_voucher.draggable(false);
                        stage.draw();

                    }
                    // company
                    if (typeof stage.find('#gift_title_company')[0] !== 'undefined') {

                        var company = stage.find('#gift_title_company')[0];
                        company.text(company_name);
                        company.draggable(false);
                        stage.draw();

                    }
                    // giftcard_date_gift_label
                    if (typeof stage.find('#giftcard_date_gift_label')[0] !== 'undefined') {

                        var datelabel = stage.find('#giftcard_date_gift_label')[0];
                        datelabel.text(date_label);
                        datelabel.draggable(false);
                        stage.draw();

                    }
                    //date value giftcard_date_gift_input
                    if (typeof stage.find('#giftcard_date_gift_input')[0] !== 'undefined') {

                        var dateValue = stage.find('#giftcard_date_gift_input')[0];
                        dateValue.text(expiryDate);
                        dateValue.draggable(false);
                        stage.draw();

                    }
                    // gift to label
                    if (typeof stage.find('#giftto_label')[0] !== 'undefined') {

                        var giftto_label = stage.find('#giftto_label')[0];
                        giftto_label.text(giftto);
                        giftto_label.draggable(false);
                        stage.draw();

                    }
                    // gift form label
                    if (typeof stage.find('#giftfrom_label')[0] !== 'undefined') {

                        var giftfrom_label = stage.find('#giftfrom_label')[0];
                        giftfrom_label.text(giftfrom);
                        giftfrom_label.draggable(false);
                        stage.draw();

                    }
                    // email
                    if (typeof stage.find('#giftcard_email')[0] !== 'undefined') {

                        var stageEmail = stage.find('#giftcard_email')[0];
                        stageEmail.text(email);
                        stageEmail.draggable(false);
                        stage.draw();

                    }
                    // url
                    if (typeof stage.find('#giftcard_website')[0] !== 'undefined') {

                        var stageWebsite = stage.find('#giftcard_website')[0];
                        stageWebsite.text(website);
                        stageWebsite.draggable(false);
                        stage.draw();

                    }
                    // value gift to
                    if (typeof stage.find('#giftto_input')[0] !== 'undefined') {

                        var giftoValue = stage.find('#giftto_input')[0];
                        giftoValue.text(voucher_gift_to.val());
                        giftoValue.draggable(false);
                        stage.draw();

                    }
                    // value gift from
                    if (typeof stage.find('#giftfrom_input')[0] !== 'undefined') {

                        var giftfromValue = stage.find('#giftfrom_input')[0];
                        giftfromValue.text(voucher_gift_from.val());
                        giftfromValue.draggable(false);
                        stage.draw();

                    }
                    // value price
                    if (typeof stage.find('#giftcard_monney')[0] !== 'undefined') {
                        var monney = stage.find('#giftcard_monney')[0];
                        if (voucher_price_value.val() > 0) {
                            if (voucherTemplate.find('#setup-shopping-payment-wrap .price-voucher.currency_right').length > 0) {
                                monney.text(voucher_price_value.val() + '' + currency);
                            } else {
                                monney.text(currency + '' + voucher_price_value.val());
                            }
                        } else {
                            monney.text('');
                        }
                        monney.draggable(false);
                        stage.draw();
                    }
                    if (typeof stage.find('#giftcard_monney_label')[0] !== 'undefined') {
                        var monney_label = stage.find('#giftcard_monney_label')[0];
                        monney_label.text(frontend_ajax_object.text_value);
                        monney_label.draggable(false);
                        stage.draw();
                    }
                    // value Desc
                    if (typeof stage.find('#giftcard_des')[0] !== 'undefined') {
                        var gift_des = stage.find('#giftcard_des')[0];
                        gift_des.text(voucher_description.val());
                        gift_des.draggable(false);
                        stage.draw();
                    }
                    //counpon label
                    if (typeof stage.find('#giftcard_counpon_label')[0] !== 'undefined') {
                        var giftcard_counpon_label = stage.find('#giftcard_counpon_label')[0];
                        giftcard_counpon_label.text(counpon);
                        giftcard_counpon_label.draggable(false);
                        stage.draw();
                    }
                    //counpon code
                    if (typeof stage.find('#giftcard_counpon')[0] !== 'undefined') {
                        var giftcard_counpon = stage.find('#giftcard_counpon')[0];
                        giftcard_counpon.text(voucher_couponcode.val());
                        giftcard_counpon.draggable(false);
                        stage.draw();
                    }
                    //note
                    if (typeof stage.find('#giftcard_note')[0] !== 'undefined') {
                        var giftcard_note = stage.find('#giftcard_note')[0];
                        giftcard_note.text(notice);
                        giftcard_note.draggable(false);
                        stage.draw();
                    }
                    // add change value to gift voucher
                    voucher_price_value.on('keyup', function() {
                        var dInput = this.value;
                        if (typeof stage.find('#giftcard_monney')[0] !== 'undefined') {

                            var node_price_value = stage.find('#giftcard_monney')[0];
                            if (voucherTemplate.find('#setup-shopping-payment-wrap .price-voucher.currency_right').length > 0) {
                                node_price_value.text(dInput + '' + currency);
                            } else {
                                node_price_value.text(currency + '' + dInput);
                            }
                            stage.draw();
                            var dataURL = stage.toDataURL({
                                pixelRatio: 1
                            });
                            addImagesGiftCard(dataURL);
                        }
                    });
                    voucher_gift_to.on('keyup', function() {
                        var dInput = this.value;
                        if (typeof stage.find('#giftto_input')[0] !== 'undefined') {
                            var node_gift_to = stage.find('#giftto_input')[0];
                            node_gift_to.text(dInput);
                            stage.draw();
                            var dataURL = stage.toDataURL({
                                pixelRatio: 1
                            });
                            addImagesGiftCard(dataURL);
                        }
                    });
                    voucher_gift_from.on('keyup', function() {
                        var dInput = this.value;
                        if (typeof stage.find('#giftfrom_input')[0] !== 'undefined') {
                            var node_gift_from = stage.find('#giftfrom_input')[0];
                            node_gift_from.text(dInput);
                            stage.draw();
                            var dataURL = stage.toDataURL({
                                pixelRatio: 1
                            });
                            addImagesGiftCard(dataURL);
                        }
                    });
                    voucher_description.on('keyup', function() {
                        var dInput = this.value;
                        if (typeof stage.find('#giftcard_des')[0] !== 'undefined') {
                            var node_description = stage.find('#giftcard_des')[0];
                            node_description.text(dInput);
                            stage.draw();
                            var dataURL = stage.toDataURL({
                                pixelRatio: 1
                            });
                            addImagesGiftCard(dataURL);
                        }
                    });
                    var dataURL = stage.toDataURL({
                        pixelRatio: 1
                    });
                    addImagesGiftCard(dataURL);

                    setTimeout(() => {
                        stages.forEach(function(stage) {
                            stage.draw();
                        })
                    }, 500);

                })

            }
        } else {
            if (typeof stage1 != 'undefined') {
                stage1.destroy();
            }
            if (typeof stage2 != 'undefined') {
                stage2.destroy();
            }
        }
    }

    // function resize canvas
    function fitStageIntoParentContainer() {
        if (stage1 != null) {
            var container = document.querySelector('#template_giftcard_container_1');
            // now we need to fit stage into parent
            var containerWidth = container.offsetWidth;
            // to do this we need to scale the stage
            var scale = containerWidth / stageWidth;
            stage1.width(stageWidth * scale);
            stage1.height(stageHeight * scale);
            stage1.scale({
                x: scale,
                y: scale
            });
            stage1.draw();
        }
        if (stage2 != null) {
            setTimeout(function() {
                var container = document.querySelector('#template_giftcard_container_2');
                // now we need to fit stage into parent
                var containerWidth = container.offsetWidth;
                // to do this we need to scale the stage
                var scale = containerWidth / stageWidth;
                stage2.width(stageWidth * scale);
                stage2.height(stageHeight * scale);
                stage2.scale({
                    x: scale,
                    y: scale
                });
                stage2.draw();
            }, 1500);
        }
    }
    fitStageIntoParentContainer();
    // adapt the stage on any window resize
    window.addEventListener('resize', fitStageIntoParentContainer);
    jQuery(document).on('click', '#giftvoucher-template', function(event) {
        if (stage2 != null) {
            fitStageIntoParentContainer();
            var dataURL_2 = stage2.toDataURL({
                pixelRatio: 2
            });
            addImagesGiftCard(dataURL_2);
        }
    });

    // function show preview giftCard
    if (voucherTemplate.find('#voucher-preview-pdf').length) {
        document.getElementById('voucher-preview-pdf').addEventListener('click', function() {
                //show add stage preview pdf

                // voucherTemplate.find('.voucher-content-step').addClass('loading');
                var preview = new Konva.Text({
                    x: 50,
                    y: stage2.getAttr('height') / 2 + 30,
                    text: frontend_ajax_object.preview,
                    fontSize: 55,
                    fontFamily: 'Calibri',
                    align: 'center',
                    fill: 'red',
                    verticalAlign: 'middle',
                    fontStyle: 'bold',
                    width: stage2.getAttr('width'),
                    rotation: -20,
                    id: 'preview'
                });
                var layerPreview = stage2.find('Layer')[0];
                layerPreview.add(preview);

                var dataURL_2 = stage2.toDataURL({
                    pixelRatio: 1.2
                });
                addImagesGiftCard(dataURL_2);
                // //stage.add(preview);

                if (typeof stage2.find('#giftcard_counpon')[0] !== 'undefined') {
                    var giftcard_counpon = stage2.find('#giftcard_counpon')[0];
                    giftcard_counpon.text('XXXXXXXX');
                    stage2.draw();
                    var dataURL_2 = stage2.toDataURL({
                        pixelRatio: 1.2
                    });
                    addImagesGiftCard(dataURL_2);
                }

                var stageWidth_2 = jQuery("#template_giftcard_container_2").attr("data-width");
                var stageHeight_2 = jQuery("#template_giftcard_container_2").attr("data-height");
                var scalestage_2 = null;

                var voucherType = voucherTemplate.find('.format-category-voucher .active').data('type');
                if (voucherType == 'landscape') {
                    var pdf = new jsPDF('l', 'pt', 'a4', true);
                    scalestage_2 = 2;
                } else {
                    var pdf = new jsPDF('p', 'pt', 'a4', true);
                    scalestage_2 = 2;
                }

                var dataURL = stage2.toDataURL();
                addImagesGiftCard(dataURL);
                let pageWidth = pdf.internal.pageSize.getWidth();
                let pageHeight = pdf.internal.pageSize.getHeight();
                let widthRatio = pageWidth / stage2.width();
                let heightRatio = pageHeight / stage2.height();
                let ratio = widthRatio > heightRatio ? heightRatio : widthRatio;
                let canvasWidth = stage2.width() * ratio;
                let canvasHeight = stage2.height() * ratio;
                let marginX = (pageWidth - canvasWidth) / 2;
                let marginY = (pageHeight - canvasHeight) / 2;
                var dataURL_2 = stage2.toDataURL({
                    pixelRatio: 1.2
                });
                pdf.addImage(dataURL_2, 'PNG', marginX, marginY, canvasWidth, canvasHeight, '', 'FAST');

                pdf.output('dataurlnewwindow');

                pdf.output('save', 'preview.pdf');
                if (typeof stage2.find('#giftcard_counpon')[0] !== 'undefined') {
                    var giftcard_counpon1 = stage2.find('#giftcard_counpon')[0];
                    giftcard_counpon1.text(voucher_couponcode.val());
                    stage2.draw();
                }
                if (typeof stage2.find('#preview')[0] !== 'undefined') {
                    var textPreview = stage2.find('#preview')[0];
                    textPreview.text('');
                    stage2.draw();
                }
                var dataURL_2 = stage2.toDataURL({
                    pixelRatio: 1
                });
                addImagesGiftCard(dataURL_2);
                jQuery('#show-preview-gift-card').trigger('click');
            },
            false
        );
    }
    // funtion resize giftcard    
    function scrollTopGiftCard() {
        document.getElementById('giftvoucher-template').scrollIntoView();
    }
    //function set image
    function addImagesGiftCard(dataURL) {
        if (jQuery('#giftvoucher-template').find('#show-preview-gift-card').length > 0) {
            jQuery('#giftvoucher-template').find('#show-preview-gift-card').attr('href', dataURL);
        } else {
            jQuery('#giftvoucher-template').append('<a id="show-preview-gift-card" href="' + dataURL + '"/></a>');
        }
    }

    //function check buying_for
    function showhiddenBuyingFor(buying_for) {
        if (buying_for == 'yourself') {
            voucherTemplate.find('#voucher-from').find('label').text(frontend_ajax_object.your_name);
            voucherTemplate.find('#voucher-to').hide();
            if (typeof stage1.find('#giftto_label')[0] !== 'undefined') {
                var giftto_label = stage1.find('#giftto_label')[0];
                giftto_label.text(giftto);
                giftto_label.hide();
                giftto_label.draggable(false);
                stage1.draw();
            }
            if (typeof stage1.find('#giftfrom_label')[0] !== 'undefined') {
                var giftfrom_label = stage1.find('#giftfrom_label')[0];
                giftfrom_label.text(frontend_ajax_object.your_name);
                giftfrom_label.draggable(false);
                stage1.draw();
            }
            if (typeof stage1.find('#giftto_input')[0] !== 'undefined') {
                var giftoValue = stage1.find('#giftto_input')[0];
                giftoValue.text(voucher_gift_to.val());
                giftoValue.hide();
                giftoValue.draggable(false);
                stage1.draw();
            }

            if (typeof stage2.find('#giftto_label')[0] !== 'undefined') {
                var giftto_label_2 = stage2.find('#giftto_label')[0];
                giftto_label_2.text(giftto);
                giftto_label_2.hide();
                giftto_label_2.draggable(false);
                stage2.draw();
            }
            if (typeof stage2.find('#giftfrom_label')[0] !== 'undefined') {
                var giftfrom_label_2 = stage2.find('#giftfrom_label')[0];
                giftfrom_label_2.text(frontend_ajax_object.your_name);
                giftfrom_label_2.draggable(false);
                stage2.draw();
            }
            if (typeof stage2.find('#giftto_input')[0] !== 'undefined') {
                var giftoValue_2 = stage2.find('#giftto_input')[0];
                giftoValue_2.text(voucher_gift_to.val());
                giftoValue_2.hide();
                giftoValue_2.draggable(false);
                stage2.draw();
            }
            voucherTemplate.find('#recipient_email').hide();
        } else {
            voucherTemplate.find('#voucher-from').find('label').text(frontend_ajax_object.gift_from);
            voucherTemplate.find('#voucher-to').show();
            if (typeof stage1.find('#giftto_label')[0] !== 'undefined') {
                var giftto_label = stage1.find('#giftto_label')[0];
                giftto_label.text(giftto);
                giftto_label.show();
                giftto_label.draggable(false);
                stage1.draw();
            }
            if (typeof stage1.find('#giftto_input')[0] !== 'undefined') {
                var giftoValue = stage1.find('#giftto_input')[0];
                giftoValue.text(voucher_gift_to.val());
                giftoValue.show();
                giftoValue.draggable(false);
                stage1.draw();
            }
            if (typeof stage1.find('#giftfrom_label')[0] !== 'undefined') {
                var giftfrom_label = stage1.find('#giftfrom_label')[0];
                giftfrom_label.text(frontend_ajax_object.gift_from);
                giftfrom_label.draggable(false);
                stage1.draw();
            }

            if (typeof stage2.find('#giftto_label')[0] !== 'undefined') {
                var giftto_label_2 = stage2.find('#giftto_label')[0];
                giftto_label_2.text(giftto);
                giftto_label_2.show();
                giftto_label_2.draggable(false);
                stage2.draw();
            }
            if (typeof stage2.find('#giftto_input')[0] !== 'undefined') {
                var giftoValue_2 = stage2.find('#giftto_input')[0];
                giftoValue_2.text(voucher_gift_to.val());
                giftoValue_2.show();
                giftoValue_2.draggable(false);
                stage2.draw();
            }
            if (typeof stage2.find('#giftfrom_label')[0] !== 'undefined') {
                var giftfrom_label_2 = stage2.find('#giftfrom_label')[0];
                giftfrom_label_2.text(frontend_ajax_object.gift_from);
                giftfrom_label_2.draggable(false);
                stage2.draw();
            }
            voucherTemplate.find('#recipient_email').show();
        }
    }


});