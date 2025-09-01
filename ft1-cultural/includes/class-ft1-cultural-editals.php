<?php
/**
 * Classe para gerenciar editais
 */
class FT1_Cultural_Editals {

    public static function create_edital($data) {
        $sanitized_data = array(
            'title' => sanitize_text_field($data['title']),
            'description' => wp_kses_post($data['description']),
            'start_date' => sanitize_text_field($data['start_date']),
            'end_date' => sanitize_text_field($data['end_date']),
            'budget' => floatval($data['budget']),
            'status' => sanitize_text_field($data['status'])
        );

        return FT1_Cultural_Database::save_edital($sanitized_data);
    }

    public static function update_edital($id, $data) {
        $data['id'] = intval($id);
        return self::create_edital($data);
    }

    public static function get_edital_stats($edital_id) {
        global $wpdb;
        
        $proponents_table = $wpdb->prefix . 'ft1_proponents';
        $contracts_table = $wpdb->prefix . 'ft1_contracts';

        $proponents_count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $proponents_table WHERE edital_id = %d",
            $edital_id
        ));

        $contracts_count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $contracts_table WHERE edital_id = %d",
            $edital_id
        ));

        $signed_contracts = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $contracts_table WHERE edital_id = %d AND status = 'signed'",
            $edital_id
        ));

        return array(
            'proponents' => intval($proponents_count),
            'contracts' => intval($contracts_count),
            'signed_contracts' => intval($signed_contracts)
        );
    }
}