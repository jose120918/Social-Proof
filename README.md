# Social Proof (Pro) – Documentación técnica y ejecutiva

## Descripción general
Plugin modular de Social Proof para WooCommerce y WordPress que permite activar o desactivar, desde un panel central independiente, las siguientes funciones:

- Indicador de visitas en fichas de producto.
- Ventana flotante con compras recientes (datos reales o simulados).
- Aviso de precio dinámico (mensaje informativo, sin alterar precios).
- Captación de correos para newsletter con envío automático y exportación CSV.

> Cumple con la Ley 1581 de 2012 (Colombia) al incluir un texto de consentimiento editable en el formulario de newsletter.

## Instalación
1. Copia la carpeta del plugin en `wp-content/plugins`.
2. Activa el plugin desde **Plugins** en el escritorio de WordPress.
3. Accede al menú **Social Proof** (menú superior izquierdo del administrador) para configurar cada módulo.

## Panel central y módulos
Todas las opciones se almacenan mediante la API de Settings (`jsn_group`). Cada módulo tiene su propia tarjeta dentro del panel:

### 1) Indicador de visitas
- **Activar módulo**: `jsn_pv_enabled`.
- Rango: `jsn_pv_min`, `jsn_pv_max`.
- Texto: `jsn_pv_text` admite `%n`, `%title%`, `%category%`.
- Estilos: `jsn_pv_bg_color`, `jsn_pv_text_color`, `jsn_pv_font_size`, `jsn_pv_icon_class`.
- **Shortcode**: `[jsn_viewer]` (útil para Elementor).
- Hook automático: `woocommerce_single_product_summary` (prioridad 15).

### 2) Ventana flotante de refuerzo de compra
- **Activar módulo**: `jsn_popup_enabled`.
- Fuente: `jsn_mode` (`fake` o `real`).
- Texto y comportamiento: `jsn_heading_text`, `jsn_interval`, `jsn_position`, `jsn_show_mobile`.
- Estilos: `jsn_close_bg_color`, `jsn_close_text_color`.
- Modo simulado: `jsn_product_ids`, `jsn_cities`.
- Cacheo de pedidos reales durante 10 minutos (`jsn_real_orders_cache`).

### 3) Dinámica de precios
- **Activar módulo**: `jsn_price_enabled`.
- Mensaje: `jsn_price_label`.
- Estilos: `jsn_price_bg`, `jsn_price_text`.
- Hook: `woocommerce_single_product_summary` (prioridad 11). Solo muestra un aviso, no modifica precios.

### 4) Newsletter y captación de correos
- **Activar módulo**: `jsn_newsletter_enabled`.
- Apariencia: `jsn_newsletter_button_color`.
- Legal: `jsn_newsletter_disclaimer` (incluye referencia a Ley 1581 de 2012 por defecto).
- Contenido del envío: `jsn_newsletter_subject`, `jsn_newsletter_email_html`.
- Shortcode: `[jsn_newsletter]` (compatible con Elementor).
- Almacenamiento: tabla `{$wpdb->prefix}jsn_newsletter` creada en la activación.
- Exportación: botón de descarga CSV desde el panel (`admin-post.php?action=jsn_exportar_correos`).
- Procesamiento: AJAX `jsn_guardar_correo` valida nonce, guarda correo, envía email HTML y responde en JSON. Usa listener global sobre `submit` para evitar recargas en pop-ups y contenidos insertados dinámicamente (Elementor, modales, etc.).
- Estilos del formulario: layout responsivo con botón a ancho completo, separación entre campos y textos, y envolvente con bordes y sombra suave para mayor legibilidad en pop-ups.
- Cupón y confirmación: `jsn_newsletter_coupon_code` para rellenar el placeholder `%COUPON_CODE%` en la plantilla HTML (incluida por defecto con el diseño proporcionado) y `jsn_newsletter_success_text` para el mensaje de éxito que ve el usuario.

## Flujo de datos y consideraciones técnicas
- **Seguridad**: uso de `wp_verify_nonce`, saneado de entradas con `sanitize_email`, `sanitize_text_field`, y sanitización de HTML con `wp_kses_post`.
- **Compatibilidad**: sin dependencias externas; se apoya en WooCommerce si está activo para datos reales del popup.
- **Rendimiento**: caché de pedidos reales en transiente y uso moderado de scripts en línea por módulo activo.
- **Accesibilidad/UX**: mensajes cortos en español, formularios con estado de envío y validación básica; botón a ancho completo y espaciados mejorados en móviles y pop-ups.

## Personalización rápida
- Cambiar colores y textos desde el panel **Social Proof**.
- Insertar shortcodes en plantillas, widgets o Elementor.
- Editar la plantilla HTML del correo directamente en el campo “HTML del correo”.

## Exportación y cumplimiento
- Descarga CSV con todos los correos registrados (email, fecha, IP).
- El formulario incluye por defecto el texto de aceptación de tratamiento de datos conforme a la Ley 1581 de 2012 (Colombia); se puede ajustar desde el panel.

## Desarrollo y estructura
- Archivo principal: `plugin-social.php`.
- Clases por módulo: `JSN_Modulo_Visitas`, `JSN_Modulo_Notificaciones`, `JSN_Modulo_Precio`, `JSN_Modulo_Newsletter`.
- Núcleo y activación: `JSN_Social_Proof_Plugin` (crea tabla, registra menú, ajustes, shortcodes y exportaciones).

## Recomendaciones de despliegue
- Probar en entorno de staging antes de producción.
- Verificar envío de correos configurando un proveedor SMTP en el sitio.
- Para evitar sobrecarga en tiendas grandes, mantener la caché del popup en modo real habilitada (valor por defecto).
