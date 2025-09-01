<?php
/**
 * Classe para operações do banco de dados
 */
class FT1_Cultural_Database {

    public static function get_editals($limit = 20, $offset = 0) {
        global $wpdb;
        $table = $wpdb->prefix . 'ft1_editals';
        
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table ORDER BY created_at DESC LIMIT %d OFFSET %d",
            $limit, $offset
        ));
    }

    public static function get_edital($id) {
        global $wpdb;
        $table = $wpdb->prefix . 'ft1_editals';
        
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE id = %d",
            $id
        ));
    }

    public static function save_edital($data) {
        global $wpdb;
        $table = $wpdb->prefix . 'ft1_editals';

        if (isset($data['id']) && $data['id'] > 0) {
            // Atualizar
            return $wpdb->update($table, $data, array('id' => $data['id']));
        } else {
            // Inserir
            unset($data['id']);
            $data['created_by'] = get_current_user_id();
            return $wpdb->insert($table, $data);
        }
    }

    public static function get_proponents($edital_id = null, $limit = 20, $offset = 0) {
        global $wpdb;
        $table = $wpdb->prefix . 'ft1_proponents';
        
        if ($edital_id) {
            return $wpdb->get_results($wpdb->prepare(
                "SELECT * FROM $table WHERE edital_id = %d ORDER BY created_at DESC LIMIT %d OFFSET %d",
                $edital_id, $limit, $offset
            ));
        } else {
            return $wpdb->get_results($wpdb->prepare(
                "SELECT * FROM $table ORDER BY created_at DESC LIMIT %d OFFSET %d",
                $limit, $offset
            ));
        }
    }

    public static function get_proponent($id) {
        global $wpdb;
        $table = $wpdb->prefix . 'ft1_proponents';
        
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE id = %d OR unique_id = %s",
            $id, $id
        ));
    }

    public static function save_proponent($data) {
        global $wpdb;
        $table = $wpdb->prefix . 'ft1_proponents';

        if (!isset($data['unique_id']) || empty($data['unique_id'])) {
            $data['unique_id'] = 'FT1' . date('Y') . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
        }

        if (isset($data['id']) && $data['id'] > 0) {
            return $wpdb->update($table, $data, array('id' => $data['id']));
        } else {
            unset($data['id']);
            return $wpdb->insert($table, $data);
        }
    }

    public static function get_contracts($proponent_id = null, $limit = 20, $offset = 0) {
        global $wpdb;
        $table = $wpdb->prefix . 'ft1_contracts';
        
        if ($proponent_id) {
            return $wpdb->get_results($wpdb->prepare(
                "SELECT * FROM $table WHERE proponent_id = %d ORDER BY created_at DESC LIMIT %d OFFSET %d",
                $proponent_id, $limit, $offset
            ));
        } else {
            return $wpdb->get_results($wpdb->prepare(
                "SELECT c.*, p.name as proponent_name, e.title as edital_title 
                 FROM $table c 
                 LEFT JOIN {$wpdb->prefix}ft1_proponents p ON c.proponent_id = p.id
                 LEFT JOIN {$wpdb->prefix}ft1_editals e ON c.edital_id = e.id
                 ORDER BY c.created_at DESC LIMIT %d OFFSET %d",
                $limit, $offset
            ));
        }
    }

    public static function save_contract($data) {
        global $wpdb;
        $table = $wpdb->prefix . 'ft1_contracts';

        if (!isset($data['contract_number']) || empty($data['contract_number'])) {
            $data['contract_number'] = 'CT' . date('Y') . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
        }

        if (isset($data['id']) && $data['id'] > 0) {
            return $wpdb->update($table, $data, array('id' => $data['id']));
        } else {
            unset($data['id']);
            return $wpdb->insert($table, $data);
        }
    }

    public static function save_document($data) {
        global $wpdb;
        $table = $wpdb->prefix . 'ft1_documents';
        
        return $wpdb->insert($table, $data);
    }

    public static function get_contract($id) {
        global $wpdb;
        $table = $wpdb->prefix . 'ft1_contracts';
        
        return $wpdb->get_row($wpdb->prepare(
            "SELECT c.*, p.name as proponent_name, e.title as edital_title 
             FROM $table c 
             LEFT JOIN {$wpdb->prefix}ft1_proponents p ON c.proponent_id = p.id
             LEFT JOIN {$wpdb->prefix}ft1_editals e ON c.edital_id = e.id
             WHERE c.id = %d",
            $id
        ));
    }

    public static function delete_edital($id) {
        global $wpdb;
        $table = $wpdb->prefix . 'ft1_editals';
        
        return $wpdb->delete($table, array('id' => intval($id)));
    }

    public static function delete_proponent($id) {
        global $wpdb;
        $table = $wpdb->prefix . 'ft1_proponents';
        
        return $wpdb->delete($table, array('id' => intval($id)));
    }

    public static function delete_contract($id) {
        global $wpdb;
        $table = $wpdb->prefix . 'ft1_contracts';
        
        return $wpdb->delete($table, array('id' => intval($id)));
    }

    public static function delete_document($id) {
        global $wpdb;
        $table = $wpdb->prefix . 'ft1_documents';
        
        // Buscar arquivo para deletar do sistema
        $document = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE id = %d",
            $id
        ));
        
        if ($document && file_exists($document->file_path)) {
            unlink($document->file_path);
        }
        
        return $wpdb->delete($table, array('id' => intval($id)));
    }

    public static function get_documents($proponent_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'ft1_documents';
        
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table WHERE proponent_id = %d ORDER BY uploaded_at DESC",
            $proponent_id
        ));
    }
}