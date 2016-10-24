<?php
/**
 * Configuração de permissões.
 */

$delibera_permissoes = array(
	'administrator' => array('Novo' => false, 'Caps' => array
	(
		'delete_pautas',
		'delete_private_pautas',
		'edit_pauta',
		'edit_pautas',
		'edit_private_pautas',
		'publish_pautas',
		'read_pauta',
		'read_private_pautas',
		'delete_published_pautas',
		'forcar_prazo',
		'delibera_reabrir_pauta',
		'edit_published_pautas',
		'edit_published_pauta',
		'edit_encaminhamento',
		'votar',
		'relatoria',
		'edit_others_pautas',
		'edit_others_pauta',
		'delete_others_pautas',
		'delete_others_pauta',
		'manage_tema_term',
		'edit_tema_term',
		'delete_tema_term',
		'assign_tema_term',
		'manage_delibera_cat_term',
		'edit_delibera_cat_term',
		'delete_delibera_cat_term',
		'assign_delibera_cat_term'
	)),
	'coordenador' => array('nome'=> 'Coordenador', 'Novo' => true, 'From' => 'administrator', 'Caps' => array
	(
		'delete_pautas',
		'delete_private_pautas',
		'edit_pauta',
		'edit_pautas',
		'edit_private_pautas',
		'publish_pautas',
		'read_pauta',
		'read_private_pautas',
		'delete_published_pautas',
		'forcar_prazo',
		'delibera_reabrir_pauta',
		'edit_published_pautas',
		'edit_published_pauta',
		'edit_encaminhamento',
		'votar',
		'relatoria',
		'edit_others_pautas',
		'edit_others_pauta',
		'delete_others_pautas',
		'delete_others_pauta',
		'manage_tema_term',
		'edit_tema_term',
		'delete_tema_term',
		'assign_tema_term',
		'manage_delibera_cat_term',
		'edit_delibera_cat_term',
		'delete_delibera_cat_term',
		'assign_delibera_cat_term',
	)),
	'contributor' => array('Novo' => false, 'Caps' => array
	(
		'read_pauta',
		'votar',
	)),
	'subscriber' => array('Novo' => false, 'Caps' => array
	(
		'read_pauta',
		'votar',
	)),
	'author' => array('Novo' => false, 'Caps' => array
	(
		'read_pauta',
		'votar',
	)),
	'editor' => array('Novo' => false, 'Caps' => array
	(
		'read_pauta',
		'votar',
	)),
	/*'super admin' => array('Novo' => false, 'Caps' => array
	(
		'read_pauta',
		'votar',
	)),*/
);


?>