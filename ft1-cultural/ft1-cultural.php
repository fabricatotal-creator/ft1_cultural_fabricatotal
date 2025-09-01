<?php
/**
 * Plugin Name: FT1 Cultural
 * Plugin URI: https://fabricar1.com.br
 * Description: Sistema CRM robusto para gerenciamento de editais culturais, proponentes e contratos com assinatura digital
 * Version: 1.0.0
 * Author: Fabricar1 Soluções de Mercado
 * Author URI: https://fabricar1.com.br
 * License: Proprietary
 * Text Domain: ft1-cultural
 * Domain Path: /languages
 * 
 * @package FT1Cultural
 * @author Fabricar1 Soluções de Mercado
 * @copyright 2025 Fabricar1 Soluções de Mercado. Todos os direitos reservados.
 * @license Proprietary
 */

// Se este arquivo for chamado diretamente, abortar
if (!defined('WPINC')) {
    die;
}

// Definir constantes do plugin
define('FT1_CULTURAL_VERSION', '1.0.0');
define('FT1_CULTURAL_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('FT1_CULTURAL_PLUGIN_URL', plugin_dir_url(__FILE__));
define('FT1_CULTURAL_PLUGIN_FILE', __FILE__);

/**
 * Código que roda durante a ativação do plugin
 */
function activate_ft1_cultural() {
    require_once FT1_CULTURAL_PLUGIN_DIR . 'includes/class-ft1-cultural-activator.php';
    FT1_Cultural_Activator::activate();
}

/**
 * Código que roda durante a desativação do plugin
 */
function deactivate_ft1_cultural() {
    require_once FT1_CULTURAL_PLUGIN_DIR . 'includes/class-ft1-cultural-deactivator.php';
    FT1_Cultural_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_ft1_cultural');
register_deactivation_hook(__FILE__, 'deactivate_ft1_cultural');

/**
 * A classe core do plugin
 */
require FT1_CULTURAL_PLUGIN_DIR . 'includes/class-ft1-cultural.php';

/**
 * Inicia a execução do plugin
 */
function run_ft1_cultural() {
    $plugin = new FT1_Cultural();
    $plugin->run();
}
run_ft1_cultural();