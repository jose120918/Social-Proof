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
                    <h2>👁️ Indicador de visitas</h2>
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

    public function renderizar_shortcode() {
        return $this->obtener_html();
    }

    public function renderizar_en_hook() {
        echo $this->obtener_html();
    }

    private function obtener_html() {
        if ( ! function_exists( 'is_product' ) || ! is_product() || ! get_option( 'jsn_pv_enabled' ) ) {
            return '';
        }

        $min     = (int) get_option( 'jsn_pv_min', 10 );
        $max     = (int) get_option( 'jsn_pv_max', 30 );
        $prod_id = get_the_ID();

        $reemplazos = array(
            '%n%'        => '<span class="jsn-count">...</span>',
            '%n'         => '<span class="jsn-count">...</span>',
            '%title%'    => '<strong>' . get_the_title( $prod_id ) . '</strong>',
            '%category%' => '<strong>' . strip_tags( wc_get_product_category_list( $prod_id, ', ', '', '' ) ) . '</strong>',
        );

        $texto = get_option( 'jsn_pv_text', '%n personas están viendo %title%' );
        foreach ( $reemplazos as $llave => $valor ) {
            $texto = str_replace( $llave, $valor, $texto );
        }

        $estilo = sprintf(
            'display:none; background:%s; color:%s; padding:10px 15px; border-radius:5px; margin-bottom:15px; align-items:center; gap:10px; font-size:%spx;',
            esc_attr( get_option( 'jsn_pv_bg_color', '#f5f5f5' ) ),
            esc_attr( get_option( 'jsn_pv_text_color', '#333333' ) ),
            esc_attr( get_option( 'jsn_pv_font_size', 14 ) )
        );

        ob_start();
        ?>
        <div id="jsn-viewer-<?php echo esc_attr( $prod_id ); ?>" style="<?php echo esc_attr( $estilo ); ?>">
            <i class="<?php echo esc_attr( get_option( 'jsn_pv_icon_class', 'dashicons dashicons-visibility' ) ); ?>"></i>
            <span><?php echo wp_kses_post( $texto ); ?></span>
        </div>
        <script>
            (function () {
                var box = document.getElementById('jsn-viewer-<?php echo esc_js( $prod_id ); ?>');
                if (!box) return;
                var countSpan = box.querySelector('.jsn-count');
                var min = <?php echo (int) $min; ?>, max = <?php echo (int) $max; ?>;
                var key = 'jsn_v_<?php echo esc_js( $prod_id ); ?>';

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
}
