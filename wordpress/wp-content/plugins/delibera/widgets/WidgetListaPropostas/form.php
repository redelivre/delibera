<p>
<label for="<?php echo $this->get_field_name( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> 
<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
</p>
<p><label for="<?php echo $this->get_field_id( 'situacao' ); ?>"><?php _e('Pautas em qual situação?', 'delibera'); ?></label>
<select id="<?php echo $this->get_field_id( 'situacao' ); ?>" name="<?php echo $this->get_field_name( 'situacao' ); ?>">
	<?php
		echo "<option value='todas' " . selected( $situacao, 'todas', false ) . ">".__('Todas as Pautas', 'delibera')."</option>";
		foreach (get_terms('situacao') as $term)
			echo "<option value='{$term->slug}' " . selected( $situacao, $term->slug, false ) . ">{$term->name}</option>";
	?>
</select></p>
<p><label for="<?php echo $this->get_field_id( 'items' ); ?>"><?php _e('How many items would you like to display?'); ?></label>
<select id="<?php echo $this->get_field_id( 'items' ); ?>" name="<?php echo $this->get_field_name( 'items' ); ?>">
<?php
		for ( $i = 1; $i <= 20; ++$i )
			echo "<option value='$i' " . selected( $items, $i, false ) . ">$i</option>";
?>
</select></p>
<p>
<label for="<?php echo $this->get_field_id( 'show_summary' ); ?>"><?php _e('Display item content?'); ?></label>
<?php $i = 1; ?>
</p>
<p>
	<label for="<?php echo $this->get_field_id( 'show_summary' )."-".$i; ?>"><input type="radio" id="<?php echo $this->get_field_id( 'show_summary' )."-".$i++; ?>" name="<?php echo $this->get_field_name( 'show_summary' ); ?>" type="checkbox" value="0" <?php if ( $show_summary == 0 ) echo 'checked="checked"'; ?>/>Não</label>
	<label for="<?php echo $this->get_field_id( 'show_summary' )."-".$i; ?>"><input type="radio" id="<?php echo $this->get_field_id( 'show_summary' )."-".$i++; ?>" name="<?php echo $this->get_field_name( 'show_summary' ); ?>" type="checkbox" value="1" <?php if ( $show_summary == 1) echo 'checked="checked"'; ?>/>Resumo</label>
	<label for="<?php echo $this->get_field_id( 'show_summary' )."-".$i; ?>"><input type="radio" id="<?php echo $this->get_field_id( 'show_summary' )."-".$i++; ?>" name="<?php echo $this->get_field_name( 'show_summary' ); ?>" type="checkbox" value="2" <?php if ( $show_summary == 2) echo 'checked="checked"'; ?>/>Tudo</label>
</p>
<p><input id="<?php echo $this->get_field_id( 'show_author' ); ?>" name="<?php echo $this->get_field_name( 'show_author' ); ?>" type="checkbox" value="1" <?php if ( $show_author ) echo 'checked="checked"'; ?>/>
<label for="<?php echo $this->get_field_id( 'show_author' ); ?>"><?php _e('Display item author if available?'); ?></label></p>
<p><input id="<?php echo $this->get_field_id( 'show_date' ); ?>" name="<?php echo $this->get_field_name( 'show_date' ); ?>" type="checkbox" value="1" <?php if ( $show_date ) echo 'checked="checked"'; ?>/>
<label for="<?php echo $this->get_field_id( 'show_date' ); ?>"><?php _e('Display item date?'); ?></label></p>
<p><input id="<?php echo $this->get_field_id( 'show_prazo' ); ?>" name="<?php echo $this->get_field_name( 'show_prazo' ); ?>" type="checkbox" value="1" <?php if ( $show_prazo ) echo 'checked="checked"'; ?>/>
<label for="<?php echo $this->get_field_id( 'show_prazo' ); ?>"><?php _e('Mostrar Prazo?'); ?></label></p>
<p><input id="<?php echo $this->get_field_id( 'show_comment_link' ); ?>" name="<?php echo $this->get_field_name( 'show_comment_link' ); ?>" type="checkbox" value="1" <?php if ( $show_comment_link ) echo 'checked="checked"'; ?>/>
<label for="<?php echo $this->get_field_id( 'show_comment_link' ); ?>"><?php _e('Mostrar link dos comentários?'); ?></label></p>
<p><input id="<?php echo $this->get_field_id( 'show_situacao' ); ?>" name="<?php echo $this->get_field_name( 'show_situacao' ); ?>" type="checkbox" value="1" <?php if ( $show_situacao ) echo 'checked="checked"'; ?>/>
<label for="<?php echo $this->get_field_id( 'show_situacao' ); ?>"><?php _e('Mostrar Situação?'); ?></label></p>
