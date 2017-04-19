<?php

/**
 * Baseado em no comments-template
 */

/**
 * Gera o HTML de exibição de um comentário
 * do Delibera.
 */
class Delibera_Walker_Comment extends Walker_Comment
{
    /**
     * @see Walker::start_el()
     * @since 2.7.0
     *
     * @param string $output Passed by reference. Used to append additional content.
     * @param object $comment Comment data object.
     * @param int $depth Depth of comment in reference to parents.
     * @param array $args
     */
    function start_el(&$output, $comment, $depth = 0, $args = array(), $current_object_id = 0)
    {
        global $deliberaThemes;
        $depth++;
        $GLOBALS['comment_depth'] = $depth;
        $args['avatar_size'] = '85';

        if (!empty($args['callback'])) {
            call_user_func($args['callback'], $comment, $args, $depth);
            return;
        }

        $GLOBALS['comment'] = $comment;
        
        $tipo = get_comment_meta($comment->comment_ID, "delibera_comment_tipo", true);
        $situacao = delibera_get_situacao($comment->comment_post_ID);
        
        extract($args, EXTR_SKIP);

        if ('div' == $args['style']) {
            $tag = 'div';
            $add_below = 'comment';
        } else {
            $tag = 'li';
            $add_below = 'div-comment';
        }
        
        $classes = array();
        
        if (!empty($args['has_children'])) {
            $classes[] = 'parent';
        }
        
        if ($tipo == 'encaminhamento' || $tipo == 'resolucao' || $tipo == 'encaminhamento_selecionado') {
            $classes[] = 'encaminhamento';
            
            if (in_array($situacao->slug, array('comresolucao', 'emvotacao'))) {
                $classes[] = 'encaminhamentos-selecionados';
            }
        }

        ob_start();
        ?>

        <?php if (($tipo == 'resolucao' || $tipo == 'encaminhamento') && $situacao->slug == 'comresolucao') : ?>
            <?php $nvotos = get_comment_meta($comment->comment_ID, "delibera_comment_numero_votos", true); ?>
            <?php $classes[] = 'clearfix'; ?>
            <li <?php comment_class($classes); ?>>
                <div class="alignleft votos">
                    <span><?php echo ($nvotos == 1) ? sprintf(__('%d voto', 'delibera'), $nvotos) : sprintf(__('%d votos', 'delibera'), $nvotos); ?></span>
                </div>
                <div class="alignleft content">
                    <?php comment_text(); ?>
                </div>
            </li>
        <?php  elseif ($situacao->slug == 'emvotacao' && $tipo == 'voto') : ?>
            <?php $avatar = get_avatar_with_title($comment->user_id); ?>
            <li><?php echo $avatar; ?></li>
        <?php else : ?>
            <li <?php comment_class($classes); ?>>
                <article id="delibera-comment-<?php echo $comment->comment_ID; ?>">
                    <header class="coment-meta comment-author vcard clearfix">
                        <div class="alignleft">
                            <?php echo get_avatar($comment, 44); ?>
                            <cite class="fn"><a href="<?php echo get_author_posts_url($comment->user_id); ?>" rel="external nofollow" class="url"><?php echo $comment->comment_author; ?></a></cite>
                            <a href="<?php echo htmlspecialchars(delibera_get_comment_link($comment->comment_ID)); ?>">
                                <time datetime="<?php echo get_comment_date('c'); ?>">
                                    <?php
                                    $time = mysql2date('G', $comment->comment_date);
                                    
                                    $time_diff = time() - $time;
                                    
                                    if ($time_diff > 0 && $time_diff < 30*24*60*60) {
                                        printf(__('há %s', 'delibera'), human_time_diff(mysql2date('U', $comment->comment_date, true)));
                                    } else {
                                        echo get_comment_date('d \d\e F \d\e Y à\s H:i');
                                    }
                                    
                                    ?>
                                </time>
                            </a>
                        </div>
                        <div class="alignright textright">
                            <span class="type"><?php delibera_get_comment_type_label($comment); ?></span>
                            <?php
                                if ($situacao->slug == 'discussao' || ($situacao->slug == 'relatoria' && current_user_can('relatoria'))) {
                                    echo "<br/>";
                                    delibera_edit_comment_link(__('Edit'),'', '');
                                    delibera_delete_comment_link(__('Deletar'),'', '');
                                }
                            ?>
                        </div>
                    </header>
                    <section class="comment-content">
                        <?php if ($comment->comment_approved == '0') : ?>
                            <em class="delibera-comment-awaiting-moderation"><?php _e('Seu comentário está aguardando moderação.', 'delibera') ?></em>
                            <br />
                        <?php endif; ?>
                        <?php comment_text(); ?>
                        <?php delibera_comment_edit_form(); ?>
                    </section>
                    <section class="actions clearfix">
                        <?php if (delibera_comments_is_open($comment->comment_post_ID) && $situacao->slug != 'emvotacao' && $situacao->slug != 'relatoria') : ?>
                            <div class="bottom alignleft">
                                <div class="reply">
                                    <?php
                                    if ($situacao->slug != 'validacao' && is_user_logged_in()) {            
                                        $args['reply_text'] = __("Responder", 'delibera');
                                        ?>
                                        <?php comment_reply_link(array_merge($args, array('add_below' => $add_below, 'depth' => $depth, 'max_depth' => $args['max_depth']))); ?>
                                        <?php
                                    } else if (is_user_logged_in()) {
                                        ?>
                                        <a href="<?php delibera_get_comment_link();?>#respond" class="comment-reply-link"><?php _e('De sua opinião', 'delibera'); ?></a>
                                        <?php
                                    } else {
                                        ?>
                                        <a href="<?php echo wp_login_url(delibera_get_comment_link());?>#respond" class="comment-reply-link"><?php _e('Faça login e de sua opinião', 'delibera'); ?></a>
                                        <?php
                                    }
                                    ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        <?php
                        if ($situacao->slug == 'relatoria' && current_user_can('relatoria'))    {
                            $baseouseem = get_comment_meta($comment->comment_ID, 'delibera-baseouseem', true);
                            if (!empty($baseouseem)) {
                                $elements = explode(',', $baseouseem);
                                $result = '';
                                $count = count($elements);
                                
                                foreach ($elements as $key => $element) {
                                    $reference_comment = get_comment($element);
                                    $result .= "<a href='#delibera-comment-{$reference_comment->comment_ID}'>{$reference_comment->comment_author}</a>";
                                    
                                    if ($key + 1 < $count) {
                                        $result .= ', ';
                                    }
                                }
                                echo '<div>'.__('Proposta baseada em:', 'delibera') . '&nbsp;' . $result . '</div>';
                            }
                        }
                        
                        if (($tipo == "encaminhamento" || $tipo == 'encaminhamento_selecionado') && current_user_can('relatoria') && $situacao->slug == "relatoria") {
                            $selecionados = delibera_get_comments_encaminhamentos_selecionados_ids($comment->comment_post_ID);
                            
                            if (!$selecionados) {
                                $selecionados = array();
                            }
                            ?>
                            <div class="bottom alignleft">
                                <p>
                                    <input id="baseadoem-checkbox-<?php echo $comment->comment_ID; ?>" type="checkbox" name="baseadoem-checkbox[]" value="<?php echo $comment->comment_ID; ?>" class="baseadoem-checkbox" autocomplete="off" />
                                    <label for="baseadoem-checkbox-<?php echo $comment->comment_ID; ?>"><?php _e('Criar novo encaminhamento baseado neste', 'delibera'); ?></label>
                                </p>
                                <p>
                                    <input id="usar-na-votacao-<?php echo $comment->comment_ID; ?>" class="usar-na-votacao" type="checkbox" name="usar_na_votacao[]" value="<?php echo $comment->comment_ID; ?>" <?php echo (in_array($comment->comment_ID, $selecionados)) ? ' checked="checked" ' : ''; ?> />
                                    <label for="usar-na-votacao-<?php echo $comment->comment_ID; ?>"><?php _e('Usar este encaminhamento na votação', 'delibera'); ?></label>
                                    <img class="usar-na-votacao-feedback" src="<?php echo $deliberaThemes->getThemeUrl(); ?>/img/accept.png" style="display: none;" />
                                </p>
                            </div>
                            <?php 
                        }
                        
                        $ncurtiu = get_comment_meta($comment->comment_ID, 'delibera_numero_curtir', true);
                        $ndiscordou = get_comment_meta($comment->comment_ID, 'delibera_numero_discordar', true);
                        
                        if (is_user_logged_in() || $ncurtiu || $ndiscordou) : ?>
                            <div class="bottom alignright textright">
                                <?php
                                
                                $curtir = delibera_gerar_curtir($comment->comment_ID, 'comment');
                                $discordar = delibera_gerar_discordar($comment->comment_ID, 'comment');
                                
                                if ($curtir) {
                                    echo $curtir;
                                }
                                
                                if ($discordar) {
                                    echo $discordar;
                                }                                                
                                ?>
                            </div>
                        <?php endif; ?>
                    </section><!-- .reply -->
                </article>
            </li>
        <?php
        endif;

        $output .= ob_get_clean();
    }
}
