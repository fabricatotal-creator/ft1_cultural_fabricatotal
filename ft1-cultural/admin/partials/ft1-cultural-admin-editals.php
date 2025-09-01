<?php
/**
 * Página de gerenciamento de editais
 */

// Buscar editais
$editals = FT1_Cultural_Database::get_editals();
?>

<div class="ft1-cultural-admin">
    <div class="ft1-header">
        <h1>📋 Gerenciar Editais</h1>
        <p>Cadastro e controle de editais culturais</p>
    </div>

    <div style="margin-bottom: 20px;">
        <button class="ft1-button ft1-modal-trigger" data-modal="ft1-edital-modal">
            ➕ Novo Edital
        </button>
    </div>

    <div class="ft1-table">
        <input type="text" class="ft1-search" placeholder="🔍 Buscar editais..." style="margin-bottom: 20px; padding: 10px; width: 300px; border: 1px solid #ddd; border-radius: 4px;">
        
        <table>
            <thead>
                <tr>
                    <th data-sort="title">📋 Título</th>
                    <th data-sort="budget">💰 Orçamento</th>
                    <th data-sort="start_date">📅 Início</th>
                    <th data-sort="end_date">📅 Fim</th>
                    <th data-sort="status">📊 Status</th>
                    <th>👥 Proponentes</th>
                    <th>🔧 Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($editals)): ?>
                    <?php foreach ($editals as $edital): ?>
                        <?php $stats = FT1_Cultural_Editals::get_edital_stats($edital->id); ?>
                        <tr>
                            <td data-sort="title">
                                <strong><?php echo esc_html($edital->title); ?></strong>
                                <?php if ($edital->description): ?>
                                    <br><small><?php echo esc_html(wp_trim_words($edital->description, 10)); ?></small>
                                <?php endif; ?>
                            </td>
                            <td data-sort="budget">
                                R$ <?php echo number_format($edital->budget, 2, ',', '.'); ?>
                            </td>
                            <td data-sort="start_date">
                                <?php echo date('d/m/Y', strtotime($edital->start_date)); ?>
                            </td>
                            <td data-sort="end_date">
                                <?php echo date('d/m/Y', strtotime($edital->end_date)); ?>
                            </td>
                            <td data-sort="status">
                                <span class="ft1-status <?php echo esc_attr($edital->status); ?>">
                                    <?php echo esc_html(ucfirst($edital->status)); ?>
                                </span>
                            </td>
                            <td>
                                <strong><?php echo intval($stats['proponents']); ?></strong> proponentes<br>
                                <small><?php echo intval($stats['contracts']); ?> contratos (<?php echo intval($stats['signed_contracts']); ?> assinados)</small>
                            </td>
                            <td>
                                <button class="ft1-button ft1-btn-edit" data-type="edital" data-id="<?php echo intval($edital->id); ?>" style="margin-right: 5px;">
                                    ✏️ Editar
                                </button>
                                <a href="<?php echo admin_url('admin.php?page=ft1-proponents&edital=' . $edital->id); ?>" class="ft1-button secondary">
                                    👥 Ver Proponentes
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 40px;">
                            <p>Nenhum edital cadastrado ainda.</p>
                            <button class="ft1-button ft1-modal-trigger" data-modal="ft1-edital-modal">
                                Criar Primeiro Edital
                            </button>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Novo/Editar Edital -->
<div id="ft1-edital-modal" class="ft1-modal">
    <div class="ft1-modal-content">
        <div class="ft1-modal-header">
            <span class="ft1-close">&times;</span>
            <h2>📋 Cadastrar Edital</h2>
        </div>
        <div class="ft1-modal-body">
            <form id="ft1-edital-form" class="ft1-form">
                <input type="hidden" id="edital-id" name="id" value="">
                
                <div class="ft1-form-group">
                    <label for="edital-title">Título do Edital *</label>
                    <input type="text" id="edital-title" name="title" required>
                </div>
                
                <div class="ft1-form-group">
                    <label for="edital-description">Descrição</label>
                    <textarea id="edital-description" name="description" rows="4"></textarea>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div class="ft1-form-group">
                        <label for="edital-start-date">Data de Início *</label>
                        <input type="date" id="edital-start-date" name="start_date" required>
                    </div>
                    
                    <div class="ft1-form-group">
                        <label for="edital-end-date">Data de Fim *</label>
                        <input type="date" id="edital-end-date" name="end_date" required>
                    </div>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div class="ft1-form-group">
                        <label for="edital-budget">Orçamento (R$)</label>
                        <input type="text" id="edital-budget" name="budget" data-mask="money" placeholder="0,00">
                    </div>
                    
                    <div class="ft1-form-group">
                        <label for="edital-status">Status</label>
                        <select id="edital-status" name="status">
                            <option value="active">Ativo</option>
                            <option value="inactive">Inativo</option>
                            <option value="finished">Finalizado</option>
                        </select>
                    </div>
                </div>
                
                <div style="margin-top: 30px; text-align: right;">
                    <button type="button" class="ft1-button secondary ft1-close">Cancelar</button>
                    <button type="submit" class="ft1-button" style="margin-left: 10px;">💾 Salvar Edital</button>
                </div>
            </form>
        </div>
    </div>
</div>