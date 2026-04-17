<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$enabled_post_types    = (array) get_option( 'hfs_enabled_post_types', array( 'post', 'page' ) );
$global_header_scripts = get_option( 'hfs_global_header_scripts', '' );
$global_footer_scripts = get_option( 'hfs_global_footer_scripts', '' );
$legacy_fallback       = get_option( 'hfs_legacy_fallback', '1' );

$all_post_types = get_post_types( array( 'public' => true ), 'objects' );
// Remove attachment post type.
unset( $all_post_types['attachment'] );
?>
<div class="hfs-wrap">

	<div class="hfs-page-header">
		<div class="hfs-page-header__icon">
			<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg>
		</div>
		<div>
			<h1 class="hfs-page-header__title"><?php esc_html_e( 'Header Footer Scripts', 'header-footer-scripts' ); ?></h1>
			<p class="hfs-page-header__desc"><?php esc_html_e( 'Inject custom scripts into the &lt;head&gt; or footer globally or per page/post.', 'header-footer-scripts' ); ?></p>
		</div>
	</div>

	<?php settings_errors( 'hfs_settings' ); ?>

	<form method="post" action="options.php">
		<?php settings_fields( 'hfs_settings' ); ?>

		<div class="hfs-grid">

			<!-- Card: Enabled Post Types -->
			<div class="hfs-card">
				<div class="hfs-card-header">
					<h2 class="hfs-card-title"><?php esc_html_e( 'Enabled Post Types', 'header-footer-scripts' ); ?></h2>
					<p class="hfs-card-desc"><?php esc_html_e( 'Select where the per-page Header and Footer Scripts fields will appear.', 'header-footer-scripts' ); ?></p>
				</div>
				<div class="hfs-card-content">
					<?php if ( empty( $all_post_types ) ) : ?>
						<p class="hfs-muted"><?php esc_html_e( 'No public post types found.', 'header-footer-scripts' ); ?></p>
					<?php else : ?>
						<div class="hfs-toggle-list">
							<?php foreach ( $all_post_types as $post_type ) : ?>
								<?php $checked = in_array( $post_type->name, $enabled_post_types, true ); ?>
								<label class="hfs-toggle-row" for="hfs_pt_<?php echo esc_attr( $post_type->name ); ?>">
									<span class="hfs-toggle-info">
										<span class="hfs-toggle-label"><?php echo esc_html( $post_type->labels->singular_name ); ?></span>
										<span class="hfs-badge hfs-badge--muted"><?php echo esc_html( $post_type->name ); ?></span>
									</span>
									<span class="hfs-switch">
										<input
											type="checkbox"
											id="hfs_pt_<?php echo esc_attr( $post_type->name ); ?>"
											name="hfs_enabled_post_types[]"
											value="<?php echo esc_attr( $post_type->name ); ?>"
											<?php checked( $checked ); ?>
										>
										<span class="hfs-switch__track"></span>
										<span class="hfs-switch__thumb"></span>
									</span>
								</label>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
				</div>
			</div>

			<!-- Card: Global Header Scripts -->
			<div class="hfs-card">
				<div class="hfs-card-header">
					<h2 class="hfs-card-title">
						<?php esc_html_e( 'Global Header Scripts', 'header-footer-scripts' ); ?>
						<span class="hfs-badge hfs-badge--blue">&lt;head&gt;</span>
					</h2>
					<p class="hfs-card-desc"><?php esc_html_e( 'Scripts injected into &lt;head&gt; on every page of your site.', 'header-footer-scripts' ); ?></p>
				</div>
				<div class="hfs-card-content">
					<div class="hfs-field">
						<textarea
							id="hfs_global_header_scripts"
							name="hfs_global_header_scripts"
							class="hfs-code-editor"
							rows="8"
							placeholder="<!-- e.g. Google Analytics, GTM, custom meta tags -->"
						><?php echo esc_textarea( $global_header_scripts ); ?></textarea>
					</div>
				</div>
			</div>

			<!-- Card: Global Footer Scripts -->
			<div class="hfs-card">
				<div class="hfs-card-header">
					<h2 class="hfs-card-title">
						<?php esc_html_e( 'Global Footer Scripts', 'header-footer-scripts' ); ?>
						<span class="hfs-badge hfs-badge--purple">&lt;/body&gt;</span>
					</h2>
					<p class="hfs-card-desc"><?php esc_html_e( 'Scripts injected before &lt;/body&gt; on every page of your site.', 'header-footer-scripts' ); ?></p>
				</div>
				<div class="hfs-card-content">
					<div class="hfs-field">
						<textarea
							id="hfs_global_footer_scripts"
							name="hfs_global_footer_scripts"
							class="hfs-code-editor"
							rows="8"
							placeholder="<!-- e.g. chat widgets, conversion pixels -->"
						><?php echo esc_textarea( $global_footer_scripts ); ?></textarea>
					</div>
				</div>
			</div>

			<!-- Card: Legacy Fallback -->
			<div class="hfs-card hfs-card--full">
				<div class="hfs-card-header">
					<h2 class="hfs-card-title">
						<?php esc_html_e( 'Legacy Data Migration', 'header-footer-scripts' ); ?>
						<span class="hfs-badge hfs-badge--muted">_auhfc</span>
					</h2>
					<p class="hfs-card-desc"><?php esc_html_e( 'If your previous plugin stored scripts in the _auhfc post meta, enable this to use that data as a fallback when no HFS scripts are set on a page.', 'header-footer-scripts' ); ?></p>
				</div>
				<div class="hfs-card-content">
					<label class="hfs-toggle-row" for="hfs_legacy_fallback" style="max-width:420px;">
						<span class="hfs-toggle-info">
							<span class="hfs-toggle-label"><?php esc_html_e( 'Enable legacy _auhfc fallback', 'header-footer-scripts' ); ?></span>
						</span>
						<span class="hfs-switch">
							<input
								type="checkbox"
								id="hfs_legacy_fallback"
								name="hfs_legacy_fallback"
								value="1"
								<?php checked( '1', $legacy_fallback ); ?>
							>
							<span class="hfs-switch__track"></span>
							<span class="hfs-switch__thumb"></span>
						</span>
					</label>
					<p class="hfs-muted" style="margin-top:10px;font-size:0.8125rem;">
						<?php esc_html_e( 'When enabled: if a page has no HFS scripts saved, the plugin reads the legacy _auhfc data instead. Your HFS scripts always take priority if both exist. When disabled: only HFS scripts run.', 'header-footer-scripts' ); ?>
					</p>
				</div>
			</div>

		</div><!-- .hfs-grid -->

		<div class="hfs-actions">
			<button type="submit" class="hfs-btn hfs-btn--primary">
				<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
				<?php esc_html_e( 'Save Changes', 'header-footer-scripts' ); ?>
			</button>
		</div>

	</form>
</div>
