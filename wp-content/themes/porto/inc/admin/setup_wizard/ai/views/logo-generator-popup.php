<?php
/**
 * AI Logo Generator — Magnific inline popup (docked bottom-right).
 *
 * @package Porto
 */

defined( 'ABSPATH' ) || exit;
?>
<div id="porto-ai-logo-popup" class="porto-ai-title-popup porto-ai-logo-popup mfp-hide" role="dialog" aria-label="<?php esc_attr_e( 'Generate Logo', 'porto' ); ?>">
	<div class="porto-ai-title-popup__header">
		<span class="porto-ai-title-popup__title"><span aria-hidden="true">✨</span> <?php esc_html_e( 'Generate Logo', 'porto' ); ?></span>
		<button type="button" class="porto-ai-title-popup__close" data-porto-ai-logo-close aria-label="<?php esc_attr_e( 'Close', 'porto' ); ?>">&times;</button>
	</div>

	<form class="porto-ai-title-popup__form" id="porto-ai-logo-form">
		<label>
			<span><?php esc_html_e( 'Business name', 'porto' ); ?></span>
			<input type="text" id="porto-ai-logo-name" value="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" placeholder="<?php esc_attr_e( 'Your brand name', 'porto' ); ?>" />
		</label>

		<label>
			<span><?php esc_html_e( 'Industry / keywords', 'porto' ); ?></span>
			<input type="text" id="porto-ai-logo-keywords" placeholder="<?php esc_attr_e( 'e.g. furniture, modern, premium', 'porto' ); ?>" />
		</label>

		<label>
			<span><?php esc_html_e( 'Style', 'porto' ); ?></span>
			<select id="porto-ai-logo-style">
				<option value="modern, minimal"><?php esc_html_e( 'Modern / Minimal', 'porto' ); ?></option>
				<option value="elegant, luxury"><?php esc_html_e( 'Elegant / Luxury', 'porto' ); ?></option>
				<option value="bold, playful"><?php esc_html_e( 'Bold / Playful', 'porto' ); ?></option>
				<option value="corporate, professional"><?php esc_html_e( 'Corporate / Professional', 'porto' ); ?></option>
				<option value="vintage, retro"><?php esc_html_e( 'Vintage / Retro', 'porto' ); ?></option>
				<option value="hand-drawn, organic"><?php esc_html_e( 'Hand-drawn / Organic', 'porto' ); ?></option>
			</select>
		</label>

		<button type="submit" class="btn btn-primary porto-ai-logo-run"><?php esc_html_e( 'Generate logo', 'porto' ); ?></button>
	</form>

	<div class="porto-ai-logo-results" id="porto-ai-logo-results"></div>

	<p class="porto-ai-logo-note"><?php esc_html_e( 'Generation can take ~15 seconds. When you pick a logo it is saved to your Media Library and set as your site logo.', 'porto' ); ?></p>
</div>