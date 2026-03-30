<?php
/* Funciones del tema hijo */

add_action( 'wp_enqueue_scripts', 'inmopress_enqueue_styles' );
function inmopress_enqueue_styles() {
	wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' );
}

// Aquí moveremos tu código personalizado.
