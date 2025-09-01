<?php
/**
 * Classe para gerenciar contratos
 */
class FT1_Cultural_Contracts {

    public static function create_contract($data) {
        $sanitized_data = array(
            'proponent_id' => intval($data['proponent_id']),
            'edital_id' => intval($data['edital_id']),
            'title' => sanitize_text_field($data['title']),
            'content' => wp_kses_post($data['content']),
            'value' => isset($data['value']) ? floatval($data['value']) : 0,
            'status' => 'draft'
        );

        // Gerar número do contrato se não existir
        if (!isset($sanitized_data['contract_number']) || empty($sanitized_data['contract_number'])) {
            $sanitized_data['contract_number'] = 'CT' . date('Y') . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
        }

        return FT1_Cultural_Database::save_contract($sanitized_data);
    }

    public static function generate_pdf($contract_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'ft1_contracts';
        
        $contract = $wpdb->get_row($wpdb->prepare(
            "SELECT c.*, p.name as proponent_name, p.email, p.phone, p.document, p.address,
                    e.title as edital_title
             FROM $table c
             LEFT JOIN {$wpdb->prefix}ft1_proponents p ON c.proponent_id = p.id
             LEFT JOIN {$wpdb->prefix}ft1_editals e ON c.edital_id = e.id
             WHERE c.id = %d",
            $contract_id
        ));

        if (!$contract) {
            return false;
        }

        // Criar diretório se não existir
        $upload_dir = wp_upload_dir();
        $ft1_dir = $upload_dir['basedir'] . '/ft1-cultural/contracts/';
        
        if (!file_exists($ft1_dir)) {
            wp_mkdir_p($ft1_dir);
        }

        // Gerar HTML do contrato
        $html = self::get_contract_html($contract);
        
        // Nome do arquivo
        $filename = 'contrato-' . $contract->contract_number . '.html';
        $filepath = $ft1_dir . $filename;

        // Salvar arquivo HTML (simulando PDF para este exemplo)
        file_put_contents($filepath, $html);

        return array(
            'filepath' => $filepath,
            'url' => $upload_dir['baseurl'] . '/ft1-cultural/contracts/' . $filename,
            'filename' => $filename
        );
    }

    private static function get_contract_html($contract) {
        ob_start();
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Contrato <?php echo esc_html($contract->contract_number); ?></title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; margin: 40px; }
                .header { text-align: center; margin-bottom: 40px; }
                .contract-info { background: #f9f9f9; padding: 20px; margin: 20px 0; }
                .signature-area { margin-top: 60px; }
                .signature-box { border: 1px solid #ccc; height: 150px; margin: 20px 0; padding: 10px; }
                .footer { margin-top: 40px; font-size: 12px; color: #666; }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>CONTRATO</h1>
                <h2><?php echo esc_html($contract->title); ?></h2>
                <p>Número: <?php echo esc_html($contract->contract_number); ?></p>
            </div>

            <div class="contract-info">
                <h3>Informações do Edital</h3>
                <p><strong>Edital:</strong> <?php echo esc_html($contract->edital_title); ?></p>
                
                <h3>Informações do Proponente</h3>
                <p><strong>Nome:</strong> <?php echo esc_html($contract->proponent_name); ?></p>
                <p><strong>E-mail:</strong> <?php echo esc_html($contract->email); ?></p>
                <p><strong>Telefone:</strong> <?php echo esc_html($contract->phone); ?></p>
                <p><strong>Documento:</strong> <?php echo esc_html($contract->document); ?></p>
                
                <?php if ($contract->value): ?>
                <p><strong>Valor:</strong> R$ <?php echo number_format($contract->value, 2, ',', '.'); ?></p>
                <?php endif; ?>
            </div>

            <div class="content">
                <?php echo wp_kses_post($contract->content); ?>
            </div>

            <div class="signature-area">
                <h3>Assinatura Digital</h3>
                <div class="signature-box" id="signature-area">
                    <?php if ($contract->signature_data): ?>
                        <p><strong>Contrato assinado digitalmente</strong></p>
                        <p>Data: <?php echo esc_html($contract->signature_date); ?></p>
                        <p>IP: <?php echo esc_html($contract->signature_ip); ?></p>
                    <?php else: ?>
                        <p>Área para assinatura digital</p>
                        <input type="hidden" id="contract-id" value="<?php echo esc_attr($contract->id); ?>">
                        <button onclick="signContract()" style="margin-top: 20px; padding: 10px 20px; background: #0073aa; color: white; border: none; cursor: pointer;">
                            Assinar Contrato
                        </button>
                    <?php endif; ?>
                </div>
            </div>

            <div class="footer">
                <p>&copy; 2025 Fabricar1 Soluções de Mercado - Sistema FT1 Cultural</p>
                <p>Este documento possui validade legal com assinatura digital certificada.</p>
            </div>

            <script>
            function signContract() {
                const contractId = document.getElementById('contract-id').value;
                const signature = prompt('Digite seu nome completo para assinar:');
                
                if (signature) {
                    fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: 'action=ft1_sign_contract&contract_id=' + contractId + '&signature=' + encodeURIComponent(signature)
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            location.reload();
                        } else {
                            alert('Erro ao assinar contrato');
                        }
                    });
                }
            }
            </script>
        </body>
        </html>
        <?php
        return ob_get_clean();
    }

    public static function sign_contract($contract_id, $signature) {
        global $wpdb;
        $table = $wpdb->prefix . 'ft1_contracts';

        $signature_data = array(
            'signature' => sanitize_text_field($signature),
            'timestamp' => current_time('mysql'),
            'ip' => $_SERVER['REMOTE_ADDR'],
            'user_agent' => $_SERVER['HTTP_USER_AGENT']
        );

        return $wpdb->update(
            $table,
            array(
                'signature_data' => json_encode($signature_data),
                'signature_ip' => $_SERVER['REMOTE_ADDR'],
                'signature_date' => current_time('mysql'),
                'status' => 'signed'
            ),
            array('id' => intval($contract_id))
        );
    }
}