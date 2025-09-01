<?php
/**
 * Funcionalidades públicas do plugin
 */
class FT1_Cultural_Public {

    private $plugin_name;
    private $version;

    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    public function enqueue_styles() {
        wp_enqueue_style($this->plugin_name, FT1_CULTURAL_PLUGIN_URL . 'public/css/ft1-cultural-public.css', array(), $this->version, 'all');
    }

    public function enqueue_scripts() {
        wp_enqueue_script($this->plugin_name, FT1_CULTURAL_PLUGIN_URL . 'public/js/ft1-cultural-public.js', array('jquery'), $this->version, false);
        wp_localize_script($this->plugin_name, 'ft1_public_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('ft1_public_nonce')
        ));
    }

    public function init_shortcodes() {
        add_shortcode('ft1_cultural_dashboard', array($this, 'dashboard_shortcode'));
        add_shortcode('ft1_cultural_profile', array($this, 'profile_shortcode'));
        add_shortcode('ft1_cultural_editals', array($this, 'editals_shortcode'));
    }

    public function dashboard_shortcode($atts) {
        $atts = shortcode_atts(array(), $atts, 'ft1_cultural_dashboard');
        
        ob_start();
        include FT1_CULTURAL_PLUGIN_DIR . 'public/partials/ft1-cultural-public-dashboard.php';
        return ob_get_clean();
    }

    public function profile_shortcode($atts) {
        $atts = shortcode_atts(array(), $atts, 'ft1_cultural_profile');
        
        ob_start();
        include FT1_CULTURAL_PLUGIN_DIR . 'public/partials/ft1-cultural-public-profile.php';
        return ob_get_clean();
    }

    public function editals_shortcode($atts) {
        $atts = shortcode_atts(array(
            'limit' => 10,
            'status' => 'active'
        ), $atts, 'ft1_cultural_editals');
        
        ob_start();
        include FT1_CULTURAL_PLUGIN_DIR . 'public/partials/ft1-cultural-public-editals.php';
        return ob_get_clean();
    }

    public function sign_contract() {
        check_ajax_referer('ft1_public_nonce', 'nonce');
        
        $contract_id = intval($_POST['contract_id']);
        $signature = sanitize_text_field($_POST['signature']);

        if (empty($signature)) {
            wp_send_json_error(array('message' => 'Assinatura é obrigatória'));
        }

        $result = FT1_Cultural_Contracts::sign_contract($contract_id, $signature);

        if ($result !== false) {
            wp_send_json_success(array('message' => 'Contrato assinado com sucesso!'));
        } else {
            wp_send_json_error(array('message' => 'Erro ao assinar contrato'));
        }
    }
}