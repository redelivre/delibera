<?php
	function delibera_widgets_add_meta_box()
	{
		add_meta_box( 'delibera_widgets-meta-box', __('Delibera'), 'delibera_widgets_nav_menu_item_link_meta_box', 'nav-menus', 'side', 'default' );
	}
	add_action( 'admin_init', 'delibera_widgets_add_meta_box' );
	
	function delibera_widgets_admin_script()
	{
		if(substr($_SERVER['REQUEST_URI'], -(strlen('nav-menus.php'))) == 'nav-menus.php')
		{
			wp_enqueue_script('delibera_widgets_admin_script_nav_menus',WP_CONTENT_URL.'/plugins/delibera/js/delibera_nav_menu.js', array('jquery'));
		}
	}
	add_action( 'admin_print_scripts', 'delibera_widgets_admin_script' );
	
	function delibera_widgets_nav_menu_item_link_meta_box()
	{
		?>
		<script type="text/javascript">
		<!--
		function delibera_addMenuItemToBottom()
		{
			//var processMethod = wpNavMenu.addMenuItemToBottom;
			var processMethod = delibera_addMenuItemToBottom_processMethod;
			var callback = function(){
			};
		
			var url = "<?php echo get_post_type_archive_link('pauta') ?>";
			var label = "Delibera";
		
			wpNavMenu.addItemToMenu({
				'-1': {
					'menu-item-type': 'custom',
					'menu-item-url': url,
					'menu-item-title': label,
					'menu-item-classes': 'delibera-menu-item'
				}
			}, processMethod, callback);
		}
		//-->
		</script>
	    <div class="custom-meta-box" id="custom-meta-box">
			<p><?php _e('Clique no botão abaixo para adicionar um link para a página que lista as pautas no menu.')?></p>
			<button class="button-secondary submit-add-to-menu" onclick="delibera_addMenuItemToBottom();"><?php _e('Adicionar')?></button> 
	    </div>
	    <?php
	}
	
	add_action( 'widgets_init', function()
	{
		$widgets = array('WidgetListaPropostas');
		foreach ($widgets as $widgetName)
		{
			require_once dirname(__FILE__).'/widgets/'.$widgetName.'/'.$widgetName.'.php';
			register_widget( $widgetName );
		}
	});
?>