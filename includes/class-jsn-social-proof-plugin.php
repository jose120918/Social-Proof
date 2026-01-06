<?php
/**
 * Núcleo del plugin.
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class JSN_Social_Proof_Plugin {
    /** @var JSN_Social_Proof_Plugin */
    private static $instancia = null;

    /** @var array */
    private $modulos = array();

    public static function instancia() {
        if ( null === self::$instancia ) {
            self::$instancia = new self();
        }
        return self::$instancia;
    }

    private function __construct() {
        $this->modulos = array(
            'visitas'    => new JSN_Modulo_Visitas( $this ),
            'popup'      => new JSN_Modulo_Notificaciones( $this ),
            'precio'     => new JSN_Modulo_Precio( $this ),
            'newsletter' => new JSN_Modulo_Newsletter( $this ),
        );

        add_action( 'admin_menu', array( $this, 'registrar_menu' ) );
        add_action( 'admin_init', array( $this, 'registrar_ajustes' ) );
        add_action( 'init', array( $this, 'registrar_shortcodes' ) );
        add_action( 'admin_post_jsn_exportar_correos', array( $this, 'exportar_correos' ) );
    }

    public static function activar_plugin() {
        global $wpdb;
        $tabla   = $wpdb->prefix . 'jsn_newsletter';
        $collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE {$tabla} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            email VARCHAR(190) NOT NULL,
            created_at DATETIME NOT NULL,
            ip_address VARCHAR(100) NULL,
            PRIMARY KEY (id),
            UNIQUE KEY email (email)
        ) {$collate};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );
    }

    public function registrar_menu() {
        $titulo = 'Notificador de últimas compras';
        add_menu_page(
            $titulo,
            $titulo,
            'manage_options',
            'jsn-social-proof',
            array( $this, 'render_pagina_config' ),
            'dashicons-megaphone',
            56
        );
    }

    public function registrar_ajustes() {
        foreach ( $this->modulos as $modulo ) {
            $modulo->registrar_ajustes();
        }
    }

    public function registrar_shortcodes() {
        foreach ( $this->modulos as $modulo ) {
            $modulo->registrar_shortcodes();
        }
    }

    public function render_pagina_config() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
        ?>
        <div class="wrap jsn-admin">
            <h1>Notificador de últimas compras</h1>
            <p class="description">Panel central para activar/desactivar módulos y ajustar textos, estilos y plantillas.</p>
            <div class="jsn-banner">
                <div>
                    <strong>Panel central</strong>
                    <p>Controla visitas, popup, aviso de precios y newsletter desde aquí.</p>
                </div>
                <div class="jsn-badges">
                    <span class="jsn-badge">Versión 5.4.0</span>
                </div>
            </div>

            <style>
                .jsn-admin .jsn-banner { display:flex; justify-content:space-between; align-items:center; padding:14px 16px; background:#f0f6ff; border:1px solid #b6d1ff; border-radius:8px; margin:12px 0 20px; }
                .jsn-badges .jsn-badge { background:#111827; color:#fff; padding:6px 10px; border-radius:14px; font-size:12px; }
                .jsn-card { background: #fff; border: 1px solid #c3c4c7; box-shadow: 0 1px 1px rgba(0,0,0,.04); padding: 18px; margin-bottom: 18px; border-radius: 8px; }
                .jsn-card-head { display:flex; justify-content:space-between; align-items:center; gap:16px; border-bottom:1px solid #f0f0f1; padding-bottom:10px; margin-bottom:12px; }
                .jsn-eyebrow { text-transform:uppercase; letter-spacing:0.4px; font-size:11px; color:#6b7280; margin:0 0 4px 0; }
                .jsn-grid { display:grid; grid-template-columns:repeat(auto-fit, minmax(260px, 1fr)); gap:16px; }
                .jsn-inline { display:flex; flex-wrap:wrap; gap:10px; align-items:center; }
                .jsn-toggle { display:flex; align-items:center; gap:8px; font-weight:600; }
                .jsn-toggle input { transform: scale(1.2); }
                .jsn-foot-note { margin-top:10px; font-size:12px; color:#4b5563; display:flex; gap:10px; flex-wrap:wrap; align-items:center; }
                .jsn-admin .description { color:#4b5563; }
            </style>

            <form method="post" action="options.php">
                <?php settings_fields( 'jsn_group' ); ?>
                <?php
                foreach ( $this->modulos as $modulo ) {
                    $modulo->imprimir_configuracion();
                }
                submit_button( 'Guardar cambios' );
                ?>
            </form>
        </div>
        <?php
    }

    public function obtener_tabla_correos() {
        global $wpdb;
        return $wpdb->prefix . 'jsn_newsletter';
    }

    public function exportar_correos() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'No autorizado.', 'jsn' ) );
        }

        if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'jsn_exportar_correos' ) ) {
            wp_die( esc_html__( 'Nonce inválido.', 'jsn' ) );
        }

        global $wpdb;
        $tabla = $this->obtener_tabla_correos();
        $datos = $wpdb->get_results( "SELECT email, created_at, ip_address FROM {$tabla} ORDER BY created_at DESC", ARRAY_A );

        header( 'Content-Type: text/csv; charset=utf-8' );
        header( 'Content-Disposition: attachment; filename="jsn_correos.csv"' );
        $salida = fopen( 'php://output', 'w' );
        fputcsv( $salida, array( 'email', 'fecha', 'ip' ) );

        foreach ( $datos as $fila ) {
            fputcsv( $salida, $fila );
        }

        fclose( $salida );
        exit;
    }
}
