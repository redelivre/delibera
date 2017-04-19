<?php

/**
 * Limitando Caracteres
 *
 * Esse arquivo tem como objetivo limitar os caracteres
 * vindos do single e pages
 * 
 * @author Xemele <https://softwarepublico.gov.br/social/xemele/>
 * @date (setembro 2008)
 *
 * @uses  <?php echo limit_chars(get_the_excerpt(), 140); ?>
 * 
 */

function limit_chars($content, $length) {
	$content = strip_tags($content);

	if(strlen($content) > $length) {
		$content = substr($content, 0, $length);
		$content = substr($content, 0, strrpos($content, " "))."...";
	}
	print $content;
}
