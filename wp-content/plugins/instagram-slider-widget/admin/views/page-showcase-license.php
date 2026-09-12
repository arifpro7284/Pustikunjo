<div class="wrap">
    <div class="factory-bootstrap-483 factory-fontawesome-000">
		<?php wp_nonce_field( 'shopifeed_check_license' ); ?>
        <div id="wis-license-wrapper"
             data-loader="<?php echo esc_url( WIS_PLUGIN_URL . '/admin/assets/img/loader.gif' ); ?>"
             data-plugin="<?php echo esc_attr( get_class( $this->plugin ) ); ?>">

            <div class="factory-bootstrap-483 onp-page-wrap <?php echo esc_attr( $this->get_license_type() ); ?>-license-manager-content"
                 id="license-manager">
                <div>
                    <h3><?php printf( __( 'Activate %s', 'instagram-slider-widget' ), esc_html( $this->plan_name ) ); ?></h3>
					<?php echo esc_html( $this->get_plan_description() ); ?>
                </div>
                <br>

                <div class="onp-container">
                    <div class="license-details">
						<?php if ( 'free' == $this->get_license_type() ): ?>
                            <a href="<?php echo esc_url( $this->plugin->get_support()->get_pricing_url( true, 'license_page' ) ); ?>"
                               class="purchase-premium" target="_blank" rel="noopener">
                            <span class="btn btn-gold btn-inner-wrap">
                            <?php printf( __( 'Upgrade to Premium', 'instagram-slider-widget' ), esc_html( $this->premium->get_price() ) ); ?>
                            </span>
                            </a>
                            <p><?php printf( __( 'Your current license for %1$s:', 'instagram-slider-widget' ), esc_html( $this->plugin->getPluginTitle() ) ); ?></p>
						<?php endif; ?>
                        <div class="license-details-block <?php echo esc_attr( $this->get_license_type() ); ?>-details-block">
							<?php if ( $this->is_premium ): ?>
                                <a data-action="deactivate" href="#"
                                   class="btn btn-default btn-small license-delete-button wis-control-btn shopifeed-control-btn">
									<?php _e( 'Delete Key', 'instagram-slider-widget' ); ?>
                                </a>
                                <a data-action="sync" href="#"
                                   class="btn btn-default btn-small license-synchronization-button wis-control-btn shopifeed-control-btn">
									<?php _e( 'Synchronization', 'instagram-slider-widget' ); ?>
                                </a>
							<?php endif; ?>
                            <h3>
								<?php echo esc_html( ucfirst( $this->get_plan() ) ); ?>

								<?php if ( $this->is_premium && $this->premium_has_subscription ): ?>
                                    <span style="font-size: 15px;">
                                    (<?php printf( __( 'Automatic renewal, every %s', '' ), esc_html( $this->get_billing_cycle_readable() ) ); ?>
                                                )
                                </span>
								<?php endif; ?>
                            </h3>
							<?php if ( $this->is_premium ): ?>
                                <div class="license-key-identity">
                                    <code><?php echo esc_html( $this->get_hidden_license_key() ); ?></code>
                                </div>
							<?php endif; ?>
                            <div class="license-key-description">
                                <p><?php _e( 'Public License is a GPLv2 compatible license allowing you to change and use this version of the plugin for free. Please keep in mind this license covers only free edition of the plugin. Premium versions are distributed with other type of a license.', 'instagram-slider-widget' ); ?>
                                </p>
								<?php if ( $this->is_premium && $this->premium_has_subscription ): ?>
                                    <p class="activate-trial-hint">
										<?php _e( 'You have an active update subscription. To cancel your subscription, click <a data-action="unsubscribe" class="wis-control-btn" href="#">cancel subscription</a>', 'instagram-slider-widget' ); ?>
                                    </p>
								<?php endif; ?>

								<?php if ( 'trial' == $this->get_license_type() ): ?>
                                    <p class="activate-error-hint">
										<?php printf( __( 'Your license has expired, please extend the license to get updates and support.', 'instagram-slider-widget' ) ); ?>
                                    </p>
								<?php endif; ?>
                            </div>
                            <table class="license-params" colspacing="0" colpadding="0">
                                <tr>
                                    <!--<td class="license-param license-param-domain">
										<span class="license-value"><?php echo esc_attr( $_SERVER['SERVER_NAME'] ); ?></span>
										<span class="license-value-name"><?php _e( 'domain', 'instagram-slider-widget' ); ?></span>
									</td>-->
                                    <td class="license-param license-param-days">
                                        <span class="license-value"><?php echo esc_html( $this->get_plan() ); ?></span>
                                        <span class="license-value-name"><?php _e( 'plan', 'instagram-slider-widget' ); ?></span>
                                    </td>
									<?php if ( $this->is_premium ) : ?>
                                        <td class="license-param license-param-sites">
                                        <span class="license-value">
                                            <?php echo esc_html( $this->premium_license->get_count_active_sites() ); ?>
                                            <?php _e( 'of', 'instagram-slider-widget' ); ?>
                                            <?php echo esc_html( $this->premium_license->get_sites_quota() ); ?></span>
                                            <span class="license-value-name"><?php _e( 'active sites', 'instagram-slider-widget' ); ?></span>
                                        </td>
									<?php endif; ?>
                                    <td class="license-param license-param-version">
                                        <span class="license-value"><?php echo esc_html( $this->plugin->getPluginVersion() ); ?></span>
                                        <span class="license-value-name"><span><?php _e( 'version', 'instagram-slider-widget' ); ?></span></span>
                                    </td>
									<?php if ( $this->is_premium ): ?>
                                        <td class="license-param license-param-days">
											<?php if ( 'trial' == $this->get_license_type() ): ?>
                                                <span class="license-value"><?php _e( 'EXPIRED!', 'instagram-slider-widget' ); ?></span>
                                                <span class="license-value-name"><?php _e( 'please update the key', 'instagram-slider-widget' ); ?></span>
											<?php else: ?>
                                                <span class="license-value">
													<?php
													if ( $this->premium_license->is_lifetime() ) {
														echo 'infiniate';
													} else {
														echo esc_html( $this->get_expiration_days() );
													}
													?>
                                                            <small> <?php _e( 'day(s)', 'instagram-slider-widget' ); ?></small>
                                             </span>
                                                <span class="license-value-name"><?php _e( 'remained', 'instagram-slider-widget' ); ?></span>
											<?php endif; ?>
                                        </td>
									<?php endif; ?>
                                </tr>
                            </table>
                        </div>
                    </div>
                    <div class="license-input">
                        <form action="" method="post">
							<?php if ( $this->is_premium ): ?>
                        <p><?php _e( 'Have a key to activate the premium version? Paste it here:', 'instagram-slider-widget' ); ?><p>
						<?php else: ?>
                            <p><?php _e( 'Have a key to activate the plugin? Paste it here:', 'instagram-slider-widget' ); ?>
                            <p>
								<?php endif; ?>
                                <button data-action="activate"
                                        class="btn btn-default wis-control-btn shopifeed-control-btn"
                                        type="button"
                                        id="license-submit">
									<?php _e( 'Activate License Key', 'instagram-slider-widget' ); ?>
                                </button>
                            <div class="license-key-wrap">
                                <input type="text" id="license-key" name="licensekey" value=""
                                       class="form-control"/>
                            </div>
							<?php if ( $this->is_premium ): ?>
                                <p style="margin-top: 10px;">
									<?php printf( __( '<a href="%s" target="_blank" rel="noopener">Learn more</a> about the premium version and get the license key to activate it now!', 'instagram-slider-widget' ), esc_url( $this->plugin->get_support()->get_pricing_url( true, 'license_page' ) ) ); ?>
                                </p>
							<?php else: ?>
                                <p style="margin-top: 10px;">
									<?php printf( __( 'Can’t find your key? Go to <a href="%s" target="_blank" rel="noopener">this page</a> and login using the e-mail address associated with your purchase.', 'instagram-slider-widget' ), esc_url( "https://users.freemius.com/" ) ); ?>
                                </p>
							<?php endif; ?>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>