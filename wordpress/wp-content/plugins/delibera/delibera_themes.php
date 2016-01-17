<?php

/**
 * Controla os distintos temas do Delibera disponíveis.
 * 
 * Os temas do Delibera podem ser salvos em dois lugares distintos.
 * Na pasta themes dentro da pasta do plugin. Cada tema deve estar dentro
 * de uma sub pasta cujo o nome é o nome do tema. Um tema do Delibera
 * também pode ser salvo dentro de uma pasta chamada delibera dentro
 * do tema atual do Wordpress.
 */
class DeliberaThemes
{
    /**
     * Diretório onde ficam os temas
     * dentro do plugin
     * @var string
     */
    public $baseDir;
    
    /**
     * URL do diretório onde ficam
     * os temas dentro do plugin
     * @var string
     */
    public $baseUrl;
    
    /**
     * Caminho para o diretório
     * do tema padrão
     * @var string
     */
    public $defaultThemePath;
    
    /**
     * URL para o diretório do
     * tema padrão
     * @var string
     */
    public $defaultThemeUrl;
    
    /**
     * Caminho para o tema do Delibera
     * dentro do tema atual do WP.
     * @var string
     */
    public $wpThemePath;
    
    /**
     * Url para o diretório do tema do Delibera
     * dentro do tema atual do WP
     * @var string
     */
    public $wpThemeUrl;
    
    /**
     * Nome do tema atual do Wordpress
     * @var string
     */
    public $wpThemeName;
    
    function __construct()
    {
        $this->baseDir = __DIR__ . '/themes/';
        $this->baseUrl = plugins_url('/delibera/themes/');
        $this->defaultThemePath = $this->baseDir . 'atenas/';
        $this->defaultThemeUrl = $this->baseUrl . 'atenas/';
        
        $this->wpThemePath = get_stylesheet_directory() . '/delibera';
        $this->wpThemeUrl = get_stylesheet_directory_uri() . '/delibera';
        $this->wpThemeName = wp_get_theme()->get_stylesheet();
    }
    
    /**
     * Retorna o diretório de um tema. 
     * Se não um nome de tema for passado como
     * parâmetro, retorna o diretório do tema atual.
     * 
     * @param string $themeName
     * @return string
     */
    public function getThemeDir($themeName = '')
    {
        if (!empty($themeName)) {
            $themePath = $this->baseDir . $themeName;
        } else {
            $conf = delibera_get_config();
            $themePath = $conf['theme'];
        }

        if (file_exists($themePath)) {
            return $themePath;
        } else {
            return $this->defaultThemePath;
        }
    }
    
    /**
     * Retorna a URL para o diretório 
     * principal do tema atual.
     * 
     * @return string
     */
    public function getThemeUrl()
    {
        $conf = delibera_get_config();

        if (file_exists($conf['theme'])) {
            // TODO: melhorar a separacao entre tema distribuido junto com o plugin e tema do delibera dentro do tema do wp
            if (strpos($conf['theme'], '/wp-content/themes') === false) {
                // tema distribuido junto com o plugin
                return $this->baseUrl . basename($conf['theme']);
            } else {
                // tema dentro do tema atual do wp
                return $this->wpThemeUrl;
            }
        } else {
            return $this->defaultThemeUrl;
        }
    }

    /**
     * Retorna o caminho no sistema de arquivos
     * para um arquivo no tema atual. Se o arquivo
     * não existir, retorna o caminho para o arquivo 
     * no tema padrão.
     * 
     * @param string $file_name
     * @return string
     */
    public function themeFilePath($fileName)
    {
        $filePath = $this->getThemeDir() . '/' . $fileName;
        
        if (file_exists($filePath)) {
            return $filePath;
        } else {
            return $this->defaultThemePath . $fileName;
        }
    }
    
    /**
     * Retorna a url para um arquivo no tema atual.
     * Se o arquivo não existir, retorna o caminho
     * para o arquivo no tema padrão.
     * 
     * @param string $file_name
     * @return string
     */
    public function themeFileUrl($fileName)
    {
        $filePath = $this->getThemeDir() . '/' . $fileName;
        
        if (file_exists($filePath)) {
            return $this->getThemeUrl() . '/' . $fileName;
        } else {
            return $this->defaultThemeUrl . $fileName;
        }
    }
    
    /**
     * Inclui os arquivos do tema relacionados com 
     * a listagem de pautas e retorna o template
     * a ser usado.
     * 
     * @param string $archiveTemplate
     * @return string
     */
    public function archiveTemplate($archiveTemplate)
    {
        global $post;
    
        if (get_post_type($post) == "pauta" || is_post_type_archive('pauta'))
        {
        	if(file_exists(get_stylesheet_directory()."/archive-pauta.php"))
        	{	
        		$archive_template = get_stylesheet_directory()."/archive-pauta.php";
        	}
        	else 
        	{
        		$archiveTemplate = $this->themeFilePath('archive-pauta.php');
        	}
        }
                
        return $archiveTemplate;
    }
    
    /**
     * Inclui os arquivos do tema relacionados com 
     * a página de uma pauta e retorna o template
     * a ser usado.
     * 
     * @param string $singleTemplate
     * @return string
     */
    public function singleTemplate($singleTemplate)
    {
        global $post;
    
        if (get_post_type($post) == "pauta" || is_post_type_archive('pauta'))
        {
        	if(file_exists(get_stylesheet_directory()."/single-pauta.php"))
        	{	
        		$singleTemplate = get_stylesheet_directory()."/single-pauta.php";
        	}
        	else
        	{
        		$singleTemplate = $this->themeFilePath('single-pauta.php');
        	}
        }
        
        return $singleTemplate;
    }
    
    /**
     * Inclui os arquivos CSS
     * 
     * @return null
     */
    public function publicStyles()
    {
        global $post, $wp_query;
        
        if (get_post_type($post) == "pauta" || is_post_type_archive('pauta') || $wp_query->get('tpl') === 'nova-pauta')
        {
        	if(file_exists(get_stylesheet_directory()."/delibera_style.css"))
        	{
        		wp_enqueue_style('delibera_style', get_stylesheet_directory_uri()."/delibera_style.css");
        	}
        	else
        	{
        		wp_enqueue_style('delibera_style', $this->themeFileUrl('delibera_style.css'));
        	}
        }
    }
    
    /**
     * Adiciona o CSS do admin conforme o
     * tema.
     * 
     * @return null
     */
    public function adminPrintStyles()
    {
        wp_enqueue_style('delibera_admin_style', $this->themeFileUrl('delibera_admin.css'));
    }
    
    /**
     * Carrega o arquivo de template do loop
     * de pautas para o tema atual. Se o arquivo
     * não existir usa o arquivo do tema padrão.
     * 
     * @return null
     */
    public function archiveLoop()
    {
    	if(file_exists(get_stylesheet_directory()."/loop-pauta.php"))
    	{
    		load_template(get_stylesheet_directory()."/loop-pauta.php");
    	}
    	else
    	{
       		load_template($this->themeFilePath('delibera-loop-archive.php'), true);
    	}
    }
    
    /**
     * Retorna um array com os temas disponíveis.
     * 
     * @return array
     */
    public function getAvailableThemes()
    {
        $themes = array();
        $dirs = glob($this->baseDir . '*', GLOB_ONLYDIR);
        
        foreach ($dirs as $dir) {
            $themes[$dir] = basename($dir);
        }
        
        // adiciona o tema do delibera de dentro do tema atual do wp se um existir
        if (file_exists($this->wpThemePath)) {
            $themes[$this->wpThemePath] = $this->wpThemeName;
        }
        
        return $themes;
    }
    
    /**
     * Gera o select box com os temas disponíveis
     * para a interface de admin do Delibera.
     *
     * @param string $currentTheme o tema atual 
     * @return string
     */
    public function getSelectBox($currentTheme)
    {
        $themes = $this->getAvailableThemes();
        
        $html = "<select name='theme' id='theme'>";
        
        foreach ($themes as $themePath => $themeName) {
            $html .= "<option value='{$themePath}'" . selected($themePath, $currentTheme, false) . ">{$themeName}</option>";
        }
        
        $html .= "</select>";
        
        return $html;
    }
}

$deliberaThemes = new DeliberaThemes;

add_filter('archive_template', array($deliberaThemes, 'archiveTemplate'));
add_filter('single_template', array($deliberaThemes, 'singleTemplate'));
add_action('admin_print_styles', array($deliberaThemes, 'adminPrintStyles'));
add_action('wp_enqueue_scripts', array($deliberaThemes, 'publicStyles'), 100);

// inclui arquivos específicos do tema
require_once($deliberaThemes->themeFilePath('functions.php'));
require_once($deliberaThemes->themeFilePath('delibera_comments_template.php'));

/**
 * Usa o template de comentário do Delibera
 * no lugar do padrão do Wordpress para as pautas
 * 
 * @param string $path
 * @return string
 */
function delibera_comments_template($path)
{
    global $deliberaThemes;
    
    if (get_post_type() == 'pauta') {
        return $deliberaThemes->themeFilePath('delibera_comments.php');
    }
    
    return $path;
}
add_filter('comments_template', 'delibera_comments_template');


