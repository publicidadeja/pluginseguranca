<?php
class Secure_Dacast_Admin {
    private $plugin_name;
    private $version;

    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;

        add_action('admin_menu', array($this, 'add_plugin_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_styles'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('admin_init', array($this, 'register_settings'));
    }

    public function add_plugin_admin_menu() {
        add_menu_page(
            'Secure Dacast', 
            'Secure Dacast', 
            'manage_options', 
            'secure-dacast',
            array($this, 'display_plugin_setup_page'),
            'dashicons-lock',
            65
        );

        add_submenu_page(
            'secure-dacast',
            'Configurações',
            'Configurações',
            'manage_options',
            'secure-dacast-settings',
            array($this, 'display_plugin_settings_page')
        );

        add_submenu_page(
            'secure-dacast',
            'Logs',
            'Logs',
            'manage_options',
            'secure-dacast-logs',
            array($this, 'display_plugin_logs_page')
        );
    }

    public function register_settings() {
        register_setting('secure_dacast_options', 'secure_dacast_protected_pages');
        register_setting('secure_dacast_options', 'secure_dacast_security_settings');
    }

    public function display_plugin_setup_page() {
        include_once 'partials/secure-dacast-admin-display.php';
    }

    public function display_plugin_settings_page() {
        include_once 'partials/secure-dacast-admin-settings.php';
    }

    public function display_plugin_logs_page() {
        include_once 'partials/secure-dacast-admin-logs.php';
    }

    public function enqueue_styles() {
        wp_enqueue_style(
            $this->plugin_name,
            plugin_dir_url(__FILE__) . 'css/admin-style.css',
            array(),
            $this->version,
            'all'
        );
    }

    public function enqueue_scripts() {
        wp_enqueue_script(
            $this->plugin_name,
            plugin_dir_url(__FILE__) . 'js/admin-script.js',
            array('jquery'),
            $this->version,
            false
        );

        wp_localize_script($this->plugin_name, 'secureDacastAdmin', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('secure_dacast_admin')
        ));
    }
}