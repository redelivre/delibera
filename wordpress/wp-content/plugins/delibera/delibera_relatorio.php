<?php

add_action('admin_menu', function() {
    add_submenu_page('delibera-config', __('Relatório', 'delibera'), __('Relatório', 'delibera'), 'manage_options', 'delibera_relatorio', 'delibera_relatorio_page');
}, 100);

/**
 * Gera a página para o administrador exportar
 * um relatório em xls das opiniões e encaminhamentos
 * feito pelos usuários nas pautas
 * 
 * @return null
 */
function delibera_relatorio_page() {
    ?>
    <div class="wrap span-20">
        <h2><?php echo __('Relatorio', 'delibera'); ?></h2>
        
        <p><?php _e('Utilize esta página para exportar uma tabela do Excel com todas as opiniões e propostas de encaminhamento feitas pelos usuários nas pautas.', 'delibera'); ?></p>
        
        <form method="post" action="<?php echo plugins_url(); ?>/delibera/delibera_relatorio_xls.php" class="clear prepend-top">
            <p class="clear prepend-top">
                <input type="submit" class="button-primary" value="Exportar" />
            </p>
        </form>
    </div>
    <?php 
}

