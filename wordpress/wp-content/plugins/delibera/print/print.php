<?php
/*
+----------------------------------------------------------------+
|																							|
|	WordPress 2.7 Plugin: WP-Print 2.50										|
|	Copyright (c) 2008 Lester "GaMerZ" Chan									|
|																							|
|	File Written By:																	|
|	- Lester "GaMerZ" Chan															|
|	- http://lesterchan.net															|
|																							|
|	File Information:																	|
|	- Process Printing Page															|
|	- wp-content/plugins/wp-print/print.php									|
|																							|
+----------------------------------------------------------------+
*/


### Variables
$links_text = '';

### Actions
add_action('init', 'print_content');

### Filters
add_filter('wp_title', 'print_pagetitle');
add_filter('comments_template', 'print_template_comments');
//add_filter('comments_array', 'delibera_print_comments');
remove_filter('comments_array', 'delibera_get_comments_filter');

define('PRINT', true);

global $withcomments;
$withcomments = true;

### Load Print Post/Page Template
if(file_exists(TEMPLATEPATH.'/print-posts.php')) {
	include(TEMPLATEPATH.'/print-posts.php');
} else {
	include(WP_PLUGIN_DIR.'/delibera/print/print-posts.php');
}
?>