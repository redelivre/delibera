<?php
/**
 * Inicializa gerenciamento dos temas do plugin.
 */

// PHP 5.3 and later:
namespace Delibera;

class Themes
{
    function __construct()
    {
    	add_filter('archive_template', array($this, 'archiveTemplate'));
    	add_filter('single_template', array($this, 'singleTemplate'));
    	add_action('admin_print_styles', array($this, 'adminPrintStyles'));
    	add_action('wp_enqueue_scripts', array($this, 'publicStyles'), 100);
    	
    	add_filter('comments_template', array($this, 'commentsTemplate'));
    	
    }

    /**
     * Retorna o diretório de um tema.
     * Se não um nome de tema for passado como
     * parâmetro, retorna o diretório do tema atual.
     *
     * @param string $themeName
     * @return string
     */
    public static function getThemeDir($themeName = '')
    {
		return plugin_dir_path(__FILE__).'/themes/generic';
    }

    /**
     * Retorna a URL para o diretório
     * principal do tema atual.
     *
     * @return string
     */
    public static function getThemeUrl()
    {
        return plugin_dir_url(__FILE__).'/themes/generic';
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
    public static function themeFilePath($fileName)
    {
        $filePath = self::getThemeDir() . '/' . $fileName;

        if (file_exists($filePath))
        {
            return $filePath;
        }
        return false;
    }

    /**
     * Retorna a url para um arquivo no tema atual.
     * Se o arquivo não existir, retorna o caminho
     * para o arquivo no tema padrão.
     *
     * @param string $file_name
     * @return string
     */
    public static function themeFileUrl($fileName)
    {
        $filePath = self::getThemeDir() . '/' . $fileName;

        if (file_exists($filePath)) {
            return self::getThemeUrl() . '/' . $fileName;
        }
        return false;
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
        		$archiveTemplate = self::themeFilePath('archive-pauta.php');
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
        		$singleTemplate = self::themeFilePath('single-pauta.php');
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
        		wp_enqueue_style('delibera_style', self::themeFileUrl('delibera_style.css'));
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
        wp_enqueue_style('delibera_admin_style', self::themeFileUrl('delibera_admin.css'));
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
       		load_template(self::themeFilePath('delibera-loop-archive.php'), true);
    	}
    }

    /**
     * Usa o template de comentário do Delibera
     * no lugar do padrão do Wordpress para as pautas
     *
     * @param string $path
     * @return string
     * @package Tema
     */
    function commentsTemplate($path)
    {
    	if (get_post_type() == 'pauta') {
    		return self::themeFilePath('delibera_comments.php');
    	}
    
    	return $path;
    }
    
    public function checkPath($path)
    {
    	if(strpos($path, 'home/hacklab') !== false) // need to remove old hardcode path from config
    	{
    		$theme = basename($path);
    		$path = $this->baseDir . $theme;
    	}
    	return $path;
    }
}
global $DeliberaThemes;
$DeliberaThemes = new \Delibera\Themes();

// inclui arquivos específicos do tema
require_once(\Delibera\Themes::themeFilePath('functions.php'));
require_once(\Delibera\Themes::themeFilePath('delibera_comments_template.php'));

