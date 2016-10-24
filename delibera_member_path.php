<?php
namespace Delibera\Member;
include __DIR__ . DIRECTORY_SEPARATOR . 'delibera_user_display.php';

use Delibera\Theme;

class MemberPath
{

	public function __construct()
	{
		// echo __NAMESPACE__;
		add_filter('query_vars', array(
			$this,
			'userpage_rewrite_add_var'
		));
		add_action('init', array(
			$this,
			'userpage_rewrite_rule'
		));
		add_action('template_redirect', 
				array(
					$this,
					'userpage_rewrite_catch'
				));
		add_action('init', array(
			$this,
			'check_rewrite'
		));
	}
	
	// Create the query var so that WP catches the custom /member/username url
	public function userpage_rewrite_add_var($vars)
	{
		$vars[] = 'pautasfor';
		$vars[] = 'commentsfor';
		$vars[] = 'merbers';
		return $vars;
	}
	
	// Create the rewrites
	public function userpage_rewrite_rule()
	{
		add_rewrite_tag('%pautasfor%', '([^&]+)');
		add_rewrite_rule('^delibera/([^/]*)/pautas?', 
				'index.php?pautasfor=$matches[1]', 'top');
		add_rewrite_tag('%commentsfor%', '([^&]+)');
		add_rewrite_rule('^delibera/([^/]*)/comentarios?', 
				'index.php?commentsfor=$matches[1]', 'top');
		add_rewrite_tag('%members%', '');
		add_rewrite_rule('^delibera/membros', 'index.php?members&paged=1', 
				'top');
	}
	
	// Catch the URL and redirect it to a template file
	public function userpage_rewrite_catch()
	{
		global $wp_query;
		if(array_key_exists('pautasfor', $wp_query->query_vars))
		{
			$conf = delibera_get_config();
			load_template($conf['theme'] . '/page-author-pautas.php', true);
			exit();
		}
		if(array_key_exists('commentsfor', $wp_query->query_vars))
		{
			$conf = delibera_get_config();
			load_template($conf['theme'] . '/page-author-comments.php', true);
			exit();
		}
		if(array_key_exists('members', $wp_query->query_vars))
		{
			// var_dump(\Delibera\Theme\UserDisplay::getOrderBy('active'));
			
			// $per_page = isset( $_GET['per-page'] ) ? esc_html(
			// $_GET['per-page'] ) : '20' ;
			// $search = isset( $_GET['search'] ) ? esc_html( $_GET['search'] )
			// : '' ;
			// $order = isset( $_GET['order-by'] ) ? esc_html( $_GET['order-by']
			// ) : '' ;
			// $order_by = \Delibera\Theme\UserDisplay::getOrderBy($order);
			// $paged = get_query_var( 'paged' );
			// var_dump(\Delibera\Theme\UserDisplay::getUsers( $order_by ,
			// $search , $per_page , $paged ));
			
			$conf = delibera_get_config();
			load_template($conf['theme'] . '/page-authors.php', true);
			exit();
		}
	}

	public function check_rewrite()
	{
		$rules = get_option('rewrite_rules');
		$found = false;
		if(is_array($rules))
		{
			foreach($rules as $rule)
			{
				if(strpos($rule, 'delibera') !== false)
				{
					$found = true;
					break;
				}
			}
			if(! $found)
			{
				flush_rewrite_rules();
			}
		}
	}

	/**
	 * retrive Author Pautas Url
	 * @param int $ID User ID
	 */
	public static function getAuthorPautasUrl($ID)
	{
		return get_site_url().'/delibera/' . deliberaEncryptor('encrypt', $ID) . '/pautas';
	}
}
$member_path = new \Delibera\Member\MemberPath(); 
