<?php

if (have_posts()) :
    while (have_posts()) :
        the_post();
        $temas = wp_get_post_terms($post->ID, 'tema');
        
        $situacao = delibera_get_situacao($post->ID);
        ?>
        <div class="topic clearfix">
            <div class="meta clearfix">
                <div class="status alignleft"><?php echo $situacao->name; ?></div>
                <div class="deadline alignright">
                    <?php if (delibera_get_prazo($post->ID) == -1) {
                        echo 'Prazo encerrado';
                    } else {
                        printf(_n('Encerra em um dia', 'Encerra em %1$s dias', delibera_get_prazo($post->ID), 'delibera'), number_format_i18n(delibera_get_prazo($post->ID)));
                    } ?>
                </div>
            </div>
            <h1><a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>"><?php the_title(); ?></a></h1>
            <p class="meta">Discussão criada por <span class="author"><?php the_author(); ?></span> em <span class="date"><?php echo get_the_date('d/m/y'); ?></span></p>
            <p><?php the_excerpt(); ?></p>

            <?php if (!empty($temas)) : ?>
                <ul class="meta meta-tags">
                    <li>Tema:</li>
                    <?php $size = count($temas) - 1; ?>
                    <?php foreach ($temas as $key => $tema) : ?>
                        <li><a href="<?php echo get_post_type_archive_link('pauta') . "?tema_filtro[{$tema->slug}]=on"; ?>"><?php echo $tema->name; ?></a><?php echo ($key != $size) ? ',' : ''; ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <div class="actions bottom clearfix">
                <?php $label = delibera_get_comments_count_by_type($post->ID); ?>
                <?php if ($label) : ?>
                    <div class="number-of-comments alignleft">
                        <a href="<?php the_permalink(); ?>#comments"><?php echo $label; ?></a>
                    </div>
                <?php endif; ?>
                <?php if (in_array($situacao->slug, array('emvotacao', 'discussao', 'validacao'))) : ?>
                    <div class="alignright bottom textright">
                        <a class="btn" href="<?php the_permalink() ?>"><?php echo delibera_get_situation_button($post->ID); ?></a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php
    endwhile;
endif; ?>
