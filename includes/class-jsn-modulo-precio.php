<?php
/**
 * Módulo: aviso de precio dinámico.
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class JSN_Modulo_Precio extends JSN_Modulo_Base {
    public function __construct( $plugin ) {
        parent::__construct( $plugin );
        add_action( 'woocommerce_single_product_summary', array( $this, 'renderizar_aviso' ), 11 );
    }

    public function registrar_ajustes() {
        register_setting( 'jsn_group', 'jsn_price_enabled' );
        register_setting( 'jsn_group', 'jsn_price_label' );
        register_setting( 'jsn_group', 'jsn_price_bg' );
        register_setting( 'jsn_group', 'jsn_price_text' );
    }

    public function imprimir_configuracion() {
        ?>
        <div class="jsn-card" id="mod-precio">
            <div class="jsn-card-head">
                <div>
                    <p class="jsn-eyebrow">Módulo</p>
                    <h2>💹 Dinámica de precios</h2>
                    <p class="description">Añade un aviso informativo junto al precio.</p>
                </div>
                <label class="jsn-toggle">
                    <input type="checkbox" name="jsn_price_enabled" value="1" <?php checked( 1, get_option( 'jsn_price_enabled', 0 ), true ); ?> />
                    <span>Activo</span>
                </label>
            </div>
            <div class="jsn-grid">
                <div>
                    <h4>Texto</h4>
                    <input type="text" name="jsn_price_label" value="<?php echo esc_attr( get_option( 'jsn_price_label', 'Precio dinámico sujeto a demanda y disponibilidad.' ) ); ?>" class="large-text">
                    <p class="description">Mensaje corto de refuerzo.</p>
                </div>
                <div>
                    <h4>Colores</h4>
                    <div class="jsn-inline">
                        <label>Fondo <input type="color" name="jsn_price_bg" value="<?php echo esc_attr( get_option( 'jsn_price_bg', '#fff8e6' ) ); ?>"></label>
                        <label>Texto <input type="color" name="jsn_price_text" value="<?php echo esc_attr( get_option( 'jsn_price_text', '#8a6d3b' ) ); ?>"></label>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    public function renderizar_aviso() {
        if ( ! get_option( 'jsn_price_enabled', 0 ) ) {
            return;
        }
        if ( ! function_exists( 'is_product' ) || ! is_product() ) {
            return;
        }

        $bg   = esc_attr( get_option( 'jsn_price_bg', '#fff8e6' ) );
        $text = esc_attr( get_option( 'jsn_price_text', '#8a6d3b' ) );
        $msg  = wp_kses_post( get_option( 'jsn_price_label', 'Precio dinámico sujeto a demanda y disponibilidad.' ) );

        echo '<div class="jsn-precio-dinamico" style="margin:8px 0; padding:10px 12px; border-radius:6px; background:' . $bg . '; color:' . $text . '; font-size:13px; display:flex; align-items:center; gap:8px;">';
        echo '<span class="dashicons dashicons-chart-line" aria-hidden="true"></span>';
        echo '<span>' . $msg . '</span>';
        echo '</div>';
    }
}
