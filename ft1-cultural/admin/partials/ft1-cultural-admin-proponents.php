<?php
/**
 * Página de gerenciamento de proponentes
 */

// Buscar edital se especificado
$edital_id = isset($_GET['edital']) ? intval($_GET['edital']) : null;
$edital = null;

if ($edital_id) {
    $edital = FT1_Cultural_Database::get_edital($edital_id);
}

// Buscar proponentes
$proponents = FT1_Cultural_Database::get_proponents($edital_id);

// Buscar todos os editais para o select
$all_editals = FT1_Cultural_Database::get_editals(100);
?>

<div class="ft1-cultural-admin">
    <div class="ft1-header">
        <h1>👥 Gerenciar Proponentes</h1>
        <?php if ($edital): ?>
            <p>Proponentes do edital: <strong><?php echo esc_html($edital->title); ?></strong></p>
        <?php else: ?>
            <p>Cadastro e controle de proponentes</p>
        <?php endif; ?>
    </div>

    <div style="margin-bottom: 20px;">
        <button class="ft1-button ft1-modal-trigger" data-modal="ft1-proponent-modal">
            ➕ Novo Proponente
        </button>
        
        <?php if ($edital): ?>
            <a href="<?php echo admin_url('admin.php?page=ft1-proponents'); ?>" class="ft1-button secondary" style="margin-left: 10px;">
                📋 Ver Todos os Proponentes
            </a>
        <?php endif; ?>
    </div>

    <div class="ft1-table">
        <input type="text" class="ft1-search" placeholder="🔍 Buscar proponentes..." style="margin-bottom: 20px; padding: 10px; width: 300px; border: 1px solid #ddd; border-radius: 4px;">
        
        <table>
            <thead>
                <tr>
                    <th data-sort="unique_id">🆔 ID Único</th>
                    <th data-sort="name">👤 Nome</th>
                    <th data-sort="email">📧 E-mail</th>
                    <th data-sort="phone">📞 Telefone</th>
                    <th>📋 Edital</th>
                    <th>📄 Documentos</th>
                    <th>🔧 Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($proponents)): ?>
                    <?php foreach ($proponents as $proponent): ?>
                        <?php 
                        $documents = FT1_Cultural_Database::get_documents($proponent->id);
                        $proponent_edital = FT1_Cultural_Database::get_edital($proponent->edital_id);
                        ?>
                        <tr>
                            <td data-sort="unique_id">
                                <code><?php echo esc_html($proponent->unique_id); ?></code>
                            </td>
                            <td data-sort="name">
                                <strong><?php echo esc_html($proponent->name); ?></strong>
                                <?php if ($proponent->document): ?>
                                    <br><small>📄 <?php echo esc_html($proponent->document); ?></small>
                                <?php endif; ?>
                            </td>
                            <td data-sort="email">
                                <a href="mailto:<?php echo esc_attr($proponent->email); ?>">
                                    <?php echo esc_html($proponent->email); ?>
                                </a>
                            </td>
                            <td data-sort="phone">
                                <?php if ($proponent->phone): ?>
                                    <a href="tel:<?php echo esc_attr($proponent->phone); ?>">
                                        <?php echo esc_html($proponent->phone); ?>
                                    </a>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($proponent_edital): ?>
                                    <a href="<?php echo admin_url('admin.php?page=ft1-proponents&edital=' . $proponent_edital->id); ?>">
                                        <?php echo esc_html($proponent_edital->title); ?>
                                    </a>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong><?php echo count($documents); ?></strong> documento(s)
                                <?php if (count($documents) > 0): ?>
                                    <br><small>Último: <?php echo date('d/m/Y H:i', strtotime($documents[0]->uploaded_at)); ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <button class="ft1-button ft1-btn-edit" data-type="proponent" data-id="<?php echo intval($proponent->id); ?>" style="margin-right: 5px;">
                                    ✏️ Editar
                                </button>
                                <button class="ft1-button secondary ft1-modal-trigger" data-modal="ft1-documents-modal" data-proponent-id="<?php echo intval($proponent->id); ?>">
                                    📄 Docs
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 40px;">
                            <p>Nenhum proponente cadastrado ainda.</p>
                            <button class="ft1-button ft1-modal-trigger" data-modal="ft1-proponent-modal">
                                Cadastrar Primeiro Proponente
                            </button>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Novo/Editar Proponente -->
<div id="ft1-proponent-modal" class="ft1-modal">
    <div class="ft1-modal-content">
        <div class="ft1-modal-header">
            <span class="ft1-close">&times;</span>
            <h2>👤 Cadastrar Proponente</h2>
        </div>
        <div class="ft1-modal-body">
            <form id="ft1-proponent-form" class="ft1-form">
                <input type="hidden" id="proponent-id" name="id" value="">
                
                <div class="ft1-form-group">
                    <label for="proponent-name">Nome Completo *</label>
                    <input type="text" id="proponent-name" name="name" required>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div class="ft1-form-group">
                        <label for="proponent-email">E-mail *</label>
                        <input type="email" id="proponent-email" name="email" required>
                    </div>
                    
                    <div class="ft1-form-group">
                        <label for="proponent-phone">Telefone</label>
                        <input type="text" id="proponent-phone" name="phone" data-mask="phone" placeholder="(00) 00000-0000">
                    </div>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div class="ft1-form-group">
                        <label for="proponent-document">CPF/CNPJ</label>
                        <input type="text" id="proponent-document" name="document" placeholder="000.000.000-00">
                    </div>
                    
                    <div class="ft1-form-group">
                        <label for="proponent-edital">Edital</label>
                        <select id="proponent-edital" name="edital_id">
                            <option value="">Selecionar edital...</option>
                            <?php foreach ($all_editals as $edital_option): ?>
                                <option value="<?php echo intval($edital_option->id); ?>" <?php echo $edital_id == $edital_option->id ? 'selected' : ''; ?>>
                                    <?php echo esc_html($edital_option->title); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="ft1-form-group">
                    <label for="proponent-address">Endereço</label>
                    <textarea id="proponent-address" name="address" rows="3"></textarea>
                </div>
                
                <div style="margin-top: 30px; text-align: right;">
                    <button type="button" class="ft1-button secondary ft1-close">Cancelar</button>
                    <button type="submit" class="ft1-button" style="margin-left: 10px;">💾 Salvar Proponente</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Documentos -->
<div id="ft1-documents-modal" class="ft1-modal">
    <div class="ft1-modal-content">
        <div class="ft1-modal-header">
            <span class="ft1-close">&times;</span>
            <h2>📄 Gerenciar Documentos</h2>
        </div>
        <div class="ft1-modal-body">
            <div class="ft1-file-upload">
                <input type="file" name="document" multiple accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                <p>📎 Clique aqui para enviar documentos</p>
                <small>Formatos aceitos: PDF, DOC, DOCX, JPG, PNG</small>
            </div>
            
            <div id="documents-list" style="margin-top: 20px;">
                <!-- Lista de documentos será carregada via JavaScript -->
            </div>
        </div>
    </div>
</div>