<?php
/**
 * Plugin Name: Notificador de ultimas compras (Pro)
 * Description: Social Proof optimizado: Notificaciones y Contador. Ubicado en menú WooCommerce > Social Proof.
 * Version: 4.1
 * Author: Jose Muñoz
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; 
}

class Jose_Sales_Notifier {

    public function __construct() {
        add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );
        
        add_action( 'wp_footer', array( $this, 'render_notification_script' ) );
        
        add_action( 'woocommerce_single_product_summary', array( $this, 'render_product_viewer_count_hook' ), 15 );
        add_shortcode( 'jsn_viewer', array( $this, 'render_product_viewer_shortcode' ) );
    }

    // --- 1. ADMIN MENU (AHORA EN WOOCOMMERCE) ---
    public function add_admin_menu() {
        // Cambiado de add_options_page a add_submenu_page bajo 'woocommerce'
        add_submenu_page(
            'woocommerce',             // Slug del padre (WooCommerce)
            'Configuración Social Proof', // Título Pagina
            'Social Proof',            // Título Menú
            'manage_options',          // Capacidad
            'jose-sales-notifier',     // Slug del menú
            array( $this, 'settings_page_html' ) // Función
        );
    }

    public function register_settings() {
        // Ajustes Popup
        register_setting( 'jsn_group', 'jsn_mode' );
        register_setting( 'jsn_group', 'jsn_heading_text' );
        register_setting( 'jsn_group', 'jsn_interval' );
        register_setting( 'jsn_group', 'jsn_position' );
        register_setting( 'jsn_group', 'jsn_show_mobile' );
        register_setting( 'jsn_group', 'jsn_close_bg_color' );
        register_setting( 'jsn_group', 'jsn_close_text_color' );
        register_setting( 'jsn_group', 'jsn_product_ids' );
        register_setting( 'jsn_group', 'jsn_cities' );

        // Ajustes Viewer
        register_setting( 'jsn_group', 'jsn_pv_enabled' );      
        register_setting( 'jsn_group', 'jsn_pv_min' );          
        register_setting( 'jsn_group', 'jsn_pv_max' );          
        register_setting( 'jsn_group', 'jsn_pv_text' );         
        register_setting( 'jsn_group', 'jsn_pv_bg_color' );     
        register_setting( 'jsn_group', 'jsn_pv_text_color' );   
        register_setting( 'jsn_group', 'jsn_pv_icon_class' );   
        register_setting( 'jsn_group', 'jsn_pv_font_size' );    
    }

    public function settings_page_html() {
        $mode = get_option('jsn_mode', 'fake');
        ?>
        <div class="wrap">
            <h1>🚀 Social Proof Manager</h1>
            <p>Aumenta la confianza de tu tienda mostrando actividad reciente.</p>
            
            <form method="post" action="options.php">
                <?php settings_fields( 'jsn_group' ); ?>
                
                <style>
                    .jsn-card { background: #fff; border: 1px solid #c3c4c7; box-shadow: 0 1px 1px rgba(0,0,0,.04); padding: 20px; margin-bottom: 20px; max-width: 800px; border-radius: 4px; }
                    .jsn-card h2 { margin-top: 0; border-bottom: 1px solid #f0f0f1; padding-bottom: 10px; color: #1d2327; }
                    .jsn-info-box { background: #e5f5fa; border-left: 4px solid #00a0d2; padding: 10px 15px; margin: 15px 0; }
                    .jsn-code { background: #f0f0f1; padding: 2px 6px; border-radius: 3px; font-family: monospace; color: #c32525; }
                    .form-table th { width: 220px; }
                </style>

                <div class="jsn-card">
                    <h2>👁️ Contador de Visitas (Ficha de Producto)</h2>
                    <p>Muestra cuántas personas están viendo el producto en tiempo real (simulado con persistencia de sesión).</p>
                    
                    <div class="jsn-info-box">
                        <strong>¿Usas Elementor?</strong> Arrastra un widget "Shortcode" y pega: <span class="jsn-code">[jsn_viewer]</span>
                    </div>

                    <table class="form-table">
                        <tr>
                            <th>Activar Funcionalidad</th>
                            <td>
                                <label>
                                    <input type="checkbox" name="jsn_pv_enabled" value="1" <?php checked( 1, get_option( 'jsn_pv_enabled' ), true ); ?> />
                                    Mostrar contador debajo del precio (o vía shortcode)
                                </label>
                            </td>
                        </tr>
                        <tr>
                            <th>Rango de Visitas</th>
                            <td>
                                <input type="number" name="jsn_pv_min" value="<?php echo esc_attr( get_option('jsn_pv_min', 10) ); ?>" class="small-text"> Mínimo
                                &nbsp;&nbsp;
                                <input type="number" name="jsn_pv_max" value="<?php echo esc_attr( get_option('jsn_pv_max', 35) ); ?>" class="small-text"> Máximo
                                <p class="description">El sistema elegirá un número al azar entre estos dos valores.</p>
                            </td>
                        </tr>
                        <tr>
                            <th>Texto del Mensaje</th>
                            <td>
                                <input type="text" name="jsn_pv_text" value="<?php echo esc_attr( get_option('jsn_pv_text', '%n personas están viendo %title%') ); ?>" class="large-text">
                                <br>
                                <p class="description"><strong>Variables disponibles:</strong></p>
                                <ul style="list-style: disc; margin-left: 20px; color: #646970; font-size: 12px;">
                                    <li><span class="jsn-code">%n</span> = Número de visitantes (ej: 15)</li>
                                    <li><span class="jsn-code">%title%</span> = Nombre del producto (ej: Shampoo Reparador)</li>
                                    <li><span class="jsn-code">%category%</span> = Categoría del producto (ej: Cuidado Capilar)</li>
                                </ul>
                            </td>
                        </tr>
                        <tr>
                            <th>Personalización Visual</th>
                            <td>
                                <strong>Fondo:</strong> <input type="color" name="jsn_pv_bg_color" value="<?php echo esc_attr( get_option('jsn_pv_bg_color', '#f5f5f5') ); ?>">
                                &nbsp;&nbsp;
                                <strong>Texto:</strong> <input type="color" name="jsn_pv_text_color" value="<?php echo esc_attr( get_option('jsn_pv_text_color', '#333333') ); ?>">
                                <br><br>
                                <strong>Tamaño Fuente:</strong> <input type="number" name="jsn_pv_font_size" value="<?php echo esc_attr( get_option('jsn_pv_font_size', 14) ); ?>" class="small-text"> px
                                <br><br>
                                <strong>Clase Icono:</strong> <input type="text" name="jsn_pv_icon_class" value="<?php echo esc_attr( get_option('jsn_pv_icon_class', 'dashicons dashicons-visibility') ); ?>" class="regular-text">
                            </td>
                        </tr>
                    </table>
                </div>

                <div class="jsn-card">
                    <h2>🔔 Notificación de Compras Recientes</h2>
                    <p>Muestra un popup emergente en la esquina indicando compras recientes.</p>

                    <table class="form-table">
                        <tr>
                            <th>Fuente de Datos</th>
                            <td>
                                <select name="jsn_mode" id="jsn_mode_select">
                                    <option value="fake" <?php selected( $mode, 'fake' ); ?>>Simulado (Productos aleatorios)</option>
                                    <option value="real" <?php selected( $mode, 'real' ); ?>>Real (Pedidos de WooCommerce)</option>
                                </select>
                                <p class="description">En modo <strong>Real</strong>, usamos caché inteligente para no ralentizar tu web.</p>
                            </td>
                        </tr>
                        <tr>
                            <th>Texto Encabezado</th>
                            <td>
                                <input type="text" name="jsn_heading_text" value="<?php echo esc_attr( get_option('jsn_heading_text', 'Alguien compró recientemente:') ); ?>" class="regular-text">
                            </td>
                        </tr>
                        <tr>
                            <th>Comportamiento</th>
                            <td>
                                <strong>Intervalo:</strong> <input type="number" name="jsn_interval" value="<?php echo esc_attr( get_option('jsn_interval', 10) ); ?>" class="small-text"> segundos entre popups.
                                <br><br>
                                <strong>Posición:</strong>
                                <select name="jsn_position">
                                    <option value="bottom-left" <?php selected( get_option('jsn_position'), 'bottom-left' ); ?>>Izquierda Abajo</option>
                                    <option value="bottom-right" <?php selected( get_option('jsn_position'), 'bottom-right' ); ?>>Derecha Abajo</option>
                                </select>
                                <br><br>
                                <label><input type="checkbox" name="jsn_show_mobile" value="1" <?php checked( 1, get_option( 'jsn_show_mobile' ), true ); ?> /> Mostrar también en celulares</label>
                            </td>
                        </tr>
                        <tr>
                            <th>Estilo Botón Cerrar (X)</th>
                            <td>
                                Fondo: <input type="color" name="jsn_close_bg_color" value="<?php echo esc_attr( get_option('jsn_close_bg_color', '#1a1a1a') ); ?>">
                                &nbsp;
                                Icono: <input type="color" name="jsn_close_text_color" value="<?php echo esc_attr( get_option('jsn_close_text_color', '#ffffff') ); ?>">
                            </td>
                        </tr>
                        
                        <tr class="jsn-fake" style="<?php echo ($mode === 'real') ? 'display:none;' : ''; ?>">
                            <th>Configuración Simulada</th>
                            <td>
                                <p><strong>IDs de Productos:</strong> (Separados por coma, ej: 102, 304). Si dejas vacío, toma los últimos 10.</p>
                                <input type="text" name="jsn_product_ids" value="<?php echo esc_attr( get_option('jsn_product_ids') ); ?>" class="large-text">
                                <br><br>
                                <p><strong>Ciudades:</strong> (Una por línea)</p>
                                <textarea name="jsn_cities" rows="4" class="large-text"><?php echo esc_textarea( get_option('jsn_cities', "Bogotá\nMedellín") ); ?></textarea>
                            </td>
                        </tr>
                    </table>
                </div>

                <?php submit_button(); ?>
            </form>
            
            <script>
                // Script simple para mostrar/ocultar opciones según el modo
                document.getElementById('jsn_mode_select').addEventListener('change',function(){
                    document.querySelector('.jsn-fake').style.display = (this.value==='fake') ? 'table-row' : 'none';
                });
            </script>
        </div>
        <?php
    }

    // --- 2. CONTADOR DE VISITAS ---
    private function get_viewer_html() {
        if ( ! is_product() || ! get_option('jsn_pv_enabled') ) return '';

        $min = (int) get_option('jsn_pv_min', 10);
        $max = (int) get_option('jsn_pv_max', 30);
        $prod_id = get_the_ID();
        
        // Preparar variables
        $replacements = [
            '%n%' => '<span class="jsn-count">...</span>', 
            '%n'  => '<span class="jsn-count">...</span>',
            '%title%' => '<strong>' . get_the_title($prod_id) . '</strong>',
            '%category%' => '<strong>' . strip_tags( wc_get_product_category_list( $prod_id, ', ', '', '' ) ) . '</strong>'
        ];

        $text = get_option('jsn_pv_text', '%n personas están viendo %title%');
        foreach($replacements as $key => $val) { $text = str_replace($key, $val, $text); }

        $style = sprintf(
            'display:none; background:%s; color:%s; padding:10px 15px; border-radius:5px; margin-bottom:15px; align-items:center; gap:10px; font-size:%spx;',
            esc_attr(get_option('jsn_pv_bg_color', '#f5f5f5')),
            esc_attr(get_option('jsn_pv_text_color', '#333333')),
            esc_attr(get_option('jsn_pv_font_size', 14))
        );

        ob_start();
        ?>
        <div id="jsn-viewer-<?php echo $prod_id; ?>" style="<?php echo $style; ?>">
            <i class="<?php echo esc_attr(get_option('jsn_pv_icon_class', 'dashicons dashicons-visibility')); ?>"></i>
            <span><?php echo $text; ?></span>
        </div>
        <script>
            (function(){
                var box = document.getElementById('jsn-viewer-<?php echo $prod_id; ?>');
                var countSpan = box.querySelector('.jsn-count');
                var min = <?php echo $min; ?>, max = <?php echo $max; ?>;
                var key = 'jsn_v_<?php echo $prod_id; ?>';
                
                var current = parseInt(sessionStorage.getItem(key));
                if(isNaN(current)) current = Math.floor(Math.random() * (max - min + 1)) + min;
                
                function updateDisplay(val) {
                    countSpan.innerText = val;
                    sessionStorage.setItem(key, val);
                }
                
                updateDisplay(current);
                box.style.display = 'flex';

                setInterval(function(){
                    var change = Math.floor(Math.random() * 3) - 1; 
                    var next = current + change;
                    if(next >= min && next <= max) {
                        current = next;
                        updateDisplay(current);
                    }
                }, 4000);
            })();
        </script>
        <?php
        return ob_get_clean();
    }

    public function render_product_viewer_shortcode() { return $this->get_viewer_html(); }
    public function render_product_viewer_count_hook() { echo $this->get_viewer_html(); }

    // --- 3. POPUP DE VENTAS ---
    private function get_data() {
        $mode = get_option('jsn_mode', 'fake');
        $data = [];

        if ( $mode === 'real' ) {
            $cached_data = get_transient( 'jsn_real_orders_cache' );
            if ( false !== $cached_data ) return $cached_data;

            if ( class_exists( 'WooCommerce' ) ) {
                try {
                    $orders = wc_get_orders([ 
                        'limit' => 20, 
                        'orderby' => 'date', 
                        'order' => 'DESC', 
                        'status' => ['completed', 'processing', 'on-hold'],
                        'type' => 'shop_order' 
                    ]);
                    
                    foreach ( $orders as $order ) {
                        if ( ! method_exists( $order, 'get_billing_city' ) ) continue;
                        $city = $order->get_billing_city();
                        if ( empty($city) ) continue;
                        
                        $items = $order->get_items();
                        $top_item = null; $max_p = 0;
                        foreach($items as $item){
                            $p = $item->get_product();
                            if($p && $p->get_price() >= $max_p) { $max_p = $p->get_price(); $top_item = $p; }
                        }
                        if(!$top_item) continue;

                        $img_id = $top_item->get_image_id();
                        $img = $img_id ? wp_get_attachment_image_url($img_id, 'thumbnail') : wc_placeholder_img_src();
                        
                        $data[] = [
                            'name' => $top_item->get_name(),
                            'image' => $img,
                            'url' => $top_item->get_permalink(),
                            'city' => ucfirst(strtolower($city)),
                            'time' => human_time_diff( $order->get_date_created()->getTimestamp(), current_time('timestamp') )
                        ];
                    }
                    set_transient( 'jsn_real_orders_cache', $data, 600 );

                } catch (Exception $e) { return []; }
            }
        } else {
            if ( class_exists( 'WooCommerce' ) ) {
                $ids = array_filter(array_map('trim', explode(',', get_option('jsn_product_ids'))));
                $args = ['status'=>'publish', 'limit'=>20, 'orderby'=>'date', 'order'=>'DESC'];
                if(!empty($ids)) $args['include'] = $ids;
                
                $products = wc_get_products($args);
                foreach($products as $p) {
                    $img_id = $p->get_image_id();
                    $data[] = [
                        'name' => $p->get_name(),
                        'image' => $img_id ? wp_get_attachment_image_url($img_id, 'thumbnail') : wc_placeholder_img_src(),
                        'url' => $p->get_permalink(),
                        'city' => null,
                        'time' => null
                    ];
                }
            }
        }
        return apply_filters( 'jsn_popup_data', $data );
    }

    public function render_notification_script() {
        if ( function_exists('is_cart') && ( is_cart() || is_checkout() ) ) return;
        if ( wp_is_mobile() && ! get_option('jsn_show_mobile') ) return;

        $notifications = $this->get_data();
        if ( empty( $notifications ) ) return;

        $js_config = [
            'data' => $notifications,
            'cities' => array_map('trim', explode("\n", get_option('jsn_cities', "Bogotá"))),
            'interval' => (int) get_option('jsn_interval', 10) * 1000,
            'mode' => get_option('jsn_mode', 'fake')
        ];

        $pos = get_option('jsn_position', 'bottom-left');
        $style_pos = ($pos === 'bottom-right') ? 'right:20px; left:auto;' : 'left:20px; right:auto;';
        ?>
        <style>
            #jsn-popup { position:fixed; bottom:20px; <?php echo $style_pos; ?> background:#fff; width:320px; box-shadow:0 4px 15px rgba(0,0,0,0.15); border-radius:8px; display:flex; align-items:stretch; z-index:9999; opacity:0; transform:translateY(20px); transition:all 0.5s ease; pointer-events:none; min-height:90px; }
            #jsn-popup.jsn-visible { opacity:1; transform:translateY(0); pointer-events:auto; }
            #jsn-popup img { width:90px; height:auto; flex-shrink:0; object-fit:cover; border-radius:8px 0 0 8px; }
            .jsn-cont { padding:15px; flex:1; display:flex; flex-direction:column; justify-content:center; line-height:1.3; font-family:sans-serif; }
            .jsn-close { position:absolute; top:0; right:0; width:24px; height:24px; text-align:center; line-height:24px; cursor:pointer; background:<?php echo esc_attr(get_option('jsn_close_bg_color', '#000')); ?>; color:<?php echo esc_attr(get_option('jsn_close_text_color', '#fff')); ?>; border-bottom-left-radius:4px; }
            .jsn-head, .jsn-meta { font-size:12px; color:#888; display:block; margin-bottom:5px; }
            .jsn-link { font-size:14px; font-weight:bold; color:#333; text-decoration:none; display:-webkit-box; -webkit-line-clamp:3; -webkit-box-orient:vertical; overflow:hidden; margin-bottom:5px; }
        </style>

        <div id="jsn-popup">
            <div class="jsn-close">&times;</div>
            <img id="jsn-img" src="">
            <div class="jsn-cont">
                <span class="jsn-head"><?php echo esc_html(get_option('jsn_heading_text', 'Alguien compró:')); ?></span>
                <a href="#" id="jsn-link" class="jsn-link"></a>
                <span class="jsn-meta" id="jsn-meta"></span>
            </div>
        </div>

        <script>
        document.addEventListener('DOMContentLoaded', function() {
            var cfg = <?php echo json_encode($js_config); ?>;
            var popup = document.getElementById('jsn-popup');
            
            function show() {
                var item = cfg.data[Math.floor(Math.random() * cfg.data.length)];
                var meta = '';
                
                if (cfg.mode === 'real') {
                    meta = 'Hace ' + item.time + ', desde ' + item.city;
                } else {
                    var city = cfg.cities[Math.floor(Math.random() * cfg.cities.length)];
                    meta = 'Hace ' + (Math.floor(Math.random()*59)+1) + ' minutos, desde ' + city;
                }

                document.getElementById('jsn-img').src = item.image;
                document.getElementById('jsn-link').innerText = item.name;
                document.getElementById('jsn-link').href = item.url;
                document.getElementById('jsn-meta').innerText = meta;
                
                popup.classList.add('jsn-visible');
                setTimeout(function(){ popup.classList.remove('jsn-visible'); }, 6000);
            }
            
            setInterval(show, cfg.interval);
            document.querySelector('.jsn-close').onclick = function(){ popup.classList.remove('jsn-visible'); };
        });
        </script>
        <?php
    }
}

new Jose_Sales_Notifier();