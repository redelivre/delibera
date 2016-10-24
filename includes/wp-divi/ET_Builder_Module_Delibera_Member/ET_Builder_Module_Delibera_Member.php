<?php

class ET_Builder_Module_Delibera_Member extends ET_Builder_Module {
	function init() {
		$this->name = esc_html__( 'Delibera', 'et_builder' );
		$this->slug = 'et_pb_delibera_pauta22v';

		$this->whitelisted_fields = array(
			'name',
			'position',
			'image_url',
			'animation',
			'background_layout',
			'content_new',
			'admin_label',
			'module_id',
			'module_class',
			'icon_color',
			'icon_hover_color',
			'include_categories',
			'url_tema',
			'background_color',
			'button_image_url',
			'orderby',
			'order',
			'pauta_id',
			'border',
			'shadow'
		);

		$this->fields_defaults = array(
			'animation'			=> array( 'off' ),
			'background_layout'	=> array( 'light' ),
			'background_color'	=> array( '#ffffff' ),
			'button_image_url'	=> array( '' ),
			'orderby'			=> array( 'date' ),
			'order'				=> array( 'DESC' ),
			'pauta_id'			=> array( false )
		);

		$this->main_css_element = '%%order_class%%.et_pb_delibera_member';
		$this->advanced_options = array(
			'fonts' => array(
				'header' => array(
					'label'    => esc_html__( 'Header', 'et_builder' ),
					'css'      => array(
						'main' => "{$this->main_css_element} h4",
					),
					),
					'body'   => array(
						'label'    => esc_html__( 'Body', 'et_builder' ),
						'css'      => array(
							'main' => "{$this->main_css_element} *",
						),
						),
						),
						'border' => array(),
						'custom_margin_padding' => array(
							'css' => array(
								'important' => 'all',
							),
						),
							);
		$this->custom_css_options = array(
			'member_image' => array(
				'label'    => esc_html__( 'Member Image', 'et_builder' ),
				'selector' => '.et_pb_delibera_member_image',
			),
			'member_description' => array(
				'label'    => esc_html__( 'Member Description', 'et_builder' ),
				'selector' => '.et_pb_delibera_member_description',
			),
			'title' => array(
				'label'    => esc_html__( 'Title', 'et_builder' ),
				'selector' => '.et_pb_delibera_member_description h4',
			),
			'member_position' => array(
				'label'    => esc_html__( 'Member Position', 'et_builder' ),
				'selector' => '.et_pb_member_position',
			),
			'member_social_links' => array(
				'label'    => esc_html__( 'Member Social Links', 'et_builder' ),
				'selector' => '.et_pb_member_social_links',
			),
		);

		add_action('wp_enqueue_scripts', array($this, 'cssFiles'), 1000);
		add_action('wp_enqueue_scripts', array($this, 'javascriptFiles'), 1000);
	}

	function get_fields() {
		$fields = array(
			'include_categories' => array(
				'label'            => esc_html__( 'Include Categories', 'et_builder' ),
				'renderer'         => 'et_builder_include_categories_delibera_option',
				'option_category'  => 'basic_option',
				'renderer_options' => array(
					'use_terms' => true,
					'term_name' => 'tema',
					'post_type'=>'pauta'
				),
				'description'      => esc_html__( 'Choose which categories you would like to include in the feed.', 'et_builder' ),
			),
			'border' => array(
				'label'           => esc_html__( 'Borda Arredondada', 'et_builder' ),
				'type'            => 'yes_no_button',
				'option_category' => 'configuration',
				'options'         => array(
					'on'  => esc_html__( 'Yes', 'et_builder' ),
					'off' => esc_html__( 'No', 'et_builder' ),
				),
				'description'       => esc_html__( 'Habilite ou desabilite as bordas arredondas nos cards.', 'et_builder' ),
			),
			'shadow' => array(
				'label'           => esc_html__( 'Sombra', 'et_builder' ),
				'type'            => 'yes_no_button',
				'option_category' => 'configuration',
				'options'         => array(
					'on'  => esc_html__( 'Yes', 'et_builder' ),
					'off' => esc_html__( 'No', 'et_builder' ),
				),
				'description'       => esc_html__( 'Habilite ou desabilite a sombra nos cards.', 'et_builder' ),
			),
			'name' => array(
				'label'           => esc_html__( 'Title', 'et_builder' ),
				'type'            => 'text',
				'option_category' => 'basic_option',
				'description'     => 'Insira um título',
			),
			'url_tema' => array(
				'label'           => esc_html__( 'URL Tema', 'et_builder' ),
				'type'            => 'text',
				'option_category' => 'basic_option',
				'description'     => 'Se preenchida sobreescreve a url do tema',
			),
			'position' => array(
				'label'           => esc_html__( 'Subtitle', 'et_builder' ),
				'type'            => 'text',
				'option_category' => 'basic_option',
				'description'     =>'insira um subtitulo',
			),
			'image_url' => array(
				'label'              => esc_html__( 'Image URL', 'et_builder' ),
				'type'               => 'upload',
				'option_category'    => 'basic_option',
				'upload_button_text' => esc_attr__( 'Upload an image', 'et_builder' ),
				'choose_text'        => esc_attr__( 'Choose an Image', 'et_builder' ),
				'update_text'        => esc_attr__( 'Set As Image', 'et_builder' ),
				'description'        => esc_html__( 'Upload your desired image, or type in the URL to the image you would like to display.', 'et_builder' ),
			),
			'animation' => array(
				'label'             => esc_html__( 'Animation', 'et_builder' ),
				'type'              => 'select',
				'option_category'   => 'configuration',
				'options'           => array(
					'off'     => esc_html__( 'No Animation', 'et_builder' ),
					'fade_in' => esc_html__( 'Fade In', 'et_builder' ),
					'left'    => esc_html__( 'Left To Right', 'et_builder' ),
					'right'   => esc_html__( 'Right To Left', 'et_builder' ),
					'top'     => esc_html__( 'Top To Bottom', 'et_builder' ),
					'bottom'  => esc_html__( 'Bottom To Top', 'et_builder' ),
				),
				'description'       => esc_html__( 'This controls the direction of the lazy-loading animation.', 'et_builder' ),
			),
			'background_layout' => array(
				'label'           => esc_html__( 'Text Color', 'et_builder' ),
				'type'            => 'select',
				'option_category' => 'color_option',
				'options'           => array(
					'light' => esc_html__( 'Dark', 'et_builder' ),
					'dark'  => esc_html__( 'Light', 'et_builder' ),
				),
				'description' => esc_html__( 'Here you can choose the value of your text. If you are working with a dark background, then your text should be set to light. If you are working with a light background, then your text should be dark.', 'et_builder' ),
			),
			'background_color' => array(
				'label'       => esc_html__( 'Background Color', 'et_builder' ),
				'type'        => 'color-alpha',
				'description' => esc_html__( 'Use the color picker to choose a background color for this module.', 'et_builder' ),
			),
			'content_new' => array(
				'label'           => esc_html__( 'Description', 'et_builder' ),
				'type'            => 'tiny_mce',
				'option_category' => 'basic_option',
				'description'     => esc_html__( 'Input the main text content for your module here.', 'et_builder' ),
			),
			'icon_color' => array(
				'label'             => esc_html__( 'Icon Color', 'et_builder' ),
				'type'              => 'color',
				'custom_color'      => true,
				'tab_slug'          => 'advanced',
			),
			'icon_hover_color' => array(
				'label'             => esc_html__( 'Icon Hover Color', 'et_builder' ),
				'type'              => 'color',
				'custom_color'      => true,
				'tab_slug'          => 'advanced',
			),
			'disabled_on' => array(
				'label'           => esc_html__( 'Disable on', 'et_builder' ),
				'type'            => 'multiple_checkboxes',
				'options'         => array(
					'phone'   => esc_html__( 'Phone', 'et_builder' ),
					'tablet'  => esc_html__( 'Tablet', 'et_builder' ),
					'desktop' => esc_html__( 'Desktop', 'et_builder' ),
				),
				'additional_att'  => 'disable_on',
				'option_category' => 'configuration',
				'description'     => esc_html__( 'This will disable the module on selected devices', 'et_builder' ),
			),
			'admin_label' => array(
				'label'       => esc_html__( 'Admin Label', 'et_builder' ),
				'type'        => 'text',
				'description' => esc_html__( 'This will change the label of the module in the builder for easy identification.', 'et_builder' ),
			),
			'module_id' => array(
				'label'           => esc_html__( 'CSS ID', 'et_builder' ),
				'type'            => 'text',
				'option_category' => 'configuration',
				'tab_slug'        => 'custom_css',
				'option_class'    => 'et_pb_custom_css_regular',
			),
			'module_class' => array(
				'label'           => esc_html__( 'CSS Class', 'et_builder' ),
				'type'            => 'text',
				'option_category' => 'configuration',
				'tab_slug'        => 'custom_css',
				'option_class'    => 'et_pb_custom_css_regular',
			),
			'button_image_url' => array(
				'label'              => esc_html__( 'Button Image URL', 'et_builder' ),
				'type'               => 'upload',
				'option_category'    => 'basic_option',
				'upload_button_text' => esc_attr__( 'Upload an image', 'et_builder' ),
				'choose_text'        => esc_attr__( 'Choose an Image', 'et_builder' ),
				'update_text'        => esc_attr__( 'Set As Image', 'et_builder' ),
				'description'        => esc_html__( 'Upload your desired image, or type in the URL to the image you would like to display as link button.', 'et_builder' ),
			),
			'orderby' => array(
				'label'             => esc_html__( 'Ordenar por', 'et_builder' ),
				'type'              => 'select',
				'option_category'   => 'basic_option',
				'options'           => array(
					'date'			=> esc_html__( 'Cronológico, última ou primeira criada', 'et_builder' ),
					'rand'			=> esc_html__( 'Randômico', 'et_builder' ),
					'pauta'			=> esc_html__( 'Pauta Selecionada', 'et_builder' ),
				),
				'description'       => esc_html__( 'Ordem de onde pegar a pauta', 'et_builder' ),
			),
			'order' => array(
				'label'             => esc_html__( 'Ordem', 'et_builder' ),
				'type'              => 'select',
				'option_category'   => 'basic_option',
				'options'           => array(
					'DESC'			=> esc_html__( 'Descendete', 'et_builder' ),
					'ASC'			=> esc_html__( 'Acendente', 'et_builder' ),
				),
				'description'       => esc_html__( 'Ordem', 'et_builder' ),
			),
			'pauta_id' => array(
				'label'				=> esc_html__( 'Selecionar uma pauta', 'et_builder' ),
				'type'				=> 'select',
				'option_category'	=> 'basic_option',
				'renderer_options'	=> array(
					'post_type'		=> 'pauta'
				),
				'options'			=> $this->get_pautas(),
				'description'		=> esc_html__( 'Escolha qual pauta deve mostrar na caixa.', 'et_builder' ),
			),
		);
		return $fields;
	}

	function cssFiles()
	{
		if (!is_pauta())
		{
			if(file_exists(get_stylesheet_directory()."/delibera_style.css"))
			{
				wp_enqueue_style('delibera_style', get_stylesheet_directory_uri()."/delibera_style.css");
			}
			else
			{
				global $deliberaThemes;
				wp_enqueue_style('delibera_style', $deliberaThemes->themeFileUrl('delibera_style.css'));
			}
		}
		wp_enqueue_style('ET_Builder_Module_Delibera_Member', plugins_url("frontend/css", __FILE__)."/ET_Builder_Module_Delibera_Member.css");
	}

	function javascriptFiles()
	{
		if (!is_pauta())
		{
			wp_enqueue_script('delibera-concordar', DELIBERA_DIR_URL . '/js/delibera_concordar.js', array('jquery'));
				
			$data = array(
				'ajax_url' => admin_url('admin-ajax.php'),
			);
			wp_localize_script('delibera-concordar', 'delibera', $data);
		}
	}

	function shortcode_callback( $atts, $content = null, $function_name ) {
		$module_id         	= $this->shortcode_atts['module_id'];
		$module_class      	= $this->shortcode_atts['module_class'];
		$name              	= $this->shortcode_atts['name'];
		$position          	= $this->shortcode_atts['position'];
		$image_url         	= $this->shortcode_atts['image_url'];
		$animation         	= $this->shortcode_atts['animation'];
		$background_layout 	= $this->shortcode_atts['background_layout'];
		$background_color   = $this->shortcode_atts['background_color'];
		$icon_color			= $this->shortcode_atts['icon_color'];
		$icon_hover_color	= $this->shortcode_atts['icon_hover_color'];
		$include_categories = $this->shortcode_atts['include_categories'];
		$url_tema			= $this->shortcode_atts['url_tema'];
		$button_image_url	= $this->shortcode_atts['button_image_url'];
		$orderby			= $this->shortcode_atts['orderby'];
		$order				= $this->shortcode_atts['order'];
		$pauta_id			= $this->shortcode_atts['pauta_id'];
		$border				= $this->shortcode_atts['border'];
		$shadow				= $this->shortcode_atts['shadow'];


		$module_class = ET_Builder_Element::add_module_order_class( $module_class, $function_name );

		$image = $social_links = '';

		$args = array(
			'post_type'			=> 'pauta',
			'orderby'			=> $orderby,
			'order'				=> $order,
			'post_status'       => 'publish',
			'posts_per_page'	=> 1,
		);

		if($include_categories)
		{
			$args['tax_query']	= array(
				array(
					'taxonomy' => 'tema',
					'field' => 'tag_ID',
					'terms' => $include_categories,
					'include_children' => false
				)
			);
		}

		$wp_posts = array();

		if($orderby == 'pauta' && $pauta_id != false && $pauta_id > 0)
		{
			$post = get_post($pauta_id);
			if(!is_object($post))
			{
				$args['orderby'] = 'rand';
				$post = get_posts($args);
				$post->post_title = __("Error: invalid pauta id!", 'et_builder');
			}
			$wp_posts = array($post);
		}
		else 
		{
			$wp_posts = get_posts($args);
		}

    	$image_code = '';
		$pauta_url = "";
		$titulo = "";
		$tema = '';
		$temaLink = "";
		$autor = "";
		$tags = "";
		$avatar = "";
		$except = "";
		$output = "";

		foreach($wp_posts as $key=>$value)
		{

			$term_list = wp_get_post_terms($wp_posts[$key]->ID, 'tema', array("fields" => "all"));
            //print_r($term_list);
			if(is_array($term_list) && count($term_list) > 0)
			{
				$tema = $term_list[0]->name;
				$temaLink = get_term_link($term_list[0]->slug,"tema");
			}

			$autor = get_userdata($wp_posts[$key]->post_author)->display_name;

			$tags = get_the_tag_list('#',' #','',$wp_posts[$key]->ID);

			$autor_url = \Delibera\Member\MemberPath::getAuthorPautasUrl($wp_posts[$key]->post_author);
			
			$avatar = get_avatar( $wp_posts[$key]->post_author, '32');

			if (has_post_thumbnail( $wp_posts[$key]->ID ) ){
				$image_pauta_url = wp_get_attachment_image_src( get_post_thumbnail_id( $wp_posts[$key]->ID  ), 'thumbnail' );
				$image_code = $image_pauta_url[0];
				$module_class .= ' has-thumbnail';
			}
			$pauta_url = $wp_posts[$key]->guid;
			$titulo = get_the_title($wp_posts[$key]);
			$except = apply_filters( 'get_the_excerpt', $wp_posts[$key]->post_excerpt );
			
	
			if($url_tema!=="")
				$temaLink = $url_tema;
	
			if ( '' !== $icon_color ) {
				ET_Builder_Element::set_style( $function_name, array(
					'selector'    => '%%order_class%% .et_pb_member_social_links a',
					'declaration' => sprintf(
							'color: %1$s;',
							esc_html( $icon_color )
							),
				) );
			}

			if ( '' !== $icon_hover_color ) {
				ET_Builder_Element::set_style( $function_name, array(
					'selector'    => '%%order_class%% .et_pb_member_social_links a:hover',
					'declaration' => sprintf(
							'color: %1$s;',
							esc_html( $icon_hover_color )
							),
				) );
			}

			if($image_code !='')
				$image_url = $image_code;

			if ( '' !== $image_url ) {
				$image = sprintf(
						'<div class="' . ($border == 'on' ? 'et_pb_delibera_member_image_border' : 'et_pb_delibera_member_image' ) . ' et-waypoint%3$s" style="background-image:url(%1$s);" title="%2$s" >
							<img src="%1$s" style="visibility: hidden;" />
						</div>',
						esc_url( $image_url ),
						esc_attr( $titulo ),
						esc_attr( " et_pb_animation_{$animation}" )
						);
			}



			$style = $class = '';

			if ( '' !== $background_color ) {
				$style .= sprintf( 'background-color:%s;',
						esc_attr( $background_color )
						);
			}

			$style = '' !== $style ? " style='{$style}'" : '';

			global $deliberaThemes;
			$svg = $deliberaThemes->themeFileUrl('images/icons.svg');
			$like = delibera_gerar_curtir($wp_posts[$key]->ID);
			$unlike = delibera_gerar_discordar($wp_posts[$key]->ID);
			$comment_count = delibera_comment_number($wp_posts[$key]->ID,'todos');

			$button = $button_image_url != '' ? '<img src="'.$button_image_url.'">' : __('Participar', 'et_builder');

			$output = sprintf(
				'<div%3$s class="et_pb_module et_pb_delibera_member%4$s%9$s et_pb_bg_layout_%8$s clearfix">
					%2$s
					<div class="tema" %20$s><a href="%12$s">%11$s</a></div>
					<div class="et_pb_delibera_member_description">
						<a href="%10$s">
							%5$s
							%6$s
							%1$s
							%7$s
	        			</a>
						<div class="tags" id="tags">%14$s</div>
						<a href="%22$s">
							<div class="user" id="user">
								<div class="imageInterna">%15$s</div>
								<div class="name">%13$s</div>
							</div>
						</a>
						%16$s
	        			%17$s
						<div class="comments"><span class="comments-count">%18$s</span><i class="delibera-icon-chat"></i></div>
					</div> <!-- .et_pb_delibera_member_description -->
					<a href="%10$s" ><div class="faixa" %20$s>%21$s</div></a>
				</div> <!-- .et_pb_delibera_member -->',
				$this->shortcode_content,
				( '' !== $image ? $image : '' ),
				( '' !== $module_id ? sprintf( ' id="%1$s"', esc_attr( $module_id ) ) : '' ),
				( '' !== $module_class ? sprintf( ' %1$s', esc_attr( str_replace("delibera","team",$module_class) ) ) : '' ),
				( '' !== $titulo ? sprintf( '<h4>%1$s</h4>', esc_html( $titulo ) ) : '' ),
				( '' !== $position ? sprintf( '<p class="et_pb_member_position">%1$s</p>', esc_html( $position ) ) : '' ),
				$social_links,
				$background_layout,
				( '' === $image ? ' et_pb_delibera_member_no_image' : '' ),
				$pauta_url,
				$tema,
				$temaLink,
				$autor,
				$tags,
				$avatar,
				$like,
				$unlike,
				$comment_count,
				$svg,
				( '' !== $style ? $style : '' ),
				$button,
				$autor_url
			);
		}
		return $output;
	}
	
	/**
	 * @return array array of pauta in format: id => tittle 
	 */
	function get_pautas()
	{
		$pautas = get_posts(array(
			'post_type' 		=> 'pauta',
			'posts_per_page'	=> -1,
			'orderby'			=> 'date',
			'order'				=> 'DESC',
			'post_status'       => 'publish',
		));
		$ret = array('-1' => __('Selecione uma pauta', 'et_builder'));
		if(is_array($pautas))
		{
			/** @var WP_POST $pauta **/ 
			foreach ($pautas as $pauta)
			{
				$ret[$pauta->ID] = get_the_title($pauta);
			}
		}
		return $ret;
	}
}
new ET_Builder_Module_Delibera_Member;

