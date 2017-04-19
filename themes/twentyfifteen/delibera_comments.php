<?php

require_once(ABSPATH . 'wp-admin/includes/screen.php');

global $delibera_comments_padrao;

$situacao = delibera_get_situacao($id);

if ($situacao->slug == 'comresolucao') {
    $title = __('Encaminhamentos propostos', 'delibera');
} else if ($situacao->slug == 'validacao') {
    $title = '';
    $votes = delibera_get_comments_validacoes($post->ID);
    $approvals = (int) get_post_meta($post->ID, 'numero_validacoes', true);
    $rejections = (int) get_post_meta($post->ID, 'delibera_numero_comments_validacoes', true) - $approvals;
} else if ($situacao->slug == 'relatoria') {
    $title = __('Encaminhamentos propostos na discussão', 'delibera');
} else if ($situacao->slug == 'emvotacao') {
    $title = __('Usuários que já votaram', 'delibera');
} else {
    $title = __('Discussão sobre a pauta', 'delibera');
}

if (($situacao->slug == "validacao" || $situacao->slug == "emvotacao") && !$delibera_comments_padrao === true) {
    comment_form(); 
}

?>

<div class="actions">
    <?php if ($situacao->slug == 'relatoria' && !current_user_can('relatoria')) : ?>
        <h2>Pauta em relatoria</h2>
    <?php endif; ?>
    
    <div id="<?php echo ($situacao->slug == 'comresolucao') ? 'encaminhamentos' : 'comments'; ?>" class="comments-area">
        <?php if (have_comments()) : ?>
            <h2 class="comments-title bottom"><?php echo $title; ?></h2>
            <?php if ($situacao->slug == 'validacao') : ?>
                <div class="votes">
                    <div class="votes-agree">
                        <h3>Pessoas que votaram a favor (<?php echo $approvals; ?>)</h3>
                        <?php if ($approvals) : ?>
                            <ul class="clearfix">
                                <?php foreach ($votes as $vote) : ?>
                                    <?php if (get_comment_meta($vote->comment_ID, 'delibera_validacao', true) == 'S') : ?>
                                        <?php $avatar = get_avatar_with_title($vote->user_id); ?>
                                        <li><?php echo $avatar; ?></li>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                    <div class="votes-disagree">
                        <h3>Pessoas que votaram contra (<?php echo $rejections; ?>)</h3>
                        <?php if ($rejections) : ?>
                            <ul class="clearfix">
                                <?php foreach ($votes as $vote) : ?>
                                    <?php if (get_comment_meta($vote->comment_ID, 'delibera_validacao', true) == 'N') : ?>
                                        <?php $avatar = get_avatar_with_title($vote->user_id); ?>
                                        <li><?php echo $avatar; ?></li>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>
            <?php elseif ($situacao->slug == 'relatoria') :
                $args['walker'] = new Delibera_Walker_Comment();
                
                $encaminhamentos = delibera_get_comments_all_encaminhamentos($post->ID);
                $discussoes = delibera_get_comments_discussoes($post->ID);
                ?>
                <ol class="commentslist">
                    <?php wp_list_comments($args, $encaminhamentos); ?>
                </ol>
                
                <h2 class="comments-title bottom"><?php _e('Discussão sobre a pauta', 'delibera'); ?></h2>
                <ol class="commentslist">
                    <?php wp_list_comments($args, $discussoes); ?>
                </ol>
            <?php elseif ($situacao->slug == 'emvotacao') : ?>
                <div class="votes">
                    <ul class="clearfix">
                        <?php delibera_wp_list_comments(); ?>
                    </ul>
                </div>
                
                <?php
                $args['walker'] = new Delibera_Walker_Comment();
                $comments = delibera_get_comments($post->ID, array('discussao', 'encaminhamento', 'encaminhamento_selecionado'));
                ?>
                
                <h2 class="comments-title bottom"><?php _e('Histórico da pauta', 'delibera'); ?></h2>
                
                <ol class="commentslist">
                    <?php wp_list_comments($args, $comments); ?>
                </ol>
            <?php else : ?>
                <ol class="commentlist">
                    <?php delibera_wp_list_comments(); ?>
                </ol>
            <?php endif; ?>
        <?php else : 
            if (!comments_open()) : ?>
                <p class="nocomments">
                    <?php if (!is_user_logged_in()) : ?>
                        <?php printf(
                            __('Para participar, você precisa <a href="%1$s" title="Faça o login">fazer o login</a> ou <a href="%2$s" title="Registre-se" class="register">registrar-se no site</a>.', 'delibera'), 
                            wp_login_url(get_permalink()),
                            site_url('wp-login.php?action=register', 'login')."&lang="
                        ); ?>
                    <?php else : ?>
                        <?php _e('Você não tem permissão para participar desta discussão.', 'delibera'); ?>
                    <?php endif; ?>
                </p>
            <?php endif; // end ! comments_open() ?>
        <?php endif; // end have_comments() ?>
 
        <?php if ($situacao->slug == 'relatoria' && current_user_can('relatoria')) : ?>
            <div class="new-encaminhamento">
                <div class="box">
                    <?php comment_form(); ?>
                </div>
            </div>
        <?php elseif (($situacao->slug != "validacao" && $situacao->slug != "emvotacao" && $situacao->slug != "naovalidada" && $situacao->slug != 'relatoria') || $delibera_comments_padrao === true) :
            comment_form();
            if (function_exists('ecu_upload_form_default')) {
                ecu_upload_form_default();
            } 
        endif; ?>
    </div>
</div>

    
