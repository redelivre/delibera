<?php 
$titulo     = isset($_POST['nova-pauta-titulo'])    ? stripslashes($_POST['nova-pauta-titulo']) : '';
$conteudo   = isset($_POST['nova-pauta-conteudo'])  ? stripslashes($_POST['nova-pauta-conteudo']) : '';
$resumo     = isset($_POST['nova-pauta-resumo'])    ? stripslashes($_POST['nova-pauta-resumo']) : '';

?>
<div class="clearfix">
    <form method="post" id="nova-pauta-form" class="clearfix">
        <?php wp_nonce_field('delibera_nova_pauta'); ?>
        <div class="clearfix">
            <div class="alignleft">
                <div class="row">
                    <label for="nova-pauta-resumo"><?php _e( 'Título da pauta', 'delibera' ); ?></label><br/>
                    <input type="text" name="nova-pauta-titulo" id="nova-pauta-titulo" value="<?php echo htmlentities($titulo) ?>" placeholder="<?php _e( 'Digite o título da pauta aqui', 'delibera' ); ?>"/>
                </div>
                
                <div class="divider"></div>
        
                <div class="row">
                    <?php wp_editor($conteudo, 'nova-pauta-conteudo'); ?>
                </div>
                
                <div class="divider"></div>
        
                <div class="row">
                    <label for="nova-pauta-resumo"><?php _e( 'Resumo da pauta', 'delibera' ); ?></label><br/>
                    <textarea name="nova-pauta-resumo" id="nova-pauta-resumo"><?php echo htmlentities($resumo) ?></textarea>
                </div>
            </div>

            <div class="alignright">
                <div class="row">
                    <?php $temas = get_terms('tema', array('hide_empty' => true)); ?>
                    <label>Temas:</label>
                    <ul>
                        <?php foreach($temas as $tema): ?>
                        <li><label class="checkbox"><input type="checkbox" name="tema[]" value="<?php echo $tema->term_id; ?>" /> <?php echo $tema->name ?></label></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
        
        <div class="divider"></div>
        
        <div class="textright">
            <input type="submit" value="<?php _e( 'Criar pauta', 'delibera' ); ?>" class="btn btn-success"/>
        </div>
    </form>
</div>