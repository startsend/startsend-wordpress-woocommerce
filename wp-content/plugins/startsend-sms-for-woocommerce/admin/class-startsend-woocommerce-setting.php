<?php

class Startsend_WooCommerce_Setting implements Startsend_Register_Interface
{

    /**
     * Array for storing settings
     * @var WeDevs_Settings_API
     */
    private $settings_api;

    /**
     * For
     * @var StartSend
     */
    private $service;

    /**
     * Array for select right gate for send SMS
     * @var string[]
     */
    protected $countriesDefaultGate = [
        'https://app.startsend.ru/api/v1/' => 'Russia',
        'https://app.sms.by/api/v1/' => 'Belarus',
    ];

    /**
     * Startsend_WooCommerce_Setting constructor.
     */
    function __construct()
    {
        $this->settings_api = new WeDevs_Settings_API;
        $api_token = Startsend_get_options( "Startsend_woocommerce_api_token", 'Startsend_setting', '' );
        $api_gate = Startsend_get_options( "Startsend_woocommerce_api_gate", 'Startsend_setting', "https://app.startsend.ru/api/v1/" );
        if ($api_token && $api_gate) {
            $this->service = new StartSend($api_token, $api_gate);
        }
    }

    /**
     * Register all inits and admin menu
     */
    public function register()
    {
        add_action('admin_init', array($this, 'admin_init'));
        add_action('admin_menu', array($this, 'admin_menu'));
    }

    function admin_init()
    {

        //set the settings
        $this->settings_api->set_sections($this->get_settings_sections());
        $this->settings_api->set_fields($this->get_settings_fields());

        //initialize settings
        $this->settings_api->admin_init();
    }

    function admin_menu()
    {
        add_options_page('StartSend WooCommerce', 'StartsendAPI SMS Settings', 'manage_options', 'startsend-woocoommerce-setting', array(
            $this,
            'plugin_page'
        ));
    }

    function get_settings_sections()
    {
        $sections = array(
            array(
                'id' => 'Startsend_setting',
                'title' => __('Startsend SMS Settings', 'startsend-woocoommerce')
            ),
            array(
                'id' => 'admin_setting',
                'title' => __('Admin Settings', 'startsend-woocoommerce')
            ),
            array(
                'id' => 'customer_setting',
                'title' => __('Customer Settings', 'startsend-woocoommerce')
            )
        );

        $sections = apply_filters('Startsend_setting_section', $sections
        );

        return $sections;
    }

    /**
     * Returns all the settings fields
     *
     * @return array settings fields
     */
    function get_settings_fields()
    {
        global $woocommerce;

        $default_country = Startsend_get_options('Startsend_woocommerce_api_gate', 'Startsend_setting', '' );

        $additional_billing_fields = '';
        $additional_billing_fields_desc = '';
        $additional_billing_fields_array = $this->get_additional_billing_fields();
        foreach ($additional_billing_fields_array as $field) {
            $additional_billing_fields .= ', [' . $field . ']';
        }
        if ($additional_billing_fields) {
            $additional_billing_fields_desc = '<br />Custom tags: ' . substr($additional_billing_fields, 2);
        }

        // Ошибочка вышла закрываем тут багу
        if ($this->service) {
            $alphaNameResponse = $this->service->getAlphaNames();
        }


        $alphaNameOptions = [0 => 'StartSend'];

        if ($default_country === 'https://app.sms.by/api/v1/') {
            $alphaNameOptions = [0 => 'Sms.by'];
        }

        if ($alphaNameResponse) {
            $alphaNameOptions = (array) $alphaNameResponse;
        }

        $settings_fields = array(
            'Startsend_setting' => array(
                array(
                    'name' => 'Startsend_woocommerce_api_token',
                    'label' => __('API Key', 'startsend-woocoommerce'),
                    'desc' => __('Your Startsend API account token. Account can be registered at official sites for <a href="https://sms.by">Belarus</a> or for <a href="https://startsend.ru">Russia</a>', 'startsend-woocoommerce'),
                    'type' => 'text',
                ),
                array(
                    'name' => 'Startsend_woocommerce_sms_from',
                    'label' => __('Message From', 'startsend-woocoommerce'),
                    'class' => array('chzn-drop'),
                    'desc' => __('Sender of the SMS when a message is received at a mobile phone', 'startsend-woocoommerce'),
                    'type' => 'select',
                    'options' => $alphaNameOptions,
                ),
                array(
                    'name' => 'Startsend_woocommerce_api_gate',
                    'label' => __('Default country', 'startsend-woocoommerce'),
                    'class' => array('chzn-drop'),
                    'placeholder' => __('Select a country', 'startsend-woocoommerce'),
                    'desc' => __('Selected country will be use as default country info for mobile gate provided.'),
                    'type' => 'select',
                    'options' => $this->countriesDefaultGate,
                ),
                array(
                    'name' => 'export_Startsend_log',
                    'label' => 'Export Log',
                    'desc' => '<a href="' . admin_url('admin.php?page=startsend-download-file&file=StartSend') . '" class="button button-secondary">Export</a><div id="mocean_sms[keyword-modal]" class="modal"></div>',
                    'type' => 'html'
                )
            ),
            'admin_setting' => array(
                array(
                    'name' => 'Startsend_woocommerce_admin_suborders_send_sms',
                    'label' => __('Enable Suborders SMS Notifications', 'startsend-woocoommerce'),
                    'desc' => ' ' . __('Enable', 'startsend-woocoommerce'),
                    'type' => 'checkbox',
                    'default' => 'off'
                ),
                array(
                    'name' => 'Startsend_woocommerce_admin_send_sms_on',
                    'label' => __('	Send notification on', 'startsend-woocoommerce'),
                    'desc' => __('Choose when to send a status notification message to your admin', 'startsend-woocoommerce'),
                    'type' => 'multicheck',
                    'default' => array(
                        'on-hold' => 'on-hold',
                        'processing' => 'processing'
                    ),
                    'options' => array(
                        'pending' => ' Pending',
                        'on-hold' => ' On-hold',
                        'processing' => ' Processing',
                        'completed' => ' Completed',
                        'cancelled' => ' Cancelled',
                        'refunded' => ' Refunded',
                        'failed' => ' Failed'
                    )
                ),
                array(
                    'name' => 'Startsend_woocommerce_admin_sms_recipients',
                    'label' => __('Mobile Number', 'startsend-woocoommerce'),
                    'desc' => __('Mobile number to receive new order SMS notification. To send to multiple receivers, separate each entry with comma such as 0123456789, 0167888945', 'startsend-woocoommerce'),
                    'type' => 'text',
                ),
                array(
                    'name' => 'Startsend_woocommerce_admin_sms_template',
                    'label' => __('Admin SMS Message', 'startsend-woocoommerce'),
                    'desc' => 'Customize your SMS with <button type="button" id="mocean_sms[open-keywords]" data-attr-type="admin" data-attr-target="admin_setting[Startsend_woocommerce_admin_sms_template]" class="button button-secondary">Keywords</button>',
                    'type' => 'textarea',
                    'rows' => '8',
                    'cols' => '500',
                    'css' => 'min-width:350px;',
                    'default' => __('[shop_name] : You have a new order with order ID [order_id] and order amount [order_currency] [order_amount]. The order is now [order_status].', 'startsend-woocoommerce')
                )
            ),
            'customer_setting' => array(
                array(
                    'name' => 'Startsend_woocommerce_suborders_send_sms',
                    'label' => __('Enable Suborders SMS Notifications', 'startsend-woocoommerce'),
                    'desc' => ' ' . __('Enable', 'startsend-woocoommerce'),
                    'type' => 'checkbox',
                    'default' => 'off'
                ),
                array(
                    'name' => 'Startsend_woocommerce_send_sms',
                    'label' => __('	Send notification on', 'startsend-woocoommerce'),
                    'desc' => __('Choose when to send a status notification message to your customer', 'startsend-woocoommerce'),
                    'type' => 'multicheck',
                    'options' => array(
                        'pending' => ' Pending',
                        'on-hold' => ' On-hold',
                        'processing' => ' Processing',
                        'completed' => ' Completed',
                        'cancelled' => ' Cancelled',
                        'refunded' => ' Refunded',
                        'failed' => ' Failed'
                    )
                ),
                array(
                    'name' => 'Startsend_woocommerce_sms_template_default',
                    'label' => __('Default Customer SMS Message', 'startsend-woocoommerce'),
                    'desc' => 'Customize your SMS with <button type="button" id="mocean_sms[open-keywords]" data-attr-type="default" data-attr-target="customer_setting[Startsend_woocommerce_sms_template_default]" class="button button-secondary">Keywords</button>',
                    'type' => 'textarea',
                    'rows' => '8',
                    'cols' => '500',
                    'css' => 'min-width:350px;',
                    'default' => __('[shop_name] : Thank you for purchasing. Your order ([order_id]) is now [order_status].', 'startsend-woocoommerce')
                ),
                array(
                    'name' => 'Startsend_woocommerce_sms_template_pending',
                    'label' => __('Pending SMS Message', 'startsend-woocoommerce'),
                    'desc' => 'Customize your SMS with <button type="button" id="mocean_sms[open-keywords]" data-attr-type="pending" data-attr-target="customer_setting[Startsend_woocommerce_sms_template_pending]" class="button button-secondary">Keywords</button>',
                    'type' => 'textarea',
                    'rows' => '8',
                    'cols' => '500',
                    'css' => 'min-width:350px;',
                    'default' => __('[shop_name] : Thank you for purchasing. Your order ([order_id]) is now [order_status].', 'startsend-woocoommerce')
                ),
                array(
                    'name' => 'Startsend_woocommerce_sms_template_on-hold',
                    'label' => __('On-hold SMS Message', 'startsend-woocoommerce'),
                    'desc' => 'Customize your SMS with <button type="button" id="mocean_sms[open-keywords]" data-attr-type="on_hold" data-attr-target="customer_setting[Startsend_woocommerce_sms_template_on-hold]" class="button button-secondary">Keywords</button>',
                    'type' => 'textarea',
                    'rows' => '8',
                    'cols' => '500',
                    'css' => 'min-width:350px;',
                    'default' => __('[shop_name] : Thank you for purchasing. Your order ([order_id]) is now [order_status].', 'startsend-woocoommerce')
                ),
                array(
                    'name' => 'Startsend_woocommerce_sms_template_processing',
                    'label' => __('Processing SMS Message', 'startsend-woocoommerce'),
                    'desc' => 'Customize your SMS with <button type="button" id="mocean_sms[open-keywords]" data-attr-type="processing" data-attr-target="customer_setting[Startsend_woocommerce_sms_template_processing]" class="button button-secondary">Keywords</button>',
                    'type' => 'textarea',
                    'rows' => '8',
                    'cols' => '500',
                    'css' => 'min-width:350px;',
                    'default' => __('[shop_name] : Thank you for purchasing. Your order ([order_id]) is now [order_status].', 'startsend-woocoommerce')
                ),
                array(
                    'name' => 'Startsend_woocommerce_sms_template_completed',
                    'label' => __('Completed SMS Message', 'startsend-woocoommerce'),
                    'desc' => 'Customize your SMS with <button type="button" id="mocean_sms[open-keywords]" data-attr-type="completed" data-attr-target="customer_setting[Startsend_woocommerce_sms_template_completed]" class="button button-secondary">Keywords</button>',
                    'type' => 'textarea',
                    'rows' => '8',
                    'cols' => '500',
                    'css' => 'min-width:350px;',
                    'default' => __('[shop_name] : Thank you for purchasing. Your order ([order_id]) is now [order_status].', 'startsend-woocoommerce')
                ),
                array(
                    'name' => 'Startsend_woocommerce_sms_template_cancelled',
                    'label' => __('Cancelled SMS Message', 'startsend-woocoommerce'),
                    'desc' => 'Customize your SMS with <button type="button" id="mocean_sms[open-keywords]" data-attr-type="cancelled" data-attr-target="customer_setting[Startsend_woocommerce_sms_template_cancelled]" class="button button-secondary">Keywords</button>',
                    'type' => 'textarea',
                    'rows' => '8',
                    'cols' => '500',
                    'css' => 'min-width:350px;',
                    'default' => __('[shop_name] : Thank you for purchasing. Your order ([order_id]) is now [order_status].', 'startsend-woocoommerce')
                ),
                array(
                    'name' => 'Startsend_woocommerce_sms_template_refunded',
                    'label' => __('Refunded SMS Message', 'startsend-woocoommerce'),
                    'desc' => 'Customize your SMS with <button type="button" id="mocean_sms[open-keywords]" data-attr-type="refunded" data-attr-target="customer_setting[Startsend_woocommerce_sms_template_refunded]" class="button button-secondary">Keywords</button>',
                    'type' => 'textarea',
                    'rows' => '8',
                    'cols' => '500',
                    'css' => 'min-width:350px;',
                    'default' => __('[shop_name] : Thank you for purchasing. Your order ([order_id]) is now [order_status].', 'startsend-woocoommerce')
                ),
                array(
                    'name' => 'Startsend_woocommerce_sms_template_failed',
                    'label' => __('Failed SMS Message', 'startsend-woocoommerce'),
                    'desc' => 'Customize your SMS with <button type="button" id="mocean_sms[open-keywords]" data-attr-type="failed" data-attr-target="customer_setting[Startsend_woocommerce_sms_template_failed]" class="button button-secondary">Keywords</button>',
                    'type' => 'textarea',
                    'rows' => '8',
                    'cols' => '500',
                    'css' => 'min-width:350px;',
                    'default' => __('[shop_name] : Thank you for purchasing. Your order ([order_id]) is now [order_status].', 'startsend-woocoommerce')
                )
            )
        );



        // Get balance
        if ($this->service) {
            $balanceResponse = $this->service->getBalance();

            if (!function_exists('curl_version')) {
                echo "<h1>Не установлено curl расширение для вашего PHP!</h1>";
            }
            if (isset($balanceResponse->currency) && $balanceResponse->status === 'OK') {
                $currency = $balanceResponse->currency;
                $smsBalance = number_format($balanceResponse->result[0]->balance, 2, ',', '');
                $viberBalance = number_format($balanceResponse->result[0]->viber_balance, 2, ',', '');
                $settings_fields['Startsend_setting'][] = array(
                    'name' => 'export_Startsend_balance_info',
                    'label' => __('Balance info'),
                    'desc' => __('SMS balance: ').$smsBalance.' '.$currency .__(' ; Viber balance: ').$viberBalance. ' '. $currency,
                    'type' => 'html'
                );
            }
        }




        $settings_fields = apply_filters('Startsend_setting_fields', $settings_fields);

        return $settings_fields;
    }

    function plugin_page()
    {
        echo '<div class="wrap">';

        $this->settings_api->show_navigation();
        $this->settings_api->show_forms();
        echo '<input type="hidden" value="' . join(",", $this->get_additional_billing_fields()) . '" id="Startsend_new_billing_field" />';

        echo '</div>';
    }

    /**
     * Get all the pages
     *
     * @return array page names with key value pairs
     */
    function get_pages()
    {
        $pages = get_pages();
        $pages_options = array();
        if ($pages) {
            foreach ($pages as $page) {
                $pages_options[$page->ID] = $page->post_title;
            }
        }

        return $pages_options;
    }

    function get_additional_billing_fields()
    {
        $default_billing_fields = array(
            'billing_first_name',
            'billing_last_name',
            'billing_company',
            'billing_address_1',
            'billing_address_2',
            'billing_city',
            'billing_state',
            'billing_country',
            'billing_postcode',
            'billing_phone',
            'billing_email'
        );
        $additional_billing_field = array();
        $billing_fields = array_filter(get_option('wc_fields_billing', array()));
        foreach ($billing_fields as $field_key => $field_info) {
            if (!in_array($field_key, $default_billing_fields) && $field_info['enabled']) {
                array_push($additional_billing_field, $field_key);
            }
        }

        return $additional_billing_field;
    }
}

?>
