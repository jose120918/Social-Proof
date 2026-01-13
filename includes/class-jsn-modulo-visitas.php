<?php
/**
 * Módulo: indicador de visitas.
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class JSN_Modulo_Visitas extends JSN_Modulo_Base {
    public function __construct( $plugin ) {
        parent::__construct( $plugin );
        add_action( 'woocommerce_single_product_summary', array( $this, 'renderizar_en_hook' ), 15 );
        add_shortcode( 'jsn_viewer', array( $this, 'renderizar_shortcode' ) );
    }

    public function registrar_ajustes() {
        register_setting( 'jsn_group', 'jsn_pv_enabled' );
        register_setting( 'jsn_group', 'jsn_pv_min' );
        register_setting( 'jsn_group', 'jsn_pv_max' );
        register_setting( 'jsn_group', 'jsn_pv_text' );
        register_setting( 'jsn_group', 'jsn_pv_bg_color' );
        register_setting( 'jsn_group', 'jsn_pv_text_color' );
        register_setting( 'jsn_group', 'jsn_pv_icon_class' );
        register_setting( 'jsn_group', 'jsn_pv_font_size' );
    }

    public function imprimir_configuracion() {
        ?>
        <div class="jsn-card" id="mod-visitas">
            <div class="jsn-card-head">
                <div>
                    <p class="jsn-eyebrow">Módulo</p>
                    <h2>👁️ Indicador de visitas <span class="jsn-tip" data-tip="Muestra cuántas personas ven el producto en ficha o shortcode.">?</span></h2>
                    <p class="description">Muestra cuántas personas ven el producto en tiempo real.</p>
                </div>
                <label class="jsn-toggle">
                    <input type="checkbox" name="jsn_pv_enabled" value="1" <?php checked( 1, get_option( 'jsn_pv_enabled' ), true ); ?> />
                    <span>Activo</span>
                </label>
            </div>
            <div class="jsn-grid">
                <div>
                    <h4>Rango</h4>
                    <div class="jsn-inline">
                        <label>Mínimo <input type="number" name="jsn_pv_min" value="<?php echo esc_attr( get_option( 'jsn_pv_min', 10 ) ); ?>" class="small-text"></label>
                        <label>Máximo <input type="number" name="jsn_pv_max" value="<?php echo esc_attr( get_option( 'jsn_pv_max', 35 ) ); ?>" class="small-text"></label>
                    </div>
                    <p class="description">Número aleatorio entre mínimo y máximo, guardado en la sesión.</p>
                </div>
                <div>
                    <h4>Texto</h4>
                    <input type="text" name="jsn_pv_text" value="<?php echo esc_attr( get_option( 'jsn_pv_text', '%n personas están viendo %title%' ) ); ?>" class="large-text">
                    <p class="description">Variables: <code>%n</code> / <code>%n%</code>, <code>%title%</code>, <code>%category%</code></p>
                </div>
                <div>
                    <h4>Estilo</h4>
                    <div class="jsn-inline">
                        <label>Fondo <input type="color" name="jsn_pv_bg_color" value="<?php echo esc_attr( get_option( 'jsn_pv_bg_color', '#f5f5f5' ) ); ?>"></label>
                        <label>Texto <input type="color" name="jsn_pv_text_color" value="<?php echo esc_attr( get_option( 'jsn_pv_text_color', '#333333' ) ); ?>"></label>
                        <label>Tamaño <input type="number" name="jsn_pv_font_size" value="<?php echo esc_attr( get_option( 'jsn_pv_font_size', 14 ) ); ?>" class="small-text"> px</label>
                    </div>
                    <label>Icono <input type="text" name="jsn_pv_icon_class" value="<?php echo esc_attr( get_option( 'jsn_pv_icon_class', 'dashicons dashicons-visibility' ) ); ?>" class="regular-text"></label>
                </div>
            </div>
            <div class="jsn-foot-note">Shortcode: <code>[jsn_viewer]</code> • Hook: <code>woocommerce_single_product_summary</code></div>
        </div>
        <?php
    }

    public function registrar_shortcodes() {}

    public function renderizar_shortcode( $atributos = array() ) {
        return $this->obtener_html( $atributos, false );
    }

    public function renderizar_en_hook() {
        echo $this->obtener_html( array(), true );
    }

    private function obtener_html( $atributos = array(), $solo_producto = false ) {
        $atributos = shortcode_atts(
            array(
                'activar'       => '',
                'minimo'        => '',
                'maximo'        => '',
                'texto'         => '',
                'fondo'         => '',
                'color_texto'   => '',
                'icono'         => '',
                'tamano_fuente' => '',
                'id'            => '',
            ),
            $atributos,
            'jsn_viewer'
        );

        $activado = $this->interpretar_activacion( $atributos['activar'], get_option( 'jsn_pv_enabled' ) );
        if ( ! $activado ) {
            return '';
        }

        $es_producto = function_exists( 'is_product' ) && is_product();
        if ( $solo_producto && ! $es_producto ) {
            return '';
        }

        $min      = (int) $this->resolver_atributo_numerico( $atributos['minimo'], get_option( 'jsn_pv_min', 10 ) );
        $max      = (int) $this->resolver_atributo_numerico( $atributos['maximo'], get_option( 'jsn_pv_max', 30 ) );
        $post_id  = get_the_ID();
        $titulo   = get_the_title( $post_id );
        $categoria = $this->obtener_categoria_contexto( $post_id, $es_producto );

        $reemplazos = array(
            '%n%'        => '<span class="jsn-count">...</span>',
            '%n'         => '<span class="jsn-count">...</span>',
            '%title%'    => '<strong>' . esc_html( $titulo ) . '</strong>',
            '%category%' => '<strong>' . esc_html( $categoria ) . '</strong>',
        );

        $texto = $this->resolver_atributo_texto( $atributos['texto'], get_option( 'jsn_pv_text', '%n personas están viendo %title%' ) );
        foreach ( $reemplazos as $llave => $valor ) {
            $texto = str_replace( $llave, $valor, $texto );
        }

        $estilo = sprintf(
            'display:none; background:%s; color:%s; padding:10px 15px; border-radius:5px; margin-bottom:15px; align-items:center; gap:10px; font-size:%spx;',
            esc_attr( $this->resolver_atributo_texto( $atributos['fondo'], get_option( 'jsn_pv_bg_color', '#f5f5f5' ) ) ),
            esc_attr( $this->resolver_atributo_texto( $atributos['color_texto'], get_option( 'jsn_pv_text_color', '#333333' ) ) ),
            esc_attr( $this->resolver_atributo_numerico( $atributos['tamano_fuente'], get_option( 'jsn_pv_font_size', 14 ) ) )
        );

        $icono = $this->resolver_atributo_texto( $atributos['icono'], get_option( 'jsn_pv_icon_class', 'dashicons dashicons-visibility' ) );
        $identificador = ! empty( $atributos['id'] ) ? sanitize_key( $atributos['id'] ) : $post_id;

        ob_start();
        ?>
        <div id="jsn-viewer-<?php echo esc_attr( $identificador ); ?>" style="<?php echo esc_attr( $estilo ); ?>">
            <i class="<?php echo esc_attr( $icono ); ?>"></i>
            <span><?php echo wp_kses_post( $texto ); ?></span>
        </div>
        <script>
            (function () {
                var box = document.getElementById('jsn-viewer-<?php echo esc_js( $identificador ); ?>');
                if (!box) return;
                var countSpan = box.querySelector('.jsn-count');
                var min = <?php echo (int) $min; ?>, max = <?php echo (int) $max; ?>;
                var key = 'jsn_v_<?php echo esc_js( $identificador ); ?>';

                var current = parseInt(sessionStorage.getItem(key));
                if (isNaN(current)) current = Math.floor(Math.random() * (max - min + 1)) + min;

                function actualizar(valor) {
                    countSpan.innerText = valor;
                    sessionStorage.setItem(key, valor);
                }

                actualizar(current);
                box.style.display = 'flex';

                setInterval(function () {
                    var cambio = Math.floor(Math.random() * 3) - 1;
                    var siguiente = current + cambio;
                    if (siguiente >= min && siguiente <= max) {
                        current = siguiente;
                        actualizar(current);
                    }
                }, 4000);
            })();
        </script>
        <?php
        return ob_get_clean();
    }

    private function obtener_categoria_contexto( $post_id, $es_producto ) {
        if ( $es_producto && function_exists( 'wc_get_product_category_list' ) ) {
            $categorias = strip_tags( wc_get_product_category_list( $post_id, ', ', '', '' ) );
            return $categorias ? $categorias : 'producto';
        }

        $categorias = get_the_category_list( ', ', '', $post_id );
        if ( ! empty( $categorias ) ) {
            return strip_tags( $categorias );
        }

        return 'contenido';
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

    private function resolver_atributo_numerico( $valor, $por_defecto ) {
        if ( '' === $valor || null === $valor ) {
            return (int) $por_defecto;
        }
        return (int) $valor;
    }

    private function resolver_atributo_texto( $valor, $por_defecto ) {
        if ( '' === $valor || null === $valor ) {
            return (string) $por_defecto;
        }
        return (string) $valor;
    }
}
