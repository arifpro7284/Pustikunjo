<?php
/**
 * AI Demo Assistant — floating trigger + Magnific inline panel.
 *
 * @package Porto
 */

defined( 'ABSPATH' ) || exit;
?>
<button type="button" id="porto-ai-fab" class="porto-ai-fab" data-porto-ai-open aria-label="<?php esc_attr_e( 'Open Porto AI Assistant', 'porto' ); ?>">
	<span class="porto-ai-fab__spark" aria-hidden="true">✨</span>
	<span class="porto-ai-fab__label"><?php esc_html_e( 'AI Help', 'porto' ); ?></span>
</button>

<div id="porto-ai-panel" class="porto-ai-panel mfp-hide" role="dialog" aria-label="<?php esc_attr_e( 'Porto AI Assistant', 'porto' ); ?>">
	<div class="porto-ai-panel__header">
		<span class="porto-ai-panel__title"><span aria-hidden="true">✨</span> <?php esc_html_e( 'Porto AI Assistant', 'porto' ); ?></span>
		<button type="button" class="porto-ai-panel__close" data-porto-ai-close aria-label="<?php esc_attr_e( 'Close', 'porto' ); ?>">&times;</button>
	</div>

	<div class="porto-ai-panel__messages" id="porto-ai-messages" aria-live="polite">
		<div class="porto-ai-msg porto-ai-msg--bot"><?php esc_html_e( 'Tell me what kind of website you want to build and I’ll recommend the best Porto demos for it.', 'porto' ); ?></div>
	</div>

	<div class="porto-ai-panel__chips" id="porto-ai-chips"></div>

	<form class="porto-ai-panel__form" id="porto-ai-form">
		<textarea id="porto-ai-input" rows="2" placeholder="<?php esc_attr_e( 'e.g. I need an online furniture store', 'porto' ); ?>"></textarea>
		<button type="submit" class="btn btn-primary porto-ai-send"><?php esc_html_e( 'Send', 'porto' ); ?></button>
	</form>

	<p class="porto-ai-panel__note"><?php esc_html_e( 'Your message is sent to an AI service to generate recommendations.', 'porto' ); ?></p>
</div>