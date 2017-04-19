<?php

/**
 * Gera um arquivo XLS com as opiniões e propostas de
 * encaminhamento feitos pelos usuários nas pautas
 */

// contorna problema com links simbolicos no ambiente de desenvolvimento
$wp_root = dirname(dirname($_SERVER['SCRIPT_FILENAME'])) . '/../../';

require_once($wp_root . 'wp-load.php');

if (!current_user_can('manage_options')) {
    die('Você não deveria estar aqui');
}

$pautas = get_posts(array('post_type' => 'pauta', 'post_status' => 'publish'));
$comments = array();

foreach ($pautas as $pauta) {
    $comments = array_merge(
        $comments,
        delibera_get_comments($pauta->ID, array('discussao', 'encaminhamento', 'encaminhamento_selecionado', 'resolucao'))
    );
}

foreach ($comments as $comment) {
    $situacao = delibera_get_situacao($comment->comment_post_ID);
    
    $comment->pauta_title = get_the_title($comment->comment_post_ID);
    $comment->pauta_status = $situacao->name;
    $comment->type = delibera_get_comment_type_label($comment, false, false);
    $comment->link = get_comment_link($comment);
    $comment->concordaram = (int) get_comment_meta($comment->comment_ID, 'delibera_numero_curtir', true);
    $comment->discordaram = (int) get_comment_meta($comment->comment_ID, 'delibera_numero_discordar', true);
    $comment->votes_count = (int) get_comment_meta($comment->comment_ID, "delibera_comment_numero_votos", true);;
}

header('Pragma: public');
header('Cache-Control: no-store, no-cache, must-revalidate'); // HTTP/1.1
header("Pragma: no-cache");
header("Expires: 0");
header('Content-Transfer-Encoding: none');
header('Content-Type: application/vnd.ms-excel; charset=UTF-8'); // This should work for IE & Opera
header("Content-type: application/x-msexcel; charset=UTF-8"); // This should work for the rest
header('Content-Disposition: attachment; filename=relatorio.xls');

echo utf8_decode("
<table>
    <tr>
        <td>Pauta</td>
        <td>Situação</td>
        <td>Nome</td>
        <td>E-mail</td>
        <td>Tipo de comentário</td>
        <td>Comentário</td>
        <td>Link</td>
        <td>Concordaram</td>
        <td>Discordaram</td>
        <td>Votos</td>
    </tr>"
);
    
?>

<?php foreach ($comments as $comment) : ?>

    <?php ob_start(); ?>
    
    <tr>
        <td><?php echo $comment->pauta_title; ?></td>
        <td><?php echo $comment->pauta_status; ?></td>
        <td><?php echo $comment->comment_author; ?></td>
        <td><?php echo $comment->comment_author_email; ?></td>
        <td><?php echo $comment->type; ?></td>
        <td><?php echo $comment->comment_content; ?></td>
        <td><?php echo $comment->link; ?></td>
        <td><?php echo $comment->concordaram; ?></td>
        <td><?php echo $comment->discordaram; ?></td><br />
        <td><?php echo $comment->votes_count; ?></td>
    </tr>

    <?php echo utf8_decode(ob_get_clean()); ?>
<?php endforeach; ?>

</table>