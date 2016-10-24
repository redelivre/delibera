<?php

// PHP 5.3 and later:
namespace Delibera\Conf;

class Permission
{
	protected $roleList = array(
		
	);

	public function __construct()
	{
		$this->roleList = array(
			array(
				'id' => 'delete_pautas',
				'label' => __('remover pautas','delibera'),
			),
			array(
				'id' => 'delete_private_pautas',
				'label' => __('remover pautas privadas','delibera'),
			),
			array(
				'id' => 'edit_pauta',
				'label' => __('editar pauta','delibera'),
			),
			array(
				'id' => 'edit_pautas',
				'label' => __('editar multiplas pautas','delibera'),
			),
			array(
				'id' => 'edit_private_pautas',
				'label' => __('editar multiplas pautas privadas','delibera'),
			),
			array(
				'id' => 'publish_pautas',
				'label' => __('publicar pautas','delibera'),
			),
			array(
				'id' => 'read_pauta',
				'label' => __('visualizar pautas','delibera'),
			),
			array(
				'id' => 'read_private_pautas',
				'label' => __('visualizar pautas privadas','delibera'),
			),
			array(
				'id' => 'delete_published_pautas',
				'label' => __('remover pautas publicadas','delibera'),
			),
			array(
				'id' => 'forcar_prazo',
				'label' => __('forcar o final de prazo','delibera'),
			),
			array(
				'id' => 'delibera_reabrir_pauta',
				'label' => __('reabrir pauta','delibera'),
			),
			array(
				'id' => 'edit_published_pautas',
				'label' => __('editar multiplas pautas publicadas','delibera'),
			),
			array(
				'id' => 'edit_published_pauta',
				'label' => __('editar pauta publicada','delibera'),
			),
			array(
				'id' => 'edit_encaminhamento',
				'label' => __('editar encaminhamentos','delibera'),
			),
			array(
				'id' => 'votar',
				'label' => __('votar','delibera'),
			),
			array(
				'id' => 'relatoria',
				'label' => __('fazer relatoria','delibera'),
			),
			array(
				'id' => 'edit_others_pautas',
				'label' => __('editar multiplas pautas de outras pessoas','delibera'),
			),
			array(
				'id' => 'edit_others_pauta',
				'label' => __('editar pauta de outras pessoas','delibera'),
			),
			array(
				'id' => 'delete_others_pautas',
				'label' => __('remover multiplas pautas de outras pessoas','delibera'),
			),
			array(
				'id' => 'manage_tema_term',
				'label' => __('gerenciar temas','delibera'),
			),
			array(
				'id' => 'edit_tema_term',
				'label' => __('editar temas','delibera'),
			),
			array(
				'id' => 'delete_tema_term',
				'label' => __('remover temas','delibera'),
			),
			array(
				'id' => 'assign_tema_term',
				'label' => __('atribuir temas','delibera'),
			),
			array(
				'id' => 'manage_delibera_cat_term',
				'label' => __('gerenciar categorias','delibera'),
			),
			array(
				'id' => 'edit_delibera_cat_term',
				'label' => __('editar categorias','delibera'),
			),
			array(
				'id' => 'delete_delibera_cat_term',
				'label' => __('remover categorias','delibera'),
			),
			array(
				'id' => 'assign_delibera_cat_term',
				'label' => __('atribuir categorias','delibera'),
			),
		);
		add_action('init', array($this, 'admin_init'));
		add_action( 'registered_taxonomy', array($this, 'setTaxonomyCaps'), 10, 3 );
	}
	
	public function admin_init()
	{
		add_action('delibera_menu_itens', array($this, 'addMenu'));
	}
	
	public function addMenu($base_page)
	{
		add_submenu_page($base_page, __('Delibera Permissões','delibera'),__('Delibera Permissões','delibera'), 'manage_options', 'delibera-perm', array($this, 'confPage'));
	}
	
	public function confPage()
	{
		if ($_SERVER['REQUEST_METHOD']=='POST')
		{
			if (!current_user_can('manage_options'))
			{
				die(__('Você não pode editar as configurações do delibera.','delibera'));
			}
			check_admin_referer('delibera-permission-nonce');
			$this->save();
		}
		$this->html();
	}
	
	/**
	 * * Copied from Sendpress **
	 */
	function str_lreplace($search, $replace, $subject)
	{
		$pos = strrpos($subject, $search);
		
		if($pos !== false)
		{
			$subject = substr_replace($subject, $replace, $pos, strlen($search));
		}
		
		return $subject;
	}

	function save()
	{
		foreach($this->get_editable_roles() as $role => $role_name)
		{
			if(is_super_admin() || $role != 'administrator')
			{
				$saverole = get_role($role);
				foreach ($this->roleList as $rolearray)
				{
					if( isset($_POST[$role . "_{$rolearray['id']}"] ))
					{
						$saverole->add_cap( $rolearray['id']);
					}
					else
					{
						$saverole->remove_cap($rolearray['id']);
					}
				}
			}
		}
	}
	
	function GenerateCheckbox($id, $role, $echo = false, $label = "", $checked = '')
	{
		$listrole = get_role(sanitize_title($role));
		if($listrole->has_cap($id))
		{
			$checked = 'checked';
		}
		$output = "
			<td>
				<input $checked name='" . $role ."_{$id}' type='checkbox' >".
				'<span class="delibera-conf-permission-label">'.$label.'</span>'.
			"</td>"
		;
		if($echo) echo $output;
		return $output;
	}

	function html()
	{
		$roles = array();
		foreach ($this->roleList as $cap)
		{
			foreach($this->get_editable_roles() as $role => $role_name)
			{
				if(is_super_admin() || $role != 'administrator')
				{
					if(!array_key_exists($cap['label'], $roles)) $roles[$cap['label']] = array(); 
					$roles[$cap['label']][$role] = $cap['id'];
				}
			}
		}
		$header = reset($roles);
		//echo '<pre>';print_r($roles);die();
		?>
		<form method="post" id="post">
			<!--
			<div style="float:right;" >
				<a href="" class="btn btn-large btn-default" ><i class="icon-remove"></i> <?php _e('Cancel','delibera'); ?></a> <a href="#" id="save-update" class="btn btn-primary btn-large"><i class="icon-white icon-ok"></i> <?php _e('Save','delibera'); ?></a>
			</div>
			-->
			<br class="clear"> <br class="clear">
			<table class="table table-bordered table-striped">
				<?php
				echo '<tr><th>&nbsp;</th>';
				
				foreach (array_keys($header) as $head)
				{
					echo '<th>'.$head.'</th>';
				}
				echo '</tr>';
				foreach($roles as $rolelabel => $rolelist)
				{
					echo '<tr><td><span class="delibera-conf-permission-line-label">'.$rolelabel.'</span></td>';
					foreach ($rolelist as $group => $roleid)
					{
						$this->GenerateCheckbox($roleid, $group,true);
						//echo '<pre>';print_r($group);print_r($roleid);die();
					}
					echo '</tr>';
				}
				echo "</table>";
				/*
				 * echo "<pre>";
				 * foreach ($this->get_editable_roles() as $role)
				 * {
				 * if($role['name'] != 'Administrator'){
				 * print_r($role);
				 * }
				 * }
				 * echo "</pre>";
				 */
				?>
			<?php wp_nonce_field( 'delibera-permission-nonce' ); ?>
			<input type="submit" class="delibera-permission-bt-save" value="<?php _e('Salvar', 'delibera'); ?>" />
		</form><?php
		
	}

	function get_role($role)
	{
		// $this->security_check();
		global $wp_roles;
		
		if(! isset($wp_roles))
			$wp_roles = new WP_Roles();
		
		return $wp_roles->get_role($role);
	}

	function get_editable_roles()
	{
		// $this->security_check();
		global $wp_roles;
		
		if(! isset($wp_roles))
			$wp_roles = new WP_Roles();
		
		$all_roles = $wp_roles->get_names();
		$editable_roles = apply_filters('editable_roles', $all_roles);
		
		return $all_roles;
	}
	/*** End Copied from Sendpress ***/
	
	public function setTaxonomyCaps( $taxonomy, $object_type, $args )
	{
		global $wp_taxonomies;
		if ( 'tema' == $taxonomy && ( ( is_string($object_type) && 'pauta' == $object_type ) || (is_array($object_type) && in_array('pauta', $object_type) ) ) )
		{
			$wp_taxonomies[ 'category' ]->cap->manage_terms = 'manage_delibera_cat_term';
			$wp_taxonomies[ 'category' ]->cap->edit_terms = 'edit_delibera_cat_term';
			$wp_taxonomies[ 'category' ]->cap->delete_terms = 'delete_delibera_cat_term';
			$wp_taxonomies[ 'category' ]->cap->assign_terms = 'assign_delibera_cat_term';
		}
	}
	
}

$DeliberaConfPermission = new \Delibera\Conf\Permission();
