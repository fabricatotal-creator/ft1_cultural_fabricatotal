<?php
/**
 * Classe responsável pela ativação do plugin
 */
class FT1_Cultural_Activator {

    public static function activate() {
        self::create_tables();
        self::create_pages();
        self::set_capabilities();
    }

    private static function create_tables() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        // Tabela de editais
        $table_editals = $wpdb->prefix . 'ft1_editals';
        $sql_editals = "CREATE TABLE $table_editals (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            title varchar(255) NOT NULL,
            description longtext,
            start_date date,
            end_date date,
            budget decimal(15,2),
            status varchar(50) DEFAULT 'active',
            created_by bigint(20) UNSIGNED,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;";

        // Tabela de proponentes
        $table_proponents = $wpdb->prefix . 'ft1_proponents';
        $sql_proponents = "CREATE TABLE $table_proponents (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            unique_id varchar(100) NOT NULL UNIQUE,
            name varchar(255) NOT NULL,
            email varchar(255) NOT NULL,
            phone varchar(20),
            document varchar(50),
            address text,
            edital_id mediumint(9),
            status varchar(50) DEFAULT 'active',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY edital_id (edital_id)
        ) $charset_collate;";

        // Tabela de documentos
        $table_documents = $wpdb->prefix . 'ft1_documents';
        $sql_documents = "CREATE TABLE $table_documents (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            proponent_id mediumint(9) NOT NULL,
            filename varchar(255) NOT NULL,
            file_path varchar(500) NOT NULL,
            file_type varchar(100),
            file_size bigint(20),
            uploaded_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY proponent_id (proponent_id)
        ) $charset_collate;";

        // Tabela de contratos
        $table_contracts = $wpdb->prefix . 'ft1_contracts';
        $sql_contracts = "CREATE TABLE $table_contracts (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            proponent_id mediumint(9) NOT NULL,
            edital_id mediumint(9) NOT NULL,
            contract_number varchar(100) NOT NULL,
            title varchar(255) NOT NULL,
            content longtext NOT NULL,
            value decimal(15,2),
            status varchar(50) DEFAULT 'draft',
            signature_data longtext,
            signature_ip varchar(45),
            signature_date datetime,
            sent_date datetime,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY proponent_id (proponent_id),
            KEY edital_id (edital_id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql_editals);
        dbDelta($sql_proponents);
        dbDelta($sql_documents);
        dbDelta($sql_contracts);
    }

    private static function create_pages() {
        // Criar página do dashboard
        $dashboard_page = array(
            'post_title'    => 'Dashboard FT1 Cultural',
            'post_content'  => '[ft1_cultural_dashboard]',
            'post_status'   => 'publish',
            'post_type'     => 'page',
            'post_name'     => 'ft1-cultural-dashboard'
        );

        if (!get_page_by_path('ft1-cultural-dashboard')) {
            wp_insert_post($dashboard_page);
        }
    }

    private static function set_capabilities() {
        $role = get_role('administrator');
        if ($role) {
            $role->add_cap('manage_ft1_cultural');
            $role->add_cap('edit_ft1_editals');
            $role->add_cap('edit_ft1_contracts');
        }
    }
}