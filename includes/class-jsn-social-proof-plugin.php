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
            <div class="jsn-hero">
                <div>
                    <p class="jsn-eyebrow">Panel central</p>
                    <h1>Notificador de últimas compras</h1>
                    <p class="description">Controla visitas, popup, aviso de precios y newsletter en un solo lugar.</p>
                    <div class="jsn-tags">
                        <span class="jsn-tag">Versión 5.8.1</span>
                        <span class="jsn-tag jsn-soft">WooCommerce</span>
                        <span class="jsn-tag jsn-soft">Shortcodes</span>
                    </div>
                </div>
                <div class="jsn-hero-actions">
                    <div class="jsn-tip" data-tip="Usa este panel para activar/desactivar y ajustar textos, colores y plantillas.">?</div>
                </div>
            </div>

            <style>
                .jsn-admin { max-width: 1400px; }
                .jsn-admin .jsn-hero { display:flex; flex-wrap:wrap; justify-content:space-between; align-items:flex-start; padding:18px 20px; background:linear-gradient(135deg, #f5f7ff 0%, #eef2ff 60%, #ffffff 100%); border:1px solid #e5e7eb; border-radius:14px; margin:0 0 18px; box-shadow:0 10px 30px rgba(0,0,0,0.04); gap:12px; }
                .jsn-tags { display:flex; gap:8px; margin-top:8px; flex-wrap:wrap; }
                .jsn-tag { background:#111827; color:#fff; padding:6px 10px; border-radius:999px; font-size:12px; font-weight:600; }
                .jsn-tag.jsn-soft { background:#e5e7eb; color:#111827; }
                .jsn-card { background: #fff; border: 1px solid #e5e7eb; box-shadow: 0 6px 18px rgba(17, 24, 39, 0.05); padding: 24px; margin-bottom: 20px; border-radius: 14px; transition:transform 0.12s ease, box-shadow 0.12s ease; }
                .jsn-card:hover { transform:translateY(-1px); box-shadow:0 10px 24px rgba(17, 24, 39, 0.06); }
                .jsn-card-head { display:flex; flex-wrap:wrap; justify-content:space-between; align-items:center; gap:10px; border-bottom:1px solid #f3f4f6; padding-bottom:12px; margin-bottom:14px; }
                .jsn-eyebrow { text-transform:uppercase; letter-spacing:0.5px; font-size:11px; color:#6b7280; margin:0 0 4px 0; }
                .jsn-grid { display:grid; grid-template-columns:repeat(auto-fit, minmax(300px, 1fr)); gap:20px; }
                .jsn-card .jsn-grid > div { display:flex; flex-direction:column; gap:12px; }
                .jsn-inline { display:flex; flex-wrap:wrap; gap:14px; align-items:flex-end; }
                .jsn-toggle { display:flex; align-items:center; gap:8px; font-weight:600; background:#f9fafb; padding:8px 12px; border-radius:8px; border:1px solid #e5e7eb; }
                .jsn-toggle input { transform: scale(1.2); margin:0; }
                .jsn-foot-note { margin-top:12px; font-size:12px; color:#4b5563; display:flex; gap:10px; flex-wrap:wrap; align-items:center; }
                .jsn-admin .description { color:#4b5563; }
                .jsn-hero-actions { display:flex; gap:10px; align-items:center; }
                .jsn-tip { width:18px; height:18px; border-radius:50%; background:#111827; color:#fff; display:flex; align-items:center; justify-content:center; font-weight:700; cursor:pointer; position:relative; font-size:10px; }
                .jsn-tip::after { content: attr(data-tip); position:absolute; top:24px; left:0; transform:translateX(0); background:#111827; color:#fff; padding:8px 10px; border-radius:8px; font-size:12px; line-height:1.4; width:max(220px, 32vw); max-width:360px; white-space:normal; text-align:left; box-shadow:0 6px 18px rgba(0,0,0,0.15); opacity:0; pointer-events:none; transition:opacity 0.15s ease; z-index:100; }
                .jsn-tip:hover::after { opacity:1; }
                .jsn-card label:not(.jsn-toggle) { display:flex; flex-direction:column; gap:6px; font-weight:600; color:#111827; }
                .jsn-card input[type="text"],
                .jsn-card input[type="number"],
                .jsn-card input[type="email"],
                .jsn-card textarea,
                .jsn-card select { padding:10px 12px; border-radius:8px; border:1px solid #d1d5db; box-shadow: inset 0 1px 2px rgba(0,0,0,0.03); }
                .jsn-card textarea { min-height:90px; }
                .jsn-card .description { margin-top:-4px; }
                @media (max-width: 960px) {
                    .jsn-admin { padding-right: 8px; padding-left: 0; }
                    .jsn-card { padding:16px; }
                    .jsn-grid { grid-template-columns:repeat(auto-fit, minmax(240px, 1fr)); }
                    .jsn-tip::after { width: 80vw; max-width: 380px; }
                }
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
