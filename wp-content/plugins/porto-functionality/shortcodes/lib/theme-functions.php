<?php

if ( ! function_exists( 'porto_get_template_part' ) ) :
	function porto_get_template_part( $slug, $name = null, $args = array() ) {
		if ( empty( $args ) ) {
			return get_template_part( $slug, $name );
		}

		if ( is_array( $args ) ) {
			extract( $args ); // @codingStandardsIgnoreLine
		}

		$templates = array();
		$name      = (string) $name;
		if ( '' !== $name ) {
			$templates[] = "{$slug}-{$name}.php";
		}
		$templates[] = "{$slug}.php";
		$template    = locate_template( $templates );
		$template    = apply_filters( 'porto_get_template_part', $template, $slug, $name );

		if ( $template ) {
			include $template;
		}
	}
endif;
