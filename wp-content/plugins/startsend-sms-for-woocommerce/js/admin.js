let caretPosition = 0;
jQuery(function ($) {
    $("#Startsend_setting\\[Startsend_woocommerce_sms_from\\]").focusout(function () {
        var sender_id = $("#Startsend_setting\\[Startsend_woocommerce_sms_from\\]").val().trim();
        if ($.isNumeric(sender_id) && sender_id.length > 20) {
            alert('Message From is too long, max 20 digits for numeric SMS sender.');
        } else if (!$.isNumeric(sender_id) && sender_id.length > 11) {
            alert('Message From is too long, max 11 characters for alphanumeric SMS sender.');
        }
        $("#Startsend_setting\\[Startsend_woocommerce_sms_from\\]").val(sender_id);
    });

    $("#admin_setting\\[Startsend_woocommerce_admin_sms_recipients\\]").focusout(function () {
        var admin_mobile_no = $("#admin_setting\\[Startsend_woocommerce_admin_sms_recipients\\]").val().trim();
        var admin_mobile_no_array = new Array();
        var counter;
        if (admin_mobile_no != '') {
            admin_mobile_no_array = admin_mobile_no.split(",");
            for (counter = 0; counter < admin_mobile_no_array.length; counter++) {
                admin_mobile_no_array[counter] = admin_mobile_no_array[counter].trim();
                if (!$.isNumeric(admin_mobile_no_array[counter])) {
                    alert('Invalid mobile number, must be numeric.');
                    break;
                } 
                // else if (admin_mobile_no_array[counter].substring(0, 1) == '0') {
                //     alert('Mobile number must include country code, e.g. 60123456789, 6545214889.');
                //     break;
                // }
            }
        }
    });

    const setupPhoneHelper = function () {
        let selectedValue = $("#multivendor_setting\\[Startsend_multivendor_selected_plugin\\]").val();
        let phoneFieldLocation = '[edit profile page > StartsendAPI WooCommerce > phone]';
        if (selectedValue === 'dokan') {
            phoneFieldLocation = '[vendor dasboard > Settings > Store > Phone No]';
        } else if (selectedValue === 'wc_marketplace') {
            phoneFieldLocation = '[vendor dashboard > Store Settings > Storefront > Phone]';
        } else if (selectedValue === 'wcfm_marketplace') {
            phoneFieldLocation = '[store manager > Settings > Store > Store Phone';
        }

        let helperText = `<strong>Vendor are required to fill up their phone on <span style="color: #ff0000;">${phoneFieldLocation}</span> in order to receive sms</strong>`;
        $("#multivendor_setting\\[multivendor_helper_desc\\]").html(helperText);
    };

    $("#multivendor_setting\\[Startsend_multivendor_selected_plugin\\]").change(setupPhoneHelper);
    setupPhoneHelper();

    $('#mocean_sms\\[open-keywords\\]').click(function (e) {
        const type = $(e.target).attr('data-attr-type');
        const target = $(e.target).attr('data-attr-target');

        caretPosition = document.getElementById(target).selectionStart;

        let shopKeywords;
        if (type === 'multivendor') {
            shopKeywords = ['shop_name', 'shop_email', 'shop_url', 'vendor_shop_name'];
        } else {
            shopKeywords = ['shop_name', 'shop_email', 'shop_url'];
        }
        const orderKeywords = ['order_id', 'order_currency', 'order_amount', 'order_product_with_qty', 'order_product', 'order_status'];
        let billingKeywords = ['billing_first_name', 'billing_last_name', 'billing_phone', 'billing_email', 'billing_company', 'billing_address', 'billing_country', 'billing_city', 'billing_state', 'billing_postcode', 'payment_method'];

        if ($('#Startsend_new_billing_field') && $('#Startsend_new_billing_field').val() !== '') {
            let newFields = $('#Startsend_new_billing_field').val().split(',');
            for (let i in newFields) {
                billingKeywords.push(newFields[i]);
            }
        }

        const buildTable = function (keywords) {
            const chunkedKeywords = keywords.array_chunk(3);

            let tableCode = '';
            chunkedKeywords.forEach(function (row, rowIndex) {
                if (rowIndex === 0) {
                    tableCode += '<table class="widefat fixed striped"><tbody>';
                }

                tableCode += '<tr>';
                row.forEach(function (col) {
                    tableCode += `<td class="column"><button class="button-link" onclick="Startsend_bind_text_to_field('${target}', '[${col}]')">[${col}]</button></td>`;
                });
                tableCode += '</tr>';

                if (rowIndex === chunkedKeywords.length - 1) {
                    tableCode += '</tbody></table>';
                }
            });

            return tableCode;
        };

        $('#mocean_sms\\[keyword-modal\\]').off();
        $('#mocean_sms\\[keyword-modal\\]').on($.modal.AFTER_CLOSE, function () {
            document.getElementById(target).focus();
            document.getElementById(target).setSelectionRange(caretPosition, caretPosition);
        });

        let mainTable = '';
        mainTable += '<h2>Shop</h2>';
        mainTable += buildTable(shopKeywords);

        mainTable += '<h2>Order</h2>';
        mainTable += buildTable(orderKeywords);

        mainTable += '<h2>Billing</h2>';
        mainTable += buildTable(billingKeywords);

        mainTable += '<div style="margin-top: 10px"><small>*Press on keyword to add to sms template</small></div>';

        $('#mocean_sms\\[keyword-modal\\]').html(mainTable);
        $('#mocean_sms\\[keyword-modal\\]').modal();
    });
});

function Startsend_bind_text_to_field(target, keyword) {
    const startStr = document.getElementById(target).value.substring(0, caretPosition);
    const endStr = document.getElementById(target).value.substring(caretPosition);
    document.getElementById(target).value = startStr + keyword + endStr;
    caretPosition += keyword.length;
}

Object.defineProperty(Array.prototype, 'array_chunk', {
    value: function (chunkSize) {
        const array = this;
        return [].concat.apply([],
            array.map(function (elem, i) {
                return i % chunkSize ? [] : [array.slice(i, i + chunkSize)];
            })
        );
    }
});
