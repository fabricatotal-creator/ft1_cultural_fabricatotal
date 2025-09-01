<?php
/**
 * Página de gerenciamento de contratos
 */

// Buscar contratos
$contracts = FT1_Cultural_Database::get_contracts();

// Buscar proponentes e editais para os selects
$proponents = FT1_Cultural_Database::get_proponents(null, 100);
$editals = FT1_Cultural_Database::get_editals(100);
?>

<div class="ft1-cultural-admin">
    <div class="ft1-header">
        <h1>📄 Gerenciar Contratos</h1>
        <p>Criação, envio e controle de contratos com assinatura digital</p>
    </div>

    <div style="margin-bottom: 20px;">
        <button class="ft1-button ft1-modal-trigger" data-modal="ft1-contract-modal">
            ➕ Novo Contrato
        </button>
    </div>

    <div class="ft1-table">
        <input type="text" class="ft1-search" placeholder="🔍 Buscar contratos..." style="margin-bottom: 20px; padding: 10px; width: 300px; border: 1px solid #ddd; border-radius: 4px;">
        
        <table>
            <thead>
                <tr>
                    <th data-sort="contract_number">🔢 Número</th>
                    <th data-sort="title">📄 Título</th>
                    <th data-sort="proponent_name">👤 Proponente</th>
                    <th data-sort="edital_title">📋 Edital</th>
                    <th data-sort="value">💰 Valor</th>
                    <th data-sort="status">📊 Status</th>
                    <th>🔧 Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($contracts)): ?>
                    <?php foreach ($contracts as $contract): ?>
                        <tr>
                            <td data-sort="contract_number">
                                <code><?php echo esc_html($contract->contract_number); ?></code>
                            </td>
                            <td data-sort="title">
                                <strong><?php echo esc_html($contract->title); ?></strong>
                                <br><small>Criado em <?php echo date('d/m/Y H:i', strtotime($contract->created_at)); ?></small>
                            </td>
                            <td data-sort="proponent_name">
                                <?php echo esc_html($contract->proponent_name); ?>
                            </td>
                            <td data-sort="edital_title">
                                <?php echo esc_html($contract->edital_title); ?>
                            </td>
                            <td data-sort="value">
                                <?php if ($contract->value): ?>
                                    R$ <?php echo number_format($contract->value, 2, ',', '.'); ?>
                                <?php else: ?>
                                    <span style="color: #999;">-</span>
                                <?php endif; ?>
                            </td>
                            <td data-sort="status">
                                <span class="ft1-status <?php echo esc_attr($contract->status); ?>">
                                    <?php 
                                    $status_labels = [
                                        'draft' => 'Rascunho',
                                        'sent' => 'Enviado',
                                        'signed' => 'Assinado'
                                    ];
                                    echo esc_html($status_labels[$contract->status] ?? ucfirst($contract->status)); 
                                    ?>
                                </span>
                                <?php if ($contract->signature_date): ?>
                                    <br><small>✅ <?php echo date('d/m/Y H:i', strtotime($contract->signature_date)); ?></small>
                                <?php elseif ($contract->sent_date): ?>
                                    <br><small>📤 <?php echo date('d/m/Y H:i', strtotime($contract->sent_date)); ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div style="display: flex; flex-wrap: wrap; gap: 5px;">
                                    <button class="ft1-button ft1-btn-edit" data-type="contract" data-id="<?php echo intval($contract->id); ?>">
                                        ✏️ Editar
                                    </button>
                                    
                                    <?php if ($contract->status !== 'signed'): ?>
                                        <button class="ft1-button success ft1-btn-send-email" data-contract-id="<?php echo intval($contract->id); ?>">
                                            📧 E-mail
                                        </button>
                                        <button class="ft1-button secondary ft1-btn-send-whatsapp" data-contract-id="<?php echo intval($contract->id); ?>">
                                            💬 WhatsApp
                                        </button>
                                    <?php endif; ?>
                                    
                                    <a href="<?php echo wp_upload_dir()['baseurl'] . '/ft1-cultural/contracts/contrato-' . $contract->contract_number . '.html'; ?>" 
                                       target="_blank" class="ft1-button" style="font-size: 12px;">
                                        👁️ Ver
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 40px;">
                            <p>Nenhum contrato criado ainda.</p>
                            <button class="ft1-button ft1-modal-trigger" data-modal="ft1-contract-modal">
                                Criar Primeiro Contrato
                            </button>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Novo/Editar Contrato -->
<div id="ft1-contract-modal" class="ft1-modal">
    <div class="ft1-modal-content" style="max-width: 800px;">
        <div class="ft1-modal-header">
            <span class="ft1-close">&times;</span>
            <h2>📄 Criar Contrato</h2>
        </div>
        <div class="ft1-modal-body">
            <form id="ft1-contract-form" class="ft1-form">
                <input type="hidden" id="contract-id" name="id" value="">
                
                <div class="ft1-form-group">
                    <label for="contract-title">Título do Contrato *</label>
                    <input type="text" id="contract-title" name="title" required placeholder="Ex: Contrato de Prestação de Serviços">
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div class="ft1-form-group">
                        <label for="contract-proponent">Proponente *</label>
                        <select id="contract-proponent" name="proponent_id" required>
                            <option value="">Selecionar proponente...</option>
                            <?php foreach ($proponents as $proponent): ?>
                                <option value="<?php echo intval($proponent->id); ?>">
                                    <?php echo esc_html($proponent->name . ' (' . $proponent->unique_id . ')'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="ft1-form-group">
                        <label for="contract-edital">Edital *</label>
                        <select id="contract-edital" name="edital_id" required>
                            <option value="">Selecionar edital...</option>
                            <?php foreach ($editals as $edital): ?>
                                <option value="<?php echo intval($edital->id); ?>">
                                    <?php echo esc_html($edital->title); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="ft1-form-group">
                    <label for="contract-value">Valor do Contrato (R$)</label>
                    <input type="text" id="contract-value" name="value" data-mask="money" placeholder="R$ 0,00">
                </div>
                
                <div class="ft1-form-group">
                    <label for="contract-content">Conteúdo do Contrato *</label>
                    <textarea id="contract-content" name="content" rows="15" required placeholder="Digite aqui o conteúdo completo do contrato..."></textarea>
                    <small style="color: #666;">
                        💡 Dica: Use variáveis como {nome_proponente}, {email_proponente}, {documento_proponente} que serão substituídas automaticamente.
                    </small>
                </div>
                
                <div style="margin-top: 30px; text-align: right;">
                    <button type="button" class="ft1-button secondary ft1-close">Cancelar</button>
                    <button type="submit" class="ft1-button" style="margin-left: 10px;">💾 Criar Contrato</button>
                </div>
            </form>
        </div>
    </div>
</div>