<?php

/**
* PAGINACAO
* 
* 
* @author Kaleb Martins <kaleb@paradoxodigital.com.br>
* @date (setembro 2015)
* 
*/

function paginacao($pages = '', $range = 2) {
    $showitems = ($range * 2) + 1;

    global $paged;
    if (empty($paged))
        $paged = 1;

    if ($pages == '') {
        global $wp_query;
        $pages = $wp_query->max_num_pages;
        if (!$pages) {
            $pages = 1;
        }
    }

    // Na tela
    if (1 != $pages) {
        echo "<div class='paginacao'>";

        if ($paged > 1)
            echo "<a class='anterior' href='" . get_pagenum_link(1) . "'>Anterior</a>";
        
        if ($paged > 2 && $paged > $range + 1 && $showitems < $pages)
            echo "<a href='" . get_pagenum_link(1) . "'>&laquo;</a>";
        if ($paged > 1 && $showitems < $pages)
            echo "<a href='" . get_pagenum_link($paged - 1) . "'>&lsaquo;</a>";

        for ($i = 1; $i <= $pages; $i++) {
            if (1 != $pages && (!($i >= $paged + $range + 1 || $i <= $paged - $range - 1) || $pages <= $showitems )) {
                echo ($paged == $i) ? "<span class='current'>" . $i . "</span>" : "<a href='" . get_pagenum_link($i) . "' class='inactive' >" . $i . "</a>";
            }
        }

        if ($paged < $pages && $showitems < $pages)
            echo "<a href='" . get_pagenum_link($paged + 1) . "'>&rsaquo;</a>";
        if ($paged < $pages - 1 && $paged + $range - 1 < $pages && $showitems < $pages)
            echo "<a href='" . get_pagenum_link($pages) . "'>&raquo;</a>";
        
        if ($paged < $pages)
            echo "<a class='proxima' href='" . get_pagenum_link($pages) . "'>Próxima</a>";

        echo "</div>\n";
    }
}