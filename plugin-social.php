<?php
/**
 * Plugin Name: Notificador de últimas compras (Pro)
 * Description: Social Proof modular: notificaciones, contador de vistas, aviso de precio dinámico y captación de correos.
 * Version: 5.7.0
 * Author: Jose Muñoz
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Rutas básicas.
define( 'JSN_SP_PATH', plugin_dir_path( __FILE__ ) );
define( 'JSN_SP_URL', plugin_dir_url( __FILE__ ) );

// Inclusiones.
require_once JSN_SP_PATH . 'includes/class-jsn-modulo-base.php';
require_once JSN_SP_PATH . 'includes/class-jsn-modulo-visitas.php';
require_once JSN_SP_PATH . 'includes/class-jsn-modulo-notificaciones.php';
require_once JSN_SP_PATH . 'includes/class-jsn-modulo-precio.php';
require_once JSN_SP_PATH . 'includes/class-jsn-modulo-newsletter.php';
require_once JSN_SP_PATH . 'includes/class-jsn-social-proof-plugin.php';

// Inicializar plugin.
JSN_Social_Proof_Plugin::instancia();
register_activation_hook( __FILE__, array( 'JSN_Social_Proof_Plugin', 'activar_plugin' ) );
