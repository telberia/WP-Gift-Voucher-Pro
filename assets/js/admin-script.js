(function($) {

    $('#voucher_bgcolor, #voucher_color, #color_text_name_voucher, #color_text_voucher, #color_text_voucher_price').wpColorPicker();

    $('.wpgiftv-row .nav-tab').on('click', function(e) {
        e.preventDefault();
        $('.wpgiftv-row .nav-tab').removeClass('nav-tab-active');
        $(this).addClass('nav-tab-active');
        var tab = $(this).attr('href');
        $('.wpgiftv-row .tab-content').removeClass('tab-content-active');
        $(tab).addClass('tab-content-active');
    });

})(jQuery);

function redeemVoucher(voucher_id, amount) {
    jQuery("#redeem_Voucher").attr("disabled", true);
    var voucher_amount = jQuery('#voucher_amount' + voucher_id).val();
    if (amount >= voucher_amount) {
        var data = {
            'action': 'wpgv_redeem_voucher',
            'voucher_id': voucher_id,
            'voucher_amount': voucher_amount,
        };

        jQuery.post(ajaxurl, data, function(response) {
            alert('Got this from the server: ' + response);
            location.reload();
        });
    } else {
        alert('Please enter an amount less than or equal to the remaining balance');
    }
}

// admin add gift voucher order

var form = jQuery("#example-advanced-form").show();

form.steps({
    headerTag: "h3",
    bodyTag: "fieldset",
    transitionEffect: "slideLeft",
    onStepChanging: function(event, currentIndex, newIndex) {
        // Allways allow previous action even if the current form is not valid!
        if (currentIndex > newIndex) {
            return true;
        }
        // Forbid next action on "Warning" step if the user is to young

        // Needed in some cases if the user went back (clean up)
        if (currentIndex < newIndex) {
            // To remove error styles
            form.find(".body:eq(" + newIndex + ") label.error").remove();
            form.find(".body:eq(" + newIndex + ") .error").removeClass("error");
        }
        form.validate().settings.ignore = ":disabled,:hidden";
        return form.valid();
    },
    onStepChanged: function(event, currentIndex, priorIndex) {
        if (currentIndex === 2) {
            var template_name = jQuery('input[name=template_id]:checked').data("title");
            jQuery(".wpgv-itemtitle").html(template_name);
        }

        if (currentIndex === 3) {
            jQuery(".wizard .actions a[href='#finish']").hide();
        }
    },
    onFinishing: function(event, currentIndex) {
        form.validate().settings.ignore = ":disabled";
        return form.valid();
    },
    onFinished: function(event, currentIndex) {
        alert("Submitted!");
    },
    labels: {
        finish: "finish",
        next: "Continue",
        previous: "Back",
    }
}).validate({
    errorPlacement: function errorPlacement(error, element) { element.after(error); },

});


jQuery("#buying_for_selectbox").change(function() {
    var selected_option = jQuery('#buying_for_selectbox').val();
    if (selected_option == "yourself") {
        jQuery("#shipping_email").val("");
        jQuery("#wpgv-shipping_email").hide();
        jQuery(".shippingasemail").hide();
        jQuery("#buying_for").val("yourself");
        jQuery(".fromname").hide();
        jQuery(".voucherrecipientname").hide();
    } else {
        jQuery("#wpgv-shipping_email").show();
        jQuery(".shippingasemail").show();
        jQuery("#buying_for").val("someone_else");
        jQuery(".fromname").show();
        jQuery(".voucherrecipientname").show();
    }
});

jQuery("#shipping_selectbox").change(function() {
    var selected_option = jQuery('#shipping_selectbox').val();
    if (selected_option == "shipping_as_email") {
        var buuing_for_val = jQuery("#buying_for_selectbox").val();
        if (buuing_for_val == "yourself") {
            jQuery("#shipping_email").val("");
            jQuery("#wpgv-shipping_email").hide();
            jQuery(".shippingasemail").hide();
        } else {
            jQuery("#wpgv-shipping_email").show();
            jQuery(".shippingasemail").show();
        }

        jQuery(".shipping_as_post_fields").hide();
        jQuery(".order_details_preview .wpgv_shipping_box").hide();
        var $website_commission_price = jQuery('#website_commission_price');
        var voucher_value = jQuery("#voucherAmount").val();
        var totalprice = parseFloat(voucher_value) + parseFloat($website_commission_price.data('price'));
        jQuery("#totalprice span").html(totalprice);
        jQuery(".voucherShippingInfo").html("Shipping via Email");
        jQuery("#shipping").val("shipping_as_email");
    } else {
        jQuery("#shipping_email").val("");
        jQuery("#wpgv-shipping_email").hide();
        jQuery(".shippingasemail").hide();
        jQuery(".shipping_as_post_fields").show();
        jQuery(".order_details_preview .wpgv_shipping_box").show();
        var shipping_price = jQuery('input[name=shipping_method]:checked').data("price");
        var shipping_string_value = jQuery('input[name=shipping_method]:checked').val();
        var $website_commission_price = jQuery('#website_commission_price');
        var voucher_value = jQuery("#voucherAmount").val();
        var totalprice = parseFloat(voucher_value) + parseFloat(shipping_price) + parseFloat($website_commission_price.data('price'));
        jQuery("#shippingprice span").html(shipping_price);
        jQuery("#totalprice span").html(totalprice);
        jQuery(".voucherShippingInfo").html("Shipping via Post");
        jQuery(".voucherShippingMethodInfo").html(shipping_string_value);
        jQuery("#shipping").val("shipping_as_post");
    }
});

jQuery('#voucherForName').on('input blur', function() {
    var dInput = this.value;
    jQuery("#autoyourname").html(dInput);
    jQuery(".voucherYourNameInfo").html(dInput);
});

jQuery('#voucherFromName').on('input blur', function() {
    var dInput = this.value;
    jQuery(".voucherReceiverInfo").html(dInput);
});

jQuery('#voucherAmount').on('input blur', function() {
    var dInput = this.value;
    var $website_commission_price = jQuery('#website_commission_price');
    var totalprice = parseFloat(dInput) + parseFloat($website_commission_price.data('price'));
    jQuery("#itemprice span").html(dInput);
    jQuery("#totalprice span").html(totalprice);
    jQuery(".voucherAmountInfo b").html(dInput);
});

jQuery('#voucherMessage').on('input blur', function() {
    var dInput = this.value;
    jQuery(".voucherMessageInfo").html(dInput);
});

jQuery("input[name=shipping_method]").change(function() {
    var shipping_price = jQuery('input[name=shipping_method]:checked').data("price");
    var shipping_string_value = jQuery('input[name=shipping_method]:checked').val();
    var $website_commission_price = jQuery('#website_commission_price');
    var voucher_value = jQuery("#voucherAmount").val();
    var totalprice = parseFloat(voucher_value) + parseFloat(shipping_price) + parseFloat($website_commission_price.data('price'));
    jQuery("#shippingprice span").html(shipping_price);
    jQuery("#totalprice span").html(totalprice);
    jQuery(".voucherShippingMethodInfo").html(shipping_string_value);
});

jQuery('#voucherFirstName').on('input blur', function() {
    var dInput = this.value;
    jQuery(".voucherFirstNameInfo").html(dInput);
});
jQuery('#voucherLastName').on('input blur', function() {
    var dInput = this.value;
    jQuery(".voucherLastNameInfo").html(dInput);
});
jQuery('#voucherEmail').on('input blur', function() {
    var dInput = this.value;
    jQuery(".voucherEmailInfo").html(dInput);
});
jQuery('#shipping_email').on('input blur', function() {
    var dInput = this.value;
    jQuery(".voucherShippingEmailInfo").html(dInput);
});
jQuery('#voucherAddress').on('input blur', function() {
    var dInput = this.value;
    jQuery(".voucherAddressInfo").html(dInput);
});
jQuery('#voucherPincode').on('input blur', function() {
    var dInput = this.value;
    jQuery(".voucherPincodeInfo").html(dInput);
});

if (jQuery('#coupon_code_length').length) {

    var numberlenght = jQuery('#coupon_code_length').val()
    var stringlenght = '1';
    var stringlenght1 = '9';
    for (var i = 0; i < numberlenght - 1; i++) {
        stringlenght += 0
        stringlenght1 += 0
    }
    stringlenght = parseFloat(stringlenght);
    stringlenght1 = parseFloat(stringlenght1);
    jQuery('.codeCard').val(Math.floor(stringlenght + Math.random() * stringlenght1));
}

jQuery('#voucherPaymentButton').on('click', function() {

    var nonce = jQuery('input[name=voucher_form_verify]').val(),
        templates_id = wpgv_b64EncodeUnicode(jQuery('input[name=template_id]:checked').val()),
        buying_for = wpgv_b64EncodeUnicode(jQuery('#buying_for').val()),
        forName = wpgv_b64EncodeUnicode(jQuery('#voucherForName').val()),
        fromName = wpgv_b64EncodeUnicode(jQuery('#voucherFromName').val()),
        voucherValue = wpgv_b64EncodeUnicode(jQuery('#itemprice span').html()),
        message = wpgv_b64EncodeUnicode(jQuery('#voucherMessage').val()),
        shipping = wpgv_b64EncodeUnicode(jQuery('#shipping').val()),
        shipping_email = wpgv_b64EncodeUnicode(jQuery('#shipping_email').val()),
        firstName = wpgv_b64EncodeUnicode(jQuery('#voucherFirstName').val()),
        lastName = wpgv_b64EncodeUnicode(jQuery('#voucherLastName').val()),
        email = wpgv_b64EncodeUnicode(jQuery('#voucherEmail').val()),
        address = wpgv_b64EncodeUnicode(jQuery('#voucherAddress').val()),
        pincode = wpgv_b64EncodeUnicode(jQuery('#voucherPincode').val()),
        shipping_method = wpgv_b64EncodeUnicode(jQuery('input[name=shipping_method]:checked').val()),
        expiry = wpgv_b64EncodeUnicode(jQuery('.expiryCard').val()),
        style = wpgv_b64EncodeUnicode(jQuery('#chooseStyle').val()),
        code = wpgv_b64EncodeUnicode(jQuery('.codeCard').val());

    jQuery.ajax({
        url: ajaxurl,
        type: "POST",
        data: 'action=admin_wpgv_doajax_voucher_pdf_save_func&nonce=' + nonce + '&template=' + templates_id + '&buying_for=' + buying_for + '&for=' + forName + '&from=' + fromName + '&value=' + voucherValue + '&message=' + message + '&expiry=' + expiry + '&code=' + code + '&shipping=' + shipping + '&shipping_email=' + shipping_email + '&firstname=' + firstName + '&lastname=' + lastName + '&email=' + email + '&address=' + address + '&pincode=' + pincode + '&shipping_method=' + shipping_method + '&style=' + style,
        success: function(response) {
            var data = jQuery.parseJSON(response);
            if (data.status == 1) {
                window.location.replace(data.url);
            } else {
                alert("error in add gift voucher order");
            }
        }
    });
});

function wpgv_b64EncodeUnicode(str) {
    return btoa(encodeURIComponent(str).replace(/%([0-9A-F]{2})/g, function(match, p1) {
        return String.fromCharCode(parseInt(p1, 16))
    }))
}

function wpgv_b64DecodeUnicode(str) {
    return decodeURIComponent(Array.prototype.map.call(atob(str), function(c) {
        return '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2)
    }).join(''))
}

jQuery(document).ready(function($) {

    // event upload logo company voucher_setting.php
    jQuery('.upload_logo_voucher').click(function(e) {
        e.preventDefault();
        var mediaUploader = wp.media({
                title: 'Add Voucher Logo',
                button: {
                    text: 'Upload Logo'
                },
                multiple: false // Set this to true to allow multiple files to be selected
            })
            .on('select', function() {
                var attachment = mediaUploader.state().get('selection').first().toJSON();
                var urlLogo = attachment.url;
                jQuery('.image_src_logo').attr('src', attachment.url).show();
                jQuery('#company_logo_voucher').attr('value', urlLogo)
                jQuery('#remove_logo_voucher').show();
            })
            .open();
    });
    // event upload logo company voucher_setting.php

    // get parameter from url
    function getParameterByName(name, url) {
        if (!url) url = window.location.href;
        name = name.replace(/[\[\]]/g, "\\$&");
        var regex = new RegExp("[?&]" + name + "(=([^&#]*)|&|#|$)"),
            results = regex.exec(url);
        if (!results) return null;
        if (!results[2]) return '';
        return decodeURIComponent(results[2].replace(/\+/g, " "));
    }
    // get parameter from url

    var jsontemplate;
    var pngtemplate;
    var getlogocompany;
    var stage = null;
    var get_action = getParameterByName('action');
    var resize_logo = null;

    function getJsonTemplate(getjson, getpng, getlogo, ) {
        if (getjson != null || getpng != null || getlogo != null) {
            var data;
            if (typeof getjson.children != 'undefined') {
                data = getjson
            } else {
                var context;
                $.ajax({
                    url: getjson,
                    async: false,
                    dataType: 'json',
                    success: function(json) {
                        assignVariable(json);
                    }
                });

                function assignVariable(data) {
                    context = data;
                }
                data = context;

            }

            var get_plus_day = jQuery("#voucher_expiry_value").val();
            var someDate = new Date();
            someDate.setDate(someDate.getDate() + parseInt(get_plus_day));
            var dateFormated = someDate.toISOString().substr(0, 10);
            var dateAr = dateFormated.split('-');
            var newDate = dateAr[2] + '.' + dateAr[1] + '.' + dateAr[0].slice(-2);
            // Counpon number ramdom 16
            counpon = (Math.random() + ' ').substring(2, 10) + (Math.random() + ' ').substring(2, 10);

            var htmlJson = JSON.stringify(data);
            // get json template for input json_template
            jQuery('#json_template').attr('value', htmlJson);
            jQuery('#json_template').attr('data-json-template', htmlJson);
            stage = Konva.Node.create(data, 'container-template-chosse-template');
            stageWidth = stage.getAttr('width');
            stageHeight = stage.getAttr('height');

            // add background template
            if (pngtemplate != "") {
                stageWidth = stage.getAttr('width');
                stageHeight = stage.getAttr('height');
                stage.find('#giftcard_bg').forEach(imageNode => {
                    const nativeImage = new window.Image();
                    nativeImage.onload = () => {
                        imageNode.image(nativeImage);
                        imageNode.width(stageWidth);
                        imageNode.height(stageHeight);
                        imageNode.getLayer().batchDraw();
                    }
                    nativeImage.src = getpng;
                });
            }
            // add background template

            //add logo template

            if (typeof stage.find('#giftcard_logo')[0] !== 'undefined') {
                stage.find('#giftcard_logo').forEach(imageNode => {
                    const nativeImage = new window.Image();
                    nativeImage.onload = () => {
                        imageNode.image(nativeImage);
                        var maxWidth = 40;
                        var maxHeight = 40;
                        var ratio = 0;
                        var width = nativeImage.width;
                        var height = nativeImage.height;

                        if (width > maxWidth) {
                            ratio = maxWidth / width;
                            height = height * ratio;
                            width = width * ratio;
                        }
                        // Check if current height is larger than max
                        if (height > maxHeight) {
                            ratio = maxHeight / height;
                            width = width * ratio;
                            height = height * ratio;
                        }
                        imageNode.width(width);
                        imageNode.height(height);
                        imageNode.getLayer().batchDraw();
                        imageNode.draggable(true);
                    }
                    nativeImage.src = getlogo;
                    style_cursor_pointer(stage.find('#giftcard_logo')[0]);
                });

                // Resize logo
                var layer = new Konva.Layer();
                stage.add(layer);
                var MAX_WIDTH = 100;
                var resize_logo = new Konva.Transformer({
                    boundBoxFunc: function(oldBoundBox, newBoundBox) {
                        if (Math.abs(newBoundBox.width) > MAX_WIDTH) {
                            return oldBoundBox;
                        }

                        return newBoundBox;
                    },
                });
                layer.add(resize_logo);


                var selectionRectangle = new Konva.Rect({
                    fill: 'rgba(255,255,255,1)',
                    stroke: 'black',
                    draggable: true,
                    visible: false,
                });
                layer.add(selectionRectangle);
                var scale_after = stage.find('#giftcard_logo')[0].scaleX();
                var scale_before = null;
                resize_logo.on('transformend', function() {
                    scale_before = stage.find('#giftcard_logo')[0].scaleX();
                    if (scale_after != scale_before) {
                        jQuery('#check_temp_custom').attr('value', "1");
                        stage.draw();
                        var json = stage.toJSON();
                        jQuery("#json_template").attr('value', json);
                    }
                });


                // console.log(selectionRectangle);
                $("body").click((event) => {
                    if (!$(event.target).closest('#container-template-chosse-template').length) {
                        resize_logo.nodes([]);
                        return;
                    }
                });

                stage.on('click', function(e) {
                    // console.log(jQuery(this).is(":visible"));
                    // console.log(e.target);
                    // console.log(stage);
                    if (selectionRectangle.visible()) {
                        return;
                    }
                    if (e.target.hasName('giftcard_logo')) {
                        resize_logo.nodes([e.target]);

                    } else {
                        resize_logo.nodes([]);
                        return;
                    }

                });

            }
            //add logo template

            // add name voucher
            var draggable_gift_title_first = stage.find('#gift_title_first')[0];
            if (typeof draggable_gift_title_first !== 'undefined') {
                draggable_gift_title_first.draggable(true);
                stage.draw();
                style_cursor_pointer(draggable_gift_title_first);
                draggable_gift_title_first.on('dragmove', function() {
                    jQuery('#check_temp_custom').attr('value', "1");
                    stage.draw();
                    var json = stage.toJSON();
                    jQuery("#json_template").attr('value', json);
                });
            }
            var draggable_gift_title_last = stage.find('#gift_title_last')[0];
            if (typeof draggable_gift_title_last !== 'undefined') {
                draggable_gift_title_last.draggable(true);
                stage.draw();
                style_cursor_pointer(draggable_gift_title_last);
                draggable_gift_title_last.on('dragmove', function() {
                    jQuery('#check_temp_custom').attr('value', "1");
                    stage.draw();
                    var json = stage.toJSON();
                    jQuery("#json_template").attr('value', json);
                });
            }
            var draggable_gift_title_end = stage.find('#gift_title_end')[0];
            if (typeof draggable_gift_title_end !== 'undefined') {
                draggable_gift_title_end.draggable(true);
                stage.draw();
                style_cursor_pointer(draggable_gift_title_end);
                draggable_gift_title_end.on('dragmove', function() {
                    jQuery('#check_temp_custom').attr('value', "1");
                    stage.draw();
                    var json = stage.toJSON();
                    jQuery("#json_template").attr('value', json);
                });
            }

            // Picker color all text
            var colorVoucher = '#000';
            jQuery('#color_text_voucher').iris({
                defaultColor: true,
                change: function(event, ui) {
                    colorVoucher = ui.color.toString();
                    jQuery(".wp-color-result.wp-picker-open").attr("style", "background-color:" + colorVoucher);
                    updateFillColor();
                    jQuery('#check_temp_custom').attr('value', "1");
                },
                clear: function() {},
                hide: true,
                palettes: true
            });
            // Picker color price
            jQuery('#color_text_voucher_price').iris({
                defaultColor: true,
                change: function(event, ui) {
                    colorVoucher = ui.color.toString();
                    jQuery(".wp-color-result.wp-picker-open").attr("style", "background-color:" + colorVoucher);
                    updateFillColorPrice();
                    jQuery('#check_temp_custom').attr('value', "1");
                },
                clear: function() {},
                hide: false,
                palettes: true
            });
            // Picker color name voucher
            jQuery('#color_text_name_voucher').iris({
                defaultColor: "#ddd",
                change: function(event, ui) {
                    colorVoucher = ui.color.toString();
                    jQuery(".wp-color-result.wp-picker-open").attr("style", "background-color:" + colorVoucher);
                    updateFillColorNameVoucher();
                    jQuery('#check_temp_custom').attr('value', "1");
                },
                clear: function() {},
                hide: false,
                palettes: true
            });
            // function add style cursor pointer to text
            function style_cursor_pointer(get_element_by_id) {
                get_element_by_id.on('mouseover', function() {
                    document.body.style.cursor = 'move';
                });
                get_element_by_id.on('mouseout', function() {
                    document.body.style.cursor = 'default';
                });
            }
            // function add style cursor pointer to text

            // update color name voucher
            function updateFillColorNameVoucher() {
                var gift_title_first = stage.find('#gift_title_first')[0];
                if (typeof gift_title_first !== 'undefined') {
                    gift_title_first.fill(colorVoucher);
                }
                var gift_title_last = stage.find('#gift_title_last')[0];
                if (typeof gift_title_last !== 'undefined') {
                    gift_title_last.fill(colorVoucher);
                }
                var gift_title_end = stage.find('#gift_title_end')[0];
                if (typeof gift_title_end !== 'undefined') {
                    gift_title_end.fill(colorVoucher);
                }
                stage.draw();
                var json = stage.toJSON();
                jQuery("#json_template").attr('value', json);
            }
            // update color price
            function updateFillColorPrice() {
                var giftcard_monney = stage.find('#giftcard_monney')[0];
                if (typeof giftcard_monney !== 'undefined') {
                    giftcard_monney.fill(colorVoucher);
                }
                var giftcard_monney_label_color = stage.find('#giftcard_monney_label')[0];
                if (typeof giftcard_monney_label_color !== 'undefined') {
                    giftcard_monney_label_color.fill(colorVoucher);
                }
                stage.draw();
                var json = stage.toJSON();
                jQuery("#json_template").attr('value', json);
            }
            // update color all text
            function updateFillColor() {

                var giftcard_company_color = stage.find('#gift_title_company')[0];
                if (typeof giftcard_company_color !== 'undefined') {
                    giftcard_company_color.fill(colorVoucher);
                }
                var giftcard_giftfrom_color = stage.find('#giftfrom_input')[0];
                if (typeof giftcard_giftfrom_color !== 'undefined') {
                    giftcard_giftfrom_color.fill(colorVoucher);
                }

                var giftcard_giftfrom_label_color = stage.find('#giftfrom_label')[0];
                if (typeof giftcard_giftfrom_label_color !== 'undefined') {
                    giftcard_giftfrom_label_color.fill(colorVoucher);
                }

                var giftcard_giftto_color = stage.find('#giftto_input')[0];
                if (typeof giftcard_giftto_color !== 'undefined') {
                    giftcard_giftto_color.fill(colorVoucher);
                }

                var giftcard_giftto_label_color = stage.find('#giftto_label')[0];
                if (typeof giftcard_giftto_label_color !== 'undefined') {
                    giftcard_giftto_label_color.fill(colorVoucher);
                }

                var giftcard_date_color = stage.find('#giftcard_date_gift_input')[0];
                if (typeof giftcard_date_color !== 'undefined') {
                    giftcard_date_color.fill(colorVoucher);
                }

                var giftcard_giftdate_label_color = stage.find('#giftcard_date_gift_label')[0];
                if (typeof giftcard_giftdate_label_color !== 'undefined') {
                    giftcard_giftdate_label_color.fill(colorVoucher);
                }

                var giftcard_counpon_color = stage.find('#giftcard_counpon')[0];
                if (typeof giftcard_counpon_color !== 'undefined') {
                    giftcard_counpon_color.fill(colorVoucher);
                }

                var giftcard_counpon_label_color = stage.find('#giftcard_counpon_label')[0];
                if (typeof giftcard_counpon_label_color !== 'undefined') {
                    giftcard_counpon_label_color.fill(colorVoucher);
                }

                var giftcard_des_color = stage.find('#giftcard_des')[0];
                if (typeof giftcard_des_color !== 'undefined') {
                    giftcard_des_color.fill(colorVoucher);
                }
                var giftcard_email_color = stage.find('#giftcard_email')[0];
                if (typeof giftcard_email_color !== 'undefined') {
                    giftcard_email_color.fill(colorVoucher);
                }

                var giftcard_website_color = stage.find('#giftcard_website')[0];
                if (typeof giftcard_website_color !== 'undefined') {
                    giftcard_website_color.fill(colorVoucher);
                }
                var giftcard_des2_color = stage.find('#giftcard_note')[0];
                if (typeof giftcard_des2_color !== 'undefined') {
                    giftcard_des2_color.fill(colorVoucher);
                }
                stage.draw();
                var json = stage.toJSON();
                jQuery("#json_template").attr('value', json);

            }
            // edit font size
            jQuery('#color_font_voucher_price').on('change keyup', function() {
                if (typeof stage.find('#giftcard_monney')[0] !== 'undefined') {
                    var edit_font_price = stage.find('#giftcard_monney')[0];
                    edit_font_price.fontSize(jQuery('#color_font_voucher_price').val());
                    stage.draw();
                    var json = stage.toJSON();
                    jQuery("#json_template").attr('value', json);
                    jQuery('#check_temp_custom').attr('value', "1");
                }
            });
            jQuery('#color_font_all_text').on('change keyup', function() {
                jQuery('#check_temp_custom').attr('value', "1");
                if (typeof stage.find('#giftto_input')[0] !== 'undefined') {
                    var edit_font_giftto = stage.find('#giftto_input')[0];
                    edit_font_giftto.fontSize(jQuery('#color_font_all_text').val());
                }
                if (typeof stage.find('#giftfrom_input')[0] !== 'undefined') {
                    var edit_font_giftfrom = stage.find('#giftfrom_input')[0];
                    edit_font_giftfrom.fontSize(jQuery('#color_font_all_text').val());
                }
                if (typeof stage.find('#giftcard_date_gift_input')[0] !== 'undefined') {
                    var edit_font_date = stage.find('#giftcard_date_gift_input')[0];
                    edit_font_date.fontSize(jQuery('#color_font_all_text').val());
                }
                if (typeof stage.find('#giftcard_email')[0] !== 'undefined') {
                    var edit_font_email = stage.find('#giftcard_email')[0];
                    edit_font_email.fontSize(jQuery('#color_font_all_text').val());
                }
                if (typeof stage.find('#giftcard_website')[0] !== 'undefined') {
                    var edit_font_website = stage.find('#giftcard_website')[0];
                    edit_font_website.fontSize(jQuery('#color_font_all_text').val());
                }
                if (typeof stage.find('#gift_title_company')[0] !== 'undefined') {
                    var edit_font_title_company = stage.find('#gift_title_company')[0];
                    edit_font_title_company.fontSize(jQuery('#color_font_all_text').val());
                }
                if (typeof stage.find('#giftcard_counpon')[0] !== 'undefined') {
                    var edit_font_counpon = stage.find('#giftcard_counpon')[0];
                    edit_font_counpon.fontSize(jQuery('#color_font_all_text').val());
                }
                if (typeof stage.find('#giftcard_des')[0] !== 'undefined') {
                    var edit_font_des = stage.find('#giftcard_des')[0];
                    edit_font_des.fontSize(jQuery('#color_font_all_text').val());
                }
                if (typeof stage.find('#giftfrom_label')[0] !== 'undefined') {
                    var edit_font_giftfrom_label = stage.find('#giftfrom_label')[0];
                    edit_font_giftfrom_label.fontSize(jQuery('#color_font_all_text').val());
                }
                if (typeof stage.find('#giftto_label')[0] !== 'undefined') {
                    var edit_font_giftto_label = stage.find('#giftto_label')[0];
                    edit_font_giftto_label.fontSize(jQuery('#color_font_all_text').val());
                }
                if (typeof stage.find('#giftcard_date_gift_label')[0] !== 'undefined') {
                    var edit_font_date_label = stage.find('#giftcard_date_gift_label')[0];
                    edit_font_date_label.fontSize(jQuery('#color_font_all_text').val());
                }
                if (typeof stage.find('#giftcard_counpon_label')[0] !== 'undefined') {
                    var edit_font_counpon_label = stage.find('#giftcard_counpon_label')[0];
                    edit_font_counpon_label.fontSize(jQuery('#color_font_all_text').val());
                }
                if (typeof stage.find('#giftcard_note')[0] !== 'undefined') {
                    var edit_font_giftcard_note = stage.find('#giftcard_note')[0];
                    edit_font_giftcard_note.fontSize(jQuery('#color_font_all_text').val());
                }
                if (typeof stage.find('#giftcard_monney_label')[0] !== 'undefined') {
                    var edit_font_monney_label = stage.find('#giftcard_monney_label')[0];
                    edit_font_monney_label.fontSize(jQuery('#color_font_all_text').val());
                }
                stage.draw();
                var json = stage.toJSON();
                jQuery("#json_template").attr('value', json);
            });
            // edit font size


            // draggable true

            var draggable_giftcard_note = stage.find('#giftcard_note')[0];
            if (typeof draggable_giftcard_note !== 'undefined') {
                draggable_giftcard_note.draggable(true);
                stage.draw();
                style_cursor_pointer(draggable_giftcard_note);
            }
            var draggable_gift_title_first = stage.find('#gift_title_first')[0];
            if (typeof draggable_gift_title_first !== 'undefined') {
                draggable_gift_title_first.draggable(true);
                stage.draw();
                style_cursor_pointer(draggable_gift_title_first);
            }
            var draggable_gift_title_last = stage.find('#gift_title_last')[0];
            if (typeof draggable_gift_title_last !== 'undefined') {
                draggable_gift_title_last.draggable(true);
                stage.draw();
                style_cursor_pointer(draggable_gift_title_last);
            }
            var draggable_gift_monney_label = stage.find('#giftcard_monney_label')[0];
            if (typeof draggable_gift_monney_label !== 'undefined') {
                draggable_gift_monney_label.draggable(true);
                stage.draw();
                style_cursor_pointer(draggable_gift_monney_label);
            }
            var draggable_giftto = stage.find('#giftto_input')[0];
            if (typeof draggable_giftto !== 'undefined') {
                draggable_giftto.draggable(true);
                stage.draw();
                style_cursor_pointer(draggable_giftto);
            }

            var draggable_giftfrom = stage.find('#giftfrom_input')[0];
            if (typeof draggable_giftfrom !== 'undefined') {
                draggable_giftfrom.draggable(true);
                stage.draw();
                style_cursor_pointer(draggable_giftfrom);
            }
            var draggable_giftdate = stage.find('#giftcard_date_gift_input')[0];
            if (typeof draggable_giftdate !== 'undefined') {
                draggable_giftdate.draggable(true);
                stage.draw();
                style_cursor_pointer(draggable_giftdate);
            }
            var draggable_giftemail = stage.find('#giftcard_email')[0];
            if (typeof draggable_giftemail !== 'undefined') {
                draggable_giftemail.draggable(true);
                stage.draw();
                style_cursor_pointer(draggable_giftemail);
            }

            var draggable_giftwebsite = stage.find('#giftcard_website')[0];
            if (typeof draggable_giftwebsite !== 'undefined') {
                draggable_giftwebsite.draggable(true);
                stage.draw();
                style_cursor_pointer(draggable_giftwebsite);
            }
            var draggable_gifttitlecompany = stage.find('#gift_title_company')[0];
            if (typeof draggable_gifttitlecompany !== 'undefined') {
                draggable_gifttitlecompany.draggable(true);
                stage.draw();
                style_cursor_pointer(draggable_gifttitlecompany);
            }

            var draggable_giftcoupon = stage.find('#giftcard_counpon')[0];
            if (typeof draggable_giftcoupon !== 'undefined') {
                draggable_giftcoupon.draggable(true);
                stage.draw();
                style_cursor_pointer(draggable_giftcoupon);
            }

            var draggable_giftmonney = stage.find('#giftcard_monney')[0];
            if (typeof draggable_giftmonney !== 'undefined') {
                draggable_giftmonney.draggable(true);
                stage.draw();
                style_cursor_pointer(draggable_giftmonney);
            }
            var draggable_giftcard_monney_label = stage.find('#giftcard_monney_label')[0];
            if (typeof draggable_giftcard_monney_label !== 'undefined') {
                draggable_giftcard_monney_label.draggable(true);
                stage.draw();
                style_cursor_pointer(draggable_giftcard_monney_label);
            }

            var draggable_giftdes = stage.find('#giftcard_des')[0];
            if (typeof draggable_giftdes !== 'undefined') {
                draggable_giftdes.draggable(true);
                stage.draw();
                style_cursor_pointer(draggable_giftdes);
            }

            var draggable_giftfrom_label = stage.find('#giftfrom_label')[0];
            if (typeof draggable_giftfrom_label !== 'undefined') {
                draggable_giftfrom_label.draggable(true);
                stage.draw();
                style_cursor_pointer(draggable_giftfrom_label);
            }

            var draggable_giftto_label = stage.find('#giftto_label')[0];
            if (typeof draggable_giftto_label !== 'undefined') {
                draggable_giftto_label.draggable(true);
                stage.draw();
                style_cursor_pointer(draggable_giftto_label);
            }

            var draggable_giftdate_label = stage.find('#giftcard_date_gift_label')[0];
            if (typeof draggable_giftdate_label !== 'undefined') {
                draggable_giftdate_label.draggable(true);
                stage.draw();
                style_cursor_pointer(draggable_giftdate_label);
            }
            var draggable_giftcoupon_label = stage.find('#giftcard_counpon_label')[0];
            if (typeof draggable_giftcoupon_label !== 'undefined') {
                draggable_giftcoupon_label.draggable(true);
                stage.draw();
                style_cursor_pointer(draggable_giftcoupon_label);
            }
            // draggable true

            // change json 
            var draggable_giftcard_note = stage.find('#giftcard_note')[0];
            if (typeof draggable_giftcard_note !== 'undefined') {
                draggable_giftcard_note.on('dragmove', function() {
                    jQuery('#check_temp_custom').attr('value', "1");
                    stage.draw();
                    var json = stage.toJSON();
                    jQuery("#json_template").attr('value', json);
                });
            }
            var draggable_giftcard_logo = stage.find('#giftcard_logo')[0];
            if (typeof draggable_giftcard_logo !== 'undefined') {
                draggable_giftcard_logo.on('dragmove', function() {
                    jQuery('#check_temp_custom').attr('value', "1");
                    stage.draw();
                    var json = stage.toJSON();
                    jQuery("#json_template").attr('value', json);
                });
            }

            if (typeof draggable_giftto !== 'undefined') {
                draggable_giftto.on('dragmove', function() {
                    jQuery('#check_temp_custom').attr('value', "1");
                    stage.draw();
                    var json = stage.toJSON();
                    jQuery("#json_template").attr('value', json);
                });
            }
            if (typeof draggable_giftfrom !== 'undefined') {
                draggable_giftfrom.on('dragmove', function() {
                    jQuery('#check_temp_custom').attr('value', "1");
                    stage.draw();
                    var json = stage.toJSON();
                    jQuery("#json_template").attr('value', json);
                });
            }
            if (typeof draggable_giftdate !== 'undefined') {
                draggable_giftdate.on('dragmove', function() {
                    jQuery('#check_temp_custom').attr('value', "1");
                    stage.draw();
                    var json = stage.toJSON();
                    jQuery("#json_template").attr('value', json);
                });
            }
            if (typeof draggable_giftemail !== 'undefined') {
                draggable_giftemail.on('dragmove', function() {
                    jQuery('#check_temp_custom').attr('value', "1");
                    stage.draw();
                    var json = stage.toJSON();
                    jQuery("#json_template").attr('value', json);
                });
            }
            if (typeof draggable_giftwebsite !== 'undefined') {
                draggable_giftwebsite.on('dragmove', function() {
                    jQuery('#check_temp_custom').attr('value', "1");
                    stage.draw();
                    var json = stage.toJSON();
                    jQuery("#json_template").attr('value', json);
                });
            }
            if (typeof draggable_gifttitlecompany !== 'undefined') {
                draggable_gifttitlecompany.on('dragmove', function() {
                    jQuery('#check_temp_custom').attr('value', "1");
                    stage.draw();
                    var json = stage.toJSON();
                    jQuery("#json_template").attr('value', json);
                });
            }

            if (typeof draggable_giftcoupon !== 'undefined') {
                draggable_giftcoupon.on('dragmove', function() {
                    jQuery('#check_temp_custom').attr('value', "1");
                    stage.draw();
                    var json = stage.toJSON();
                    jQuery("#json_template").attr('value', json);
                });
            }
            if (typeof draggable_giftmonney !== 'undefined') {
                draggable_giftmonney.on('dragmove', function() {
                    jQuery('#check_temp_custom').attr('value', "1");
                    stage.draw();
                    var json = stage.toJSON();
                    jQuery("#json_template").attr('value', json);
                });
            }
            if (typeof draggable_giftdes !== 'undefined') {
                draggable_giftdes.on('dragmove', function() {
                    jQuery('#check_temp_custom').attr('value', "1");
                    stage.draw();
                    var json = stage.toJSON();
                    jQuery("#json_template").attr('value', json);
                });
            }
            if (typeof draggable_giftfrom_label !== 'undefined') {
                draggable_giftfrom_label.on('dragmove', function() {
                    jQuery('#check_temp_custom').attr('value', "1");
                    stage.draw();
                    var json = stage.toJSON();
                    jQuery("#json_template").attr('value', json);
                });
            }
            if (typeof draggable_giftto_label !== 'undefined') {
                draggable_giftto_label.on('dragmove', function() {
                    jQuery('#check_temp_custom').attr('value', "1");
                    stage.draw();
                    var json = stage.toJSON();
                    jQuery("#json_template").attr('value', json);
                });
            }
            if (typeof draggable_giftdate_label !== 'undefined') {
                draggable_giftdate_label.on('dragmove', function() {
                    jQuery('#check_temp_custom').attr('value', "1");
                    stage.draw();
                    var json = stage.toJSON();
                    jQuery("#json_template").attr('value', json);
                });
            }
            if (typeof draggable_giftcoupon_label !== 'undefined') {
                draggable_giftcoupon_label.on('dragmove', function() {
                    jQuery('#check_temp_custom').attr('value', "1");
                    stage.draw();
                    var json = stage.toJSON();
                    jQuery("#json_template").attr('value', json);
                });
            }
            if (typeof draggable_giftcard_monney_label !== 'undefined') {
                draggable_giftcard_monney_label.on('dragmove', function() {
                    jQuery('#check_temp_custom').attr('value', "1");
                    stage.draw();
                    var json = stage.toJSON();
                    jQuery("#json_template").attr('value', json);
                });
            }

            // change json

            /// change form
            $('#edit_price_temp').on('change keyup', function() {
                if (typeof stage.find('#giftcard_monney')[0] !== 'undefined') {
                    var changeMoney = stage.find('#giftcard_monney')[0];
                    changeMoney.text("$" + jQuery(this).val());
                    stage.draw();
                    var json = stage.toJSON();
                    jQuery("#json_template").attr('value', json);
                    jQuery('#check_temp_custom').attr('value', "1");
                }
            });
            $('#edit_giftto_temp').on('change keyup', function() {
                if (typeof stage.find('#giftto_input')[0] !== 'undefined') {
                    var changeGiftto = stage.find('#giftto_input')[0];
                    changeGiftto.text(jQuery(this).val());
                    stage.draw();
                    var json = stage.toJSON();
                    jQuery("#json_template").attr('value', json);
                    jQuery('#check_temp_custom').attr('value', "1");
                }
            });
            $('#edit_giftform_temp').on('change keyup', function() {
                if (typeof stage.find('#giftfrom_input')[0] !== 'undefined') {
                    var changeGiftfrom = stage.find('#giftfrom_input')[0];
                    changeGiftfrom.text(jQuery(this).val());
                    stage.draw();
                    var json = stage.toJSON();
                    jQuery("#json_template").attr('value', json);
                    jQuery('#check_temp_custom').attr('value', "1");
                }
            });
            $('#edit_desc_temp').on('change keyup', function() {
                if (typeof stage.find('#giftcard_des')[0] !== 'undefined') {
                    var changeGiftdes = stage.find('#giftcard_des')[0];
                    changeGiftdes.text(jQuery(this).val());
                    stage.draw();
                    var json = stage.toJSON();
                    jQuery("#json_template").attr('value', json);
                    jQuery('#check_temp_custom').attr('value', "1");
                }
            });
            $('#edit_name_v1_temp').on('change keyup', function() {
                if (typeof stage.find('#gift_title_first')[0] !== 'undefined') {
                    var changeGiftnamev1 = stage.find('#gift_title_first')[0];
                    changeGiftnamev1.text(jQuery(this).val());
                    stage.draw();
                    var json = stage.toJSON();
                    jQuery("#json_template").attr('value', json);
                    jQuery('#check_temp_custom').attr('value', "1");
                }
            });

            $('#edit_name_v2_temp').on('change keyup', function() {
                if (typeof stage.find('#gift_title_last')[0] !== 'undefined') {
                    var changeGiftnamev2 = stage.find('#gift_title_last')[0];
                    changeGiftnamev2.text(jQuery(this).val());
                    stage.draw();
                    var json = stage.toJSON();
                    jQuery("#json_template").attr('value', json);
                    jQuery('#check_temp_custom').attr('value', "1");
                }
            });
            /// change form

            // validation form
            jQuery('#modal_edit_template #edit_desc_temp').on('keydown', function(e) {
                newLines = jQuery(this).val().split("\n").length;
                jQuery('#modal_edit_template .group_input_edit_temp .maxchar').html("Total Characters:: " + (this.value.length));
                if ((e.keyCode == 13 && newLines >= 3) || (e.keyCode != 8 && this.value.length > 250)) {
                    return false;
                }
            });
            jQuery('#modal_edit_template #edit_name_v1_temp').on('keydown', function(e) {
                newLines = jQuery(this).val().split("\n").length;
                jQuery('#modal_edit_template .group_input_edit_temp .maxchar_name_v1').html("Total Characters:: " + (this.value.length));
                if ((e.keyCode == 13 && newLines >= 3) || (e.keyCode != 8 && this.value.length > 20)) {
                    return false;
                }
            });
            jQuery('#modal_edit_template #edit_name_v2_temp').on('keydown', function(e) {
                newLines = jQuery(this).val().split("\n").length;
                jQuery('#modal_edit_template .group_input_edit_temp .maxchar_name_v2').html("Total Characters:: " + (this.value.length));
                if ((e.keyCode == 13 && newLines >= 3) || (e.keyCode != 8 && this.value.length > 20)) {
                    return false;
                }
            });
            jQuery('#modal_edit_template #edit_giftform_temp').on('keydown', function(e) {
                if (e.keyCode != 8 && this.value.length > 20 && e.keyCode != 46) {
                    return false;
                }
            });
            jQuery('#modal_edit_template #edit_giftto_temp').on('keydown', function(e) {
                if (e.keyCode != 8 && this.value.length > 20 && e.keyCode != 46) {
                    return false;
                }
            });
            jQuery('#modal_edit_template #edit_price_temp').on('keydown', function(e) {
                if (e.keyCode != 8 && this.value.length > 5 && e.keyCode != 46 && parseFloat(this.value) != 0) {
                    return false;
                }
            });
            // validation form

            // check input undefined
            if (typeof stage.find('#giftcard_counpon')[0] !== 'undefined') {
                var giftcard_counpon_label = stage.find('#giftcard_counpon')[0];
                giftcard_counpon_label.text(counpon);
                stage.draw();
            }
            if (typeof stage.find('#giftcard_date_gift_input')[0] !== 'undefined') {
                var stage_date = stage.find('#giftcard_date_gift_input')[0];
                stage_date.text(newDate);
                stage.draw();
            }
            var get_input_giftprice = jQuery('#edit_price_temp').val();
            if (get_input_giftprice.length > 0) {
                if (typeof stage.find('#giftcard_monney')[0] !== 'undefined') {
                    var check_giftprice = stage.find('#giftcard_monney')[0];
                    check_giftprice.text("$" + get_input_giftprice);
                    stage.draw();
                }
            }
            var get_input_giftto = jQuery('#edit_giftto_temp').val();
            if (get_input_giftto.length > 0) {
                if (typeof stage.find('#giftto_input')[0] !== 'undefined') {
                    var check_giftto = stage.find('#giftto_input')[0];
                    check_giftto.text(get_input_giftto);
                    stage.draw();
                }
            }
            var get_input_giftfrom = jQuery('#edit_giftform_temp').val();
            if (get_input_giftfrom.length > 0) {
                if (typeof stage.find('#giftfrom_input')[0] !== 'undefined') {
                    var check_giftfrom = stage.find('#giftfrom_input')[0];
                    check_giftfrom.text(get_input_giftfrom);
                    stage.draw();
                }
            }
            var get_input_giftdes = jQuery('#edit_desc_temp').val();
            if (get_input_giftdes.length > 0) {
                if (typeof stage.find('#giftcard_des')[0] !== 'undefined') {
                    var check_giftdes = stage.find('#giftcard_des')[0];
                    check_giftdes.text(get_input_giftdes);
                    stage.draw();
                }
            }
            if (typeof stage.find('#giftcard_email')[0] !== 'undefined') {
                var stageEmail = stage.find('#giftcard_email')[0];
                stageEmail.text(jQuery("#get_name_email").val());
                stage.draw();
            }

            if (typeof stage.find('#giftcard_website')[0] !== 'undefined') {
                var stageWebsite = stage.find('#giftcard_website')[0];
                stageWebsite.text(jQuery("#get_name_site").val());
                stage.draw();
            }
            if (typeof stage.find('#gift_title_company')[0] !== 'undefined') {
                var stage_title_company = stage.find('#gift_title_company')[0];
                stage_title_company.text(jQuery("#get_company_name").val());
                stage.draw();
            }
            // check input undefine
            var dataURL = stage.toDataURL({
                pixelRatio: 1
            });
            addImagesGiftCard(dataURL);
        }
    }

    // Add image loading + Before event ajax change value get_chosse_temp = lanscape or portail
    function getDataChosse() {
        jQuery("#wpgv_trigger_template").css("pointer-events", "none");
        jQuery(".wpgv_select_style_temp:checked").closest("label").addClass("wpgv_loading_temp");
        if (typeof jQuery(".wpgv_select_style_temp:checked").attr("data-chosse") != "undefined") {
            var data_chosse = jQuery(".wpgv_select_style_temp:checked").attr("data-chosse");
            jQuery("#get_chosse_temp").attr("value", data_chosse);
        } else {
            var chosse_template = jQuery("#chosse_template:checked").attr("value");
            jQuery("#get_chosse_temp").attr("value", chosse_template);
            var get_value_data_checked = jQuery("input[name=chosse_template]:checked").attr('data-checked');
            if (get_value_data_checked == "1") {
                var get_json_data = jQuery("#get_json_db").val();
                jQuery("#json_template").attr('value', get_json_data);

            }
        }
    }
    // Add image loading + Before event ajax change value get_chosse_temp = lanscape or portail

    // function get canvas

    if (get_action == "edit") {
        var get_src_image = jQuery("#wpgv_trigger_template").attr("data-tem-style");
        if (get_src_image != 0) {
            jQuery(".wpgv-template-box #wpgv_src_result").closest(".wpgv_line_tr").css("display", "table-row");
        }

        // check post created
        var selectedRadio = jQuery("[name='select_template']:checked").val();
        if (typeof selectedRadio === "undefined") {
            // check select template undefined
            jQuery("#chosse_template").closest("tr").hide();
            jQuery("#template-style").closest("tr").hide();
            var get_tem = jQuery(".wpgv_select_style_temp:checked").attr("id");
            if (typeof get_tem != "undefined") {
                jQuery("#select_template").prop("checked", true);
                jQuery("#template-style").closest("tr").show();
                var getlogocompany = jQuery("#get_option_url").val();
                jQuery(document).on("click", ".wpgv_select_style_temp", function() {
                    jQuery('body').addClass('wpgv-overflow-hidden');
                    jQuery('#id_bg_template').attr('value', '');
                    jQuery(".wpgv_select_style_temp").each(function() {
                        if (!jQuery(this).is(':checked')) {
                            jQuery(this).prop('disabled', true);
                            jQuery(this).closest("label").find(".voucher-content-step").addClass("wpgv_opacity_temp");
                        }
                    });
                    var wpgv_admin_url = jQuery("#wpgv_admin_url").val();
                    var file_name = jQuery(".wpgv_select_style_temp:checked").val();
                    $.ajax({
                        type: "post",
                        dataType: "html",
                        url: wpgv_admin_url,
                        data: {
                            action: "wpgv_curl_file_server",
                            file_name: file_name,
                        },
                        beforeSend: function(results) {
                            getDataChosse()
                        },
                        success: function() {
                            jQuery("#wpgv_load_temp label").removeClass("wpgv_loading_temp");
                            jQuery("#wpgv_wrap_custom_template label").removeClass("wpgv_loading_temp");
                            jQuery('#modal_edit_template').fadeIn(200);
                            var getId = jQuery(".wpgv_select_style_temp:checked").attr("id");
                            get_link_temp = jQuery("#wpgv_get_svg").val();
                            if (getId === "template-style") {
                                jQuery("#get_template_style").attr("value", file_name);
                                var getValue_temp_style = jQuery(".wpgv_select_style_temp:checked").val().replace(".png", "");
                                jsontemplate = "https://gift-card-pro.s3.eu-central-1.amazonaws.com/templates/version-1.2/json/" + getValue_temp_style + ".json";
                                pngtemplate = get_link_temp + getValue_temp_style + ".svg";
                                jQuery('#check_temp_custom').attr('value', 0);
                                var get_link_bg_s3 = jQuery("#template-style:checked").val();
                                if (typeof get_link_bg_s3 != "undefined") {
                                    var get_name_tem = get_link_temp + get_link_bg_s3.replace(".png", "") + ".svg";
                                    jQuery("#id_bg_template").attr("value", get_name_tem);
                                }
                            } else if (getId === "chosse_template") {
                                var getValue_chosse_temp = jQuery(".wpgv_select_style_temp:checked").val();
                                if (getValue_chosse_temp == "lanscape") {
                                    jQuery("#get_template_style").attr("value", "template-custom-lanscape.png");
                                    jsontemplate = get_link_temp + "template-custom-lanscape.json";
                                    pngtemplate = "";
                                } else if (getValue_chosse_temp == "portail") {
                                    jQuery("#get_template_style").attr("value", "template-custom-portail.png");
                                    jsontemplate = get_link_temp + "template-custom-portail.json";
                                    pngtemplate = "";
                                }
                            }
                            getJsonTemplate(jsontemplate, pngtemplate, getlogocompany);
                            setTimeout(function() {
                                document.getElementById("wpgv_draw_stage").click();
                            }, 1000)

                        }
                    });
                })
            } else {
                var getlogocompany = jQuery("#get_option_url").val();
                jQuery(document).on("click", ".wpgv_select_style_temp", function() {
                    jQuery('body').addClass('wpgv-overflow-hidden');
                    jQuery('#id_bg_template').attr('value', '');
                    jQuery(".wpgv_select_style_temp").each(function() {

                        if (!jQuery(this).is(':checked')) {
                            jQuery(this).prop('disabled', true);
                            jQuery(this).closest("label").find(".voucher-content-step").addClass("wpgv_opacity_temp");
                        }
                    });
                    var wpgv_admin_url = jQuery("#wpgv_admin_url").val();
                    var file_name = jQuery(".wpgv_select_style_temp:checked").val();
                    $.ajax({
                        type: "post",
                        dataType: "html",
                        url: wpgv_admin_url,
                        data: {
                            action: "wpgv_curl_file_server",
                            file_name: file_name,
                        },
                        beforeSend: function(results) {
                            getDataChosse()
                        },
                        success: function() {
                            jQuery("#wpgv_load_temp label").removeClass("wpgv_loading_temp");
                            jQuery("#wpgv_wrap_custom_template label").removeClass("wpgv_loading_temp");
                            jQuery('#modal_edit_template').fadeIn(200);
                            var getId = jQuery(".wpgv_select_style_temp:checked").attr("id");
                            get_link_temp = jQuery("#wpgv_get_svg").val();
                            if (getId === "template-style") {
                                jQuery("#get_template_style").attr("value", file_name);
                                var getValue_temp_style = jQuery(".wpgv_select_style_temp:checked").val().replace(".png", "");

                                jsontemplate = "https://gift-card-pro.s3.eu-central-1.amazonaws.com/templates/version-1.2/json/" + getValue_temp_style + ".json";
                                pngtemplate = get_link_temp + getValue_temp_style + ".svg";
                                jQuery('#check_temp_custom').attr('value', 0);
                                var get_link_bg_s3 = jQuery("#template-style:checked").val();
                                if (typeof get_link_bg_s3 != "undefined") {
                                    var get_name_tem = get_link_temp + get_link_bg_s3.replace(".png", "") + ".svg";
                                    jQuery("#id_bg_template").attr("value", get_name_tem);
                                }
                            } else if (getId === "chosse_template") {
                                var getValue_chosse_temp = jQuery(".wpgv_select_style_temp:checked").val();
                                if (getValue_chosse_temp == "lanscape") {
                                    jQuery("#get_template_style").attr("value", "template-custom-lanscape.png");
                                    jsontemplate = get_link_temp + "template-custom-lanscape.json";
                                    pngtemplate = "";
                                } else if (getValue_chosse_temp == "portail") {
                                    jQuery("#get_template_style").attr("value", "template-custom-portail.png");
                                    jsontemplate = get_link_temp + "template-custom-portail.json";
                                    pngtemplate = "";
                                }
                            }
                            getJsonTemplate(jsontemplate, pngtemplate, getlogocompany);
                            setTimeout(function() {
                                document.getElementById("wpgv_draw_stage").click();
                            }, 1000)

                        }
                    });
                })
            }
        } else {
            var getlogocompany = jQuery("#get_option_url").val();
            var checkInputChecked = jQuery(".wpgv_select_style_temp:checked").attr("id");
            if (selectedRadio == "default") {

                // checked select template default
                jQuery("#template-style").closest("tr").show();
                jQuery(document).on("click", ".wpgv_select_style_temp", function() {
                    jQuery('body').addClass('wpgv-overflow-hidden');
                    jQuery(".wpgv_select_style_temp").each(function() {
                        jQuery(this).removeAttr('checked');
                        if (!jQuery(this).is(':checked')) {
                            jQuery(this).prop('disabled', true);
                            jQuery(this).closest("label").find(".voucher-content-step").addClass("wpgv_opacity_temp");
                        }
                    });
                    jQuery(this).prop('checked', true);
                    var wpgv_admin_url = jQuery("#wpgv_admin_url").val();
                    var file_name = jQuery(".wpgv_select_style_temp:checked").val();
                    $.ajax({
                        type: "post",
                        dataType: "html",
                        url: wpgv_admin_url,
                        data: {
                            action: "wpgv_curl_file_server",
                            file_name: file_name,
                        },
                        beforeSend: function(results) {
                            getDataChosse()
                        },
                        success: function() {
                            jQuery("#wpgv_load_temp label").removeClass("wpgv_loading_temp");
                            jQuery("#wpgv_wrap_custom_template label").removeClass("wpgv_loading_temp");
                            jQuery('#modal_edit_template').fadeIn(200);
                            var getId = jQuery(".wpgv_select_style_temp:checked").attr("id");
                            var get_link_temp = jQuery("#wpgv_get_svg").val();
                            if (getId === "template-style") {
                                jQuery("#get_template_style").attr("value", file_name);
                                var getValue_temp_style = jQuery(".wpgv_select_style_temp:checked").val().replace(".png", "");
                                jsontemplate = "https://gift-card-pro.s3.eu-central-1.amazonaws.com/templates/version-1.2/json/" + getValue_temp_style + ".json";
                                pngtemplate = get_link_temp + getValue_temp_style + ".svg";
                                jQuery('#check_temp_custom').attr('value', 0);
                                var data_checked_temp = jQuery(this).attr("data-checked-temp");
                                if (data_checked_temp == 0) {
                                    jQuery("#id_bg_template").attr("value", "");
                                }
                                var get_link_bg_s3 = jQuery("#template-style:checked").val();
                                if (typeof get_link_bg_s3 != "undefined") {
                                    var get_name_tem = get_link_temp + get_link_bg_s3.replace(".png", "") + ".svg";
                                    jQuery("#id_bg_template").attr("value", get_name_tem);
                                }
                            } else if (getId === "chosse_template") {
                                var getValue_chosse_temp = jQuery(".wpgv_select_style_temp:checked").val();
                                var get_json_input = jQuery("#json_template").val();
                                if (get_json_input != "") {
                                    var get_json_parse = JSON.parse(get_json_input);
                                    var get_bg_input = jQuery("#wpgv_src_json").attr('src');
                                    if (getValue_chosse_temp == "lanscape") {
                                        jQuery("#get_template_style").attr("value", "template-custom-lanscape.png");
                                        var get_data_checked = jQuery(".chosse_template:checked").attr('data-checked');
                                        if (get_data_checked == "0") {
                                            jsontemplate = get_link_temp + "template-custom-lanscape.json";
                                            pngtemplate = "";
                                        } else {
                                            jsontemplate = get_json_parse;
                                            pngtemplate = get_bg_input;
                                        }
                                    } else if (getValue_chosse_temp == "portail") {
                                        jQuery("#get_template_style").attr("value", "template-custom-portail.png");
                                        var get_data_checked = jQuery(".chosse_template:checked").attr('data-checked');
                                        if (get_data_checked == "1") {
                                            jsontemplate = get_json_parse;
                                            pngtemplate = get_bg_input;
                                        } else {
                                            jsontemplate = get_link_temp + "template-custom-portail.json";
                                            pngtemplate = "";
                                        }
                                    }
                                } else {
                                    if (getValue_chosse_temp == "lanscape") {
                                        jQuery("#get_template_style").attr("value", "template-custom-lanscape.png");
                                        jsontemplate = get_link_temp + "template-custom-lanscape.json";
                                        pngtemplate = "";
                                    } else if (getValue_chosse_temp == "portail") {
                                        jQuery("#get_template_style").attr("value", "template-custom-portail.png");
                                        jsontemplate = get_link_temp + "template-custom-portail.json";
                                        pngtemplate = "";
                                    }
                                }
                            }
                            getJsonTemplate(jsontemplate, pngtemplate, getlogocompany);
                            setTimeout(function() {
                                document.getElementById("wpgv_draw_stage").click();
                            }, 1000)
                        }
                    });
                })

            } else if (selectedRadio == "custom") {
                // checked select template custom

                var checkInputChecked = jQuery(".wpgv_select_style_temp:checked").attr("id");
                jQuery("#chosse_template").closest("tr").show();
                if (typeof checkInputChecked === "undefined") {
                    jQuery(document).on("click", ".wpgv_select_style_temp", function() {
                        jQuery('body').addClass('wpgv-overflow-hidden');
                        jQuery(".wpgv_select_style_temp").each(function() {
                            if (!jQuery(this).is(':checked')) {
                                jQuery(this).prop('disabled', true);
                                jQuery(this).closest("label").find(".voucher-content-step").addClass("wpgv_opacity_temp");
                            }
                        });
                        var wpgv_admin_url = jQuery("#wpgv_admin_url").val();
                        var file_name = jQuery(".wpgv_select_style_temp:checked").val();
                        $.ajax({
                            type: "post",
                            dataType: "html",
                            url: wpgv_admin_url,
                            data: {
                                action: "wpgv_curl_file_server",
                                file_name: file_name,
                            },
                            beforeSend: function(results) {
                                getDataChosse()
                            },
                            success: function() {
                                jQuery("#wpgv_load_temp label").removeClass("wpgv_loading_temp");
                                jQuery("#wpgv_wrap_custom_template label").removeClass("wpgv_loading_temp");
                                jQuery('#modal_edit_template').fadeIn(200);
                                var getId = jQuery(".wpgv_select_style_temp:checked").attr("id");
                                var get_link_temp = jQuery("#wpgv_get_svg").val();
                                if (getId === "template-style") {
                                    jQuery("#get_template_style").attr("value", file_name);
                                    var getValue_temp_style = jQuery(".wpgv_select_style_temp:checked").val().replace(".png", "");

                                    jsontemplate = "https://gift-card-pro.s3.eu-central-1.amazonaws.com/templates/version-1.2/json/" + getValue_temp_style + ".json";
                                    pngtemplate = get_link_temp + getValue_temp_style + ".svg";
                                    jQuery('#check_temp_custom').attr('value', 0);
                                } else if (getId === "chosse_template") {
                                    var getValue_chosse_temp = jQuery(".chosse_template:checked").val();
                                    var get_json_input = jQuery("#json_template").val();

                                    var get_bg_input = jQuery("#wpgv_src_json").attr('src');

                                    if (getValue_chosse_temp === "lanscape") {
                                        jQuery("#get_template_style").attr("value", "template-custom-lanscape.png");
                                        var get_data_checked = jQuery(".chosse_template:checked").attr('data-checked');
                                        if (get_data_checked == "0") {
                                            jsontemplate = get_link_temp + "template-custom-lanscape.json";
                                            pngtemplate = "";
                                        } else {
                                            if (get_json_input != "") {
                                                var get_json_parse = JSON.parse(get_json_input);
                                                jsontemplate = get_json_parse;
                                                pngtemplate = get_bg_input;
                                            } else {
                                                jsontemplate = get_link_temp + "template-custom-lanscape.json";
                                                pngtemplate = "";
                                            }
                                        }
                                    } else if (getValue_chosse_temp === "portail") {
                                        jQuery("#get_template_style").attr("value", "template-custom-portail.png");
                                        var get_data_checked = jQuery(".chosse_template:checked").attr('data-checked');
                                        if (get_data_checked == "1") {
                                            if (get_json_input != "") {
                                                var get_json_parse = JSON.parse(get_json_input);
                                                jsontemplate = get_json_parse;
                                                pngtemplate = get_bg_input;
                                            } else {
                                                jsontemplate = get_link_temp + "template-custom-portail.json";
                                                pngtemplate = "";
                                            }
                                        } else {
                                            jsontemplate = get_link_temp + "template-custom-portail.json";
                                            pngtemplate = "";
                                        }
                                    }
                                }
                                getJsonTemplate(jsontemplate, pngtemplate, getlogocompany);
                                setTimeout(function() {
                                    document.getElementById("wpgv_draw_stage").click();
                                }, 1000)

                            }
                        });
                    })
                } else {
                    jQuery(document).on("click", ".wpgv_select_style_temp", function() {
                        jQuery('body').addClass('wpgv-overflow-hidden');
                        jQuery(".wpgv_select_style_temp").each(function() {
                            if (!jQuery(this).is(':checked')) {
                                jQuery(this).prop('disabled', true);
                                jQuery(this).closest("label").find(".voucher-content-step").addClass("wpgv_opacity_temp");
                            }
                        });
                        var wpgv_admin_url = jQuery("#wpgv_admin_url").val();
                        var file_name = jQuery(".wpgv_select_style_temp:checked").val();

                        $.ajax({
                            type: "post",
                            dataType: "html",
                            url: wpgv_admin_url,
                            data: {
                                action: "wpgv_curl_file_server",
                                file_name: file_name,
                            },
                            beforeSend: function(results) {
                                getDataChosse()
                            },
                            success: function() {

                                jQuery('#modal_edit_template').fadeIn(200);
                                jQuery("#wpgv_load_temp label").removeClass("wpgv_loading_temp");
                                jQuery("#wpgv_wrap_custom_template label").removeClass("wpgv_loading_temp");
                                var getId = jQuery(".wpgv_select_style_temp:checked").attr("id");
                                var get_link_temp = jQuery("#wpgv_get_svg").val();
                                if (getId === "template-style") {
                                    jQuery("#get_template_style").attr("value", file_name);
                                    jQuery('#id_bg_template').attr('value', 0);
                                    var getValue_temp_style = jQuery(".wpgv_select_style_temp:checked").val().replace(".png", "");

                                    jsontemplate = "https://gift-card-pro.s3.eu-central-1.amazonaws.com/templates/version-1.2/json/" + getValue_temp_style + ".json";
                                    pngtemplate = get_link_temp + getValue_temp_style + ".svg";
                                    jQuery('#check_temp_custom').attr('value', 0);
                                    var get_link_bg_s3 = jQuery("#template-style:checked").val();
                                    if (typeof get_link_bg_s3 != "undefined") {
                                        var get_name_tem = get_link_temp + get_link_bg_s3.replace(".png", "") + ".svg";
                                        jQuery("#id_bg_template").attr("value", get_name_tem);

                                    }
                                } else if (getId === "chosse_template") {
                                    var getValue_chosse_temp = jQuery(".wpgv_select_style_temp:checked").val();
                                    var get_json_input = jQuery("#json_template").val();
                                    var get_json_parse = JSON.parse(get_json_input);
                                    var get_bg_input = jQuery("#wpgv_src_json").attr('src');
                                    if (getValue_chosse_temp == "lanscape") {
                                        jQuery("#get_template_style").attr("value", "template-custom-lanscape.png");
                                        var get_data_checked = jQuery("input[name=chosse_template]:checked").attr('data-checked');
                                        var get_url_bg = jQuery("#id_bg_template").attr("data-id-bg");
                                        if (get_data_checked == "1") {

                                            var data_id_bg = jQuery('#id_bg_template').attr('data-id-bg');

                                            if (data_id_bg != "") {
                                                jQuery('#id_bg_template').attr('value', data_id_bg);
                                                var data_id_result = jQuery('#bg_result').attr('data-id-result');
                                                jQuery('#bg_result').attr('value', data_id_result);
                                                jsontemplate = get_json_parse;
                                                pngtemplate = get_url_bg;
                                            } else {

                                                var getValue_chosse_template = jQuery('#chosse_template').attr('data-bg-temp').replace(".png", "");
                                                var get_link_temp = jQuery("#wpgv_get_svg").val();
                                                jsontemplate = get_json_parse;
                                                pngtemplate = get_link_temp + getValue_chosse_template + ".svg";

                                            }
                                        } else {

                                            jQuery('#id_bg_template').attr('value', 0);
                                            jQuery('#bg_result').attr('value', 0);
                                            jsontemplate = get_link_temp + "template-custom-lanscape.json";
                                            pngtemplate = "";
                                        }
                                    } else if (getValue_chosse_temp == "portail") {
                                        jQuery("#get_template_style").attr("value", "template-custom-portail.png");
                                        var get_data_checked = jQuery("input[name=chosse_template]:checked").attr('data-checked');
                                        var get_url_bg = jQuery("#id_bg_template").attr("data-id-bg");
                                        if (get_data_checked == "1") {
                                            var data_id_bg = jQuery('#id_bg_template').attr('data-id-bg');
                                            if (data_id_bg != "") {
                                                jQuery('#id_bg_template').attr('value', data_id_bg);
                                                var data_id_result = jQuery('#bg_result').attr('data-id-result');
                                                jQuery('#bg_result').attr('value', data_id_result);
                                                jsontemplate = get_json_parse;
                                                pngtemplate = get_url_bg;
                                            } else {
                                                var getValue_chosse_template = jQuery('#chosse_template').attr('data-bg-temp').replace(".png", "");
                                                var get_link_temp = jQuery("#wpgv_get_svg").val();
                                                jsontemplate = get_json_parse;
                                                pngtemplate = get_link_temp + getValue_chosse_template + ".svg";
                                            }
                                        } else {
                                            jQuery('#id_bg_template').attr('value', 0);
                                            jQuery('#bg_result').attr('value', 0);
                                            jsontemplate = get_link_temp + "template-custom-portail.json";
                                            pngtemplate = "";
                                        }
                                    }
                                }
                                getJsonTemplate(jsontemplate, pngtemplate, getlogocompany);
                                setTimeout(function() {
                                    document.getElementById("wpgv_draw_stage").click();
                                }, 1000)

                            }
                        });
                    })
                }
            }
        }
    } else {
        // check post not created
        var getlogocompany = jQuery("#get_option_url").val();
        jQuery(document).on("click", ".wpgv_select_style_temp", function() {

            jQuery('body').addClass('wpgv-overflow-hidden');
            jQuery('#id_bg_template').attr('value', '');
            jQuery(".wpgv_select_style_temp").each(function() {
                if (!jQuery(this).is(':checked')) {
                    jQuery(this).prop('disabled', true);
                    jQuery(this).closest("label").find(".voucher-content-step").addClass("wpgv_opacity_temp");
                }
            });
            var wpgv_admin_url = jQuery("#wpgv_admin_url").val();
            var file_name = jQuery(".wpgv_select_style_temp:checked").val();
            $.ajax({
                type: "post",
                dataType: "html",
                url: wpgv_admin_url,
                data: {
                    action: "wpgv_curl_file_server",
                    file_name: file_name,
                },
                beforeSend: function(results) {

                    getDataChosse()
                },
                success: function() {

                    jQuery("#wpgv_load_temp label").removeClass("wpgv_loading_temp");
                    jQuery("#wpgv_wrap_custom_template label").removeClass("wpgv_loading_temp");
                    jQuery('#modal_edit_template').fadeIn(200);
                    var getId = jQuery(".wpgv_select_style_temp:checked").attr("id");
                    get_link_temp = jQuery("#wpgv_get_svg").val();
                    if (getId === "template-style") {
                        jQuery("#get_template_style").attr("value", file_name);
                        var getValue_temp_style = jQuery(".wpgv_select_style_temp:checked").val().replace(".png", "");

                        jsontemplate = "https://gift-card-pro.s3.eu-central-1.amazonaws.com/templates/version-1.2/json/" + getValue_temp_style + ".json";
                        pngtemplate = get_link_temp + getValue_temp_style + ".svg";
                        jQuery('#check_temp_custom').attr('value', 0);
                        var get_link_bg_s3 = jQuery("#template-style:checked").val();
                        if (typeof get_link_bg_s3 != "undefined") {
                            var get_name_tem = get_link_temp + get_link_bg_s3.replace(".png", "") + ".svg";
                            jQuery("#id_bg_template").attr("value", get_name_tem);
                        }
                    } else if (getId === "chosse_template") {
                        var getValue_chosse_temp = jQuery(".wpgv_select_style_temp:checked").val();
                        if (getValue_chosse_temp == "lanscape") {
                            jQuery("#get_template_style").attr("value", "template-custom-lanscape.png");
                            jsontemplate = get_link_temp + "template-custom-lanscape.json";
                            pngtemplate = "";
                        } else if (getValue_chosse_temp == "portail") {
                            jQuery("#get_template_style").attr("value", "template-custom-portail.png");
                            jsontemplate = get_link_temp + "template-custom-portail.json";
                            pngtemplate = "";
                        }
                    }
                    getJsonTemplate(jsontemplate, pngtemplate, getlogocompany);
                    setTimeout(function() {
                        document.getElementById("wpgv_draw_stage").click();
                    }, 1000)

                }
            });
        })

    }

    // change backgound template
    jQuery('#upload_bg_template').click(function(event_upload) {
        event_upload.preventDefault();
        var bgTemplate;
        if (bgTemplate) {
            bgTemplate.open();
            return;
        }
        var bgTemplate = wp.media({
                title: 'Add Background Template',
                button: {
                    text: 'Upload Background Template'
                },
                multiple: false // Set this to true to allow multiple files to be selected
            })
            .on('select', function() {
                var attachmentBackground = bgTemplate.state().get('selection').first().toJSON();
                var urlBackground = attachmentBackground.url;
                var imageObj = new Image();
                imageObj.onload = function() {
                    stage.find('#giftcard_bg')[0].image(imageObj);
                    stage.draw();
                };
                imageObj.src = urlBackground;
                var json = stage.toJSON();
                jQuery("#json_template").attr('value', json);
                jQuery('#id_bg_template').attr('value', attachmentBackground.id);
                jQuery('#check_temp_custom').attr('value', "1");
            })
            .open();
    });

    // click select template
    function status_checked() {
        jQuery(".wpgv-template-box .wpgv_select_style_temp").each(function() {
            if ($(this).is(":checked")) {
                $(this).removeAttr('checked');
            }
            if ($(this).data("checked") == 1) {
                $(this).prop("checked", true);
            }
        });
    }
    jQuery(document).on("click", ".select_template", function(event) {
        // jQuery(".select_template").removeAttr("checked");
        // jQuery(this).prop("checked", true);
        var selectedRadio = jQuery("input[type='radio'][name='select_template']:checked").val();

        if (get_action == "edit") {
            if (selectedRadio == "default") {
                jQuery("#template-style").closest("tr").fadeIn(200);
                jQuery("#chosse_template").closest("tr").fadeOut(200);
                status_checked();
                var check_select = jQuery(this).data("select");
                if (check_select == 1) {
                    jQuery(".wpgv-template-box #wpgv_src_result").closest(".wpgv_line_tr").css("display", "table-row");
                    var get_id_result = jQuery("#bg_result").data("id-result");
                    var get_template_style = jQuery("#get_template_style").data("template-style");
                    var data_id_bg = jQuery('#id_bg_template').data('id-bg');
                    var data_chosse_temp = jQuery('#get_chosse_temp').data('chosse-temp');
                    var data_status_temp = jQuery('#check_temp_custom').data('status-temp');

                    jQuery("#bg_result").attr("value", get_id_result);
                    jQuery("#get_template_style").attr("value", get_template_style);
                    jQuery('#id_bg_template').attr('value', data_id_bg);
                    jQuery('#get_chosse_temp').attr('value', data_chosse_temp);
                    jQuery('#check_temp_custom').attr('value', data_status_temp);
                } else {
                    jQuery(".wpgv-template-box #wpgv_src_result").closest(".wpgv_line_tr").css("display", "none");
                    jQuery("#bg_result").attr("value", 0);
                    jQuery("#get_template_style").attr("value", 0);
                    jQuery('#id_bg_template').attr('value', 0);
                    jQuery('#get_chosse_temp').attr('value', 0);
                    jQuery('#check_temp_custom').attr('value', 0);
                }
            } else if (selectedRadio == "custom") {
                jQuery("#chosse_template").closest("tr").fadeIn(200);
                jQuery("#template-style").closest("tr").fadeOut(200);
                status_checked();
                var check_select = jQuery(this).data("select");
                if (check_select == 1) {
                    jQuery(".wpgv-template-box #wpgv_src_result").closest(".wpgv_line_tr").css("display", "table-row");
                    var get_id_result = jQuery("#bg_result").data("id-result");
                    var get_template_style = jQuery("#get_template_style").data("template-style");
                    var data_id_bg = jQuery('#id_bg_template').data('id-bg');
                    var data_chosse_temp = jQuery('#get_chosse_temp').data('chosse-temp');
                    var data_status_temp = jQuery('#check_temp_custom').data('status-temp');

                    jQuery("#bg_result").attr("value", get_id_result);
                    jQuery("#get_template_style").attr("value", get_template_style);
                    jQuery('#id_bg_template').attr('value', data_id_bg);
                    jQuery('#get_chosse_temp').attr('value', data_chosse_temp);
                    jQuery('#check_temp_custom').attr('value', data_status_temp);
                } else {
                    jQuery(".wpgv-template-box #wpgv_src_result").closest(".wpgv_line_tr").css("display", "none");
                    jQuery("#bg_result").attr("value", 0);
                    jQuery("#get_template_style").attr("value", 0);
                    jQuery('#id_bg_template').attr('value', 0);
                    jQuery('#get_chosse_temp').attr('value', 0);
                    jQuery('#check_temp_custom').attr('value', 0);
                }
            }
        } else {
            if (selectedRadio == "default") {
                jQuery("#template-style").closest("tr").fadeIn(200);
                jQuery("#chosse_template").closest("tr").fadeOut(200);
            } else if (selectedRadio == "custom") {
                jQuery("#chosse_template").closest("tr").fadeIn(200);
                jQuery("#template-style").closest("tr").fadeOut(200);
            }
        }

    });

    // click hide modal
    jQuery('.close_popup').click(function() {
        jQuery("#wpgv_trigger_template").css("pointer-events", "auto");
        jQuery("#wpgv_trigger_template").removeClass("wpgv_loading_temp");
        jQuery(".wpgv_select_style_temp:checked").closest("label").removeClass("disable_loding_temp");
        var get_json_db = jQuery('#get_json_db').val();
        jQuery("#json_template").attr("data-json-template", get_json_db);
        jQuery("#json_template").attr("value", get_json_db);

        jQuery(".wp-color-result").attr("style", "");
        jQuery('body').removeClass('wpgv-overflow-hidden');
        jQuery(".wpgv_select_style_temp").each(function() {
            $(this).prop('disabled', false);
            jQuery(this).closest("label").find(".voucher-content-step").removeClass("wpgv_opacity_temp");
        });
        jQuery('.modal_edit_template').fadeOut(300);

        if (get_action == "edit") {
            let data_select = $('[name="select_template"]:checked').attr('data-select');
            if (data_select == 1) {
                jQuery(".wpgv-template-box .wpgv_select_style_temp").each(function() {
                    jQuery(this).prop("checked", false);
                    var data_checked = jQuery(this).attr("data-checked");
                    if (data_checked == 1) {
                        jQuery(this).prop("checked", true);
                    }
                    var get_id_result = jQuery("#bg_result").data("id-result");
                    var get_template_style = jQuery("#get_template_style").attr("data-template-style");
                    var data_id_bg = jQuery('#id_bg_template').data('id-bg');
                    var data_chosse_temp = jQuery('#get_chosse_temp').data('chosse-temp');
                    var data_status_temp = jQuery('#check_temp_custom').data('status-temp');

                    jQuery("#bg_result").attr("value", get_id_result);
                    jQuery("#get_template_style").attr("value", get_template_style);
                    jQuery('#id_bg_template').attr('value', data_id_bg);
                    jQuery('#get_chosse_temp').attr('value', data_chosse_temp);
                    jQuery('#check_temp_custom').attr('value', data_status_temp);
                })
            } else {
                jQuery("#bg_result").attr("value", 0);
                jQuery("#get_template_style").attr("value", 0);
                jQuery('#id_bg_template').attr('value', 0);
                jQuery('#get_chosse_temp').attr('value', 0);
                jQuery('#check_temp_custom').attr('value', 0);
            }
        } else {
            var json = stage.toJSON();
            jQuery('#json_template').attr('value', json);
            jQuery('#get_chosse_temp').attr('value', 0);
            jQuery(".wpgv-template-box .wpgv_select_style_temp").each(function() {
                $(this).removeAttr('checked');
            });
        }

    });

    // uncheck radio button template chosse_template
    jQuery('.template-style').click(function() {
        var selectedChosse = jQuery("input[type='radio'][name='chosse_template']:checked");
        if (selectedChosse.length > 0) {
            selectedChosse.prop('checked', false);
        }
    });

    // uncheck radio button template dafault
    jQuery('.chosse_template').click(function() {
        var selectedChosse = jQuery("input[type='radio'][name='template-style']:checked");
        if (selectedChosse.length > 0) {
            selectedChosse.prop('checked', false);
        }
    });

    jQuery('#wpgv_tripger_all').on('click', function(event) {
        jQuery(window).off('beforeunload');
        event.preventDefault();
        $('body').trigger('click');

        // get base image
        jQuery("#modal_edit_template .dialog_template").addClass("wpgv_loading_modal");
        jQuery(".wpgv_select_style_temp").each(function() {
            $(this).prop('disabled', false);
        });
        jQuery("#wpgv_appendImage").trigger("click");
        var wpgv_admin_url = jQuery("#wpgv_admin_url").val();
        var base64_img = jQuery("#show-preview-gift-card").attr("href");
        var get_id_bg_result = jQuery("#bg_result").val();
        var data_id_result = jQuery("#bg_result").attr("data-id-result");
        var get_id_template_style = jQuery("input[name=template-style]:checked").attr("id");
        if (get_id_template_style == "template-style") {
            var getValue_temp_style = jQuery(".wpgv_select_style_temp:checked").val().replace("png", "svg");
            var wpgv_get_svg = jQuery("#wpgv_get_svg").val();
            jsontemplate = wpgv_get_svg + getValue_temp_style;
        }
        $.ajax({
            type: "post",
            dataType: "html",
            url: wpgv_admin_url,
            data: {
                action: "wpgv_save_image",
                base64_img: base64_img,
                title: counpon,
                get_id_bg_result: get_id_bg_result,
                data_id_result: data_id_result,
            },
            success: function(response) {
                jQuery("#bg_result").val(response);
                $(".post-type-voucher_template #post").submit();
            },
            error: function() {
                console.log('Error');
            }
        });
    });

    function addImagesGiftCard(dataURL) {
        if (jQuery('.wrap_template').find('#show-preview-gift-card').length > 0) {
            jQuery('.wrap_template').find('#show-preview-gift-card').attr('href', dataURL);
        } else {
            jQuery('.wrap_template').append('<a id="show-preview-gift-card" href="' + dataURL + '"></a>');
        }
    }
    jQuery('#wpgv_draw_stage').on('click', function(event) {
        setTimeout(function() {
            stage.draw();
        }, 200)
        setTimeout(function() {
            stage.draw();
        }, 1000)
    });

    jQuery('#wpgv_appendImage').on('click', function(event) {
        event.preventDefault();
        if (stage != null) {
            var dataURL = stage.toDataURL({
                pixelRatio: 1 // or other value you need
            });
            addImagesGiftCard(dataURL);
        }
    });
    // jQuery(".post-type-voucher_template #publish").click(function() {
    //     jQuery("#wpgv_tripger_all").trigger("click");
    //     return false;
    // });
    jQuery("#wpgv_set_default_json").click(function() {
        jQuery("#edit_giftto_temp").prop("value", "");
        jQuery("#edit_giftform_temp").prop("value", "");
        jQuery("#edit_desc_temp").prop("value", "");
        jQuery("#edit_price_temp").prop("value", "");
        jQuery("#color_font_all_text").prop("value", "");
        jQuery("#color_font_voucher_price").prop("value", "");
        jQuery(".wp-color-result").attr("style", "");
        var get_data_json_template = jQuery("#json_template").attr("data-json-template");
        jQuery("#json_template").val(get_data_json_template);
        var getlogocompany = jQuery("#get_option_url").val();
        get_link_temp = jQuery("#wpgv_get_svg").val();
        var pngtemplate = null;
        var getId = jQuery(".wpgv_select_style_temp:checked").attr("id");
        if (getId === "template-style") {
            var getValue_temp_style = jQuery(".wpgv_select_style_temp:checked").val().replace(".png", "");
            pngtemplate = get_link_temp + getValue_temp_style + ".svg";
            jQuery("#check_temp_custom").val(0);
        } else if (getId === "chosse_template") {
            var getValue_chosse_temp = jQuery(".wpgv_select_style_temp:checked").val();
            var getValue_bg_temp = jQuery("#id_bg_template").attr("data-id-bg");
            if (getValue_chosse_temp == "lanscape") {
                pngtemplate = getValue_bg_temp;
            } else if (getValue_chosse_temp == "portail") {
                jsontemplate = get_link_temp + "template-custom-portail.json";
                pngtemplate = getValue_bg_temp;
            }
        }

        var json_par = JSON.parse(get_data_json_template);
        getJsonTemplate(json_par, pngtemplate, getlogocompany);
    });
    jQuery("#wpgv_trigger_template").click(function() {
        jQuery(this).addClass("wpgv_loading_temp");

        var get_active_style = jQuery(this).closest("#wpgv_trigger_template").data("tem-style");
        var get_active_chosse = jQuery(this).closest("#wpgv_trigger_template").data("chosse-tem");
        jQuery(".wpgv_select_style_temp:checked").each(function() {
            jQuery(this).closest("label").addClass("disable_loding_temp");
            get_id_checked = jQuery(this).attr("id");
            if (get_id_checked == "template-style") {
                get_tem_checked = jQuery(this).val();
                if (get_active_style == get_tem_checked) {
                    jQuery(this).prop("checked", true);
                    jQuery(this).trigger("click");
                }
            } else if (get_id_checked == "chosse_template") {
                get_tem_checked = jQuery(this).val();
                if (get_active_chosse == get_tem_checked) {
                    jQuery(this).prop("checked", true);
                    jQuery(this).trigger("click");
                }
            }
        });

        return false;
    });
});