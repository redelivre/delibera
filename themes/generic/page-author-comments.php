<?php
/*
Template Name: Author Page Comments
*/
get_header();
$id = $wp_query->query_vars['commentsfor'];
$user = get_user_by( 'id' , deliberaEncryptor('decrypt',$id) );

$per_page = isset( $_GET['per-page'] ) ?	esc_html( $_GET['per-page'] ) : '20' ;
$search = isset( $_GET['search'] ) ?	esc_html( $_GET['search'] ) : '' ;
$order	= isset( $_GET['order-by'] ) ?	esc_html( $_GET['order-by'] ) : '' ;
$paged = get_query_var( 'paged' );

?>
<div id="container">
	<div id="main-content" role="main">
		<div id="primary" class="content-area">
			<main id="main" class="site-main" role="main">
				<div id="user_form_search" class="user_form_search">
					<form method="get"	name="form">
						<p>
						<input type="text" name="search" placeholder="<?php _e('Pesquisar por Comentários', 'delibera'); ?> ..." value="<?php echo $search; ?>"/>
						<br>
						<br>
						<input type="submit" id="submit" class="button button-primary" value="Pesquisar"	/>
						</p>
					</form>
				</div>
<form method="get">
	<label for="per-page"><?php echo __('Comentários por Página:' , 'delibera'); ?></label>
	<select id="per-page" name="per-page"	onchange='if(this.value != 0) { this.form.submit(); }' >
		<option value="5" <?php echo $per_page=='5' ? 'selected' : '' ;?> >5</option>
		<option value="10" <?php echo $per_page=='10' ? 'selected' : '' ;?> >10</option>
		<option value="20" <?php echo $per_page=='20' ? 'selected' : '' ;?> >20</option>
	</select>
</form>
<a href="<?php echo get_site_url(); ?>/delibera/membros" ><?php _e( 'Ver todos os Membros' , 'delibera' ); ?></a>
<a href="<?php echo \Delibera\Member\MemberPath::getAuthorPautasUrl($user->ID); ?>" ><?php _e( 'Ver todas as Pautas de' , 'delibera' ); ?> <?php echo $user->display_name; ?></a>
<p>
	<div>
		<?php echo get_avatar( $user->ID ); ?>
		<h1><?php echo $user->first_name ?></h1>
	</div>
</p>

<?php $comments = \Delibera\Theme\UserDisplay::getUserComments($user, $search, $per_page, $paged); ?>
<div id="user_pager" class="user_pager">
	<p>
		<?php echo \Delibera\Theme\UserDisplay::getPaginator( \Delibera\Theme\UserDisplay::getNumberOfPages($comments[0]->comment_count,$per_page) , $paged ); ?>
	</p>
</div>
<?php
if ( $comments )
{
	?>
	<h2>Comentários: </h2>
	<br>
	<div class="user_comment">
		<?php foreach ( $comments as $comment ) {
			$type = \Delibera\Theme\UserDisplay::get_comment_meta( $comment->comment_ID , 'comment_tipo');?>
			<strong>
				<?php echo \Delibera\Theme\UserDisplay::parse_comment_type( $comment->comment_ID , 'tipo'); ?>
			</strong>
			<br>
			<?php echo $comment->comment_content;?>
			<br>
			<?php echo wp_get_attachment_image(get_comment_meta( $comment->comment_ID , 'attachmentId', true)); ?>
			<br>
			<p><?php _e('Na pauta','delibera'); ?>
				<a href="<?php echo get_comment_link( $comment->comment_ID )?>">
					<?php echo get_the_title($comment->comment_post_ID); ?>
				</a>,
				<?php _e('em','delibera'); ?>
				<?php echo mysql2date('d/m/Y', $comment->comment_date); ?>
				<?php if( $type	=== 'validacao' || $type === 'voto' )
				{
					?>
					<br><br>
					<?php _e('Numero de votos' , 'delibera' ); ?>:
					<?php
					$votaram = \Delibera\Theme\UserDisplay::get_comment_meta( $comment->comment_ID , 'votos');
					echo $votaram ? count($votaram) : '0';
					//var_dump( get_comment_meta($comment->comment_ID) );
					//var_dump( get_comment_meta($comment->comment_ID, 'delibera_votos' , true) );
					?>
					<br>
					<?php
				}
				?>
				<p><?php _e('Concordaram' , 'delibera' ); ?>:
					<?php
					$curtiram = \Delibera\Theme\UserDisplay::get_comment_meta( $comment->comment_ID , 'numero_curtir' );
					echo $curtiram ? $curtiram[0] : '0';
					?>
					<br><?php _e('Discordaram' , 'delibera' ); ?>:
					<?php
					$discordaram =	\Delibera\Theme\UserDisplay::get_comment_meta( $comment->comment_ID , 'numero_discordar' );
					echo $discordaram ? $discordaram[0] : '0';
					?>
				</p>
				<?php
			}
			?>
		</div>
		<?php
	} else { _e('Nenhum Comentário encontrado!' , 'delibera'); }
	?>
	<div id="user_pager" class="user_pager">
		<p>
			<?php echo \Delibera\Theme\UserDisplay::getPaginator(
			\Delibera\Theme\UserDisplay::getNumberOfPages($comments[0]->comment_count,$per_page)
			, $paged);
			?>
		</p>
	</div>
			</main><!-- #main -->
		</div>
	</div><!-- #content -->
</div><!-- #container -->
<?php
get_footer();
?>
</body>
</html>
