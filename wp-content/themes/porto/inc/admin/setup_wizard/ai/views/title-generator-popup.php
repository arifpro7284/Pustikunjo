<?php
/**
 * Website Title Generator — Magnific inline popup.
 *
 * @package Porto
 */

defined( 'ABSPATH' ) || exit;
?>
<div id="porto-ai-title-popup" class="porto-ai-title-popup mfp-hide" role="dialog" aria-label="<?php esc_attr_e( 'Generate Website Title', 'porto' ); ?>">
	<div class="porto-ai-title-popup__header">
		<span class="porto-ai-title-popup__title"><span aria-hidden="true">✨</span> <?php esc_html_e( 'Generate Website Title', 'porto' ); ?></span>
		<button type="button" class="porto-ai-title-popup__close" data-porto-ai-title-close aria-label="<?php esc_attr_e( 'Close', 'porto' ); ?>">&times;</button>
	</div>

	<form class="porto-ai-title-popup__form" id="porto-ai-title-form">
		<label>
			<span><?php esc_html_e( 'Business type', 'porto' ); ?></span>
			<select id="porto-ai-title-type">
				<option value="ecommerce"><?php esc_html_e( 'Ecommerce', 'porto' ); ?></option>
				<option value="business"><?php esc_html_e( 'Business', 'porto' ); ?></option>
				<option value="portfolio"><?php esc_html_e( 'Portfolio', 'porto' ); ?></option>
				<option value="agency"><?php esc_html_e( 'Agency', 'porto' ); ?></option>
				<option value="blog"><?php esc_html_e( 'Blog', 'porto' ); ?></option>
				<option value="restaurant"><?php esc_html_e( 'Restaurant', 'porto' ); ?></option>
			</select>
		</label>

		<label>
			<span><?php esc_html_e( 'Keywords', 'porto' ); ?></span>
			<input type="text" id="porto-ai-title-keywords" placeholder="<?php esc_attr_e( 'e.g. furniture, modern, premium', 'porto' ); ?>" />
			<small class="porto-ai-title-hint"><?php esc_html_e( 'Industry + descriptive words, comma-separated.', 'porto' ); ?></small>
		</label>

		<button type="submit" class="btn btn-primary porto-ai-title-run"><?php esc_html_e( 'Generate 10 titles', 'porto' ); ?></button>
	</form>

	<div class="porto-ai-title-popup__results" id="porto-ai-title-results"></div>
</div>