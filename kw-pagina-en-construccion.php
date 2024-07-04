<?php
/**
 * Plugin Name: Página en Construcción
 * Description: Permite seleccionar una página para mostrar como página en construcción y activar o desactivar el estado de mantenimiento. Compatible con todos los temas de WordPress.
 * Version: 1.1
 * Author: Albert Bustos
 * Author URI: https://kreaweb.cl
 * Plugin URI: https://kreaweb.cl
 * License: GPL2
 */

// Evita el acceso directo al archivo
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Añade una página de opciones en el menú de administración
function pec_menu() {
    add_options_page(
        'Página en Construcción',
        'Página en Construcción',
        'manage_options',
        'pec-configuracion',
        'pec_opciones_menu'
    );
}
add_action( 'admin_menu', 'pec_menu' );

// Muestra el formulario de opciones en el panel de administración
function pec_opciones_menu() {
    $activado = get_option( 'pec_activado' );
    ?>
    <div class="wrap pec-wrap">
        <h1>KW - Página en Construcción</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields( 'pec_opciones_grupo' );
            do_settings_sections( 'pec-configuracion' );
            submit_button();
            ?>
        </form>
        <div class="pec-estado">
            <?php if ( $activado === '1' ): ?>
                <div class="estado-construccion">El sitio está en <strong>Construcción</strong></div>
            <?php else: ?>
                <div class="estado-activo">El sitio está <strong>Activo</strong></div>
            <?php endif; ?>
        </div>
    </div>
    <style>
        .pec-wrap {
            background-color: #f9f9f9;
            border: 1px solid #e1e1e1;
            padding: 20px;
            border-radius: 5px;
            max-width: 800px;
            margin: 20px auto;
        }
        .pec-wrap h1 {
            color: #0073aa;
        }
        .pec-wrap table.form-table {
            width: 100%;
            background: #fff;
            border: 1px solid #e1e1e1;
            border-radius: 5px;
            padding: 20px;
        }
        .pec-wrap table.form-table th {
            padding: 10px;
        }
        .pec-wrap table.form-table td {
            padding: 10px;
        }
        .pec-wrap .submit {
            text-align: center;
        }
        .pec-wrap input[type="checkbox"] {
            margin-top: 5px;
        }
        .pec-estado {
            margin-top: 20px;
            text-align: center;
        }
        .estado-construccion {
            color: #d32f2f;
            font-weight: bold;
            font-size: 18px;
        }
        .estado-activo {
            color: #388e3c;
            font-weight: bold;
            font-size: 18px;
        }
    </style>
    <?php
}

// Registra los ajustes del plugin
function pec_registrar_opciones() {
    register_setting( 'pec_opciones_grupo', 'pec_pagina_id' );
    register_setting( 'pec_opciones_grupo', 'pec_activado' );

    add_settings_section(
        'pec_seccion_general',
        'Configuración General',
        'pec_seccion_general_callback',
        'pec-configuracion'
    );

    add_settings_field(
        'pec_pagina_id',
        'Selecciona la Página en Construcción',
        'pec_pagina_id_callback',
        'pec-configuracion',
        'pec_seccion_general'
    );

    add_settings_field(
        'pec_activado',
        'Activar Modo de Mantenimiento',
        'pec_activado_callback',
        'pec-configuracion',
        'pec_seccion_general'
    );
}
add_action( 'admin_init', 'pec_registrar_opciones' );

function pec_seccion_general_callback() {
    echo '<p>Configura la página en construcción y activa o desactiva el modo de mantenimiento.</p>';
}

function pec_pagina_id_callback() {
    $pagina_id = get_option( 'pec_pagina_id' );
    wp_dropdown_pages( array(
        'name' => 'pec_pagina_id',
        'selected' => $pagina_id,
        'show_option_none' => 'Selecciona una página',
    ) );
}

function pec_activado_callback() {
    $activado = get_option( 'pec_activado' );
    ?>
    <input type="checkbox" name="pec_activado" value="1" <?php checked( 1, $activado, true ); ?> />
    <label for="pec_activado">Activar</label>
    <?php
}

// Redirigir a la página en construcción
function pec_redirigir_mantenimiento() {
    if ( current_user_can( 'manage_options' ) ) {
        return;
    }
    if ( get_option( 'pec_activado' ) === '1' ) {
        $pagina_id = get_option( 'pec_pagina_id' );
        if ( $pagina_id ) {
            $url_pagina = get_permalink( $pagina_id );
            if ( !is_page( $pagina_id ) ) {
                wp_redirect( $url_pagina );
                exit;
            }
        }
    }
}
add_action( 'template_redirect', 'pec_redirigir_mantenimiento' );

// Agregar botón en la barra de herramientas de WordPress
function pec_agregar_toolbar($wp_admin_bar) {
    $activado = get_option( 'pec_activado' );
    $args = array(
        'id'    => 'pec_estado',
        'title' => $activado ? '<span style="color: #d32f2f;">Página en Construcción</span>' : '<span style="color: #388e3c;">Página Activa</span>',
        'href'  => admin_url('options-general.php?page=pec-configuracion'),
        'meta'  => array(
            'class' => 'pec-toolbar',
        ),
    );
    $wp_admin_bar->add_node( $args );
}
add_action('admin_bar_menu', 'pec_agregar_toolbar', 100);

// Añadir enlace para ir a la configuración desde la página de plugins
function pec_enlace_configuracion($links) {
    $settings_link = '<a href="options-general.php?page=pec-configuracion">Configurar</a>';
    array_unshift($links, $settings_link);
    return $links;
}
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'pec_enlace_configuracion');
?>
