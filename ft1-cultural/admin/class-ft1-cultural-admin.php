<?php
/**
 * Funcionalidades administrativas do plugin
 */
class FT1_Cultural_Admin {

    private $plugin_name;
    private $version;

    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    public function enqueue_styles() {
        wp_enqueue_style($this->plugin_name, FT1_CULTURAL_PLUGIN_URL . 'admin/css/ft1-cultural-admin.css', array(), $this->version, 'all');
    }

    public function enqueue_scripts() {
        wp_enqueue_script($this->plugin_name, FT1_CULTURAL_PLUGIN_URL . 'admin/js/ft1-cultural-admin.js', array('jquery'), $this->version, false);
        wp_localize_script($this->plugin_name, 'ft1_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('ft1_nonce')
        ));
    }

    public function add_admin_menu() {
        add_menu_page(
            'FT1 Cultural',
            'FT1 Cultural',
            'manage_ft1_cultural',
            'ft1-cultural',
            array($this, 'display_dashboard'),
            'dashicons-clipboard',
            30
        );

        add_submenu_page(
            'ft1-cultural',
            'Editais',
            'Editais',
            'manage_ft1_cultural',
            'ft1-editals',
            array($this, 'display_editals')
        );

        add_submenu_page(
            'ft1-cultural',
            'Proponentes',
            'Proponentes',
            'manage_ft1_cultural',
            'ft1-proponents',
            array($this, 'display_proponents')
        );

        add_submenu_page(
            'ft1-cultural',
            'Contratos',
            'Contratos',
            'manage_ft1_cultural',
            'ft1-contracts',
            array($this, 'display_contracts')
        );
    }

    public function display_dashboard() {
        include_once FT1_CULTURAL_PLUGIN_DIR . 'admin/partials/ft1-cultural-admin-dashboard.php';
    }

    public function display_editals() {
        include_once FT1_CULTURAL_PLUGIN_DIR . 'admin/partials/ft1-cultural-admin-editals.php';
    }

    public function display_proponents() {
        include_once FT1_CULTURAL_PLUGIN_DIR . 'admin/partials/ft1-cultural-admin-proponents.php';
    }

    public function display_contracts() {
        include_once FT1_CULTURAL_PLUGIN_DIR . 'admin/partials/ft1-cultural-admin-contracts.php';
    }

    public function save_edital() {
        check_ajax_referer('ft1_nonce', 'nonce');
        
        if (!current_user_can('edit_ft1_editals')) {
            wp_send_json_error(array('message' => 'Permissão negada'));
            return;
        }

        // Validar campos obrigatórios
        if (empty($_POST['title']) || empty($_POST['start_date']) || empty($_POST['end_date'])) {
            wp_send_json_error(array('message' => 'Título, data de início e data de fim são obrigatórios'));
            return;
        }

        // Processar valor do orçamento
        $budget = 0;
        if (!empty($_POST['budget'])) {
            $budget_clean = preg_replace('/[^\d,]/', '', $_POST['budget']);
            $budget_clean = str_replace(',', '.', $budget_clean);
            $budget = floatval($budget_clean);
        }

        $data = array(
            'title' => sanitize_text_field($_POST['title']),
            'description' => wp_kses_post($_POST['description']),
            'start_date' => sanitize_text_field($_POST['start_date']),
            'end_date' => sanitize_text_field($_POST['end_date']),
            'budget' => $budget,
            'status' => sanitize_text_field($_POST['status'])
        );

        if (isset($_POST['id']) && $_POST['id'] > 0) {
            $data['id'] = intval($_POST['id']);
        }

        $result = FT1_Cultural_Database::save_edital($data);

        if ($result !== false) {
            wp_send_json_success(array('message' => 'Edital salvo com sucesso!'));
        } else {
            wp_send_json_error(array('message' => 'Erro ao salvar edital'));
        }
    }

    public function save_proponent() {
        check_ajax_referer('ft1_nonce', 'nonce');
        
        if (!current_user_can('manage_ft1_cultural')) {
            wp_send_json_error(array('message' => 'Permissão negada'));
            return;
        }

        // Validar campos obrigatórios
        if (empty($_POST['name']) || empty($_POST['email'])) {
            wp_send_json_error(array('message' => 'Nome e e-mail são obrigatórios'));
            return;
        }

        $data = array(
            'name' => sanitize_text_field($_POST['name']),
            'email' => sanitize_email($_POST['email']),
            'phone' => sanitize_text_field($_POST['phone']),
            'document' => sanitize_text_field($_POST['document']),
            'address' => sanitize_textarea_field($_POST['address']),
            'edital_id' => !empty($_POST['edital_id']) ? intval($_POST['edital_id']) : null
        );

        if (isset($_POST['id']) && $_POST['id'] > 0) {
            $data['id'] = intval($_POST['id']);
        }

        $result = FT1_Cultural_Database::save_proponent($data);

        if ($result !== false) {
            wp_send_json_success(array('message' => 'Proponente salvo com sucesso!'));
        } else {
            wp_send_json_error(array('message' => 'Erro ao salvar proponente'));
        }
    }

    public function upload_document() {
        check_ajax_referer('ft1_nonce', 'nonce');

        if (!function_exists('wp_handle_upload')) {
            require_once(ABSPATH . 'wp-admin/includes/file.php');
        }

        $upload_overrides = array('test_form' => false);
        $movefile = wp_handle_upload($_FILES['document'], $upload_overrides);

        if ($movefile && !isset($movefile['error'])) {
            $document_data = array(
                'proponent_id' => intval($_POST['proponent_id']),
                'filename' => sanitize_file_name($movefile['file']),
                'file_path' => $movefile['file'],
                'file_type' => $movefile['type'],
                'file_size' => filesize($movefile['file'])
            );

            $result = FT1_Cultural_Database::save_document($document_data);

            if ($result !== false) {
                wp_send_json_success(array(
                    'message' => 'Documento enviado com sucesso!',
                    'url' => $movefile['url']
                ));
            }
        }

        wp_send_json_error(array('message' => 'Erro ao enviar documento'));
    }

    public function send_contract() {
        check_ajax_referer('ft1_nonce', 'nonce');
        
        $contract_id = intval($_POST['contract_id']);
        $method = sanitize_text_field($_POST['method']);

        if ($method === 'email') {
            $result = FT1_Cultural_Email::send_contract_email($contract_id);
            if ($result) {
                wp_send_json_success(array('message' => 'Contrato enviado por e-mail com sucesso!'));
            }
        } elseif ($method === 'whatsapp') {
            $whatsapp_url = FT1_Cultural_Email::get_whatsapp_link($contract_id);
            if ($whatsapp_url) {
                wp_send_json_success(array(
                    'message' => 'Link do WhatsApp gerado!',
                    'whatsapp_url' => $whatsapp_url
                ));
            }
        }

        wp_send_json_error(array('message' => 'Erro ao enviar contrato'));
    }

    public function save_contract() {
        check_ajax_referer('ft1_nonce', 'nonce');
        
        if (!current_user_can('edit_ft1_contracts')) {
            wp_die('Permissão negada');
        }

        $data = array(
            'proponent_id' => intval($_POST['proponent_id']),
            'edital_id' => intval($_POST['edital_id']),
            'title' => sanitize_text_field($_POST['title']),
            'content' => wp_kses_post($_POST['content']),
            'value' => floatval(str_replace(['R$', '.', ','], ['', '', '.'], $_POST['value']))
        );

        if (isset($_POST['id']) && $_POST['id'] > 0) {
            $data['id'] = intval($_POST['id']);
        }

        $result = FT1_Cultural_Database::save_contract($data);

        if ($result !== false) {
            wp_send_json_success(array('message' => 'Contrato salvo com sucesso!'));
        } else {
            wp_send_json_error(array('message' => 'Erro ao salvar contrato'));
        }
    }

    public function get_edital() {
        check_ajax_referer('ft1_nonce', 'nonce');
        
        $id = intval($_POST['id']);
        $edital = FT1_Cultural_Database::get_edital($id);
        
        if ($edital) {
            wp_send_json_success($edital);
        } else {
            wp_send_json_error(array('message' => 'Edital não encontrado'));
        }
    }

    public function get_proponent() {
        check_ajax_referer('ft1_nonce', 'nonce');
        
        $id = intval($_POST['id']);
        $proponent = FT1_Cultural_Database::get_proponent($id);
        
        if ($proponent) {
            wp_send_json_success($proponent);
        } else {
            wp_send_json_error(array('message' => 'Proponente não encontrado'));
        }
    }

    public function get_contract() {
        check_ajax_referer('ft1_nonce', 'nonce');
        
        $id = intval($_POST['id']);
        $contract = FT1_Cultural_Database::get_contract($id);
        
        if ($contract) {
            wp_send_json_success($contract);
        } else {
            wp_send_json_error(array('message' => 'Contrato não encontrado'));
        }
    }

    public function delete_edital() {
        check_ajax_referer('ft1_nonce', 'nonce');
        
        if (!current_user_can('edit_ft1_editals')) {
            wp_die('Permissão negada');
        }

        $id = intval($_POST['id']);
        $result = FT1_Cultural_Database::delete_edital($id);

        if ($result !== false) {
            wp_send_json_success(array('message' => 'Edital excluído com sucesso!'));
        } else {
            wp_send_json_error(array('message' => 'Erro ao excluir edital'));
        }
    }

    public function delete_proponent() {
        check_ajax_referer('ft1_nonce', 'nonce');
        
        if (!current_user_can('manage_ft1_cultural')) {
            wp_die('Permissão negada');
        }

        $id = intval($_POST['id']);
        $result = FT1_Cultural_Database::delete_proponent($id);

        if ($result !== false) {
            wp_send_json_success(array('message' => 'Proponente excluído com sucesso!'));
        } else {
            wp_send_json_error(array('message' => 'Erro ao excluir proponente'));
        }
    }

    public function delete_contract() {
        check_ajax_referer('ft1_nonce', 'nonce');
        
        if (!current_user_can('edit_ft1_contracts')) {
            wp_die('Permissão negada');
        }

        $id = intval($_POST['id']);
        $result = FT1_Cultural_Database::delete_contract($id);

        if ($result !== false) {
            wp_send_json_success(array('message' => 'Contrato excluído com sucesso!'));
        } else {
            wp_send_json_error(array('message' => 'Erro ao excluir contrato'));
        }
    }

    public function get_documents() {
        check_ajax_referer('ft1_nonce', 'nonce');
        
        $proponent_id = intval($_POST['proponent_id']);
        $documents = FT1_Cultural_Database::get_documents($proponent_id);
        
        wp_send_json_success($documents);
    }

    public function delete_document() {
        check_ajax_referer('ft1_nonce', 'nonce');
        
        if (!current_user_can('manage_ft1_cultural')) {
            wp_die('Permissão negada');
        }

        $id = intval($_POST['id']);
        $result = FT1_Cultural_Database::delete_document($id);

        if ($result !== false) {
            wp_send_json_success(array('message' => 'Documento excluído com sucesso!'));
        } else {
            wp_send_json_error(array('message' => 'Erro ao excluir documento'));
        }
    }
}