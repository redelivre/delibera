<?php
/**
 * Funções usadas no painel administrativo do delibera
 */

/**
 * Função para incluir a ação de edição em massa para prazo de discussão
 */
function delibera_custom_bulk_admin_footer() {

    $current_screen = get_current_screen();

    if($current_screen->post_type == 'pauta' && $current_screen->id == "edit-pauta") {
        ?>
        <script type="text/javascript">
            jQuery(document).ready(function() {
                jQuery('<option>').val('set_prazodiscussao').text('Definir prazo de discussão').appendTo("select[name='action']");

                jQuery("select[name='action']").change(function(){
                    if (jQuery(this).val() == "set_prazodiscussao") {
                        var nova_data = prompt("Defina o novo prazo para discussão");

                        if (nova_data != null) {
                            jQuery("select[name='action']").find("option[value='set_prazodiscussao']").text('Definir prazo de discussão para ' + nova_data);
                            jQuery("select[name='action']").after("<input type='hidden' name='novo_prazo_discussao' value='"+ nova_data +"'/>");
                        }
                    }
                });
            });
        </script>
    <?php
    }
}

add_action('admin_footer', 'delibera_custom_bulk_admin_footer');

/**
 * Função que trata a ação de edição em massa do prazo de discussão
 */
function delibera_custom_bulk_action() {

    $wp_list_table = _get_list_table('WP_Posts_List_Table');
    $action = $wp_list_table->current_action();

    switch($action) {
        case 'set_prazodiscussao':
            check_admin_referer('bulk-posts');

            $pautas_ids = $_REQUEST['post'];
            $novo_prazo_discussao = $_REQUEST['novo_prazo_discussao'];
            $inovo_prazo_discussao = DateTime::createFromFormat('d/m/Y', $novo_prazo_discussao)->getTimestamp();
            $pautas_afetadas = 0;

            foreach($pautas_ids as $pauta_id) {

                delibera_set_novo_prazo_discussao_relatoria($pauta_id, $inovo_prazo_discussao, delibera_get_config());

                $pautas_afetadas++;
            }

            $sendback = admin_url( "edit.php?post_type=pauta&pautas_afetadas=$pautas_afetadas&novo_prazo=$novo_prazo_discussao");

            wp_redirect($sendback);

            exit();

        break;
    }
}

add_action('load-edit.php', 'delibera_custom_bulk_action');

/**
 * Função que exibe a mensagem de confirmação da alteração em massa
 */
function delibera_custom_bulk_admin_notices() {

    $current_screen = get_current_screen();

    if($current_screen->post_type == 'pauta' && $current_screen->id == "edit-pauta" &&
        isset($_REQUEST['pautas_afetadas']) && (int) $_REQUEST['pautas_afetadas']) {
        $mensagem = sprintf( '%s pautas definidas para o prazo de %s.', number_format_i18n( $_REQUEST['pautas_afetadas']), $_REQUEST['novo_prazo'] );
        echo "<div class='updated'><p>{$mensagem}</p></div>";
  }
}

add_action('admin_notices', 'delibera_custom_bulk_admin_notices');