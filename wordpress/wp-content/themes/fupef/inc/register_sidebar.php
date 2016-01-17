<?php

/**
 * Registrando SideBars
 *
 * Esse arquivo tem como objetivo criar Sidebars
 * dentro do WP-ADMIN
 * 
 * @author Kaleb Martins <kaleb@paradoxodigital.com.br>
 * @date (setembro 2015)
 * 
 */

if(function_exists('register_sidebar')){
	register_sidebar( array(
		'name'          => 'Sidebar',
		'id'            => 'sidebar',
		'before_widget' => '<div id="%1$s" class="%2$s widgets">',
		'after_widget'  => '</div>',		
		));
}
