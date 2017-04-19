<?php

function delibera_get_user_campos_form_registro_template()
{
	return array(
		'novo' => true, // Se é um campo novo ou já existe no perfil de usuário
		'id' => 'no_id',
		'nome' => __('Sem Nome', 'delibera'),
		'registro' => true, // Necessário no formulário de registro
		'obrigatorio' => true, // Obrigatório no formulário de registro
		'Informar' => __('Favor informar', 'delibera'),
		'funcao_registro' => false, // opcional
		'tipo_painel' => 'Texto', // Opcional: tipo do painel (Texto, DropDown ou CheckBox)
		'dados' => false, // Opcional: Função para pegar opções ou Array com dados 
		'dados_param' => false, // Opcional: Parametros para a função e dados array("Valor" => "Label", "Valor2" => "Label2")
		'funcao_painel' => false, // Opcional: Função que gera o html no painel
		'default' => '' // Valor Padrão da opção
	);
}

function delibera_get_user_campos_form_registro()
{
	$campos_form_registro = array
	(
		/*
		 * Exemplo: 
		 * array(
		 * 		'novo' => true, // Se é um campo novo ou já existe no perfil de usuário
		 * 		'id' => 'user_pais',
		 * 		'nome' => __('País', 'delibera'),
		 * 		'registro' => true, // Necessário no formulário de registro
		 * 		'obrigatorio' => true, // Obrigatório no formulário de registro
		 * 		'Informar' => __('Favor informar o país do qual deseja participar', 'delibera'),
		 * 		'funcao_registro', => 'get_tax_regiao_paises' // opcional
		 * 		'tipo_painel' => 'DropDown', // Opcional: tipo do painel (Texto, DropDown ou CheckBox)
		 * 		'dados' => 'get_paises', // Opcional: Função para pegar opções
		 * 		'dados_param' => '', // Opcional: Parametros para a função e dados array("Valor" => "Label", "Valor2" => "Label2")
		 * 		'funcao_painel' => '' // Opcional: Função que gera o html no painel
		 * 	),
		 */

	);
	
	if(has_filter('delibera_user_painel_campos'))
	{
		$campos_form_registro = apply_filters('delibera_user_painel_campos', $campos_form_registro);
	}
	
	if(function_exists('get_user_campos_form_registro'))
	{
		$get_user_campos_form_registro = get_user_campos_form_registro();
		if(is_array($get_user_campos_form_registro))
		{
			//$campos_form_registro = array_merge($campos_form_registro, $get_user_campos_form_registro);
		}
	}
	$campo_template = delibera_get_user_campos_form_registro_template();
	for($i = 0; $i < count($campos_form_registro); $i++)
	{
		$campos_form_registro[$i] = array_merge($campo_template, $campos_form_registro[$i]);
	}
	
	return $campos_form_registro;
}

function delibera_campos_usuario_registro()
{
	foreach (delibera_get_user_campos_form_registro() as $campo)
	{
		if($campo['registro'] === true)
		{
			if(array_key_exists('funcao_registro', $campo) && function_exists($campo['funcao_registro']))
			{
				call_user_func($campo['funcao_registro'], $campo);
			}
			else 
			{
				?>
				<p>
					<label><?php echo $campo['nome'] ?><br />
					<input type="text" name="<?php echo $campo['id'] ?>" id="<?php echo $campo['id'] ?>" class="input" value="<?php echo esc_attr(stripslashes($_POST[$campo['id']])); ?>" size="20" tabindex="21" /></label>
				</p>
				<?php
			}
		}
	}
}
add_action('register_form','delibera_campos_usuario_registro');

function delibera_campos_usuario_registro_check($login, $email, $errors)
{
	foreach (delibera_get_user_campos_form_registro() as $campo)
	{
		if($campo['registro'] === true && $campo['obrigatorio'] === true && $_POST[$campo['id']] == '')
		{
			$errors->add('empty_'.$campo['id'], $campo['Informar']);
		}
	}
	return $errors;
}
add_action('register_post','delibera_campos_usuario_registro_check',10,3);

function delibera_register_extra_fields($user_id, $password="", $meta=array())
{
	$userdata['ID'] = $user_id;
	
	foreach (delibera_get_user_campos_form_registro() as $campo)
	{
		if(array_key_exists($campo['id'], $_POST))
		{
			if($campo[novo] == true)
			{
				update_usermeta( $user_id, $campo['id'], $_POST[$campo['id']]);
			}
			else 
			{
				$userdata[$campo['id']] = $_POST[$campo['id']];
			}
		}
	}
	
	wp_update_user($userdata);
	$order = get_user_option("meta-box-order_pauta", $user_id);
	$order['side'] = 'submitdiv,idiomadiv,tagsdiv-post_tag,temadiv,regiaodiv,pauta_meta';
	update_user_option($user_id, "meta-box-order_pauta", $order, true);
}
add_action('user_register', 'delibera_register_extra_fields');

function delibera_extra_profile_fields( $user ) 
{
	$campos = delibera_get_user_campos_form_registro();
	if($campos > 0)
	{
		?>
		<h3><?php _e('Notificações por e-mail do Delibera', 'delibera'); ?></h3>
	
		<p><?php _e('Escolha abaixo para quais eventos do sistema do Delibera você deseja receber notificações por e-mail. As opções selecionadas aqui serão aplicadas para todas as pautas. É posssível também seguir as pautas individualmente. Para isso clique no botão "Seguir" na página de uma pauta.', 'delibera'); ?></p>
	
		<table class="Delibera-user-form-table">
		<?php
		foreach ($campos as $campo)
		{
			if($campo['novo'] == true)
			{ 
				$contactmethods[] = $campo['nome'];
				?>
					<tr>
						<td><label for="<?php echo $campo['id'] ?>"><?php echo $campo['nome'] ?></label></td>
						<td>
							<?php
							if(is_string($campo['funcao_painel']) && function_exists($campo['funcao_painel']) )
							{
								call_user_func($campo['funcao_painel'], $campo);
							}
							else 
							{
								$default = $campo['default'];
								switch($campo['tipo_painel'])
								{
									case 'DropDown':
										$valores = array();
										if(is_array($campo['dados']) && count($campo['dados']) > 0)
										{
											$valores = $campo['dados'];
										}
										elseif(is_string($campo['dados']) && function_exists($campo['dados']))
										{
											$param = $campo['dados_param'] ? $campo['dados_param'] : array();
											$ret = call_user_func($campo['dados'], $param);
											if(is_array($ret) && count($ret) > 0)
											{
												$valores = $ret;
											}
										}
										?>
										<select name="<?php echo $campo['id'] ?>" id="<?php echo $campo['id'] ?>" class="regular-dropdown" >
										<?php
										$salvo = strtolower(esc_attr( get_user_meta( $user->ID, $campo['id'], true )));
										$salvo = $salvo == '' || empty($salvo) || (is_array($salvo) && count($salvo) == 0) ? strtolower($default) : $salvo;
										foreach ($valores as $valor => $desc)
										{
											?>
											<option value="<?php echo $valor; ?>" <?php echo $salvo === strtolower($valor) ? 'selected="selected"' : ""; ?> class="regular-checkbox-value" ><?php echo $desc;?></option>
											<?php
										}
										?>
										</select><br />
										<?php
									break;
									case 'CheckBox':
										$valor = true;
										if(is_array($campo['dados']) && count($campo['dados']) > 0)
										{
											$keys = array_keys($campo['dados']);
											$valor = $keys[0];
										}
										elseif(is_string($campo['dados']) && function_exists($campo['dados']))
										{
											$param = $campo['dados_param'] ? $campo['dados_param'] : array();
											$ret = call_user_func($campo['dados'], $param);
											if(is_array($ret) && count($ret) > 0)
											{
												$keys = array_keys($ret);
												$valor = $keys[0] == 0 ? $ret[0] : $keys[0];
											}
											elseif (is_string($ret))
											{
												$valor = $ret;
											}
										}
										$salvo = strtolower(esc_attr( get_user_meta( $user->ID, $campo['id'], true )));
										$salvo = $salvo == '' || empty($salvo) || (is_array($salvo) && count($salvo) == 0) ? strtolower($default) : $salvo;
										?>
										<input type="checkbox" name="<?php echo $campo['id'] ?>" id="<?php echo $campo['id'] ?>" value="<?php echo $valor; ?>" <?php echo $salvo === strtolower($valor) ? 'checked="checked"' : '' ; ?> class="regular-checkbox" /><br />
										
										<?php
									break;
									case 'Texto':
									default:
										$salvo = esc_attr( get_user_meta( $user->ID, $campo['id'], true ));
										$salvo = $salvo == '' || empty($salvo) || (is_array($salvo) && count($salvo) == 0) ? $default : $salvo;
										?>
										<input type="text" name="<?php echo $campo['id'] ?>" id="<?php echo $campo['id'] ?>" value="<?php echo $salvo; ?>" class="regular-text" /><br />
										<?php
									break;
								}
							}
							?>
						</td>
					</tr>
				<?php
			}
		}
		?>
		</table>
		<?php
	}
}

add_action( 'show_user_profile', 'delibera_extra_profile_fields' );
add_action( 'edit_user_profile', 'delibera_extra_profile_fields' );

function delibera_profile_update($user_id)
{
	$campos = delibera_get_user_campos_form_registro();
	foreach ($campos as $campo)
	{
		switch($campo['tipo_painel'])
		{
			case 'CheckBox':
				$valor = false;
				if(array_key_exists($campo['id'], $_POST))
				{
					$valor = $_POST[$campo['id']];
				}
				else 
				{
					if(is_array($campo['dados']) && count($campo['dados']) == 2)
					{
						$keys = array_keys($campo['dados']);
						$valor = $keys[1];
					}
					elseif(is_string($campo['dados']) && function_exists($campo['dados']))
					{
						$param = $campo['dados_param'] ? $campo['dados_param'] : array();
						$ret = call_user_func($campo['dados'], $param);
						if(is_array($ret) && count($ret) == 2)
						{
							$keys = array_keys($ret);
							$valor = $keys[0] == 0 ? $ret[1] : $keys[1];
						}
					}
				}
				update_user_meta($user_id, $campo['id'], $valor);
			break;
			default:
				if(array_key_exists($campo['id'], $_POST))
				{
					update_user_meta($user_id, $campo['id'], $_POST[$campo['id']]);
				}
			break;
		}
	}
}

add_action('profile_update', 'delibera_profile_update');









/*
 * Allowing user Admin on Multisite intalation to edit users on his blog.
 * */
function mc_admin_users_caps( $caps, $cap, $user_id, $args ){

	foreach( $caps as $key => $capability ){

		
		if( $capability != 'do_not_allow' )
			continue;

		switch( $cap ) {
			case 'edit_user':
			case 'edit_users':
				$caps[$key] = 'edit_users';
				break;
			case 'delete_user':
			case 'delete_users':
				$caps[$key] = 'delete_users';
				break;
			case 'create_users':
				$caps[$key] = $cap;
				break;
		}
	}

	return $caps;
}
add_filter( 'map_meta_cap', 'mc_admin_users_caps', 10, 4 );



?>
