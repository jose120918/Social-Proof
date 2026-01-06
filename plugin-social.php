<?php
/**
 * Plugin Name: Notificador de últimas compras (Pro)
 * Description: Social Proof modular: notificaciones, contador de vistas, aviso de precio dinámico y captación de correos.
 * Version: 5.3.0
 * Author: Jose Muñoz
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Clase base para cada módulo funcional.
 */
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

/**
 * Módulo: contador de visitas en fichas de producto.
 */
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

    public function registrar_shortcodes() {
        // Ya registrado en el constructor por compatibilidad.
    }

    public function imprimir_configuracion() {
        ?>
        <div class="jsn-card" id="mod-visitas">
            <h2>👁️ Indicador de visitas</h2>
            <p>Muestra en la ficha de producto cuántas personas lo están viendo, con persistencia por sesión.</p>
            <table class="form-table">
                <tr>
                    <th>Activar módulo</th>
                    <td>
                        <label>
                            <input type="checkbox" name="jsn_pv_enabled" value="1" <?php checked( 1, get_option( 'jsn_pv_enabled' ), true ); ?> />
                            Mostrar contador en el frontend
                        </label>
                    </td>
                </tr>
                <tr>
                    <th>Rango de visitas</th>
                    <td>
                        <input type="number" name="jsn_pv_min" value="<?php echo esc_attr( get_option( 'jsn_pv_min', 10 ) ); ?>" class="small-text"> Mínimo
                        &nbsp;&nbsp;
                        <input type="number" name="jsn_pv_max" value="<?php echo esc_attr( get_option( 'jsn_pv_max', 35 ) ); ?>" class="small-text"> Máximo
                        <p class="description">Se mostrará un número aleatorio entre los valores indicados, mantenido en la sesión.</p>
                    </td>
                </tr>
                <tr>
                    <th>Texto mostrado</th>
                    <td>
                        <input type="text" name="jsn_pv_text" value="<?php echo esc_attr( get_option( 'jsn_pv_text', '%n personas están viendo %title%' ) ); ?>" class="large-text">
                        <p class="description">
                            Variables: <code>%n</code> o <code>%n%</code> = número, <code>%title%</code> = producto, <code>%category%</code> = categoría.
                        </p>
                    </td>
                </tr>
                <tr>
                    <th>Personalización</th>
                    <td>
                        <strong>Fondo:</strong> <input type="color" name="jsn_pv_bg_color" value="<?php echo esc_attr( get_option( 'jsn_pv_bg_color', '#f5f5f5' ) ); ?>">
                        &nbsp;&nbsp;
                        <strong>Texto:</strong> <input type="color" name="jsn_pv_text_color" value="<?php echo esc_attr( get_option( 'jsn_pv_text_color', '#333333' ) ); ?>">
                        <br><br>
                        <strong>Tamaño:</strong> <input type="number" name="jsn_pv_font_size" value="<?php echo esc_attr( get_option( 'jsn_pv_font_size', 14 ) ); ?>" class="small-text"> px
                        <br><br>
                        <strong>Icono:</strong> <input type="text" name="jsn_pv_icon_class" value="<?php echo esc_attr( get_option( 'jsn_pv_icon_class', 'dashicons dashicons-visibility' ) ); ?>" class="regular-text">
                    </td>
                </tr>
            </table>
        </div>
        <?php
    }

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

/**
 * Módulo: notificación de compras.
 */
class JSN_Modulo_Notificaciones extends JSN_Modulo_Base {
    public function __construct( $plugin ) {
        parent::__construct( $plugin );
        add_action( 'wp_footer', array( $this, 'renderizar_script' ) );
    }

    public function registrar_ajustes() {
        register_setting( 'jsn_group', 'jsn_mode' );
        register_setting( 'jsn_group', 'jsn_heading_text' );
        register_setting( 'jsn_group', 'jsn_interval' );
        register_setting( 'jsn_group', 'jsn_position' );
        register_setting( 'jsn_group', 'jsn_show_mobile' );
        register_setting( 'jsn_group', 'jsn_close_bg_color' );
        register_setting( 'jsn_group', 'jsn_close_text_color' );
        register_setting( 'jsn_group', 'jsn_product_ids' );
        register_setting( 'jsn_group', 'jsn_cities' );
        register_setting( 'jsn_group', 'jsn_popup_enabled' );
    }

    public function imprimir_configuracion() {
        $modo = get_option( 'jsn_mode', 'fake' );
        ?>
        <div class="jsn-card" id="mod-popup">
            <h2>🔔 Ventana flotante de refuerzo de compra</h2>
            <p>Popup lateral que muestra compras recientes (reales o simuladas).</p>
            <table class="form-table">
                <tr>
                    <th>Activar módulo</th>
                    <td>
                        <label>
                            <input type="checkbox" name="jsn_popup_enabled" value="1" <?php checked( 1, get_option( 'jsn_popup_enabled', 1 ), true ); ?> />
                            Mostrar ventana flotante en el frontend
                        </label>
                    </td>
                </tr>
                <tr>
                    <th>Fuente de datos</th>
                    <td>
                        <select name="jsn_mode" id="jsn_mode_select">
                            <option value="fake" <?php selected( $modo, 'fake' ); ?>>Simulado (productos aleatorios)</option>
                            <option value="real" <?php selected( $modo, 'real' ); ?>>Real (pedidos de WooCommerce)</option>
                        </select>
                        <p class="description">En modo real se almacena caché por 10 minutos para optimizar rendimiento.</p>
                    </td>
                </tr>
                <tr>
                    <th>Texto y comportamiento</th>
                    <td>
                        <p><strong>Encabezado:</strong></p>
                        <input type="text" name="jsn_heading_text" value="<?php echo esc_attr( get_option( 'jsn_heading_text', 'Alguien compró recientemente:' ) ); ?>" class="regular-text">
                        <br><br>
                        <strong>Intervalo:</strong> <input type="number" name="jsn_interval" value="<?php echo esc_attr( get_option( 'jsn_interval', 10 ) ); ?>" class="small-text"> segundos entre popups.
                        <br><br>
                        <strong>Posición:</strong>
                        <select name="jsn_position">
                            <option value="bottom-left" <?php selected( get_option( 'jsn_position' ), 'bottom-left' ); ?>>Izquierda abajo</option>
                            <option value="bottom-right" <?php selected( get_option( 'jsn_position' ), 'bottom-right' ); ?>>Derecha abajo</option>
                        </select>
                        <br><br>
                        <label><input type="checkbox" name="jsn_show_mobile" value="1" <?php checked( 1, get_option( 'jsn_show_mobile' ), true ); ?> /> Mostrar en móviles</label>
                    </td>
                </tr>
                <tr>
                    <th>Estilo botón cerrar</th>
                    <td>
                        Fondo: <input type="color" name="jsn_close_bg_color" value="<?php echo esc_attr( get_option( 'jsn_close_bg_color', '#1a1a1a' ) ); ?>">
                        &nbsp;
                        Texto: <input type="color" name="jsn_close_text_color" value="<?php echo esc_attr( get_option( 'jsn_close_text_color', '#ffffff' ) ); ?>">
                    </td>
                </tr>
                <tr class="jsn-fake" style="<?php echo ( 'real' === $modo ) ? 'display:none;' : ''; ?>">
                    <th>Modo simulado</th>
                    <td>
                        <p><strong>IDs de producto</strong> (separados por coma, ejemplo 102, 304). Si se deja vacío toma los últimos 20.</p>
                        <input type="text" name="jsn_product_ids" value="<?php echo esc_attr( get_option( 'jsn_product_ids' ) ); ?>" class="large-text">
                        <br><br>
                        <p><strong>Ciudades</strong> (una por línea)</p>
                        <textarea name="jsn_cities" rows="4" class="large-text"><?php echo esc_textarea( get_option( 'jsn_cities', "Bogotá\nMedellín" ) ); ?></textarea>
                    </td>
                </tr>
            </table>
        </div>
        <?php
    }

    public function renderizar_script() {
        if ( ! get_option( 'jsn_popup_enabled', 1 ) ) {
            return;
        }

        if ( function_exists( 'is_cart' ) && ( is_cart() || is_checkout() ) ) {
            return;
        }

        if ( wp_is_mobile() && ! get_option( 'jsn_show_mobile' ) ) {
            return;
        }

        $notificaciones = $this->obtener_datos();
        if ( empty( $notificaciones ) ) {
            return;
        }

        $configuracion = array(
            'data'    => $notificaciones,
            'cities'  => array_map( 'trim', explode( "\n", get_option( 'jsn_cities', 'Bogotá' ) ) ),
            'interval'=> (int) get_option( 'jsn_interval', 10 ) * 1000,
            'mode'    => get_option( 'jsn_mode', 'fake' ),
        );

        $posicion   = get_option( 'jsn_position', 'bottom-left' );
        $style_pos  = ( 'bottom-right' === $posicion ) ? 'right:20px; left:auto;' : 'left:20px; right:auto;';
        ?>
        <style>
            #jsn-popup { position:fixed; bottom:20px; <?php echo esc_html( $style_pos ); ?> background:#fff; width:320px; box-shadow:0 4px 15px rgba(0,0,0,0.15); border-radius:8px; display:flex; align-items:stretch; z-index:9999; opacity:0; transform:translateY(20px); transition:all 0.5s ease; pointer-events:none; min-height:90px; }
            #jsn-popup.jsn-visible { opacity:1; transform:translateY(0); pointer-events:auto; }
            #jsn-popup img { width:90px; height:auto; flex-shrink:0; object-fit:cover; border-radius:8px 0 0 8px; }
            .jsn-cont { padding:15px; flex:1; display:flex; flex-direction:column; justify-content:center; line-height:1.3; font-family:sans-serif; }
            .jsn-close { position:absolute; top:0; right:0; width:24px; height:24px; text-align:center; line-height:24px; cursor:pointer; background:<?php echo esc_attr( get_option( 'jsn_close_bg_color', '#000' ) ); ?>; color:<?php echo esc_attr( get_option( 'jsn_close_text_color', '#fff' ) ); ?>; border-bottom-left-radius:4px; }
            .jsn-head, .jsn-meta { font-size:12px; color:#888; display:block; margin-bottom:5px; }
            .jsn-link { font-size:14px; font-weight:bold; color:#333; text-decoration:none; display:-webkit-box; -webkit-line-clamp:3; -webkit-box-orient:vertical; overflow:hidden; margin-bottom:5px; }
        </style>

        <div id="jsn-popup">
            <div class="jsn-close">&times;</div>
            <img id="jsn-img" src="" alt="">
            <div class="jsn-cont">
                <span class="jsn-head"><?php echo esc_html( get_option( 'jsn_heading_text', 'Alguien compró:' ) ); ?></span>
                <a href="#" id="jsn-link" class="jsn-link"></a>
                <span class="jsn-meta" id="jsn-meta"></span>
            </div>
        </div>

        <script>
        document.addEventListener('DOMContentLoaded', function() {
            var cfg = <?php echo wp_json_encode( $configuracion ); ?>;
            var popup = document.getElementById('jsn-popup');
            if (!popup) return;

            function mostrar() {
                var item = cfg.data[Math.floor(Math.random() * cfg.data.length)];
                var meta = '';

                if (cfg.mode === 'real') {
                    meta = 'Hace ' + item.time + ', desde ' + item.city;
                } else {
                    var city = cfg.cities[Math.floor(Math.random() * cfg.cities.length)];
                    meta = 'Hace ' + (Math.floor(Math.random() * 59) + 1) + ' minutos, desde ' + city;
                }

                document.getElementById('jsn-img').src = item.image;
                document.getElementById('jsn-link').innerText = item.name;
                document.getElementById('jsn-link').href = item.url;
                document.getElementById('jsn-meta').innerText = meta;

                popup.classList.add('jsn-visible');
                setTimeout(function(){ popup.classList.remove('jsn-visible'); }, 6000);
            }

            setInterval(mostrar, cfg.interval);
            mostrar();
            document.querySelector('.jsn-close').onclick = function(){ popup.classList.remove('jsn-visible'); };
        });
        </script>
        <?php
    }

    private function obtener_datos() {
        $modo = get_option( 'jsn_mode', 'fake' );
        $data = array();

        if ( 'real' === $modo ) {
            $cache = get_transient( 'jsn_real_orders_cache' );
            if ( false !== $cache ) {
                return $cache;
            }

            if ( class_exists( 'WooCommerce' ) ) {
                try {
                    $orders = wc_get_orders(
                        array(
                            'limit'   => 20,
                            'orderby' => 'date',
                            'order'   => 'DESC',
                            'status'  => array( 'completed', 'processing', 'on-hold' ),
                            'type'    => 'shop_order',
                        )
                    );

                    foreach ( $orders as $order ) {
                        if ( ! method_exists( $order, 'get_billing_city' ) ) {
                            continue;
                        }

                        $city = $order->get_billing_city();
                        if ( empty( $city ) ) {
                            continue;
                        }

                        $items    = $order->get_items();
                        $producto = null;
                        $max_precio = 0;

                        foreach ( $items as $item ) {
                            $p = $item->get_product();
                            if ( $p && $p->get_price() >= $max_precio ) {
                                $max_precio = $p->get_price();
                                $producto   = $p;
                            }
                        }

                        if ( ! $producto ) {
                            continue;
                        }

                        $img_id = $producto->get_image_id();
                        $img    = $img_id ? wp_get_attachment_image_url( $img_id, 'thumbnail' ) : wc_placeholder_img_src();

                        $data[] = array(
                            'name' => $producto->get_name(),
                            'image'=> $img,
                            'url'  => $producto->get_permalink(),
                            'city' => ucfirst( strtolower( $city ) ),
                            'time' => human_time_diff( $order->get_date_created()->getTimestamp(), current_time( 'timestamp' ) ),
                        );
                    }

                    set_transient( 'jsn_real_orders_cache', $data, 600 );
                } catch ( Exception $e ) {
                    return array();
                }
            }
        } else {
            if ( class_exists( 'WooCommerce' ) ) {
                $ids  = array_filter( array_map( 'trim', explode( ',', get_option( 'jsn_product_ids' ) ) ) );
                $args = array(
                    'status' => 'publish',
                    'limit'  => 20,
                    'orderby'=> 'date',
                    'order'  => 'DESC',
                );

                if ( ! empty( $ids ) ) {
                    $args['include'] = $ids;
                }

                $productos = wc_get_products( $args );
                foreach ( $productos as $p ) {
                    $img_id = $p->get_image_id();
                    $data[] = array(
                        'name' => $p->get_name(),
                        'image'=> $img_id ? wp_get_attachment_image_url( $img_id, 'thumbnail' ) : wc_placeholder_img_src(),
                        'url'  => $p->get_permalink(),
                        'city' => null,
                        'time' => null,
                    );
                }
            }
        }

        return apply_filters( 'jsn_popup_data', $data );
    }
}

/**
 * Módulo: dinámica de precio (aviso informativo).
 */
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
            <h2>💹 Dinámica de precios</h2>
            <p>Activa un aviso informativo para indicar que el precio puede variar por demanda, sin modificar los valores de WooCommerce.</p>
            <table class="form-table">
                <tr>
                    <th>Activar módulo</th>
                    <td>
                        <label>
                            <input type="checkbox" name="jsn_price_enabled" value="1" <?php checked( 1, get_option( 'jsn_price_enabled', 0 ), true ); ?> />
                            Mostrar aviso junto al precio
                        </label>
                    </td>
                </tr>
                <tr>
                    <th>Texto del aviso</th>
                    <td>
                        <input type="text" name="jsn_price_label" value="<?php echo esc_attr( get_option( 'jsn_price_label', 'Precio dinámico sujeto a demanda y disponibilidad.' ) ); ?>" class="large-text">
                        <p class="description">Mensaje corto para reforzar urgencia o variabilidad.</p>
                    </td>
                </tr>
                <tr>
                    <th>Colores</th>
                    <td>
                        <strong>Fondo:</strong> <input type="color" name="jsn_price_bg" value="<?php echo esc_attr( get_option( 'jsn_price_bg', '#fff8e6' ) ); ?>">
                        &nbsp;&nbsp;
                        <strong>Texto:</strong> <input type="color" name="jsn_price_text" value="<?php echo esc_attr( get_option( 'jsn_price_text', '#8a6d3b' ) ); ?>">
                    </td>
                </tr>
            </table>
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

/**
 * Módulo: Newsletter.
 */
class JSN_Modulo_Newsletter extends JSN_Modulo_Base {
    public function __construct( $plugin ) {
        parent::__construct( $plugin );
        add_shortcode( 'jsn_newsletter', array( $this, 'renderizar_shortcode' ) );
        add_action( 'wp_ajax_jsn_guardar_correo', array( $this, 'guardar_correo' ) );
        add_action( 'wp_ajax_nopriv_jsn_guardar_correo', array( $this, 'guardar_correo' ) );
    }

    public function registrar_ajustes() {
        register_setting( 'jsn_group', 'jsn_newsletter_enabled' );
        register_setting( 'jsn_group', 'jsn_newsletter_button_color' );
        register_setting( 'jsn_group', 'jsn_newsletter_disclaimer' );
        register_setting( 'jsn_group', 'jsn_newsletter_email_html' );
        register_setting( 'jsn_group', 'jsn_newsletter_subject' );
        register_setting( 'jsn_group', 'jsn_newsletter_coupon_code' );
        register_setting( 'jsn_group', 'jsn_newsletter_success_text' );
    }

    public function imprimir_configuracion() {
        ?>
        <div class="jsn-card" id="mod-newsletter">
            <h2>✉️ Newsletter (captación de correos)</h2>
            <p>Formulario vía shortcode para Elementor u otro editor. Envía un correo HTML y almacena los registros en base de datos.</p>
            <table class="form-table">
                <tr>
                    <th>Activar módulo</th>
                    <td>
                        <label>
                            <input type="checkbox" name="jsn_newsletter_enabled" value="1" <?php checked( 1, get_option( 'jsn_newsletter_enabled', 1 ), true ); ?> />
                            Permitir el shortcode <code>[jsn_newsletter]</code>
                        </label>
                    </td>
                </tr>
                <tr>
                    <th>Color del botón</th>
                    <td>
                        <input type="color" name="jsn_newsletter_button_color" value="<?php echo esc_attr( get_option( 'jsn_newsletter_button_color', '#0073aa' ) ); ?>">
                        <p class="description">El color se aplica en línea para facilitar la inserción en Elementor.</p>
                    </td>
                </tr>
                <tr>
                    <th>Texto del disclaimer</th>
                    <td>
                        <textarea name="jsn_newsletter_disclaimer" rows="3" class="large-text"><?php echo esc_textarea( get_option( 'jsn_newsletter_disclaimer', 'Acepto la política de tratamiento de datos conforme a la Ley 1581 de 2012 (Colombia).' ) ); ?></textarea>
                        <p class="description">Incluye la referencia normativa colombiana solicitada.</p>
                    </td>
                </tr>
                <tr>
                    <th>Asunto del correo</th>
                    <td>
                        <input type="text" name="jsn_newsletter_subject" value="<?php echo esc_attr( get_option( 'jsn_newsletter_subject', 'Gracias por suscribirte a nuestras novedades' ) ); ?>" class="large-text">
                    </td>
                </tr>
                <tr>
                    <th>Código de cupón</th>
                    <td>
                        <input type="text" name="jsn_newsletter_coupon_code" value="<?php echo esc_attr( get_option( 'jsn_newsletter_coupon_code', 'VERANO15' ) ); ?>" class="regular-text">
                        <p class="description">Solo se cambia el texto del cupón; el resto del HTML permanece igual.</p>
                    </td>
                </tr>
                <tr>
                    <th>HTML del correo</th>
                    <td>
                        <textarea name="jsn_newsletter_email_html" rows="16" class="large-text code"><?php
                        echo esc_textarea(
                            get_option(
                                'jsn_newsletter_email_html',
                                $this->obtener_plantilla_por_defecto()
                            )
                        );
                        ?></textarea>
                        <p class="description">Plantilla enviada al usuario. Se envía como <code>text/html</code>. Usa <code>%COUPON_CODE%</code> para colocar el código configurado arriba.</p>
                    </td>
                </tr>
                <tr>
                    <th>Mensaje de éxito (frontend)</th>
                    <td>
                        <input type="text" name="jsn_newsletter_success_text" value="<?php echo esc_attr( get_option( 'jsn_newsletter_success_text', '¡Gracias! Revisa tu bandeja de entrada para ver tu cupón.' ) ); ?>" class="large-text">
                        <p class="description">Texto mostrado al usuario cuando el correo se envía correctamente.</p>
                    </td>
                </tr>
                <tr>
                    <th>Exportar CSV</th>
                    <td>
                        <a class="button" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=jsn_exportar_correos' ), 'jsn_exportar_correos' ) ); ?>">Descargar correos registrados</a>
                    </td>
                </tr>
            </table>
        </div>
        <?php
    }

    public function renderizar_shortcode() {
        if ( ! get_option( 'jsn_newsletter_enabled', 1 ) ) {
            return '<p>El formulario de newsletter está desactivado.</p>';
        }

        $color      = esc_attr( get_option( 'jsn_newsletter_button_color', '#0073aa' ) );
        $disclaimer = wp_kses_post( get_option( 'jsn_newsletter_disclaimer', 'Acepto la política de tratamiento de datos conforme a la Ley 1581 de 2012 (Colombia).' ) );
        $nonce      = wp_create_nonce( 'jsn_newsletter_nonce' );

        ob_start();
        ?>
        <div class="jsn-newsletter-wrap" style="max-width: 520px; margin: 0 auto; padding: 18px 20px; background: #f7f9fc; border: 1px solid #e4e8ef; border-radius: 12px; box-shadow: 0 6px 18px rgba(16,24,40,0.06);">
            <form class="jsn-newsletter-form" data-endpoint="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" data-nonce="<?php echo esc_attr( $nonce ); ?>" style="display:flex; flex-direction:column; gap:12px; width:100%;">
                <div class="jsn-newsletter-texto" style="display:flex; flex-direction:column; gap:6px;">
                    <div style="font-weight:700; font-size:18px; color:#111827;">Suscríbete y recibe novedades</div>
                    <div style="font-size:14px; color:#4b5563; line-height:1.5;">Ingresa tu correo para recibir promociones y noticias exclusivas.</div>
                </div>
                <div class="jsn-newsletter-campo" style="display:flex; flex-direction:column; gap:6px;">
                    <label for="jsn-newsletter-email" style="font-size:14px; font-weight:600; color:#111827;">Correo electrónico</label>
                    <input type="email" id="jsn-newsletter-email" name="email" required placeholder="tu@correo.com" style="width:100%; padding:12px 14px; border:1px solid #d1d5db; border-radius:8px; font-size:14px; color:#111827; background:#fff; box-sizing:border-box;">
                </div>
                <button type="submit" style="width:100%; background:<?php echo $color; ?>; color:#fff; border:none; padding:12px 14px; border-radius:8px; cursor:pointer; font-weight:700; text-transform:uppercase; letter-spacing:0.4px; box-shadow:0 4px 10px rgba(0,0,0,0.08); transition:transform 0.1s ease, box-shadow 0.1s ease;">Enviar</button>
                <p class="jsn-newsletter-disclaimer" style="font-size:12px; color:#4b5563; line-height:1.5; margin:0;"><?php echo $disclaimer; ?></p>
                <div class="jsn-newsletter-mensaje" aria-live="polite" style="margin-top:4px; font-size:13px; min-height:18px; color:#374151;"></div>
            </form>
        </div>
        <script>
        (function(){
            if (window.JSNNewsletterGlobalHandler) return;
            window.JSNNewsletterGlobalHandler = true;

            function manejarSubmit(event) {
                var form = event.target;
                if (!form.classList.contains('jsn-newsletter-form')) return;
                event.preventDefault();

                var mensaje = form.querySelector('.jsn-newsletter-mensaje');
                var input = form.querySelector('input[name="email"]');
                if (!input) return;

                var correo = input.value;
                mensaje.textContent = 'Enviando...';
                mensaje.style.color = '#374151';

                var datos = new FormData();
                datos.append('action', 'jsn_guardar_correo');
                datos.append('nonce', form.dataset.nonce || '');
                datos.append('email', correo);

                fetch(form.dataset.endpoint, { method:'POST', body: datos, credentials:'same-origin' })
                    .then(function(r){ return r.json(); })
                    .then(function(res){
                        if(res && res.success){
                            mensaje.style.color = '#0f5132';
                            mensaje.textContent = res.data.mensaje || 'Registro exitoso.';
                            form.reset();
                        } else {
                            mensaje.style.color = '#842029';
                            mensaje.textContent = (res && res.data && res.data.mensaje) ? res.data.mensaje : 'No se pudo guardar el correo.';
                        }
                    })
                    .catch(function(){
                        mensaje.style.color = '#842029';
                        mensaje.textContent = 'Error de comunicación. Inténtalo de nuevo.';
                    });
            }

            document.addEventListener('submit', manejarSubmit, true);
        })();
        </script>
        <?php
        return ob_get_clean();
    }

    public function guardar_correo() {
        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'jsn_newsletter_nonce' ) ) {
            wp_send_json_error( array( 'mensaje' => 'Petición no autorizada.' ), 403 );
        }

        $email = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
        if ( empty( $email ) || ! is_email( $email ) ) {
            wp_send_json_error( array( 'mensaje' => 'Correo inválido.' ) );
        }

        global $wpdb;
        $tabla = $this->plugin->obtener_tabla_correos();

        $existe = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$tabla} WHERE email = %s", $email ) );
        if ( $existe ) {
            wp_send_json_success( array( 'mensaje' => 'Ya tenemos tu correo registrado. ¡Gracias!' ) );
        }

        $wpdb->insert(
            $tabla,
            array(
                'email'      => $email,
                'created_at' => current_time( 'mysql' ),
                'ip_address' => isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '',
            ),
            array( '%s', '%s', '%s' )
        );

        $plantilla = get_option(
            'jsn_newsletter_email_html',
            $this->obtener_plantilla_por_defecto()
        );
        $asunto    = get_option( 'jsn_newsletter_subject', 'Gracias por suscribirte a nuestras novedades' );
        $cupon     = get_option( 'jsn_newsletter_coupon_code', 'VERANO15' );
        $plantilla = str_replace( '%COUPON_CODE%', esc_html( $cupon ), $plantilla );

        $contenido = $this->sanitizar_html_correo( $plantilla );

        $headers = array( 'Content-Type: text/html; charset=UTF-8' );
        wp_mail( $email, $asunto, $contenido, $headers );

        $exito = get_option( 'jsn_newsletter_success_text', '¡Gracias! Revisa tu bandeja de entrada para ver tu cupón.' );
        wp_send_json_success( array( 'mensaje' => $exito ) );
    }

    /**
     * Sanitiza el HTML del correo permitiendo los elementos necesarios.
     */
    private function sanitizar_html_correo( $html ) {
        $permitidos = wp_kses_allowed_html( 'post' );

        $extra = array(
            'html' => array(
                'lang'  => true,
                'xmlns' => true,
                'dir'   => true,
            ),
            'head' => array(),
            'meta' => array(
                'charset' => true,
                'name'    => true,
                'content' => true,
                'http-equiv' => true,
            ),
            'title' => array(),
            'table' => array(
                'align'       => true,
                'border'      => true,
                'cellpadding' => true,
                'cellspacing' => true,
                'width'       => true,
                'style'       => true,
            ),
            'tr' => array(
                'align' => true,
                'style' => true,
            ),
            'td' => array(
                'align'   => true,
                'style'   => true,
                'width'   => true,
                'height'  => true,
                'valign'  => true,
                'colspan' => true,
                'rowspan' => true,
            ),
            'th' => array(
                'align'   => true,
                'style'   => true,
                'width'   => true,
                'height'  => true,
                'valign'  => true,
                'colspan' => true,
                'rowspan' => true,
            ),
            'tbody' => array( 'style' => true ),
            'thead' => array( 'style' => true ),
            'tfoot' => array( 'style' => true ),
            'img' => array(
                'src'    => true,
                'alt'    => true,
                'width'  => true,
                'height' => true,
                'style'  => true,
            ),
            'div' => array(
                'style' => true,
                'align' => true,
            ),
            'span' => array(
                'style' => true,
            ),
            'p' => array(
                'style' => true,
                'align' => true,
            ),
            'h1' => array(
                'style' => true,
                'align' => true,
            ),
            'h2' => array(
                'style' => true,
                'align' => true,
            ),
            'a' => array(
                'href'   => true,
                'style'  => true,
                'target' => true,
                'title'  => true,
            ),
            'style' => array(),
            'body'  => array(
                'style' => true,
            ),
        );

        $permitidos = array_merge( $permitidos, $extra );

        $sanitizado = wp_kses( $html, $permitidos );
        if ( '' === trim( $sanitizado ) ) {
            $sanitizado = $this->obtener_plantilla_por_defecto( get_option( 'jsn_newsletter_coupon_code', 'VERANO15' ) );
        }
        return $sanitizado;
    }

    /**
     * Plantilla HTML por defecto con placeholder de cupón.
     */
    private function obtener_plantilla_por_defecto( $codigo = '%COUPON_CODE%' ) {
        return "<!DOCTYPE html>\n<html lang=\"es\">\n<head>\n    <meta charset=\"UTF-8\">\n    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\n    <title>Descuento La Veranera</title>\n    <style>\n        body { margin: 0; padding: 0; background-color: #f4f4f4; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; }\n        table { border-collapse: collapse; }\n        img { display: block; max-width: 100%; height: auto; }\n        .wrapper { width: 100%; table-layout: fixed; background-color: #f4f4f4; padding-bottom: 40px; }\n        .main-content { background-color: #ffffff; margin: 0 auto; width: 100%; max-width: 600px; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 10px rgba(0,0,0,0.05); }\n        .btn-primary { background-color: #1e2632; color: #ffffff !important; text-decoration: none; padding: 12px 30px; border-radius: 4px; display: inline-block; font-weight: bold; font-size: 16px; letter-spacing: 0.5px; }\n    </style>\n</head>\n<body>\n    <div class=\"wrapper\">\n        <table align=\"center\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">\n            <tr>\n                <td align=\"center\" style=\"padding: 20px 0;\">\n                    </td>\n            </tr>\n            <tr>\n                <td align=\"center\">\n                    <table class=\"main-content\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\">\n                        <tr>\n                            <td align=\"center\" style=\"padding: 40px 20px 20px 20px; background-color: #ffffff;\">\n                                <img src=\"https://laveranera.prototipo.com.co/wp-content/uploads/2025/07/La-veranera.png\" alt=\"Logo La Veranera\" width=\"180\" style=\"width: 180px;\">\n                            </td>\n                        </tr>\n\n                        <tr>\n                            <td align=\"center\" style=\"background-color: #fafafa; padding: 0;\">\n                                <div style=\"height: 2px; background-color: #eee; width: 90%;\"></div>\n                            </td>\n                        </tr>\n\n                        <tr>\n                            <td align=\"center\" style=\"padding: 40px 30px; text-align: center;\">\n                                <h1 style=\"color: #1e2632; font-family: 'Georgia', serif; font-size: 24px; margin-bottom: 20px; text-transform: uppercase; letter-spacing: 1px;\">\n                                    ¡Gracias por unirte!\n                                </h1>\n                                <p style=\"color: #666666; font-size: 16px; line-height: 1.6; margin-bottom: 30px;\">\n                                    Estamos felices de que seas parte de la familia <strong>La Veranera</strong>. Como lo prometimos, aquí tienes un obsequio especial para que disfrutes de nuestras tablas de quesos y sangrías con una sonrisa.\n                                </p>\n                                \n                                <div style=\"background-color: #f9f9f9; border: 2px dashed #1e2632; padding: 20px; display: inline-block; margin-bottom: 30px; border-radius: 4px;\">\n                                    <span style=\"display: block; font-size: 12px; color: #888; margin-bottom: 5px; text-transform: uppercase;\">Tu código de descuento:</span>\n                                    <span style=\"font-family: 'Helvetica', sans-serif; font-size: 28px; color: #1e2632; font-weight: 800; letter-spacing: 2px;\">{$codigo}</span>\n                                </div>\n                                \n                                <p style=\"color: #666666; font-size: 14px; margin-bottom: 30px;\">\n                                    Presenta este cupón en nuestro punto físico o úsalo en tu próxima reserva.\n                                </p>\n\n                                <a href=\"https://laveranerasangriaoficial.com/\" target=\"_blank\" class=\"btn-primary\">\n                                    VER MENÚ COMPLETO\n                                </a>\n                            </td>\n                        </tr>\n\n                        <tr>\n                            <td align=\"center\" style=\"background-color: #1e2632; padding: 30px;\">\n                                <p style=\"color: #ffffff; font-size: 14px; margin: 0 0 10px 0; font-family: 'Georgia', serif;\">La Veranera</p>\n                                <p style=\"color: #8d95a1; font-size: 12px; margin: 0;\">\n                                    Sangría & Tablas de Quesos<br>\n                                    Bucaramanga, Santander\n                                </p>\n                            </td>\n                        </tr>\n                    </table>\n                </td>\n            </tr>\n            <tr>\n                <td align=\"center\" style=\"padding: 20px 0;\">\n                    <p style=\"font-size: 11px; color: #999999;\">\n                        Recibiste este correo porque te suscribiste en nuestro sitio web.\n                    </p>\n                </td>\n            </tr>\n        </table>\n    </div>\n</body>\n</html>";
    }
}

/**
 * Núcleo del plugin.
 */
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
            'visitas'        => new JSN_Modulo_Visitas( $this ),
            'popup'          => new JSN_Modulo_Notificaciones( $this ),
            'precio'         => new JSN_Modulo_Precio( $this ),
            'newsletter'     => new JSN_Modulo_Newsletter( $this ),
        );

        add_action( 'admin_menu', array( $this, 'registrar_menu' ) );
        add_action( 'admin_init', array( $this, 'registrar_ajustes' ) );
        add_action( 'init', array( $this, 'registrar_shortcodes' ) );
        add_action( 'admin_post_jsn_exportar_correos', array( $this, 'exportar_correos' ) );
    }

    public static function activar_plugin() {
        global $wpdb;
        $tabla  = $wpdb->prefix . 'jsn_newsletter';
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
        add_menu_page(
            'Social Proof',
            'Social Proof',
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
        <div class="wrap">
            <h1>Social Proof</h1>
            <p class="description">Panel central independiente para controlar cada módulo de Social Proof.</p>
            <div class="jsn-banner">
                <strong>Panel central:</strong> Social Proof
            </div>

            <style>
                .jsn-card { background: #fff; border: 1px solid #c3c4c7; box-shadow: 0 1px 1px rgba(0,0,0,.04); padding: 20px; margin-bottom: 20px; max-width: 1000px; border-radius: 6px; }
                .jsn-card h2 { margin-top: 0; border-bottom: 1px solid #f0f0f1; padding-bottom: 10px; color: #1d2327; }
                .jsn-banner { padding: 10px 15px; background: #f0f6ff; border: 1px solid #b6d1ff; border-radius: 4px; margin: 10px 0 20px; max-width: 1000px; }
                .form-table th { width: 220px; }
            </style>

            <form method="post" action="options.php">
                <?php settings_fields( 'jsn_group' ); ?>
                <?php
                foreach ( $this->modulos as $modulo ) {
                    $modulo->imprimir_configuracion();
                }
                submit_button();
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

// Inicializar plugin.
JSN_Social_Proof_Plugin::instancia();
register_activation_hook( __FILE__, array( 'JSN_Social_Proof_Plugin', 'activar_plugin' ) );
