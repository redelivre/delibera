<?php
/*
Plugin Name: Delibera
Plugin URI: http://www.ethymos.com.br
Description: O Plugin Delibera extende as funções padrão do WordPress e cria um ambiente de deliberação.
Version: 1.0.3
Author: Ethymos
Author URI: http://www.ethymos.com.br

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
"AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR
CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL,
EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO,
PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR
PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF
LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING
NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
*/
/**
 * Reponsável por importar todos os arquivos utilizados pelo plugin
 * @package Global
 */

if(!defined('__DIR__')) {
    $iPos = strrpos(__FILE__, DIRECTORY_SEPARATOR);
    define("__DIR__", substr(__FILE__, 0, $iPos) . DIRECTORY_SEPARATOR);
}

define('DELIBERA_ABOUT_PAGE', __('sobre-a-plataforma', 'delibera'));

require_once __DIR__ . DIRECTORY_SEPARATOR . 'delibera_shortcodes.php';

require_once __DIR__ . DIRECTORY_SEPARATOR . 'delibera_widgets.php';

require_once __DIR__ . DIRECTORY_SEPARATOR . 'delibera_rewrite_rules.php';

require_once __DIR__ . DIRECTORY_SEPARATOR . 'delibera_conf.php';

require_once __DIR__ . DIRECTORY_SEPARATOR . 'delibera_conf_roles.php';

require_once __DIR__ . DIRECTORY_SEPARATOR . 'delibera_conf_themes.php';

require_once __DIR__ . DIRECTORY_SEPARATOR . 'print' . DIRECTORY_SEPARATOR . 'wp-print.php';

require_once __DIR__ . DIRECTORY_SEPARATOR . 'delibera_admin_functions.php';

require_once __DIR__ . DIRECTORY_SEPARATOR . 'delibera_setup.php';

require_once __DIR__ . DIRECTORY_SEPARATOR . 'delibera_init.php';

require_once __DIR__ . DIRECTORY_SEPARATOR . 'delibera_flow.php';

require_once __DIR__ . DIRECTORY_SEPARATOR . 'delibera_utils.php';

require_once __DIR__ . DIRECTORY_SEPARATOR . 'delibera_comments.php';

require_once __DIR__ . DIRECTORY_SEPARATOR . 'delibera_comments_query.php';

require_once __DIR__ . DIRECTORY_SEPARATOR . 'delibera_comments_template.php';

require_once __DIR__ . DIRECTORY_SEPARATOR . 'delibera_comments_edit.php';

require_once __DIR__ . DIRECTORY_SEPARATOR . 'delibera_cron.php';

require_once __DIR__ . DIRECTORY_SEPARATOR . 'delibera_topic.php';

require_once __DIR__ . DIRECTORY_SEPARATOR . 'delibera_topic_deadline.php';

if (file_exists(__DIR__ . DIRECTORY_SEPARATOR . 'delibera_filtros.php'))
{
	require_once __DIR__ . DIRECTORY_SEPARATOR . 'delibera_filtros.php';
}

require_once __DIR__ . DIRECTORY_SEPARATOR . 'delibera_curtir.php';

require_once __DIR__ . DIRECTORY_SEPARATOR . 'delibera_discordar.php';

require_once __DIR__ . DIRECTORY_SEPARATOR . 'delibera_seguir.php';

if (file_exists(__DIR__.DIRECTORY_SEPARATOR.'mailer') &&
    file_exists(__DIR__.DIRECTORY_SEPARATOR.'mailer' . DIRECTORY_SEPARATOR . 'delibera_mailer.php'))
{
	//require_once __DIR__.DIRECTORY_SEPARATOR.'mailer'.DIRECTORY_SEPARATOR.'delibera_mailer.php';
}

require_once __DIR__ . DIRECTORY_SEPARATOR . 'delibera_notificar.php';

require_once __DIR__ . DIRECTORY_SEPARATOR . 'delibera_user_painel.php';

require_once __DIR__ . DIRECTORY_SEPARATOR . 'delibera_relatorio.php';

 /**
   * Redir e ciona usuários que não são membros do site
 * onde o Delibera foi instalado para a página de pautas após o
  * login se a opção   "Todos os usuários logados na rede podem participar?"
 * e s tiver habilitada.
    *
 * Se não fizermos esse redicionamento estes usuários serão redirecionados
 * para suas páginas de perfil fora do site onde o Delibera es t á  i nstalado.
 *
a d d_filter('login_redirect', function($redirect_to, $request, $user) {
    $options = delibera_get_config();

    if ($options['todos_usuarios_logados_podem_participar'] == 'S' && !is_user_member_of_blog()) {
        return site_url('pauta');
    } else {
        return $redirect_to;
    }
}, 10, 3);
TODO mundo redirecionado para a lista de pauta, talvez uma nova opções */
