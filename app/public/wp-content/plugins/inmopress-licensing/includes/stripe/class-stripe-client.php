<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Stripe Client - Cliente base para Stripe API
 * 
 * NOTA: Requiere composer install para instalar stripe/stripe-php
 * Ejecutar: composer require stripe/stripe-php:^13.0
 */
class Inmopress_Stripe_Client
{
    private static $instance = null;
    private $stripe = null;

    public static function get_instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        $this->init_stripe();
    }

    /**
     * Inicializar cliente Stripe
     */
    private function init_stripe()
    {
        if (!class_exists('\Stripe\Stripe')) {
            // Intentar cargar desde composer
            $autoload_paths = array(
                INMOPRESS_LICENSING_PATH . '../../vendor/autoload.php',
                WP_CONTENT_DIR . '/vendor/autoload.php',
                ABSPATH . 'vendor/autoload.php',
            );

            foreach ($autoload_paths as $path) {
                if (file_exists($path)) {
                    require_once $path;
                    break;
                }
            }
        }

        if (class_exists('\Stripe\Stripe')) {
            $secret_key = $this->get_secret_key();
            if ($secret_key) {
                \Stripe\Stripe::setApiKey($secret_key);
                $this->stripe = new \stdClass(); // Placeholder
            }
        }
    }

    /**
     * Obtener cliente Stripe
     */
    public function get_client()
    {
        if (!$this->stripe || !class_exists('\Stripe\Stripe')) {
            return null;
        }
        return $this->stripe;
    }

    /**
     * Obtener secret key (test o live)
     */
    private function get_secret_key()
    {
        $mode = get_option('inmopress_stripe_mode', 'test');
        $key_option = $mode === 'live' ? 'inmopress_stripe_secret_key_live' : 'inmopress_stripe_secret_key_test';
        return get_option($key_option, '');
    }

    /**
     * Obtener webhook secret
     */
    public static function get_webhook_secret()
    {
        $mode = get_option('inmopress_stripe_mode', 'test');
        $secret_option = $mode === 'live' ? 'inmopress_stripe_webhook_secret_live' : 'inmopress_stripe_webhook_secret_test';
        return get_option($secret_option, '');
    }

    /**
     * Crear o actualizar customer en Stripe
     */
    public static function create_or_update_customer($user_id, $email, $name)
    {
        if (!class_exists('\Stripe\Customer')) {
            return null;
        }

        $stripe_customer_id = get_user_meta($user_id, 'inmopress_stripe_customer_id', true);

        try {
            if ($stripe_customer_id) {
                $customer = \Stripe\Customer::update($stripe_customer_id, array(
                    'email' => $email,
                    'name' => $name,
                ));
            } else {
                $customer = \Stripe\Customer::create(array(
                    'email' => $email,
                    'name' => $name,
                    'metadata' => array(
                        'wp_user_id' => $user_id,
                        'installation_id' => get_option('inmopress_installation_id'),
                    ),
                ));
                update_user_meta($user_id, 'inmopress_stripe_customer_id', $customer->id);
            }

            return $customer;
        } catch (Exception $e) {
            error_log('Stripe Customer Error: ' . $e->getMessage());
            return null;
        }
    }
}
