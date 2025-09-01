<?php
/**
 * Classe para envio de e-mails e WhatsApp
 */
class FT1_Cultural_Email {

    public static function send_contract_email($contract_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'ft1_contracts';
        
        $contract = $wpdb->get_row($wpdb->prepare(
            "SELECT c.*, p.name as proponent_name, p.email
             FROM $table c
             LEFT JOIN {$wpdb->prefix}ft1_proponents p ON c.proponent_id = p.id
             WHERE c.id = %d",
            $contract_id
        ));

        if (!$contract || !$contract->email) {
            return false;
        }

        // Gerar PDF do contrato
        $pdf_data = FT1_Cultural_Contracts::generate_pdf($contract_id);
        
        if (!$pdf_data) {
            return false;
        }

        $subject = 'Contrato para Assinatura - ' . $contract->title;
        $message = self::get_email_template($contract, $pdf_data['url']);
        
        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . get_bloginfo('name') . ' <' . get_option('admin_email') . '>'
        );

        $sent = wp_mail($contract->email, $subject, $message, $headers);

        if ($sent) {
            // Atualizar status do contrato
            $wpdb->update(
                $table,
                array(
                    'sent_date' => current_time('mysql'), 
                    'status' => 'sent'
                ),
                array('id' => $contract_id)
            );
        }

        return $sent;
    }

    private static function get_email_template($contract, $pdf_url) {
        ob_start();
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #0073aa; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; background: #f9f9f9; }
                .button { background: #0073aa; color: white; padding: 15px 30px; text-decoration: none; display: inline-block; margin: 20px 0; border-radius: 5px; }
                .footer { text-align: center; margin-top: 30px; font-size: 12px; color: #666; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>FT1 Cultural</h1>
                    <p>Sistema de Gerenciamento de Editais</p>
                </div>
                
                <div class="content">
                    <h2>Olá, <?php echo esc_html($contract->proponent_name); ?>!</h2>
                    
                    <p>Você possui um contrato para assinatura digital:</p>
                    
                    <ul>
                        <li><strong>Contrato:</strong> <?php echo esc_html($contract->title); ?></li>
                        <li><strong>Número:</strong> <?php echo esc_html($contract->contract_number); ?></li>
                        <?php if ($contract->value): ?>
                        <li><strong>Valor:</strong> R$ <?php echo number_format($contract->value, 2, ',', '.'); ?></li>
                        <?php endif; ?>
                    </ul>
                    
                    <p>Clique no botão abaixo para visualizar e assinar o contrato:</p>
                    
                    <a href="<?php echo esc_url($pdf_url); ?>" class="button">Visualizar e Assinar Contrato</a>
                    
                    <p><strong>Importante:</strong> Este contrato possui validade legal e sua assinatura digital será certificada com dados de IP e timestamp para garantir autenticidade.</p>
                </div>
                
                <div class="footer">
                    <p>&copy; 2025 Fabricar1 Soluções de Mercado</p>
                    <p>Sistema FT1 Cultural - Todos os direitos reservados</p>
                </div>
            </div>
        </body>
        </html>
        <?php
        return ob_get_clean();
    }

    public static function get_whatsapp_link($contract_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'ft1_contracts';
        
        $contract = $wpdb->get_row($wpdb->prepare(
            "SELECT c.*, p.name as proponent_name, p.phone
             FROM $table c
             LEFT JOIN {$wpdb->prefix}ft1_proponents p ON c.proponent_id = p.id
             WHERE c.id = %d",
            $contract_id
        ));

        if (!$contract || !$contract->phone) {
            return false;
        }

        $pdf_data = FT1_Cultural_Contracts::generate_pdf($contract_id);
        
        if (!$pdf_data) {
            return false;
        }

        $phone = preg_replace('/[^0-9]/', '', $contract->phone);
        if (strlen($phone) === 11) {
            $phone = '55' . $phone;
        } elseif (strlen($phone) === 10) {
            $phone = '55' . $phone;
        }

        $message = "Olá *{$contract->proponent_name}*!\n\n";
        $message .= "Você possui um contrato para assinatura digital:\n\n";
        $message .= "📋 *Contrato:* {$contract->title}\n";
        $message .= "🔢 *Número:* {$contract->contract_number}\n";
        
        if ($contract->value) {
            $message .= "💰 *Valor:* R$ " . number_format($contract->value, 2, ',', '.') . "\n";
        }
        
        $message .= "\n🔗 *Link para assinatura:*\n{$pdf_data['url']}\n\n";
        $message .= "⚠️ *Importante:* Este contrato possui validade legal com assinatura digital certificada.\n\n";
        $message .= "---\n*FT1 Cultural* - Fabricar1 Soluções";

        $whatsapp_url = "https://wa.me/{$phone}?text=" . urlencode($message);
        
        return $whatsapp_url;
    }
}