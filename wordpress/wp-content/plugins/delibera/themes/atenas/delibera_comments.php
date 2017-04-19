<?php

require_once(ABSPATH . 'wp-admin/includes/screen.php');

global $delibera_comments_padrao;

$situacao = delibera_get_situacao($id);

if (($situacao->slug == "validacao" || $situacao->slug == "emvotacao") && !$delibera_comments_padrao === true) {
    comment_form(); 
}

?>
<div id="<?php echo $delibera_comments_padrao === true ? 'comments' : 'delibera-comments'; ?>">
    <?php if (post_password_required()) : ?>
        <p class="nopassword"><?php _e( 'This post is password protected. Enter the password to view any comments.', 'twentyten' ); ?></p>
        </div><!-- #comments -->
        <?php
        return;
    endif; ?>

    <?php if (have_comments()) : ?>
        <h3 id="<?php echo $delibera_comments_padrao === true ? 'comments-title' : 'delibera-comments-title'; ?>">
            <?php comments_number(__('No responses'), __('One response'), __('% responses')); ?>
        </h3>

        <?php if (get_comment_pages_count() > 1 && get_option('page_comments')) : // Are there comments to navigate through? ?>
            <div class="navigation">
                <div class="nav-previous"><?php previous_comments_link(__('<span class="meta-nav">&larr;</span> Older Comments', 'twentyten')); ?></div>
                <div class="nav-next"><?php next_comments_link(__('Newer Comments <span class="meta-nav">&rarr;</span>', 'twentyten')); ?></div>
            </div> <!-- .navigation -->
        <?php endif; // check for comment navigation ?>

        <ol class="commentlist">
            <?php
                global $delibera_comments_padrao;
                delibera_wp_list_comments( );
            ?>
        </ol>

        <?php if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) : // Are there comments to navigate through? ?>
            <div class="navigation">
                <div class="nav-previous"><?php previous_comments_link(__('<span class="meta-nav">&larr;</span> Older Comments', 'twentyten')); ?></div>
                <div class="nav-next"><?php next_comments_link(__('Newer Comments <span class="meta-nav">&rarr;</span>', 'twentyten')); ?></div>
            </div><!-- .navigation -->
        <?php endif; ?>
    <?php endif; // end have_comments() ?>

    <?php
    if (($situacao->slug != "validacao" && $situacao->slug != "emvotacao" && $situacao->slug != "naovalidada") || $delibera_comments_padrao === true) {
        comment_form();
        if (function_exists('ecu_upload_form_default')) {
            ecu_upload_form_default();
        } 
    }
    ?>
</div><!-- #comments -->
<?php
	if ( !is_user_logged_in() )
	{
		printf(
        __( 'Para participar, você precisa <a href="%1$s" title="Faça o login">fazer o login</a> ou <a href="%2$s" title="Registre-se" class="register">registrar-se no site</a>.', 'delibera' ), 
        wp_login_url( get_permalink() ),
        site_url('wp-login.php?action=register', 'login')."&lang=");
	}
	