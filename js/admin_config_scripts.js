jQuery(document).ready(function()
{
    if(jQuery('#remover_votacao_simples')[0].checked)
        jQuery('#votacao_simples').attr("disabled", true);

    if(jQuery('#votacao_simples')[0].checked)
        jQuery('#remover_votacao_simples').attr("disabled", true);

    jQuery('#votacao_simples').on('click' , function(){
        disable_remover_votacao_simples();
    });

    jQuery('#remover_votacao_simples').on('click' , function(){
        disable_votacao_simples();
    });

    function disable_remover_votacao_simples(){
        if(jQuery('#votacao_simples')[0].checked){
            if(!jQuery('#remover_votacao_simples')[0].checked){
                jQuery('#remover_votacao_simples').attr("disabled", true);
            }else{
                jQuery('#remover_votacao_simples')[0].checked = false;
                jQuery('#remover_votacao_simples').attr("disabled", true);
            }
        }else{
            if(!jQuery('#remover_votacao_simples')[0].checked){
                jQuery('#remover_votacao_simples').attr("disabled", false);
            }else{
                jQuery('#remover_votacao_simples')[0].checked = false;
                jQuery('#remover_votacao_simples').attr("disabled", false);
            }
        }

    }

    function disable_votacao_simples(){
        if(jQuery('#remover_votacao_simples')[0].checked){
            if(!jQuery('#votacao_simples')[0].checked){
                jQuery('#votacao_simples').attr("disabled", true);
            }else{
                jQuery('#votacao_simples')[0].checked = false;
                jQuery('#votacao_simples').attr("disabled", true);
            }
        }else{
            if(!jQuery('#votacao_simples')[0].checked){
                jQuery('#votacao_simples').attr("disabled", false);
            }else{
                jQuery('#votacao_simples')[0].checked = false;
                jQuery('#votacao_simples').attr("disabled", false);
            }
        }

    }
});