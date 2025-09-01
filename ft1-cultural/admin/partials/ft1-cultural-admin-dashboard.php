<?php
/**
 * Dashboard administrativo
 */

// Buscar estatísticas
global $wpdb;

$editals_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}ft1_editals WHERE status = 'active'");
$proponents_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}ft1_proponents");
$contracts_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}ft1_contracts");
$signed_contracts = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}ft1_contracts WHERE status = 'signed'");

// Buscar editais recentes
$recent_editals = FT1_Cultural_Database::get_editals(5);

// Buscar contratos recentes
$recent_contracts = FT1_Cultural_Database::get_contracts(null, 5);
?>

<div class="ft1-cultural-admin">
    <div class="ft1-header">
        <h1>🎭 FT1 Cultural - Dashboard</h1>
        <p>Sistema de Gerenciamento de Editais Culturais</p>
        <small>Desenvolvido por <strong>Fabricar1 Soluções de Mercado</strong></small>
    </div>

    <div class="ft1-stats">
        <div class="ft1-stat-card">
            <span class="ft1-stat-number"><?php echo intval($editals_count); ?></span>
            <span class="ft1-stat-label">Editais Ativos</span>
        </div>
        
        <div class="ft1-stat-card">
            <span class="ft1-stat-number"><?php echo intval($proponents_count); ?></span>
            <span class="ft1-stat-label">Proponentes Cadastrados</span>
        </div>
        
        <div class="ft1-stat-card">
            <span class="ft1-stat-number"><?php echo intval($contracts_count); ?></span>
            <span class="ft1-stat-label">Contratos Gerados</span>
        </div>
        
        <div class="ft1-stat-card">
            <span class="ft1-stat-number"><?php echo intval($signed_contracts); ?></span>
            <span class="ft1-stat-label">Contratos Assinados</span>
        </div>
    </div>

    <div class="ft1-grid">
        <div class="ft1-card">
            <div class="ft1-card-header">
                <h3>📋 Editais Recentes</h3>
            </div>
            <div class="ft1-card-body">
                <?php if (!empty($recent_editals)): ?>
                    <ul style="list-style: none; padding: 0;">
                        <?php foreach ($recent_editals as $edital): ?>
                            <li style="padding: 10px 0; border-bottom: 1px solid #eee;">
                                <strong><?php echo esc_html($edital->title); ?></strong><br>
                                <small>
                                    💰 R$ <?php echo number_format($edital->budget, 2, ',', '.'); ?> | 
                                    📅 <?php echo date('d/m/Y', strtotime($edital->start_date)); ?>
                                </small>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <a href="<?php echo admin_url('admin.php?page=ft1-editals'); ?>" class="ft1-button" style="margin-top: 15px;">
                        Ver Todos os Editais
                    </a>
                <?php else: ?>
                    <p>Nenhum edital cadastrado ainda.</p>
                    <a href="<?php echo admin_url('admin.php?page=ft1-editals'); ?>" class="ft1-button">
                        Criar Primeiro Edital
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <div class="ft1-card">
            <div class="ft1-card-header">
                <h3>📄 Contratos Recentes</h3>
            </div>
            <div class="ft1-card-body">
                <?php if (!empty($recent_contracts)): ?>
                    <ul style="list-style: none; padding: 0;">
                        <?php foreach ($recent_contracts as $contract): ?>
                            <li style="padding: 10px 0; border-bottom: 1px solid #eee;">
                                <strong><?php echo esc_html($contract->title); ?></strong><br>
                                <small>
                                    👤 <?php echo esc_html($contract->proponent_name); ?> | 
                                    <span class="ft1-status <?php echo esc_attr($contract->status); ?>">
                                        <?php echo esc_html(ucfirst($contract->status)); ?>
                                    </span>
                                </small>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <a href="<?php echo admin_url('admin.php?page=ft1-contracts'); ?>" class="ft1-button" style="margin-top: 15px;">
                        Ver Todos os Contratos
                    </a>
                <?php else: ?>
                    <p>Nenhum contrato gerado ainda.</p>
                    <a href="<?php echo admin_url('admin.php?page=ft1-contracts'); ?>" class="ft1-button">
                        Criar Primeiro Contrato
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="ft1-card" style="margin-top: 30px;">
        <div class="ft1-card-header">
            <h3>🚀 Ações Rápidas</h3>
        </div>
        <div class="ft1-card-body">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                <a href="<?php echo admin_url('admin.php?page=ft1-editals'); ?>" class="ft1-button">
                    📋 Gerenciar Editais
                </a>
                <a href="<?php echo admin_url('admin.php?page=ft1-proponents'); ?>" class="ft1-button secondary">
                    👥 Gerenciar Proponentes
                </a>
                <a href="<?php echo admin_url('admin.php?page=ft1-contracts'); ?>" class="ft1-button success">
                    📄 Gerenciar Contratos
                </a>
            </div>
        </div>
    </div>

    <div style="margin-top: 30px; text-align: center; color: #666; font-size: 14px;">
        <p>
            <strong>FT1 Cultural v<?php echo FT1_CULTURAL_VERSION; ?></strong><br>
            &copy; 2025 <strong>Fabricar1 Soluções de Mercado</strong> - Todos os direitos reservados<br>
            Sistema proprietário para gerenciamento de editais culturais
        </p>
    </div>
</div>