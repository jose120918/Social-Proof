<?php
/**
 * Módulo: ventana flotante de compras.
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

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
            <div class="jsn-card-head">
                <div>
                    <p class="jsn-eyebrow">Módulo</p>
                    <h2>🔔 Ventana flotante <span class="jsn-tip" data-tip="Popup que rota productos o pedidos recientes cada cierto intervalo.">?</span></h2>
                    <p class="description">Popup lateral con actividad de compras recientes.</p>
                </div>
                <label class="jsn-toggle">
                    <input type="checkbox" name="jsn_popup_enabled" value="1" <?php checked( 1, get_option( 'jsn_popup_enabled', 1 ), true ); ?> />
                    <span>Activo</span>
                </label>
            </div>

            <div class="jsn-grid">
                <div>
                    <h4>Fuente</h4>
                    <select name="jsn_mode" id="jsn_mode_select">
                        <option value="fake" <?php selected( $modo, 'fake' ); ?>>Simulado (productos)</option>
                        <option value="real" <?php selected( $modo, 'real' ); ?>>Pedidos reales</option>
                    </select>
                    <p class="description">Modo real usa caché de 10 minutos.</p>
                </div>
                <div>
                    <h4>Texto y tiempos</h4>
                    <label>Encabezado <input type="text" name="jsn_heading_text" value="<?php echo esc_attr( get_option( 'jsn_heading_text', 'Alguien compró recientemente:' ) ); ?>" class="regular-text"></label>
                    <label>Intervalo (s) <input type="number" name="jsn_interval" value="<?php echo esc_attr( get_option( 'jsn_interval', 10 ) ); ?>" class="small-text"></label>
                    <label>Posición
                        <select name="jsn_position">
                            <option value="bottom-left" <?php selected( get_option( 'jsn_position' ), 'bottom-left' ); ?>>Izquierda abajo</option>
                            <option value="bottom-right" <?php selected( get_option( 'jsn_position' ), 'bottom-right' ); ?>>Derecha abajo</option>
                        </select>
                    </label>
                    <label><input type="checkbox" name="jsn_show_mobile" value="1" <?php checked( 1, get_option( 'jsn_show_mobile' ), true ); ?> /> Mostrar en móviles</label>
                </div>
                <div>
                    <h4>Estilo de cierre</h4>
                    <div class="jsn-inline">
                        <label>Fondo <input type="color" name="jsn_close_bg_color" value="<?php echo esc_attr( get_option( 'jsn_close_bg_color', '#1a1a1a' ) ); ?>"></label>
                        <label>Icono <input type="color" name="jsn_close_text_color" value="<?php echo esc_attr( get_option( 'jsn_close_text_color', '#ffffff' ) ); ?>"></label>
                    </div>
                </div>
                <div class="jsn-fake" style="<?php echo ( 'real' === $modo ) ? 'display:none;' : ''; ?>">
                    <h4>Modo simulado</h4>
                    <label>IDs de producto <input type="text" name="jsn_product_ids" value="<?php echo esc_attr( get_option( 'jsn_product_ids' ) ); ?>" class="large-text"></label>
                    <label>Ciudades <textarea name="jsn_cities" rows="3" class="large-text"><?php echo esc_textarea( get_option( 'jsn_cities', "Bogotá\nMedellín" ) ); ?></textarea></label>
                </div>
            </div>
        </div>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var select = document.getElementById('jsn_mode_select');
                if (!select) return;
                select.addEventListener('change', function(){
                    var fakeBox = document.querySelector('.jsn-fake');
                    if(fakeBox){ fakeBox.style.display = (this.value === 'fake') ? 'block' : 'none'; }
                });
            });
        </script>
        <?php
    }

    public function renderizar_script() {
        if ( ! get_option( 'jsn_popup_enabled', 1 ) ) {
            return;
        }
        if ( function_exists( 'is_cart' ) && ( is_cart() || is_checkout() || is_account_page() ) ) {
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
            'data'     => $notificaciones,
            'cities'   => array_map( 'trim', explode( "\n", get_option( 'jsn_cities', 'Bogotá' ) ) ),
            'interval' => (int) get_option( 'jsn_interval', 10 ) * 1000,
            'mode'     => get_option( 'jsn_mode', 'fake' ),
        );

        $posicion  = get_option( 'jsn_position', 'bottom-left' );
        $style_pos = ( 'bottom-right' === $posicion ) ? 'right:20px; left:auto;' : 'left:20px; right:auto;';
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

                        $items      = $order->get_items();
                        $producto   = null;
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
