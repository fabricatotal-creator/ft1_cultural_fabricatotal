<?php
/**
 * Dashboard público do usuário
 */

// Verificar se o usuário está logado
if (!is_user_logged_in()) {
    echo '<div class="ft1-alert info">Faça login para acessar seu dashboard.</div>';
    return;
}

$current_user = wp_get_current_user();
$user_email = $current_user->user_email;

// Buscar proponente do usuário atual
global $wpdb;
$proponent = $wpdb->get_row($wpdb->prepare(
    "SELECT * FROM {$wpdb->prefix}ft1_proponents WHERE email = %s",
    $user_email
));

if (!$proponent) {
    echo '<div class="ft1-alert info">
        <h3>Bem-vindo ao FT1 Cultural!</h3>
        <p>Você ainda não possui um cadastro como proponente. Entre em contato com a administração para cadastrar seus dados.</p>
    </div>';
    return;
}

// Buscar contratos do proponente
$contracts = FT1_Cultural_Database::get_contracts($proponent->id);

// Buscar documentos do proponente
$documents = FT1_Cultural_Database::get_documents($proponent->id);

// Buscar edital do proponente
$edital = FT1_Cultural_Database::get_edital($proponent->edital_id);
?>

<div class="ft1-cultural-public">
    <div class="ft1-public-header">
        <h1>🎭 Meu Painel - FT1 Cultural</h1>
        <p>Bem-vindo ao seu espaço pessoal de gerenciamento</p>
    </div>

    <div class="ft1-public-nav">
        <ul class="ft1-nav-tabs">
            <li class="ft1-nav-tab active">
                <a href="#dashboard">📊 Dashboard</a>
            </li>
            <li class="ft1-nav-tab">
                <a href="#profile">👤 Meu Perfil</a>
            </li>
            <li class="ft1-nav-tab">
                <a href="#contracts">📄 Contratos</a>
            </li>
            <li class="ft1-nav-tab">
                <a href="#documents">📎 Documentos</a>
            </li>
        </ul>
    </div>

    <!-- Aba Dashboard -->
    <div id="dashboard" class="ft1-tab-content active">
        <div class="ft1-public-content">
            <h2>📊 Visão Geral</h2>
            
            <div class="ft1-stats-grid">
                <div class="ft1-stat-card">
                    <span class="ft1-stat-number"><?php echo count($contracts); ?></span>
                    <span class="ft1-stat-label">Contratos</span>
                </div>
                
                <div class="ft1-stat-card">
                    <span class="ft1-stat-number"><?php echo count($documents); ?></span>
                    <span class="ft1-stat-label">Documentos</span>
                </div>
                
                <div class="ft1-stat-card">
                    <span class="ft1-stat-number">
                        <?php 
                        $signed = array_filter($contracts, function($c) { return $c->status === 'signed'; });
                        echo count($signed);
                        ?>
                    </span>
                    <span class="ft1-stat-label">Assinados</span>
                </div>
                
                <div class="ft1-stat-card">
                    <span class="ft1-stat-number">1</span>
                    <span class="ft1-stat-label">Edital Ativo</span>
                </div>
            </div>

            <?php if ($edital): ?>
            <div class="ft1-edital-card" style="margin-top: 30px;">
                <div class="ft1-edital-header">
                    <h3 class="ft1-edital-title">📋 Seu Edital</h3>
                </div>
                <div class="ft1-edital-body">
                    <h4><?php echo esc_html($edital->title); ?></h4>
                    <?php if ($edital->description): ?>
                        <p class="ft1-edital-description"><?php echo esc_html($edital->description); ?></p>
                    <?php endif; ?>
                    
                    <div class="ft1-edital-meta">
                        <div class="ft1-edital-meta-item">
                            <div class="ft1-edital-meta-label">Orçamento</div>
                            <div class="ft1-edital-meta-value">R$ <?php echo number_format($edital->budget, 2, ',', '.'); ?></div>
                        </div>
                        <div class="ft1-edital-meta-item">
                            <div class="ft1-edital-meta-label">Status</div>
                            <div class="ft1-edital-meta-value"><?php echo ucfirst($edital->status); ?></div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Aba Perfil -->
    <div id="profile" class="ft1-tab-content">
        <div class="ft1-public-content">
            <div class="ft1-profile-avatar">
                <?php echo strtoupper(substr($proponent->name, 0, 2)); ?>
            </div>
            
            <div class="ft1-profile-info">
                <h2 class="ft1-profile-name"><?php echo esc_html($proponent->name); ?></h2>
                <p class="ft1-profile-email"><?php echo esc_html($proponent->email); ?></p>
                <p><strong>ID:</strong> <code><?php echo esc_html($proponent->unique_id); ?></code></p>
            </div>

            <div class="ft1-form">
                <h3>📝 Informações Pessoais</h3>
                
                <div class="ft1-form-group">
                    <label>Nome Completo</label>
                    <input type="text" value="<?php echo esc_attr($proponent->name); ?>" readonly>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div class="ft1-form-group">
                        <label>E-mail</label>
                        <input type="email" value="<?php echo esc_attr($proponent->email); ?>" readonly>
                    </div>
                    <div class="ft1-form-group">
                        <label>Telefone</label>
                        <input type="text" value="<?php echo esc_attr($proponent->phone); ?>" readonly>
                    </div>
                </div>
                
                <div class="ft1-form-group">
                    <label>Documento</label>
                    <input type="text" value="<?php echo esc_attr($proponent->document); ?>" readonly>
                </div>
                
                <div class="ft1-form-group">
                    <label>Endereço</label>
                    <textarea readonly rows="3"><?php echo esc_textarea($proponent->address); ?></textarea>
                </div>
                
                <div class="ft1-alert info">
                    <p>📝 Para alterar seus dados pessoais, entre em contato com a administração.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Aba Contratos -->
    <div id="contracts" class="ft1-tab-content">
        <div class="ft1-public-content">
            <h2>📄 Meus Contratos</h2>
            
            <?php if (!empty($contracts)): ?>
                <div class="ft1-contracts-list">
                    <?php foreach ($contracts as $contract): ?>
                        <div class="ft1-contract-item">
                            <div class="ft1-contract-header">
                                <h3 class="ft1-contract-title"><?php echo esc_html($contract->title); ?></h3>
                                <span class="ft1-contract-status <?php echo esc_attr($contract->status); ?>">
                                    <?php 
                                    $status_labels = [
                                        'draft' => 'Rascunho',
                                        'sent' => 'Enviado',
                                        'signed' => 'Assinado'
                                    ];
                                    echo esc_html($status_labels[$contract->status] ?? ucfirst($contract->status)); 
                                    ?>
                                </span>
                            </div>
                            
                            <div class="ft1-contract-meta">
                                <strong>Número:</strong> <?php echo esc_html($contract->contract_number); ?> |
                                <?php if ($contract->value): ?>
                                    <strong>Valor:</strong> R$ <?php echo number_format($contract->value, 2, ',', '.'); ?> |
                                <?php endif; ?>
                                <strong>Criado em:</strong> <?php echo date('d/m/Y H:i', strtotime($contract->created_at)); ?>
                            </div>
                            
                            <?php if ($contract->status === 'signed' && $contract->signature_date): ?>
                                <div class="ft1-alert success">
                                    ✅ <strong>Contrato assinado digitalmente</strong><br>
                                    Data: <?php echo date('d/m/Y H:i', strtotime($contract->signature_date)); ?><br>
                                    IP: <?php echo esc_html($contract->signature_ip); ?>
                                </div>
                            <?php endif; ?>
                            
                            <div style="margin-top: 20px;">
                                <a href="<?php echo wp_upload_dir()['baseurl'] . '/ft1-cultural/contracts/contrato-' . $contract->contract_number . '.html'; ?>" 
                                   target="_blank" class="ft1-button">
                                    👁️ Visualizar Contrato
                                </a>
                                
                                <?php if ($contract->status !== 'signed'): ?>
                                    <button class="ft1-button success ft1-sign-contract" 
                                            data-contract-id="<?php echo intval($contract->id); ?>"
                                            style="margin-left: 10px;">
                                        ✍️ Assinar Digitalmente
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="ft1-alert info">
                    <h3>📄 Nenhum contrato encontrado</h3>
                    <p>Você ainda não possui contratos. Eles aparecerão aqui quando forem criados pela administração.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Aba Documentos -->
    <div id="documents" class="ft1-tab-content">
        <div class="ft1-public-content">
            <h2>📎 Meus Documentos</h2>
            
            <?php if (!empty($documents)): ?>
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 20px; margin-top: 30px;">
                    <?php foreach ($documents as $document): ?>
                        <div class="ft1-document-card" style="background: white; border: 1px solid #e0e0e0; border-radius: 8px; padding: 20px; text-align: center;">
                            <div style="font-size: 48px; margin-bottom: 15px;">
                                <?php
                                $ext = pathinfo($document->filename, PATHINFO_EXTENSION);
                                $icon = '📄';
                                if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) $icon = '🖼️';
                                elseif ($ext === 'pdf') $icon = '📕';
                                elseif (in_array($ext, ['doc', 'docx'])) $icon = '📘';
                                echo $icon;
                                ?>
                            </div>
                            <h4 style="margin: 0 0 10px 0; font-size: 14px;"><?php echo esc_html($document->filename); ?></h4>
                            <p style="color: #666; font-size: 12px; margin: 0 0 15px 0;">
                                Enviado em <?php echo date('d/m/Y H:i', strtotime($document->uploaded_at)); ?>
                            </p>
                            <a href="<?php echo esc_url($document->file_path); ?>" 
                               target="_blank" 
                               class="ft1-button secondary" 
                               style="font-size: 12px; padding: 8px 16px;">
                                👁️ Visualizar
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="ft1-alert info">
                    <h3>📎 Nenhum documento encontrado</h3>
                    <p>Você ainda não possui documentos enviados. Entre em contato com a administração para enviar seus documentos.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div style="margin-top: 50px; text-align: center; padding: 30px; background: #f8f9fa; border-radius: 12px;">
        <p style="color: #666; margin: 0; font-size: 14px;">
            <strong>FT1 Cultural</strong> - Sistema desenvolvido por <strong>Fabricar1 Soluções de Mercado</strong><br>
            © 2025 Todos os direitos reservados
        </p>
    </div>
</div>

<style>
.ft1-tab-content {
    display: none;
}
.ft1-tab-content.active {
    display: block;
}
</style>