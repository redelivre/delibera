<?php

/**
 * Criar a página about
 */
function delibera_create_about_page()
{
	global $post;
	$post_tmp = $post;
	$post = array(
			'post_name' => DELIBERA_ABOUT_PAGE,
			'post_title' => __('Sobre a plataforma', 'delibera'),
			'post_content' => __('Use está página para explicar para os usuários como utilizar o sistema', 'delibera'),
			'post_type' => 'page',
			'post_status' => 'publish',
	);
	wp_insert_post($post);
	$post = $post_tmp;
}

/*
 * Rotinas de instalação do plugin
*/
function delibera_instalacao()
{
	if(is_multisite())
	{
		delibera_wpmu_new_blog(get_current_blog_id());
	}

	if (!get_page_by_slug(DELIBERA_ABOUT_PAGE)) {
		delibera_create_about_page();
	}
}
register_activation_hook(WP_PLUGIN_DIR.'/delibera/delibera.php', 'delibera_instalacao');

function delibera_install_roles()
{
	// simple check to see if pautas capabilities are in place. We only set them if not.
	$Role = get_role('administrator');
	if(!$Role->has_cap('publish_pautas'))
	{
		// Inicialização das configurações padrão
		$opt = delibera_get_config();

		update_option('delibera-config', $opt);
		if(file_exists(__DIR__.DIRECTORY_SEPARATOR.'delibera_roles.php'))
		{
			$delibera_permissoes = array();
			require_once __DIR__.DIRECTORY_SEPARATOR.'delibera_roles.php';
			delibera_roles_install($delibera_permissoes);
		}
	}
}
add_action('admin_init', 'delibera_install_roles');

function delibera_roles_install($delibera_permissoes)
{

	// Criação das regras
	foreach ($delibera_permissoes as $nome => $permisao)
	{
		if($permisao['Novo'] == true)
		{
			$Role = get_role($permisao['From']);
				
			if(!is_object($Role))
			{
				throw new Exception(sprintf(__('Permissão original (%s) não localizada','delibera'),$permisao['From']));
			}
				
			$cap = $Role->capabilities;
			add_role($nome, $permisao["nome"], $cap);
		}

		$Role = get_role($nome);
		if(!is_object($Role))
		{
			throw new Exception(sprintf(__('Permissão %s não localizada','delibera'),$nome));
		}

		foreach ($permisao['Caps'] as $cap)
		{
				
			$Role->add_cap($cap);
		}
	}

}

function delibera_roles_uninstall($delibera_permissoes)
{

	foreach ($delibera_permissoes as $nome => $permisao)
	{
		if($permisao['Novo'] == true)
		{
			remove_role($nome);
		}
		else
		{
			$Role = get_role($nome);
			if(!is_object($Role))
			{
				throw new Exception(sprintf(__('Permissão %s não localizada','delibera'),$nome));
			}

			foreach ($permisao['Caps'] as $cap)
			{
				$Role->remove_cap($cap);
			}
		}
	}

}

/*
 * Desinstalação do Plugin
*/
function delibera_desinstalacao()
{
	delete_option('delibera-config');
	if(file_exists(__DIR__.DIRECTORY_SEPARATOR.'delibera_roles.php'))
	{
		$delibera_permissoes = array();
		require_once __DIR__.DIRECTORY_SEPARATOR.'delibera_roles.php';
		delibera_roles_uninstall($delibera_permissoes);
	}
}
register_deactivation_hook( __FILE__, 'delibera_desinstalacao' );


/**
 * 	Para Multisites
 */
function delibera_wpmu_new_blog($blog_id)
{
	if($blog_id != 1)
	{
		$id = get_current_blog_id(); // Qual o blog que chamou essa função
		if($id != 1)
		{
			switch_to_blog(1); // Precisamos pegar o permalink e as linguas no caso do qtranlate ativo do blog raíz 
		}
		/** Antes de mudar **/
		$permalink_structure = get_option('permalink_structure');
		$qtrans = array();
		if(function_exists('qtrans_enableLanguage'))
		{
			$qtrans['enabled_languages'] = get_option('qtranslate_enabled_languages');
			$qtrans['default_language'] = get_option('qtranslate_default_language');
		}
		
		/** Depois de mudar de blog temos que ir para o novo blog onde o plugin foi ativado **/
		if($id != 1)
		{
			restore_current_blog(); // Volta se antes estava no blog novo
		}
		else
		{
			switch_to_blog($blog_id); // Ou vai se não estava
		}
		
		if(function_exists('qtrans_enableLanguage'))
		{
			update_option('qtranslate_enabled_languages', $qtrans['enabled_languages']);
			update_option('qtranslate_default_language', $qtrans['default_language']);
		}
		update_option('permalink_structure', $permalink_structure);
		
		delibera_init(); // Criando o post type
		
		flush_rewrite_rules();
		
		if($id == 1) // se estávamos no blog 1, vamos voltar para ele
		{
			restore_current_blog();
		}
	}
}

/**
 * 	Para Multisites
 */
function delibera_wpmu_new_blog_action($blog_id, $user_id = 0, $domain = '', $path = '', $site_id = '', $meta = '' )
{
	delibera_wpmu_new_blog($blog_id);
}
add_action('wpmu_new_blog','delibera_wpmu_new_blog_action',90,6);


?>