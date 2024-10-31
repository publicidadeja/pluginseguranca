<?php
/**
 * Plugin Name: Secure Dacast Access
 * Plugin Description: Controle de acesso seguro para conteúdo Dacast
 * Version: 1.0.0
 * Author: Seu Nome
 */

if (!defined('WPINC')) {
    die;
}

define('SECURE_DACAST_VERSION', '1.0.0');
define('SECURE_DACAST_PLUGIN_DIR', plugin_dir_path(__FILE__));

class Secure_Dacast {
    protected $version;
    protected $access_control;

    public function __construct() {
        $this->version = SECURE_DACAST_VERSION;
        $this->load_dependencies();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }

    private function load_dependencies() {
        require_once SECURE_DACAST_PLUGIN_DIR . 'includes/class-access-control.php';
        require_once SECURE_DACAST_PLUGIN_DIR . 'includes/class-session-manager.php';
        $this->access_control = new Secure_Dacast_Access_Control();
    }

    private function define_admin_hooks() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
    }

    private function define_public_hooks() {
        $this->access_control->init();
    }

    public function add_admin_menu() {
        add_menu_page(
            'Secure Dacast',
            'Secure Dacast',
            'manage_options',
            'secure-dacast',
            array($this, 'display_plugin_admin_page'),
            'dashicons-lock'
        );

        add_submenu_page(
            'secure-dacast',
            'Usuários Autorizados',
            'Usuários Autorizados',
            'manage_options',
            'secure-dacast-users',
            array($this, 'display_users_page')
        );

        add_submenu_page(
            'secure-dacast',
            'Configurações',
            'Configurações',
            'manage_options',
            'secure-dacast-settings',
            array($this, 'display_settings_page')
        );

        add_submenu_page(
            'secure-dacast',
            'Sessões Ativas',
            'Sessões Ativas',
            'manage_options',
            'secure-dacast-sessions',
            array($this, 'display_sessions_page')
        );
    }

    public function display_plugin_admin_page() {
        require_once SECURE_DACAST_PLUGIN_DIR . 'admin/partials/secure-dacast-admin.php';
    }

    public function display_users_page() {
        require_once SECURE_DACAST_PLUGIN_DIR . 'admin/partials/secure-dacast-users.php';
    }

    public function display_settings_page() {
        require_once SECURE_DACAST_PLUGIN_DIR . 'admin/partials/secure-dacast-settings.php';
    }

    public function display_sessions_page() {
        require_once SECURE_DACAST_PLUGIN_DIR . 'admin/partials/secure-dacast-sessions.php';
    }

    public function register_settings() {
        register_setting(
            'secure_dacast_options',
            'secure_dacast_settings',
            array(
                'type' => 'array',
                'sanitize_callback' => array($this, 'sanitize_settings')
            )
        );

        register_setting(
            'secure_dacast_options',
            'secure_dacast_protected_pages',
            array(
                'type' => 'array',
                'sanitize_callback' => array($this, 'sanitize_protected_pages')
            )
        );
    }

    public function sanitize_settings($input) {
        $defaults = array(
            'session_timeout' => 30,
            'max_attempts' => 5,
            'block_duration' => 60,
            'watermark_enabled' => true,
            'screenshot_protection' => true,
            'watermark_text' => '{cpf} - {email} - {datetime}',
            'notify_access' => false,
            'notify_email' => get_option('admin_email')
        );

        return wp_parse_args($input, $defaults);
    }

    public function sanitize_protected_pages($input) {
        return array_map('absint', (array)$input);
    }

    public static function activate() {
        require_once SECURE_DACAST_PLUGIN_DIR . 'includes/class-activator.php';
        require_once SECURE_DACAST_PLUGIN_DIR . 'includes/class-session-manager.php';
        
        Secure_Dacast_Activator::activate();
        
        $session_manager = new Secure_Dacast_Session_Manager();
        $session_manager->create_tables();

        $default_settings = array(
            'session_timeout' => 30,
            'max_attempts' => 5,
            'block_duration' => 60,
            'watermark_enabled' => true,
            'screenshot_protection' => true,
            'watermark_text' => '{cpf} - {email} - {datetime}',
            'notify_access' => false,
            'notify_email' => get_option('admin_email')
        );

        if (!get_option('secure_dacast_settings')) {
            add_option('secure_dacast_settings', $default_settings);
        }
    }

    public static function deactivate() {
        global $wpdb;
        
        $table_sessions = $wpdb->prefix . 'secure_dacast_sessions';
        $wpdb->query("TRUNCATE TABLE IF EXISTS $table_sessions");

        if (isset($_COOKIE['secure_dacast_access'])) {
            setcookie(
                'secure_dacast_access',
                '',
                time() - 3600,
                COOKIEPATH,
                COOKIE_DOMAIN,
                true,
                true
            );
        }

        if (session_id()) {
            session_destroy();
        }

        wp_cache_flush();
    }
}

// Inicialização do plugin
function run_secure_dacast() {
    $plugin = new Secure_Dacast();
}

// Hooks de ativação e desativação
register_activation_hook(__FILE__, array('Secure_Dacast', 'activate'));
register_deactivation_hook(__FILE__, array('Secure_Dacast', 'deactivate'));

// Inicializa o plugin
run_secure_dacast();