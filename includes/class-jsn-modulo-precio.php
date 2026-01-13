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
        add_shortcode( 'jsn_precio', array( $this, 'renderizar_shortcode' ) );
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
                    <h2>💹 Dinámica de precios <span class="jsn-tip" data-tip="Solo muestra un aviso informativo; no modifica precios en WooCommerce.">?</span></h2>
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
        echo $this->obtener_html( array(), true );
    }

    public function renderizar_shortcode( $atributos = array() ) {
        return $this->obtener_html( $atributos, false );
    }

    private function obtener_html( $atributos = array(), $solo_producto = false ) {
        $atributos = shortcode_atts(
            array(
                'activar'     => '',
                'texto'       => '',
                'fondo'       => '',
                'color_texto' => '',
                'icono'       => '',
            ),
            $atributos,
            'jsn_precio'
        );

        $activado = $this->interpretar_activacion( $atributos['activar'], get_option( 'jsn_price_enabled', 0 ) );
        if ( ! $activado ) {
            return '';
        }

        $es_producto = function_exists( 'is_product' ) && is_product();
        if ( $solo_producto && ! $es_producto ) {
            return '';
        }

        $bg     = esc_attr( $this->resolver_atributo_texto( $atributos['fondo'], get_option( 'jsn_price_bg', '#fff8e6' ) ) );
        $text   = esc_attr( $this->resolver_atributo_texto( $atributos['color_texto'], get_option( 'jsn_price_text', '#8a6d3b' ) ) );
        $msg    = wp_kses_post( $this->resolver_atributo_texto( $atributos['texto'], get_option( 'jsn_price_label', 'Precio dinámico sujeto a demanda y disponibilidad.' ) ) );
        $icono  = esc_attr( $this->resolver_atributo_texto( $atributos['icono'], 'dashicons dashicons-chart-line' ) );

        $salida  = '<div class="jsn-precio-dinamico" style="margin:8px 0; padding:10px 12px; border-radius:6px; background:' . $bg . '; color:' . $text . '; font-size:13px; display:flex; align-items:center; gap:8px;">';
        $salida .= '<span class="' . $icono . '" aria-hidden="true"></span>';
        $salida .= '<span>' . $msg . '</span>';
        $salida .= '</div>';

        return $salida;
    }

    private function interpretar_activacion( $valor, $por_defecto ) {
        if ( '' === $valor || null === $valor ) {
            return (bool) $por_defecto;
        }

        $valor = strtolower( trim( (string) $valor ) );
        $verdaderos = array( '1', 'true', 'si', 'sí', 'on' );
        $falsos     = array( '0', 'false', 'no', 'off' );

        if ( in_array( $valor, $verdaderos, true ) ) {
            return true;
        }
        if ( in_array( $valor, $falsos, true ) ) {
            return false;
        }

        return (bool) $por_defecto;
    }

    private function resolver_atributo_texto( $valor, $por_defecto ) {
        if ( '' === $valor || null === $valor ) {
            return (string) $por_defecto;
        }
        return (string) $valor;
    }
}
