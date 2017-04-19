<?php


/**
 * Plugin Name: Fake Page Plugin 2
 * Plugin URI: http://scott.sherrillmix.com/blog/blogger/creating-a-better-fake-post-with-a-wordpress-plugin/
 * Description: Creates a fake page without a 404 error (based on <a href="http://headzoo.com/tutorials/wordpress-creating-a-fake-post-with-a-plugin">Sean Hickey's Fake Plugin Page</a>)
 * Author: Scott Sherrill-Mix
 * Author URI: http://scott.sherrillmix.com/blog/
 * Version: 1.1
 */
 
class FakePage
{
	/**
	 * The slug for the fake post.  This is the URL for your plugin, like:
	 * http://site.com/about-me or http://site.com/?page_id=about-me
	 * @var string
	 */
	var $page_slug = 'delibera-template-archive';
	 
	/**
	 * The title for your fake post.
	 * @var string
	 */
	var $page_title = 'Delibera Template Archive';
	 
	/**
	 * Allow pings?
	 * @var string
	 */
	var $ping_status = 'open';
	
	var $pages;
	
	protected $post_count = 0;
	
	protected $_req = false;
	
	public function getReq()
	{
		global $wp;
		if($this->_req === false)
		{
			$areq = explode('/', $wp->request);
			$req = end($areq);
			
			if(empty($req) && isset($_SERVER['HTTP_REFERER'])) // Ajax
			{
				$referer = $_SERVER['HTTP_REFERER'];
				
				$referer = $referer[strlen($referer) - 1] == "/" ? substr($referer, 0, -1) : $referer;
				
				$areferer = explode('/', $referer);
				
				$referer = end($areferer);
				
				$req = $referer;
			}
			$this->_req = $req;
		}
		return $this->_req;
	}
	 
	/**
	 * Class constructor
	 */
	function FakePage()
	{
		$this->pages = array();
		
		$this->NewFakePage('delibera-template-archive', 'Delibera Template Archive', false, "", true);
		$this->NewFakePage('delibera-template-validacao', 'Delibera Template Validação', 'validacao', "", false);
		$this->NewFakePage('delibera-template-discussao', 'Delibera Template Discussão', 'discussao', "", false);
		$this->NewFakePage('delibera-template-votacao-relatoria', 'Delibera Template Votação Relatoria', '', "", false);
		$this->NewFakePage('delibera-template-relatoria', 'Delibera Template Relatoria', '', "", false);
		$this->NewFakePage('delibera-template-votacao', 'Delibera Template Votação', '', "", false);
		
		/**
		 * We'll wait til WordPress has looked for posts, and then
		 * check to see if the requested url matches our target.
		 */
		/*add_filter('the_posts',array(&$this,'detectPost'));
		//remove_action('wp_ajax_delibera_filtros_archive', 'delibera_filtros_archive_callback');
		//remove_action('wp_ajax_nopriv_delibera_filtros_archive', 'delibera_filtros_archive_callback');
		//add_filter('pre_get_posts', array(&$this,'detectQuery'));*/
		
		add_filter('delibera_filtros_archive_callback_filter', array(&$this,'query_posts'));
		add_filter('body_class', array(&$this,'body_class'), 10, 2);
		add_filter('the_posts',array(&$this,'detectPost'));
		add_filter('delibera_get_situacao',array(&$this,'delibera_get_situacao'));
		
		//add_filter('post_type_link',array(&$this,'post_type_link'), 10, 4);
	}
	
	function NewFakePage($slug, $title, $situacao, $content = '', $is_archive = false)
	{
		$page = new stdClass();
		$page->page_slug = $slug;
		$page->page_title = $title;
		$page->content = $content;
		$page->is_archive = $is_archive;
		$page->situacao = $situacao;
		$this->pages[$slug] = $page;
	}
	
	function body_class($classes, $class)
	{
		if($this->is_fake())
		{
			for ($i = 0; $i < count($classes); $i++)
			{
				if($classes[$i] == 'post-type-archive-')
				{
					$classes[$i] = 'post-type-archive-pauta';
				}
			}
		}
		return $classes;
	}
	
	function is_fake($post = false)
	{
		global $wp;
		global $wp_query;
		
		if(array_key_exists('fake', $wp_query->query_vars) && $wp_query->query_vars['fake'] == true)
		{
			return true;
		}
		
		if (isset($_SERVER['HTTP_REFERER'])) {
			$referer = $_SERVER['HTTP_REFERER'];
			$referer = $referer[strlen($referer) - 1] == "/" ? substr($referer, 0, -1) : $referer;
			$areferer = explode('/', $referer);
			$referer = end($areferer);
		} else {
			$referer = '';
		}
		
		$request = false;
		foreach ($this->pages as $page)
		{
			if(
					strtolower($this->getReq()) == strtolower($page->page_slug) ||
					(isset($wp->query_vars['page_id']) && $wp->query_vars['page_id'] == $page->page_slug) ||
					strtolower($referer) == strtolower($page->page_slug) ||
					($post != false && $post->post_slug == $page->page_slug)
			)
			{
				$request = true;
				break;
			}
		}
		return $request;
	}
	 
	/**
	 * Called by the 'detectPost' action
	 */
	function createPost($slug = false, $page = false)
	{
		
		$page = $page === false ? $this:$page;
		$slug = $slug === false ? $this->page_slug : $slug; 
		
		/**
		 * What we are going to do here, is create a fake post.  A post
		 * that doesn't actually exist. We're gonna fill it up with
		 * whatever values you want.  The content of the post will be
		 * the output from your plugin.
		 */
		
		/**
		 * Create a fake post.
		 */
		$post = new stdClass;
		 
		/**
		 * The author ID for the post.  Usually 1 is the sys admin.  Your
		 * plugin can find out the real author ID without any trouble.
		 */
		$post->post_author = 1;
		 
		/**
		 * The safe name for the post.  This is the post slug.
		 */
		$post->post_name = $slug;
		 
		/**
		 * Not sure if this is even important.  But gonna fill it up anyway.
		 */
		$post->guid = get_bloginfo('wpurl') . '/' . $slug;
		 
		 
		/**
		 * The title of the page.
		 */
		$post->post_title = $page->page_title;
		 
		/**
		 * This is the content of the post.  This is where the output of
		 * your plugin should go.  Just store the output from all your
		 * plugin function calls, and put the output into this var.
		 */
		$post->post_content = $page->content;
		
		$this->post_count++;
		 
		/**
		 * Fake post ID to prevent WP from trying to show comments for
		 * a post that doesn't really exist.
		 */
		$post->ID = -$this->post_count;;
		 
		/**
		 * Static means a page, not a post.
		 */
		$post->post_status = 'static';
		 
		/**
		 * Turning off comments for the post.
		 */
		$post->comment_status = 'closed';
		 
		/**
		 * Let people ping the post?  Probably doesn't matter since
		 * comments are turned off, so not sure if WP would even
		 * show the pings.
		 */
		$post->ping_status = $this->ping_status;
		 
		$post->comment_count = 0;
		 
		/**
		 * You can pretty much fill these up with anything you want.  The
		 * current date is fine.  It's a fake post right?  Maybe the date
		 * the plugin was activated?
		 */
		$post->post_date = current_time('mysql');
		$post->post_date_gmt = current_time('mysql', 1);
		
		$post->post_type = 'pauta';
		$post->name = 'pauta';
		
		return($post);
	}
	 
	function getContent()
	{
		//require dirname(__FILE__).DIRECTORY_SEPARATOR.'themes'.DIRECTORY_SEPARATOR.'archive-pauta.php';
	}
	 
	function detectPost($posts){
		global $wp;
		global $wp_query;
		/**
		 * Check if the requested page matches our target
		 */
		
		if ($this->is_fake()){
			//Add the fake post
			
			$slug = $this->getReq();
			
			$posts=NULL;
			if($this->pages[$slug]->is_archive)
			{
				foreach ($this->pages as $page_slug => $page)
				{
					$posts[]=$this->createPost($page_slug, $page);
				}
			}
			else
			{
				$posts[]=$this->createPost($slug, $this->pages[$slug]);
			}
			
			/**
			 * Trick wp_query into thinking this is a page (necessary for wp_title() at least)
			 * Not sure if it's cheating or not to modify global variables in a filter
			 * but it appears to work and the codex doesn't directly say not to.
			 */
			$wp_query->is_page = false;
			//Not sure if this one is necessary but might as well set it like a true page
			$wp_query->is_singular = !$this->pages[$slug]->is_archive;
			$wp_query->is_home = false;
			$wp_query->is_archive = $this->pages[$slug]->is_archive;
			$wp_query->is_post_type_archive = $this->pages[$slug]->is_archive;
			$wp_query->is_category = false;
			//Longer permalink structures may not match the fake post slug and cause a 404 error so we catch the error here
			unset($wp_query->query["error"]);
			$wp_query->query_vars["error"]="";
			$wp_query->is_404=false;
			$wp_query->queried_object = $posts[0];
			
		}
		return $posts;
	}
	
	function query_posts($args)
	{
		if($this->is_fake())
		{
		 	//query_posts('fake=true');
		 	return 'fake=true';
		}
		return $args;
	}
	
	function delibera_get_situacao($situacao)
	{
		if($this->is_fake())
		{
			$sitName = is_string($this->pages[$this->getReq()]->situacao) ? $this->pages[$this->getReq()]->situacao : 'validacao';
			$sit = new stdClass();
			$sit->name = $sitName;
			return $sit;
		}
		return $situacao;
	}
	
}
 
/**
 * Create an instance of our class.
 */
new FakePage;

?>
