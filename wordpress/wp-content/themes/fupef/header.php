<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
	<meta charset="<?php bloginfo('charset');?>">	
	<meta name="description" content="<?php bloginfo('name'); ?> - <?php echo get_bloginfo('description'); ?>">
	<meta name="keywords" content="">
	<meta name="viewport" content="user-scalable=0, initial-scale=1.0">

	<title><?php bloginfo( 'name' ); ?> <?php wp_title(); ?></title>

	<link rel="shortcut icon" href="<?php bloginfo('template_url');?>/public/img/favicon.png">
	<link rel="apple-touch-icon" href="<?php bloginfo('template_url');?>/public/img/favicon.png">
	
	<link href="<?php bloginfo('stylesheet_directory'); ?>/style.css" rel="stylesheet">
	
	<base href="<?php bloginfo(url); ?>">

	<?php wp_head(); ?>
</head>

<body>
	<header role="banner">
		<div class="container">
			<a href="<?php bloginfo('url'); ?>" title="<?php bloginfo('name'); ?>"><img src="<?php bloginfo('template_url') ?>/public/img/logo.png"></a>			

			<nav role="navigation">
				<div class="container">
					<?php $menu_principal = array('container' => false, 'menu' => 'Menu Principal', 'echo' => false, 'items_wrap' => '%3$s', 'depth' => 0 );
					echo strip_tags(wp_nav_menu( $menu_principal ), '<a>' ); ?>
				</div>
			</nav>
		</div>
	</header>

	<div class="search-form">
		<div class="container">
			<form method="get" action="<?php bloginfo('home'); ?>/">
				<input name="s" type="search" placeholder="Pesquise" required>
				<input type="submit" value="Buscar">
			</form>
		</div>
	</div>


	<div class="container">