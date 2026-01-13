<?php
/**
 * Módulo: newsletter.
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

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
        register_setting( 'jsn_group', 'jsn_newsletter_button_text' );
        register_setting( 'jsn_group', 'jsn_newsletter_title' );
        register_setting( 'jsn_group', 'jsn_newsletter_description' );
        register_setting( 'jsn_group', 'jsn_newsletter_label' );
        register_setting( 'jsn_group', 'jsn_newsletter_placeholder' );
        register_setting( 'jsn_group', 'jsn_newsletter_disclaimer' );
        register_setting( 'jsn_group', 'jsn_newsletter_email_html' );
        register_setting( 'jsn_group', 'jsn_newsletter_subject' );
        register_setting( 'jsn_group', 'jsn_newsletter_coupon_code' );
        register_setting( 'jsn_group', 'jsn_newsletter_success_text' );
        register_setting( 'jsn_group', 'jsn_newsletter_email_color_fondo' );
        register_setting( 'jsn_group', 'jsn_newsletter_email_color_texto' );
        register_setting( 'jsn_group', 'jsn_newsletter_email_color_principal' );
        register_setting( 'jsn_group', 'jsn_newsletter_email_color_boton' );
        register_setting( 'jsn_group', 'jsn_newsletter_email_color_boton_texto' );
    }

    public function imprimir_configuracion() {
        ?>
        <div class="jsn-card" id="mod-newsletter">
            <div class="jsn-card-head">
                <div>
                    <p class="jsn-eyebrow">Módulo</p>
                    <h2>✉️ Newsletter <span class="jsn-tip" data-tip="Shortcode minimalista, envío HTML, almacenamiento y exportación CSV.">?</span></h2>
                    <p class="description">Captura correos, envía HTML y exporta CSV.</p>
                </div>
                <label class="jsn-toggle">
                    <input type="checkbox" name="jsn_newsletter_enabled" value="1" <?php checked( 1, get_option( 'jsn_newsletter_enabled', 1 ), true ); ?> />
                    <span>Activo</span>
                </label>
            </div>

            <div class="jsn-grid">
                <div>
                    <h4>Formulario</h4>
                    <label>Color del botón <input type="color" name="jsn_newsletter_button_color" value="<?php echo esc_attr( get_option( 'jsn_newsletter_button_color', '#0073aa' ) ); ?>"></label>
                    <label>Texto del botón <input type="text" name="jsn_newsletter_button_text" value="<?php echo esc_attr( get_option( 'jsn_newsletter_button_text', 'Enviar' ) ); ?>" class="regular-text"></label>
                    <label>Título visible <input type="text" name="jsn_newsletter_title" value="<?php echo esc_attr( get_option( 'jsn_newsletter_title', 'Suscríbete y recibe novedades' ) ); ?>" class="large-text"></label>
                    <label>Descripción <textarea name="jsn_newsletter_description" rows="2" class="large-text"><?php echo esc_textarea( get_option( 'jsn_newsletter_description', 'Ingresa tu correo para recibir promociones y noticias exclusivas.' ) ); ?></textarea></label>
                    <label>Etiqueta del campo <input type="text" name="jsn_newsletter_label" value="<?php echo esc_attr( get_option( 'jsn_newsletter_label', 'Correo electrónico' ) ); ?>" class="regular-text"></label>
                    <label>Placeholder <input type="text" name="jsn_newsletter_placeholder" value="<?php echo esc_attr( get_option( 'jsn_newsletter_placeholder', 'tu@correo.com' ) ); ?>" class="regular-text"></label>
                    <label>Texto de éxito <input type="text" name="jsn_newsletter_success_text" value="<?php echo esc_attr( get_option( 'jsn_newsletter_success_text', '¡Gracias! Revisa tu bandeja de entrada para ver tu cupón.' ) ); ?>" class="large-text"></label>
                    <label>Disclaimer <textarea name="jsn_newsletter_disclaimer" rows="3" class="large-text"><?php echo esc_textarea( get_option( 'jsn_newsletter_disclaimer', 'Acepto la política de tratamiento de datos conforme a la Ley 1581 de 2012 (Colombia).' ) ); ?></textarea></label>
                </div>
                <div>
                    <h4>Correo enviado</h4>
                    <label>Asunto <input type="text" name="jsn_newsletter_subject" value="<?php echo esc_attr( get_option( 'jsn_newsletter_subject', 'Gracias por suscribirte a nuestras novedades' ) ); ?>" class="large-text"></label>
                    <label>Código de cupón <input type="text" name="jsn_newsletter_coupon_code" value="<?php echo esc_attr( get_option( 'jsn_newsletter_coupon_code', 'VERANO15' ) ); ?>" class="regular-text"></label>
                    <label>Color principal <input type="color" name="jsn_newsletter_email_color_principal" value="<?php echo esc_attr( get_option( 'jsn_newsletter_email_color_principal', '#1e2632' ) ); ?>"></label>
                    <label>Color de fondo <input type="color" name="jsn_newsletter_email_color_fondo" value="<?php echo esc_attr( get_option( 'jsn_newsletter_email_color_fondo', '#f4f4f4' ) ); ?>"></label>
                    <label>Color de texto <input type="color" name="jsn_newsletter_email_color_texto" value="<?php echo esc_attr( get_option( 'jsn_newsletter_email_color_texto', '#666666' ) ); ?>"></label>
                    <label>Color botón <input type="color" name="jsn_newsletter_email_color_boton" value="<?php echo esc_attr( get_option( 'jsn_newsletter_email_color_boton', '#1e2632' ) ); ?>"></label>
                    <label>Color texto botón <input type="color" name="jsn_newsletter_email_color_boton_texto" value="<?php echo esc_attr( get_option( 'jsn_newsletter_email_color_boton_texto', '#ffffff' ) ); ?>"></label>
                </div>
                <div>
                    <h4>Plantilla HTML</h4>
                    <textarea name="jsn_newsletter_email_html" rows="16" class="large-text code"><?php
                        echo esc_textarea(
                            get_option(
                                'jsn_newsletter_email_html',
                                $this->obtener_plantilla_por_defecto()
                            )
                        );
                    ?></textarea>
                    <p class="description">Usa <code>%COUPON_CODE%</code> y los tokens <code>%COLOR_PRINCIPAL%</code>, <code>%COLOR_FONDO%</code>, <code>%COLOR_TEXTO%</code>, <code>%COLOR_BOTON%</code>, <code>%COLOR_BOTON_TEXTO%</code>.</p>
                </div>
            </div>
            <div class="jsn-foot-note">
                Shortcode: <code>[jsn_newsletter]</code> • Exportar CSV: <a class="button" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=jsn_exportar_correos' ), 'jsn_exportar_correos' ) ); ?>">Descargar correos</a>
            </div>
        </div>
        <?php
    }

    public function renderizar_shortcode( $atributos = array() ) {
        $atributos = shortcode_atts(
            array(
                'activar'     => '',
                'color_boton' => '',
                'titulo'      => '',
                'descripcion' => '',
                'etiqueta'    => '',
                'placeholder' => '',
                'texto_boton' => '',
                'disclaimer'  => '',
                'texto_exito' => '',
            ),
            $atributos,
            'jsn_newsletter'
        );

        $activado = $this->interpretar_activacion( $atributos['activar'], get_option( 'jsn_newsletter_enabled', 1 ) );
        if ( ! $activado ) {
            return '<p>El formulario de newsletter está desactivado.</p>';
        }

        $color      = esc_attr( $this->resolver_atributo_texto( $atributos['color_boton'], get_option( 'jsn_newsletter_button_color', '#0073aa' ) ) );
        $disclaimer = wp_kses_post( $this->resolver_atributo_texto( $atributos['disclaimer'], get_option( 'jsn_newsletter_disclaimer', 'Acepto la política de tratamiento de datos conforme a la Ley 1581 de 2012 (Colombia).' ) ) );
        $titulo     = esc_html( $this->resolver_atributo_texto( $atributos['titulo'], get_option( 'jsn_newsletter_title', 'Suscríbete y recibe novedades' ) ) );
        $descripcion= wp_kses_post( $this->resolver_atributo_texto( $atributos['descripcion'], get_option( 'jsn_newsletter_description', 'Ingresa tu correo para recibir promociones y noticias exclusivas.' ) ) );
        $label      = esc_html( $this->resolver_atributo_texto( $atributos['etiqueta'], get_option( 'jsn_newsletter_label', 'Correo electrónico' ) ) );
        $placeholder= esc_attr( $this->resolver_atributo_texto( $atributos['placeholder'], get_option( 'jsn_newsletter_placeholder', 'tu@correo.com' ) ) );
        $btn_text   = esc_html( $this->resolver_atributo_texto( $atributos['texto_boton'], get_option( 'jsn_newsletter_button_text', 'Enviar' ) ) );
        $texto_exito = esc_html( $this->resolver_atributo_texto( $atributos['texto_exito'], get_option( 'jsn_newsletter_success_text', '¡Gracias! Revisa tu bandeja de entrada para ver tu cupón.' ) ) );
        $nonce      = wp_create_nonce( 'jsn_newsletter_nonce' );

        ob_start();
        ?>
        <form class="jsn-newsletter-form" data-endpoint="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" data-nonce="<?php echo esc_attr( $nonce ); ?>" data-texto-exito="<?php echo esc_attr( $texto_exito ); ?>" style="display:flex; flex-direction:column; gap:10px; width:100%; max-width:520px;">
            <div class="jsn-newsletter-texto" style="display:flex; flex-direction:column; gap:4px;">
                <div style="font-weight:700; font-size:18px; color:#111827;"><?php echo $titulo; ?></div>
                <div style="font-size:14px; color:#4b5563; line-height:1.5;"><?php echo $descripcion; ?></div>
            </div>
            <div class="jsn-newsletter-campo" style="display:flex; flex-direction:column; gap:4px;">
                <label for="jsn-newsletter-email" style="font-size:14px; font-weight:600; color:#111827;"><?php echo $label; ?></label>
                <input type="email" id="jsn-newsletter-email" name="email" required placeholder="<?php echo $placeholder; ?>" style="width:100%; padding:12px 14px; border:1px solid #d1d5db; border-radius:4px; font-size:14px; color:#111827; background:#fff; box-sizing:border-box;">
            </div>
            <button type="submit" style="width:100%; background:<?php echo $color; ?>; color:#fff; border:none; padding:12px 14px; border-radius:4px; cursor:pointer; font-weight:700; text-transform:uppercase; letter-spacing:0.4px;"><?php echo $btn_text; ?></button>
            <p class="jsn-newsletter-disclaimer" style="font-size:12px; color:#4b5563; line-height:1.5; margin:0;"><?php echo $disclaimer; ?></p>
            <div class="jsn-newsletter-mensaje" aria-live="polite" style="margin-top:4px; font-size:13px; min-height:18px; color:#374151;"></div>
        </form>
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
                            mensaje.textContent = form.dataset.textoExito || res.data.mensaje || 'Registro exitoso.';
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
        $plantilla = $this->reemplazar_tokens_plantilla( $plantilla, $cupon );

        $contenido = $this->sanitizar_html_correo( $plantilla );

        $headers = array( 'Content-Type: text/html; charset=UTF-8' );
        wp_mail( $email, $asunto, $contenido, $headers );

        $exito = get_option( 'jsn_newsletter_success_text', '¡Gracias! Revisa tu bandeja de entrada para ver tu cupón.' );
        wp_send_json_success( array( 'mensaje' => $exito ) );
    }

    private function sanitizar_html_correo( $html ) {
        $permitidos = wp_kses_allowed_html( 'post' );

        $extra = array(
            'html' => array(
                'lang'  => true,
                'xmlns' => true,
                'dir'   => true,
                'class' => true,
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
                'bgcolor'     => true,
                'class'       => true,
            ),
            'tr' => array(
                'align' => true,
                'style' => true,
                'bgcolor' => true,
                'class' => true,
            ),
            'td' => array(
                'align'   => true,
                'style'   => true,
                'width'   => true,
                'height'  => true,
                'valign'  => true,
                'colspan' => true,
                'rowspan' => true,
                'bgcolor' => true,
                'class'   => true,
            ),
            'th' => array(
                'align'   => true,
                'style'   => true,
                'width'   => true,
                'height'  => true,
                'valign'  => true,
                'colspan' => true,
                'rowspan' => true,
                'bgcolor' => true,
                'class'   => true,
            ),
            'tbody' => array( 'style' => true, 'class' => true ),
            'thead' => array( 'style' => true, 'class' => true ),
            'tfoot' => array( 'style' => true, 'class' => true ),
            'img' => array(
                'src'    => true,
                'alt'    => true,
                'width'  => true,
                'height' => true,
                'style'  => true,
                'class'  => true,
            ),
            'div' => array(
                'style' => true,
                'align' => true,
                'class' => true,
            ),
            'span' => array(
                'style' => true,
                'class' => true,
            ),
            'p' => array(
                'style' => true,
                'align' => true,
                'class' => true,
            ),
            'h1' => array(
                'style' => true,
                'align' => true,
                'class' => true,
            ),
            'h2' => array(
                'style' => true,
                'align' => true,
                'class' => true,
            ),
            'a' => array(
                'href'   => true,
                'style'  => true,
                'target' => true,
                'title'  => true,
                'class'  => true,
            ),
            'style' => array(),
            'body'  => array(
                'style' => true,
                'class' => true,
            ),
        );

        $permitidos = array_merge( $permitidos, $extra );

        $sanitizado = wp_kses( $html, $permitidos );
        if ( '' === trim( $sanitizado ) ) {
            $cupon = get_option( 'jsn_newsletter_coupon_code', 'VERANO15' );
            $sanitizado = $this->reemplazar_tokens_plantilla(
                $this->obtener_plantilla_por_defecto( $cupon ),
                $cupon
            );
        }
        return $sanitizado;
    }

    private function reemplazar_tokens_plantilla( $plantilla, $cupon ) {
        $tokens = array(
            '%COUPON_CODE%'       => esc_html( $cupon ),
            '%COLOR_PRINCIPAL%'   => esc_attr( get_option( 'jsn_newsletter_email_color_principal', '#1e2632' ) ),
            '%COLOR_FONDO%'       => esc_attr( get_option( 'jsn_newsletter_email_color_fondo', '#f4f4f4' ) ),
            '%COLOR_TEXTO%'       => esc_attr( get_option( 'jsn_newsletter_email_color_texto', '#666666' ) ),
            '%COLOR_BOTON%'       => esc_attr( get_option( 'jsn_newsletter_email_color_boton', '#1e2632' ) ),
            '%COLOR_BOTON_TEXTO%' => esc_attr( get_option( 'jsn_newsletter_email_color_boton_texto', '#ffffff' ) ),
        );

        return strtr( $plantilla, $tokens );
    }

    private function obtener_plantilla_por_defecto( $codigo = '%COUPON_CODE%' ) {
        return "<!DOCTYPE html>\n<html lang=\"es\">\n<head>\n    <meta charset=\"UTF-8\">\n    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\n    <title>Descuento La Veranera</title>\n    <style>\n        body { margin: 0; padding: 0; background-color: %COLOR_FONDO%; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; }\n        table { border-collapse: collapse; }\n        img { display: block; max-width: 100%; height: auto; }\n        .wrapper { width: 100%; table-layout: fixed; background-color: %COLOR_FONDO%; padding-bottom: 40px; }\n        .main-content { background-color: #ffffff; margin: 0 auto; width: 100%; max-width: 600px; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 10px rgba(0,0,0,0.05); }\n        .btn-primary { background-color: %COLOR_BOTON%; color: %COLOR_BOTON_TEXTO% !important; text-decoration: none; padding: 12px 30px; border-radius: 4px; display: inline-block; font-weight: bold; font-size: 16px; letter-spacing: 0.5px; }\n    </style>\n</head>\n<body>\n    <div class=\"wrapper\">\n        <table align=\"center\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">\n            <tr>\n                <td align=\"center\" style=\"padding: 20px 0;\">\n                    </td>\n            </tr>\n            <tr>\n                <td align=\"center\">\n                    <table class=\"main-content\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\">\n                        <tr>\n                            <td align=\"center\" style=\"padding: 40px 20px 20px 20px; background-color: #ffffff;\">\n                                <img src=\"https://laveranera.prototipo.com.co/wp-content/uploads/2025/07/La-veranera.png\" alt=\"Logo La Veranera\" width=\"180\" style=\"width: 180px;\">\n                            </td>\n                        </tr>\n\n                        <tr>\n                            <td align=\"center\" style=\"background-color: #fafafa; padding: 0;\">\n                                <div style=\"height: 2px; background-color: #eee; width: 90%;\"></div>\n                            </td>\n                        </tr>\n\n                        <tr>\n                            <td align=\"center\" style=\"padding: 40px 30px; text-align: center;\">\n                                <h1 style=\"color: %COLOR_PRINCIPAL%; font-family: 'Georgia', serif; font-size: 24px; margin-bottom: 20px; text-transform: uppercase; letter-spacing: 1px;\">\n                                    ¡Gracias por unirte!\n                                </h1>\n                                <p style=\"color: %COLOR_TEXTO%; font-size: 16px; line-height: 1.6; margin-bottom: 30px;\">\n                                    Estamos felices de que seas parte de la familia <strong>La Veranera</strong>. Como lo prometimos, aquí tienes un obsequio especial para que disfrutes de nuestras tablas de quesos y sangrías con una sonrisa.\n                                </p>\n                                \n                                <div style=\"background-color: #f9f9f9; border: 2px dashed %COLOR_PRINCIPAL%; padding: 20px; display: inline-block; margin-bottom: 30px; border-radius: 4px;\">\n                                    <span style=\"display: block; font-size: 12px; color: #888; margin-bottom: 5px; text-transform: uppercase;\">Tu código de descuento:</span>\n                                    <span style=\"font-family: 'Helvetica', sans-serif; font-size: 28px; color: %COLOR_PRINCIPAL%; font-weight: 800; letter-spacing: 2px;\">{$codigo}</span>\n                                </div>\n                                \n                                <p style=\"color: %COLOR_TEXTO%; font-size: 14px; margin-bottom: 30px;\">\n                                    Presenta este cupón en nuestro punto físico o úsalo en tu próxima reserva.\n                                </p>\n\n                                <a href=\"https://laveranerasangriaoficial.com/\" target=\"_blank\" class=\"btn-primary\">\n                                    VER MENÚ COMPLETO\n                                </a>\n                            </td>\n                        </tr>\n\n                        <tr>\n                            <td align=\"center\" style=\"background-color: %COLOR_PRINCIPAL%; padding: 30px;\">\n                                <p style=\"color: #ffffff; font-size: 14px; margin: 0 0 10px 0; font-family: 'Georgia', serif;\">La Veranera</p>\n                                <p style=\"color: #d1d5db; font-size: 12px; margin: 0;\">\n                                    Sangría & Tablas de Quesos<br>\n                                    Bucaramanga, Santander\n                                </p>\n                            </td>\n                        </tr>\n                    </table>\n                </td>\n            </tr>\n            <tr>\n                <td align=\"center\" style=\"padding: 20px 0;\">\n                    <p style=\"font-size: 11px; color: #999999;\">\n                        Recibiste este correo porque te suscribiste en nuestro sitio web.\n                    </p>\n                </td>\n            </tr>\n        </table>\n    </div>\n</body>\n</html>";
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
