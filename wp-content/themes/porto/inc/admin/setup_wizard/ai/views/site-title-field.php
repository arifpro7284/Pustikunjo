<?php
/**
 * Website Title field + AI generate trigger — right column of the logo/title row
 * on the Demo Content step. Rendered via the porto_ai_after_logo_section hook.
 *
 * @package Porto
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="porto-title-col porto-ai-title-field">
	<h4><?php esc_html_e( 'Website Title:', 'porto' ); ?></h4>
	<div class="porto-field-card">
		<input type="text" id="porto-ai-site-title" class="porto-field-input"
			value="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>"
			placeholder="<?php esc_attr_e( 'Your website title', 'porto' ); ?>" />
		<div class="porto-ai-title-actions">
			<button type="button" class="btn btn-dark btn-sm porto-ai-title-save"><?php esc_html_e( 'Save', 'porto' ); ?></button>
			<button type="button" class="btn btn-primary btn-sm porto-ai-title-generate" data-porto-ai-title-open>
				<span aria-hidden="true">✨</span> <?php esc_html_e( 'Generate', 'porto' ); ?>
			</button>
			<span class="porto-ai-title-msg" aria-live="polite"></span>
		</div>
		<p class="porto-field-hint"><i class="fas fa-info-circle"></i> <?php esc_html_e( 'Type a title, or generate ideas with AI, then Save.', 'porto' ); ?></p>
	</div>
</div>