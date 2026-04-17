<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$header_scripts       = get_post_meta( $post->ID, '_hfs_header_scripts', true );
$footer_scripts       = get_post_meta( $post->ID, '_hfs_footer_scripts', true );
$override_yoast       = get_post_meta( $post->ID, '_hfs_override_yoast_canonical', true );
$yoast_active         = class_exists( 'WPSEO_Options' );
?>
<div class="hfs-meta-box">

	<p class="hfs-meta-box__desc">
		<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
		<?php esc_html_e( 'Scripts added here load only on this page. Use the plugin settings to add scripts globally.', 'header-footer-scripts' ); ?>
	</p>

	<?php wp_nonce_field( 'hfs_save_meta_box_' . $post->ID, 'hfs_meta_box_nonce' ); ?>

	<div class="hfs-meta-fields">

		<div class="hfs-meta-field">
			<label class="hfs-meta-label" for="hfs_header_scripts">
				<?php esc_html_e( 'Header Scripts', 'header-footer-scripts' ); ?>
				<span class="hfs-badge hfs-badge--blue">&lt;head&gt;</span>
			</label>
			<textarea
				id="hfs_header_scripts"
				name="hfs_header_scripts"
				class="hfs-code-editor hfs-code-editor--meta"
				rows="5"
				placeholder="<!-- Scripts injected into <head> on this page only -->"
			><?php echo esc_textarea( $header_scripts ); ?></textarea>
		</div>

		<div class="hfs-meta-field">
			<label class="hfs-meta-label" for="hfs_footer_scripts">
				<?php esc_html_e( 'Footer Scripts', 'header-footer-scripts' ); ?>
				<span class="hfs-badge hfs-badge--purple">&lt;/body&gt;</span>
			</label>
			<textarea
				id="hfs_footer_scripts"
				name="hfs_footer_scripts"
				class="hfs-code-editor hfs-code-editor--meta"
				rows="5"
				placeholder="<!-- Scripts injected before </body> on this page only -->"
			><?php echo esc_textarea( $footer_scripts ); ?></textarea>
		</div>

	</div>

	<?php if ( $yoast_active ) : ?>
	<div class="hfs-meta-yoast">
		<label class="hfs-toggle-row hfs-toggle-row--compact" for="hfs_override_yoast_canonical">
			<span class="hfs-toggle-info">
				<span class="hfs-toggle-label">
					<?php esc_html_e( 'Override Yoast SEO canonical for this page', 'header-footer-scripts' ); ?>
					<span class="hfs-badge hfs-badge--green"><?php esc_html_e( 'Yoast SEO', 'header-footer-scripts' ); ?></span>
				</span>
			</span>
			<span class="hfs-switch">
				<input
					type="checkbox"
					id="hfs_override_yoast_canonical"
					name="hfs_override_yoast_canonical"
					value="1"
					<?php checked( '1', $override_yoast ); ?>
				>
				<span class="hfs-switch__track"></span>
				<span class="hfs-switch__thumb"></span>
			</span>
		</label>
		<p class="hfs-muted" style="font-size:0.75rem;margin:4px 12px 0;">
			<?php esc_html_e( 'When checked and your header scripts contain a canonical tag, Yoast\'s canonical is suppressed on this page only.', 'header-footer-scripts' ); ?>
		</p>
	</div>
	<?php endif; ?>

</div>
