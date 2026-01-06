<?php
/**
 * Clase base para los módulos.
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

abstract class JSN_Modulo_Base {
    /** @var JSN_Social_Proof_Plugin */
    protected $plugin;

    public function __construct( $plugin ) {
        $this->plugin = $plugin;
    }

    public function registrar_ajustes() {}
    public function registrar_shortcodes() {}
    public function imprimir_configuracion() {}
}
