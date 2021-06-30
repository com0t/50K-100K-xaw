<?php

/**
 * Welcome page class.
 */
class SPU_Welcome {

	/**
	 * Primary class constructor.
	 *
	 * @since 2.0.0
	 */
	public function __construct() {

		add_action( 'admin_menu', [ $this, 'register' ] );
		add_action( 'admin_head', [ $this, 'hide_menu' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'scripts' ] );
		add_action( 'admin_init', [ $this, 'redirect' ], 9999 );
		add_action( 'admin_print_scripts', [ $this, 'wppopups_admin_hide_unrelated_notices' ] );
		if( get_post_type() == 'spucpt' || ( isset($_GET['post_type']) && $_GET['post_type'] == 'spucpt') ) {
			add_action( 'admin_notices', [ $this, 'popups_small_notice' ] );
		}
	}

	public function scripts(){
		if ( isset($_GET['page']) && $_GET['page'] == 'spu-getting-started' ){
			wp_enqueue_style( 'spu-admin-css', SPU_PLUGIN_URL .'admin/assets/css/admin.css', '', SocialPopup::VERSION );
		}
	}

	public static function popups_small_notice(){
		?><div class="notice-info error is-dismiseable">
		<h3><i class=" dashicons-before dashicons-share-alt"></i>Popups plugin is discontinued</h3>
		<p>Popups plugin is now discontinued and only security updates will be released.</p>
		<p>Please migrate to <a href="<?= admin_url( 'index.php?page=spu-getting-started' );?>">WP Popups</a>, the most powerful and easy to use popup maker plugin. You will be able to import legacy popups with a few clicks.</p>

		</div><?php
	}
	/**
	 * Register the pages to be used for the Welcome screen (and tabs).
	 *
	 * These pages will be removed from the Dashboard menu, so they will
	 * not actually show. Sneaky, sneaky.
	 *
	 * @since 2.0.0
	 */
	public function register() {

		// Getting started - shows after installation.
		add_dashboard_page(
			esc_html__( 'Popups is discontinued', 'wppopups' ),
			esc_html__( 'Popups is discontinued', 'popups' ),
			apply_filters( 'spu_welcome_cap', 'manage_options' ),
			'spu-getting-started',
			[ $this, 'output' ]
		);
	}

	/**
	 * Removed the dashboard pages from the admin menu.
	 *
	 * This means the pages are still available to us, but hidden.
	 *
	 * @since 2.0.0
	 */
	public function hide_menu() {
		remove_submenu_page( 'index.php', 'spu-getting-started' );
	}

	/**
	 * Welcome screen redirect.
	 *
	 * This function checks if a new install or update has just occurred. If so,
	 * then we redirect the user to the appropriate page.
	 *
	 * @since 2.0.0
	 */
	public function redirect() {
		// Check option to disable welcome redirect.
		if ( ( isset($_GET['page']) && $_GET['page'] == 'spu-getting-started' ) || get_option( 'spu_activation_redirect', false ) ) {
			return;
		}

		// Only do this for single site installs.
		if ( is_network_admin() || isset( $_GET['activate-multi'] ) ) {
			return;
		}


		// Initial install.
		wp_safe_redirect( admin_url( 'index.php?page=spu-getting-started' ) );

	}

	/**
	 * Hide unrelated notices in popups
	 */
	function wppopups_admin_hide_unrelated_notices() {

		// Bail if we're not on a WP Popups screen or page.
		if ( ( ! isset($_GET['post_type']) || $_GET['post_type'] == 'spucpt') && get_post_type() !== 'spucpt' && ( empty( $_GET['page'] ) || ( strpos( $_REQUEST['page'], 'spu' ) === false && strpos( $_REQUEST['page'], 'popups' ) === false ) ) ) {
			return;
		}

		global $wp_filter;

		if ( ! empty( $wp_filter['user_admin_notices']->callbacks ) && is_array( $wp_filter['user_admin_notices']->callbacks ) ) {
			foreach ( $wp_filter['user_admin_notices']->callbacks as $priority => $hooks ) {
				foreach ( $hooks as $name => $arr ) {
					if ( is_object( $arr['function'] ) && $arr['function'] instanceof Closure ) {
						unset( $wp_filter['user_admin_notices']->callbacks[ $priority ][ $name ] );
						continue;
					}
					if ( ! empty( $arr['function'][0] ) && is_object( $arr['function'][0] ) && strpos( strtolower( get_class( $arr['function'][0] ) ), 'popups' ) !== false ) {
						continue;
					}
					if ( ! empty( $name ) && strpos( $name, 'popups' ) === false ) {
						unset( $wp_filter['user_admin_notices']->callbacks[ $priority ][ $name ] );
					}
				}
			}
		}

		if ( ! empty( $wp_filter['admin_notices']->callbacks ) && is_array( $wp_filter['admin_notices']->callbacks ) ) {
			foreach ( $wp_filter['admin_notices']->callbacks as $priority => $hooks ) {
				foreach ( $hooks as $name => $arr ) {
					if ( is_object( $arr['function'] ) && $arr['function'] instanceof Closure ) {
						unset( $wp_filter['admin_notices']->callbacks[ $priority ][ $name ] );
						continue;
					}
					if ( ! empty( $arr['function'][0] ) && is_object( $arr['function'][0] ) && strpos( strtolower( get_class( $arr['function'][0] ) ), 'popups' ) !== false ) {
						continue;
					}
					if ( ! empty( $name ) && strpos( $name, 'popups' ) === false ) {
						unset( $wp_filter['admin_notices']->callbacks[ $priority ][ $name ] );
					}
				}
			}
		}

		if ( ! empty( $wp_filter['all_admin_notices']->callbacks ) && is_array( $wp_filter['all_admin_notices']->callbacks ) ) {
			foreach ( $wp_filter['all_admin_notices']->callbacks as $priority => $hooks ) {
				foreach ( $hooks as $name => $arr ) {
					if ( is_object( $arr['function'] ) && $arr['function'] instanceof Closure ) {
						unset( $wp_filter['all_admin_notices']->callbacks[ $priority ][ $name ] );
						continue;
					}
					if ( ! empty( $arr['function'][0] ) && is_object( $arr['function'][0] ) && strpos( strtolower( get_class( $arr['function'][0] ) ), 'popups' ) !== false ) {
						continue;
					}
					if ( ! empty( $name ) && strpos( $name, 'popups' ) === false ) {
						unset( $wp_filter['all_admin_notices']->callbacks[ $priority ][ $name ] );
					}
				}
			}
		}
	}


	/**
	 * Getting Started screen. Shows after first install.
	 *
	 * @since 2.0.0
	 */
	public function output() {

?>
		<div id="wppopups-welcome" class="">

			<div class="container">

				<div class="intro">

					<div class="wppopups-logo-welcome" style="background-image: url(<?php echo 'data:image/svg+xml;base64,' . base64_encode( '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 153 151" width="153" height="151"><defs><image width="151" height="151" id="img1" href="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAJcAAACXCAMAAAAvQTlLAAAAAXNSR0IB2cksfwAAAMBQTFRFAHOqAHOqAHOqAAAAAHOqAHOqAHOqAHOqAHOqAHOqAHOqAHOqAHOqAHOqAHOqAHOqAHOqAHOqAHOqAHOqAHOqAHOqAHOqAHOqAHOqAHOqAHOqAHOqAHOqAHOqAHOqAHOqAHOqL22LZmZmZmZmZmZmZmZmZmZmZmZmZmZmZmZmZmZmZmZmZmZmZmZmZmZmZmZmZmZmZmZmZmZmZmZmZmZmZmZmZmZmZmZmZmZmZmZmZmZmZmZmZmZmZmZmZmZmZmZm1Y1mKQAAAEB0Uk5TbIBMANj/mOk17gwj1BfhIOPdFtca5SbsMPAp5yQoMhzfcIA+aP989OsjhdQR0RLXGd/ZFRjY1h3cGx7lqLfAXWj80ucAAAE5SURBVHic7djJTgJBFEbhhouAOLSCMjiCeFVUhgbEWd7/rViYKC7ohKRM/5pz9rfy5S6qkoqiXFblLaWosJFVRVy4cOHChetPukqirvKmpquyta3psp1dTZfFe5oui/c1XVatabrs4FDTZfWGpsuaLU2XHR1ruuzkF2AhXHYa/kkK4rKz4BsL47LztqbL4sD/E52L1XXXcIXu0ld3hQsXLly4cOHChQsXLly4cOHChQsXLly4cOHChQsXLly4cOHChQsXLly4cOHChQsXLly4cOHChQsXLly4cP0rV9rkdcrk+t30fp5+K+Lyu3tNl/cHmi7vL29MyOXDkabLk2+YlMvHE02XD6eari+YmssfJpounw00XZ/XhaDLk0dNlz89a7o8edF0+eubpis1XLhw4cKFy+z9I6vmC+sAc2Bjpu3VAAAAAElFTkSuQmCC"/></defs><style>tspan { white-space:pre }</style><use id="Layer 1" href="#img1" x="1" y="0" /></svg>' );?>
		) !important;">
					</div>

					<div class="block">
						<h1><?php esc_html_e( 'Popups plugin it\'s being discontinued but..', 'popups' ); ?></h1>
						<h6><?php _e( 'It\'s being replaced for the most powerful and intuitive plugin called', 'popups' ); ?></h6>
						<h1 style="margin: 15px 0 0 0;font-size: 29px;"> <strong>WP Popups</strong></h1>
					</div>

					<iframe style="margin: 0 auto;display: block;" width="560" height="315" src="https://www.youtube.com/embed/_yJ-xHVOQYw" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>

					<div class="block">

						<h6><?php esc_html_e( 'WP Popups was coded to be the most powerful Popup builder. Once installed you can migrate your legacy popups easily with a few clicks.', 'popups' ); ?></h6>

						<div class="button-wrap wppopups-clear">
							<div class="left">
								<a href="https://wordpress.org/plugins/wp-popups-lite/"
								   class="wppopups-btn wppopups-btn-block wppopups-btn-lg wppopups-btn-blue">
									<?php esc_html_e( 'Download WP Popups', 'popups' ); ?>
								</a>
							</div>
							<div class="right">
								<a href="https://wppopups.com/docs/how-to-import-legacy-popups-from-old-plugin/?utm_source=WordPress&amp;utm_medium=link&amp;utm_campaign=legacy-upgrade-page"
								   class="wppopups-btn wppopups-btn-block wppopups-btn-lg wppopups-btn-grey"
								   target="_blank" rel="noopener noreferrer">
									<?php esc_html_e( 'IMPORT LEGACY POPUPS', 'popups' ); ?>
								</a>
							</div>
						</div>

						<p style="text-align: center;margin-top: 30px;">Popups plugin will only receive security updates for a year and after that it will be discontinued. Please migrate to WP Popups as soon as possible.</p>

					</div>

				</div><!-- /.intro -->

				<div class="features">

					<div class="block">

						<h1><?php esc_html_e( 'WP Popups', 'popups' ); ?></h1>
						<h6><?php esc_html_e( 'WP Popups is the best multipurpose popup maker plugin for WordPress.', 'popups' ); ?></h6>

						<div class="feature-list wppopups-clear">

							<div class="feature-block first">
								<img src="<?php echo WPPOPUPS_PLUGIN_URL; ?>assets/images/icons/custom-templates.svg">
								<h5><?php esc_html_e( 'Template Builder', 'popups' ); ?></h5>
								<p><?php esc_html_e( 'Use a prebuilt template or create your own. Easily export them to use it in all your sites!', 'popups' ); ?></p>
							</div>

							<div class="feature-block last">
								<img src="<?php echo WPPOPUPS_PLUGIN_URL; ?>assets/images/icons/display-rules.svg">
								<h5><?php esc_html_e( '30+ Display rules', 'popups' ); ?></h5>
								<p><?php esc_html_e( 'Trigger popup based on multiple rules. There is no other Popup plugin with the same flexibility!', 'popups' ); ?></p>
							</div>

							<div class="feature-block first">
								<img src="<?php echo WPPOPUPS_PLUGIN_URL; ?>assets/images/icons/animations.svg">
								<h5><?php esc_html_e( '45+ Animations', 'popups' ); ?></h5>
								<p><?php esc_html_e( 'Animate your popup with some magic to capture your users attention.', 'popups' ); ?></p>
							</div>

							<div class="feature-block last">
								<img src="<?php echo WPPOPUPS_PLUGIN_URL; ?>assets/images/icons/triggers.svg">
								<h5><?php esc_html_e( 'Multiple triggers', 'popups' ); ?></h5>
								<p><?php esc_html_e( 'Show popup by using one more triggers combined,positions.svg like when user leaves the page, after X seconds, etc.', 'popups' ); ?></p>
							</div>

						</div>

						<h1><?php esc_html_e( 'Premium features', 'popups' ); ?></h1>

						<h6><?php esc_html_e( 'Upgrade to WP Popups to unlock all the magic!', 'popups' ); ?></h6>

						<div class="feature-list wppopups-clear">

							<div class="feature-block first">
								<img src="<?php echo WPPOPUPS_PLUGIN_URL; ?>assets/images/icons/ab-test.svg">
								<h5><?php esc_html_e( 'A/B Testing', 'popups' ); ?></h5>
								<p><?php esc_html_e( 'Create different versions, measure results and choose the best popup for your campaign.', 'popups' ); ?></p>
							</div>

							<div class="feature-block last">
								<img src="<?php echo WPPOPUPS_PLUGIN_URL; ?>assets/images/icons/analytics.svg">
								<h5><?php esc_html_e( 'Analytics', 'popups' ); ?></h5>
								<p><?php esc_html_e( 'Track conversions and impressions of your popups and integrate it with Google Analytics.', 'popups' ); ?></p>
							</div>

							<div class="feature-block first">
								<img src="<?php echo WPPOPUPS_PLUGIN_URL; ?>assets/images/icons/email-marketing.svg">
								<h5><?php esc_html_e( 'Email Marketing', 'popups' ); ?></h5>
								<p><?php esc_html_e( 'Integrates with all the popular email providers. Capture leads easily!', 'popups' ); ?></p>
							</div>

							<div class="feature-block last">
								<img src="<?php echo WPPOPUPS_PLUGIN_URL; ?>assets/images/icons/geolocation.svg">
								<h5><?php esc_html_e( 'Geolocation Addon', 'popups' ); ?></h5>
								<p><?php esc_html_e( 'With the geolocation addon you can show the popup just to geotargeted users of your choice.', 'popups' ); ?></p>
							</div>

							<div class="feature-block first">
								<img src="<?php echo WPPOPUPS_PLUGIN_URL; ?>assets/images/icons/age-verification.svg">
								<h5><?php esc_html_e( 'Age Verification Addon', 'popups' ); ?></h5>
								<p><?php esc_html_e( 'Create an Age Verification Popup to ask for user\'s age before seeing content.', 'popups' ); ?></p>
							</div>

							<div class="feature-block last">
								<img src="<?php echo WPPOPUPS_PLUGIN_URL; ?>assets/images/icons/idle-logout.svg">
								<h5><?php esc_html_e( 'Idle Logout Addon', 'popups' ); ?></h5>
								<p><?php esc_html_e( 'Log out your users after inactivity time, but give them a chance to continue logged by showing a popup.', 'popups' ); ?></p>
							</div>

							<div class="feature-block first">
								<img src="<?php echo WPPOPUPS_PLUGIN_URL; ?>assets/images/icons/leaving-notice.svg">
								<h5><?php esc_html_e( 'Leaving Notice Addon', 'popups' ); ?></h5>
								<p><?php esc_html_e( 'Show a warning to users when they click on external links before they leave your site.', 'popups' ); ?></p>
							</div>

							<div class="feature-block last">
								<img src="<?php echo WPPOPUPS_PLUGIN_URL; ?>assets/images/icons/login-registration.svg">
								<h5><?php esc_html_e( 'AJAX Login/Registration Addon', 'popups' ); ?></h5>
								<p><?php esc_html_e( 'Convert your popup into a login and registration form powered by ajax.', 'popups' ); ?></p>
							</div>
						</div>

						<div class="button-wrap">
							<a href="https://wppopups.com/features/?utm_source=WordPress&amp;utm_medium=link&amp;utm_campaign=liteplugin"
							   class="wppopups-btn wppopups-btn-lg wppopups-btn-grey" rel="noopener noreferrer"
							   target="_blank">
								<?php esc_html_e( 'See All Features', 'popups' ); ?>
							</a>
						</div>

					</div>

				</div>

					<div class="upgrade-cta upgrade">

					<div class="block wppopups-clear upgrade-welcome-cta">

						<div class="left">
							<h2><?php esc_html_e( 'Upgrade to PRO', 'popups' ); ?></h2>
							<ul>
								<li>
									<span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'Analytics', 'popups' ); ?>
								</li>
								<li>
									<span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'Exit intent', 'popups' ); ?>
								</li>
								<li>
									<span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'Optin templates', 'popups' ); ?>
								</li>
								<li>
									<span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'A/B Testing', 'popups' ); ?>
								</li>
								<li>
									<span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'Email Marketing', 'popups' ); ?>
								</li>
								<li>
									<span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'More Animations', 'popups' ); ?>
								</li>
								<li>
									<span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'More positions', 'popups' ); ?>
								</li>
								<li>
									<span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'Geolocation', 'popups' ); ?>
								</li>
								<li>
									<span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'More triggers', 'popups' ); ?>
								</li>
								<li>
									<span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'Multiple addons', 'popups' ); ?>
								</li>
							</ul>
						</div>

						<div class="right">
							<a href="https://wppopups.com/pricing/?utm_source=WordPress&amp;utm_medium=link&amp;utm_campaign=legacy-upgrade-page" rel="noopener noreferrer"
							   target="_blank"
							   class="wppopups-btn wppopups-btn-block wppopups-btn-lg wppopups-btn-blue wppopups-upgrade-modal">
								<?php esc_html_e( 'Upgrade Now', 'popups' ); ?>
							</a>
						</div>

					</div>

				</div>

			</div><!-- /.container -->

		</div><!-- /#wppopups-welcome -->
		<?php
		update_option('spu_activation_redirect',true);
	}
}

new SPU_Welcome();
