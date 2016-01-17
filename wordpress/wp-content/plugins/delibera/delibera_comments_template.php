<?php

class Delibera_Walker_Comment_padrao extends Walker_Comment
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
    function start_el(&$output, $comment, $depth = 0, $args = array(), $current_object_id = 0) {
        $depth++;
        $GLOBALS['comment_depth'] = $depth;

        if ( !empty($args['callback']) ) {
            call_user_func($args['callback'], $comment, $args, $depth);
            return;
        }

        $GLOBALS['comment'] = $comment;
        extract($args, EXTR_SKIP);

        if ( 'div' == $args['style'] ) {
            $tag = 'div';
            $add_below = 'comment';
        } else {
            $tag = 'li';
            $add_below = 'div-comment';
        }
?>
        <<?php echo $tag ?> <?php comment_class(empty( $args['has_children'] ) ? '' : 'parent') ?> id="comment-<?php comment_ID() ?>">
        <?php if ( 'div' != $args['style'] ) : ?>
        <div id="div-comment-<?php comment_ID() ?>" class="comment-body">
        <?php endif; ?>
        <div id="div-comment-header-<?php comment_ID() ?>" class="comment-header">
            <div class="comment-author vcard">
            <?php if ($args['avatar_size'] != 0) echo get_avatar( $comment, $args['avatar_size'] ); ?>
            <?php printf(__('<cite class="fn">%s</cite> <span class="says">says:</span>'), get_comment_author_link()) ?>
            </div>
    <?php if ($comment->comment_approved == '0') : ?>
            <em class="comment-awaiting-moderation"><?php _e('Your comment is awaiting moderation.') ?></em>
            <br />
    <?php endif; ?>
    
            <div class="comment-meta commentmetadata"><a href="<?php echo htmlspecialchars( get_comment_link( $comment->comment_ID ) ) ?>">
                <?php
                    /* translators: 1: date, 2: time */
                    printf( __('%1$s at %2$s'), get_comment_date(),  get_comment_time()) ?></a><?php edit_comment_link(__('(Edit)'),'&nbsp;&nbsp;','' );
                ?>
            </div>
        </div>
        <?php comment_text() ?>

        <?php if(comments_open($comment->comment_post_ID) && is_user_logged_in()) { ?>
        <div class="reply">
        <?php comment_reply_link(array_merge( $args, array('add_below' => $add_below, 'depth' => $depth, 'max_depth' => $args['max_depth']))) ?>
        </div>
        <?php }if ( 'div' != $args['style'] ) : ?>
        </div>
        <?php endif; ?>
<?php
    }

}
