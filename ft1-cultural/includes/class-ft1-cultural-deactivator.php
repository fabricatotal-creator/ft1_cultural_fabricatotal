<?php
/**
 * Classe responsável pela desativação do plugin
 */
class FT1_Cultural_Deactivator {

    public static function deactivate() {
        // Limpar caches se necessário
        wp_cache_flush();
    }
}