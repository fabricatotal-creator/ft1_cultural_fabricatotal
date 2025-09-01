<?php
/**
 * Classe principal do plugin FT1 Cultural
 * 
 * @package FT1Cultural
 * @author Fabricar1 Soluções de Mercado
 * @copyright 2025 Fabricar1 Soluções de Mercado
 */

class FT1_Cultural {

    protected $loader;
    protected $plugin_name;
    protected $version;

    public function __construct() {
        if (defined('FT1_CULTURAL_VERSION')) {
            $this->version = FT1_CULTURAL_VERSION;
        } else {
            $this->version = '1.0.0';
        }
        $this->plugin_name = 'ft1-cultural';

        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }

    private function load_dependencies() {
        require_once FT1_CULTURAL_PLUGIN_DIR . 'includes/class-ft1-cultural-loader.php';
        require_once FT1_CULTURAL_PLUGIN_DIR . 'includes/class-ft1-cultural-database.php';
        require_once FT1_CULTURAL_PLUGIN_DIR . 'includes/class-ft1-cultural-editals.php';
        require_once FT1_CULTURAL_PLUGIN_DIR . 'includes/class-ft1-cultural-contracts.php';
        require_once FT1_CULTURAL_PLUGIN_DIR . 'includes/class-ft1-cultural-email.php';
        require_once FT1_CULTURAL_PLUGIN_DIR . 'admin/class-ft1-cultural-admin.php';
        require_once FT1_CULTURAL_PLUGIN_DIR . 'public/class-ft1-cultural-public.php';

        $this->loader = new FT1_Cultural_Loader();
    }

    private function set_locale() {
        // Implementar internacionalização se necessário
    }

    private function define_admin_hooks() {
        $plugin_admin = new FT1_Cultural_Admin($this->get_plugin_name(), $this->get_version());

        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');
        $this->loader->add_action('admin_menu', $plugin_admin, 'add_admin_menu');
        $this->loader->add_action('wp_ajax_ft1_save_edital', $plugin_admin, 'save_edital');
        $this->loader->add_action('wp_ajax_ft1_save_proponent', $plugin_admin, 'save_proponent');
        $this->loader->add_action('wp_ajax_ft1_upload_document', $plugin_admin, 'upload_document');
        $this->loader->add_action('wp_ajax_ft1_send_contract', $plugin_admin, 'send_contract');
        $this->loader->add_action('wp_ajax_ft1_save_contract', $plugin_admin, 'save_contract');
        $this->loader->add_action('wp_ajax_ft1_delete_edital', $plugin_admin, 'delete_edital');
        $this->loader->add_action('wp_ajax_ft1_delete_proponent', $plugin_admin, 'delete_proponent');
        $this->loader->add_action('wp_ajax_ft1_delete_contract', $plugin_admin, 'delete_contract');
        $this->loader->add_action('wp_ajax_ft1_get_edital', $plugin_admin, 'get_edital');
        $this->loader->add_action('wp_ajax_ft1_get_proponent', $plugin_admin, 'get_proponent');
        $this->loader->add_action('wp_ajax_ft1_get_contract', $plugin_admin, 'get_contract');
        $this->loader->add_action('wp_ajax_ft1_get_documents', $plugin_admin, 'get_documents');
        $this->loader->add_action('wp_ajax_ft1_delete_document', $plugin_admin, 'delete_document');
    }

    private function define_public_hooks() {
        $plugin_public = new FT1_Cultural_Public($this->get_plugin_name(), $this->get_version());

        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');
        $this->loader->add_action('init', $plugin_public, 'init_shortcodes');
        $this->loader->add_action('wp_ajax_ft1_sign_contract', $plugin_public, 'sign_contract');
        $this->loader->add_action('wp_ajax_nopriv_ft1_sign_contract', $plugin_public, 'sign_contract');
    }

    public function run() {
        $this->loader->run();
    }

    public function get_plugin_name() {
        return $this->plugin_name;
    }

    public function get_loader() {
        return $this->loader;
    }

    public function get_version() {
        return $this->version;
    }
}