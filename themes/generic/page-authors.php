<?php
/*
Template Name: Authors Page
*/
get_header();
$per_page = isset( $_GET['per-page'] ) ?	esc_html( $_GET['per-page'] ) : '20' ;
$search = isset( $_GET['search'] ) ?	esc_html( $_GET['search'] ) : '' ;
$order	= isset( $_GET['order-by'] ) ?	esc_html( $_GET['order-by'] ) : '' ;
$order_by = \Delibera\Theme\UserDisplay::getOrderBy($order);
$paged = get_query_var( 'paged' );
$blogusers = \Delibera\Theme\UserDisplay::getUsers( $order_by , $search , $per_page , $paged );
$number_of_pages = \Delibera\Theme\UserDisplay::getNumberOfPages( $blogusers->get_total() , $per_page );
?>
<div id="container">
	<div id="main-content" role="main">
		<div id="primary" class="content-area">
			<main id="main" class="site-main" role="main">
				<div id="user_form_search" class="user_form_search">
					<form method="get"	name="form">
					<p>
						<input type="text" name="search" placeholder="Pesquisar Membros ..." value="<?php echo $search; ?>"/>
						<br>
						<br>
						<input type="submit" id="submit" class="button button-primary" value="Pesquisar"	/>
					</p>
					</form>
				</div>

<form method="get">
	<label for="order-by"><?php echo __('Ordenar por: ' , 'delibera' ); ?></label>
	<select id="order-by" name="order-by"	onchange='if(this.value != 0) { this.form.submit(); }' >
		<!--option value="active" <?php echo $order=='active' ? 'selected' : '' ;?> >Atividade Recente</option-->
		<!-- XXX retirando isso pois não funciona para pauta -->
		<!--option value="active" <?php echo $order=='active' ? 'selected' : '' ;?> ><?php echo __('Possui Maior Número de Posts' , 'delibera'); ?></option-->
		<option value="newest" <?php echo $order=='newest' ? 'selected' : '' ;?> ><?php echo __('Recém-registrado' , 'delibera'); ?></option>
		<option value="alphabetical" <?php echo $order=='alphabetical' ? 'selected' : '' ;?> ><?php echo __('Ordem alfabética' , 'delibera'); ?></option>
	</select>
	<label for="per-page"><?php echo __('Usuários por Página:' , 'delibera'); ?></label>
	<select id="per-page" name="per-page"	onchange='if(this.value != 0) { this.form.submit(); }' >
		<option value="5" <?php echo $per_page=='5' ? 'selected' : '' ;?> >5</option>
		<option value="10" <?php echo $per_page=='10' ? 'selected' : '' ;?> >10</option>
		<option value="20" <?php echo $per_page=='20' ? 'selected' : '' ;?> >20</option>
	</select>
</form>

<div id="user_pager" class="user_pager">
	<p>
		<?php echo \Delibera\Theme\UserDisplay::getPaginator( $number_of_pages, $paged ); ?>
	</p>
</div>
		<div id="user_list" class="user_list">
			<?php
			if ($blogusers)
			{
				foreach ( $blogusers->results as $user )
				{
					?>
					<h1>
						<p>
							<?php echo get_avatar( $user->ID ); ?>
						</p>
						<p>
							<?php echo esc_html( $user->display_name ); ?>
							-
							<?php echo count_user_posts($user->ID, 'pauta'); ?>
							<?php _e( 'pautas' , 'delibera'); ?>
						</p>
						<a id="link_pautas" class="link_pautas" href="<?php echo get_site_url()?>/delibera/<?php echo deliberaEncryptor('encrypt', $user->ID); ?>/pautas">
							<?php echo __('Todas as Pautas' , 'delibera'); ?>
						</a>
						|
						<a id="link_comments" class="link_comments" href="<?php echo get_site_url()?>/delibera/<?php echo deliberaEncryptor('encrypt', $user->ID); ?>/comentarios">
							<?php echo __('Todas os Comentários' , 'delibera'); ?>
						</a>
					</h1>

					<?php	$last_content = \Delibera\Theme\UserDisplay::getLastPauta($user);
					if ( $last_content )
					{
					?>
					<div id="user_post" class="user_post" >
						<div id="user_post_title" class="user_post_title" ><h2><a href="<?php echo $last_content->guid; ?>" ><?php echo	$last_content->post_title; ?></a></h2></div>
						<div id="user_post_content" class="user_post_content"><p><?php echo $last_content->post_content;?></div>
						</div>
					<?php
					}
				}
			}
			else
			{
				echo __('Nenhum usuário encontrado!' , 'delibera');
			}
			echo \Delibera\Theme\UserDisplay::getPaginator( $number_of_pages , $paged );
		?>
		<div>

			</main><!-- #main -->
		</div>
	</div><!-- #content -->
</div><!-- #container -->
<?php
get_footer(); 
?>
</body>
</html>
