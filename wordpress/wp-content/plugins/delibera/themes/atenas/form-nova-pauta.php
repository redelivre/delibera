<?php 
$titulo     = isset($_POST['nova-pauta-titulo'])    ? stripslashes($_POST['nova-pauta-titulo']) : '';
$conteudo   = isset($_POST['nova-pauta-conteudo'])  ? stripslashes($_POST['nova-pauta-conteudo']) : '';
$resumo     = isset($_POST['nova-pauta-resumo'])    ? stripslashes($_POST['nova-pauta-resumo']) : '';

?>
<div class="clearfix">
    <form method="post">
        <?php wp_nonce_field('delibera_nova_pauta'); ?>
        <p>
            <label for="nova-pauta-resumo"><?php _e( 'Título da pauta', 'delibera' ); ?></label>
            <input type="text" name="nova-pauta-titulo" id="nova-pauta-titulo" value="<?php echo htmlentities($titulo) ?>" placeholder="<?php _e( 'Digite o título da pauta aqui', 'delibera' ); ?>"/>
        </p>
        <p>
            <?php wp_editor($conteudo, 'nova-pauta-conteudo'); ?>
        </p>
        <p>
            <label for="nova-pauta-resumo"><?php _e( 'Resumo da pauta', 'delibera' ); ?></label>
            <textarea name="nova-pauta-resumo" id="nova-pauta-resumo"><?php echo htmlentities($resumo) ?></textarea>
        </p>
        
        <p>
            <?php $temas = get_terms('tema', array('hide_empty'    => true)); ?>
            Temas:
            <ul>
                <?php foreach($temas as $tema): ?>
                <li><label><input type="checkbox" name="tema[]" value="<?php echo $tema->term_id; ?>" /> <?php echo $tema->name ?></label></li>
                <?php endforeach; ?>
            </ul>
        </p>
        
        <input type="submit" value="<?php _e( 'Criar pauta', 'delibera' ); ?>"/>
    </form>
</div>