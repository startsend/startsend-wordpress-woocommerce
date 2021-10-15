<?php
/**
 * Created by PhpStorm.
 * User: Neoson Lam
 * Date: 2/25/2019
 * Time: 9:59 AM.
 */

class Startsend_WooCommerce_Widget implements Startsend_Register_Interface
{

    protected $log;

    /**
     * Startsend_WooCommerce_Widget constructor.
     * @param Startsend_WooCoommerce_Logger|null $log
     */
    public function __construct(Startsend_WooCoommerce_Logger $log = null)
    {
        require_once plugin_dir_path(__DIR__) . 'lib/StartSend.php';

        if ($log === null) {
            $log = new Startsend_WooCoommerce_Logger();
        }

        $this->log = $log;
    }

    public function register()
    {
        add_action('wp_dashboard_setup', array($this, 'register_widget'));
    }

    public function register_widget()
    {
        wp_add_dashboard_widget('msmswc_dashboard_widget', 'StartSend', array($this, 'display_widget'));
    }

    public function display_widget()
    {
        $api_key = Startsend_get_options('Startsend_woocommerce_api_token', 'Startsend_setting', '');
        $api_gate = Startsend_get_options('Startsend_woocommerce_api_gate', 'Startsend_setting', '');
        $Startsend_rest = new StartSend($api_key, $api_gate);
        try {
            $balance = $Startsend_rest->getBalance();

            if ($api_key && $api_gate && isset($balance->status)) {

                $b = round($balance->result[0]->balance?? 0,2);
                ?>

                <h3><?php echo $balance->status === 0 || $balance->status === 'OK' ? "Balance: {$b}  {$balance->currency}" : urldecode($balance->err_msg) ?></h3>

                <?php
            } else {
                ?>

                <h3>
                    Please setup API Key and API Secret in
                    <a href="<?php echo admin_url('options-general.php?page=startsend-woocoommerce-setting') ?>">
                        StartSend settings
                    </a>
                </h3>
                <h3>
                    There's some problem while showing balance, please refresh this page and try again.
                </h3>

                <?php
            }
        } catch (Exception $exception) {
            //errors in curl
            $this->log->add('StartSend', 'Failed get balance: ' . $exception->getMessage());
            ?>

            <h3>
                There's some problem while showing balance, please refresh this page and try again.
            </h3>

            <?php
        }
    }
}
