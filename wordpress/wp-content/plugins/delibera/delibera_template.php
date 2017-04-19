<?php

	require_once __DIR__.DIRECTORY_SEPARATOR.'delibera_fake_page.php';
	function delibera_template_menu_action($base_page)
	{
		add_submenu_page($base_page, __('Templates','delibera'), __('Templates Padrões','delibera'), 'manage_options', 'delibera-templates', 'delibera_template_page' );
	}
	add_action('delibera_menu_itens', 'delibera_template_menu_action', 10, 1);

	function delibera_template_page()
	{
		//load_template(dirname(__FILE__).DIRECTORY_SEPARATOR.'themes'.DIRECTORY_SEPARATOR.'archive-pauta.php', true);
	}
	
?>