<?php
/**
 * WordPress TOS Class
 *
 * @package    WordPress
 * @subpackage TOS
 * @since      3.4.0
 */

namespace RRZE\Tos {

	defined( 'ABSPATH' ) || exit;

	/**
	 * Class Settings
	 *
	 * @property string res
	 * @package RRZE\Tos
	 */
	class Settings {
		/**
		 * Main class.
		 *
		 * @var Main
		 */
		protected $main;

		/**
		 * Name of the option object.
		 *
		 * @var string
		 */
		protected $option_name;

		/**
		 * All option defined.
		 *
		 * @var array
		 */
		protected $options;

		/**
		 * "Screen ID" der Einstellungsseite.
		 *
		 * @var array
		 */
		protected $admin_settings_page;

		/**
		 * Settings constructor.
		 *
		 * @param Main $main
		 */
		public function __construct( Main $main ) {
			$this->main        = $main;
			$this->option_name = $this->main->options->get_option_name();
			$this->options     = $this->main->options->get_options();
			include_once ABSPATH . 'wp-admin/includes/plugin.php';

			$status_code = check_wmp();

			if ( 200 === $status_code ) {
				$this->res = get_json_wmp();
			} else {
				$this->res = '';
			}

			add_action( 'wp_ajax_tos_update_fields', [ $this, 'tos_update_ajax_handler' ] );
			add_action( 'admin_notices', [ $this, 'my_error_notice' ] );
		}

		/**
		 * Define the page names for templates & links in footer section.
		 *
		 * @return array
		 */
		public static function options_pages() {
			$pages = [
				'imprint'       => __( 'imprint', 'rrze-tos' ),
				'privacy'       => __( 'privacy', 'rrze-tos' ),
				'accessibility' => __( 'accessibility', 'rrze-tos' ),
			];

			return $pages;
		}

		/**
		 * Define names for tabs in the backend settings page.
		 *
		 * @return array
		 */
		public static function options_page_tabs() {
			$tabs['responsible'] = __( 'responsible', 'rrze-tos' );
			return $tabs + self::options_pages();
		}

		/**
		 * Check the current tab from get.
		 *
		 * @param array $get_vars Values from the URL.
		 *
		 * @return int|null|string
		 */
		private static function current_tab( $get_vars ) {
			$tabs = self::options_page_tabs();
			if ( isset( $get_vars['tab'] ) ) {
				$current = $get_vars['tab'];
			} else {
				reset( $tabs );
				$current = key( $tabs );
			}

			return $current;
		}

		/**
		 * Füge eine Optionsseite in das Menü "Einstellungen" hinzu.
		 *
		 * @return void
		 */
		public function admin_settings_page() {
			$this->admin_settings_page = add_options_page( __( 'ToS', 'rrze-tos' ),
				__( 'ToS', 'rrze-tos' ),
				'manage_options', $this->option_name,
				[
					$this,
					'settings_page',
				] );
			add_action( 'load-' . $this->admin_settings_page, [ $this, 'admin_help_menu' ] );
		}

		/**
		 * Die Ausgabe der Optionsseite.
		 *
		 * @return void
		 */
		public function settings_page() {
			global $wp_settings_sections;

			$tabs    = self::options_page_tabs();
			$current = self::current_tab( $_GET );
			$nonce   = wp_create_nonce( 'my-nonce' );
			?>
			<h2><?php esc_html_e( 'Settings &rsaquo; ToS', 'rrze-tos' ); ?></h2>
				<h3 class="nav-tab-wrapper">
					<?php
					// Add tabs to settings page.
					foreach ( $tabs as $tab => $name ) {
						$name  = ucfirst( $name );
						$class = ( $tab === $current ) ? 'nav-tab-active' : '';
						echo "<a class='nav-tab $class' href='?page=$this->option_name&tab=$tab&_wpnonce={$nonce}'>$name</a>";
					}
					?>
				</h3>
				<form method="post" action="options.php" id="tos-admin-form">
					<?php settings_fields( 'rrze_tos_options' ); ?>
					<?php do_settings_sections( 'rrze_tos_options' ); ?>
					<?php submit_button(); ?>
				</form>

<!--			<div class="wrap">-->
<!--				<div id="tabs">-->
<!--					<ul>-->
<!--						<li><a href="#tabs-1">Nunc tincidunt</a></li>-->
<!--						<li><a href="#tabs-2">Proin dolor</a></li>-->
<!--						<li><a href="#tabs-3">Aenean lacinia</a></li>-->
<!--					</ul>-->
<!--					</h3>-->
<!--					<form method="post" action="options.php" id="tos-admin-form">-->
<!--						<div id="tabs-1">-->
<!--							--><?php //settings_fields( 'rrze_tos_options' ); ?>
<!--							--><?php //do_settings_sections( 'rrze_tos_options' ); ?>
<!--							--><?php //do_settings_fields( 'rrze_tos_options', 'rrze_tos_section_general' ); ?>
<!--							--><?php //submit_button(); ?>
<!--						</div>-->
<!--						<div id="tabs-2">-->
<!--							<p>Morbi tincidunt, dui sit amet facilisis feugiat, odio metus gravida ante, ut pharetra massa metus id nunc.-->
<!--								Duis scelerisque molestie turpis. Sed fringilla, massa eget luctus malesuada, metus eros molestie lectus, ut tempus eros massa ut dolor.-->
<!--								Aenean aliquet fringilla sem. Suspendisse sed ligula in ligula suscipit aliquam. Praesent in eros vestibulum mi adipiscing adipiscing.-->
<!--								Morbi facilisis. Curabitur ornare consequat nunc. Aenean vel metus. Ut posuere viverra nulla. Aliquam erat volutpat. Pellentesque convallis.-->
<!--								Maecenas feugiat, tellus pellentesque pretium posuere, felis lorem euismod felis, eu ornare leo nisi vel felis.-->
<!--								Mauris consectetur tortor et purus.</p>-->
<!--						</div>-->
<!--					</form>-->
<!--				</div>-->
<!--			</div>-->

			<?php
		}

		/**
		 * Legt die Einstellungen der Optionsseite fest.
		 *
		 * @return void
		 */
		public function admin_settings() {
			register_setting( 'rrze_tos_options', $this->option_name, array( $this, 'options_validate' ) );
			$tab = 'accessibility';
			if ( isset( $_GET ) ) {
				$tab = self::current_tab( $_GET );
			}

			switch ( $tab ) {
				case 'accessibility' :
				default:
					// --------
					// Section General
					// --------
					add_settings_section( 'rrze_tos_section_general',
						__( 'General', 'rrze-tos' ), '__return_false',
						'rrze_tos_options' );

					add_settings_field(
						'rrze_tos_conformity',
						__( 'Are the conformity conditions of the WCAG 2.0 AA fulfilled?',
							'rrze-tos' ),
						[
							$this,
							'rrze_tos_radio_callback',
						],
						'rrze_tos_options',
						'rrze_tos_section_general',
						[
							'name'    => 'rrze_tos_conformity',
							'options' =>
								[
									'1' => __( 'Yes', 'rrze-tos' ),
									'2' => __( 'No', 'rrze-tos' ),
								],
						]
					);
					add_settings_field( 'rrze_tos_no_reason',
						__( 'If not, with what reason', 'rrze-tos' ),
						[
							$this,
							'rrze_tos_textarea_callback',
						],
						'rrze_tos_options',
						'rrze_tos_section_general',
						[
							'name'        => 'rrze_tos_no_reason',
							'description' => __( 'Please include all necessary details', 'rrze-tos' ),
						]
					);

					// --------
					// Section E-Mail Settings
					// --------
					add_settings_section( 'rrze_tos_section_email',
						__( 'E-Mail Settings', 'rrze-tos' ), '__return_false', 'rrze_tos_options' );

					add_settings_field(
						'rrze_tos_receiver_email',
						__( 'Receiver E-Mail', 'rrze-tos' ), [ $this, 'rrze_tos_textbox_callback'
					],
						'rrze_tos_options',
						'rrze_tos_section_email',
						[
							'name'         => 'rrze_tos_receiver_email',
							'autocomplete' => 'email',
							'required'     => 'required',
						]
					);
					add_settings_field(
						'rrze_tos_subject', __( 'Subject', 'rrze-tos' ),
						[ $this, 'rrze_tos_textbox_callback' ],
						'rrze_tos_options',
						'rrze_tos_section_email',
						[
							'name'     => 'rrze_tos_subject',
							'required' => 'required',
						]
					);
					add_settings_field(
						'rrze_tos_cc_email', __( 'CC', 'rrze-tos' ),
						[ $this, 'rrze_tos_textbox_callback' ],
						'rrze_tos_options',
						'rrze_tos_section_email',
						[
							'name'         => 'rrze_tos_cc_email',
							'autocomplete' => 'email',
						]
					);

					break;
				// --------
				// Tab imprint
				// --------
				case 'imprint':
					// --------
					// Section Editor
					// --------
					add_settings_section( 'rrze_tos_section_websites', __( 'Websites', 'rrze-tos' ), '__return_false', 'rrze_tos_options' );
					add_settings_field( 'rrze_tos_url_list',
						__( 'Websites', 'rrze-tos' ),
						[
							$this,
							'rrze_tos_textarea_callback',
						],
						'rrze_tos_options',
						'rrze_tos_section_websites',
						[
							'name'        => 'rrze_tos_url_list',
							'description' => __( 'Please include one website url per line', 'rrze-tos' ),
						]
					);
//					add_settings_field(
//						'rrze_tos_editor_name', __( 'Name', 'rrze-tos' ),
//						[ $this, 'rrze_tos_textbox_callback' ],
//						'rrze_tos_options',
//						'rrze_tos_section_editor',
//						[
//							'name'        => 'rrze_tos_editor_name',
//							'description' => __( 'Full name of the editor',
//								'rrze-tos' ),
//							'required'    => 'required',
//						]
//					);
//					add_settings_field(
//						'rrze_tos_editor_street', __( 'Street', 'rrze-tos' ),
//						[ $this, 'rrze_tos_textbox_callback' ],
//						'rrze_tos_options',
//						'rrze_tos_section_editor',
//						[
//							'name'        => 'rrze_tos_editor_street',
//							'description' => __( 'Street Number', 'rrze-tos' ),
//							'required'    => 'required',
//						]
//					);
//					add_settings_field(
//						'rrze_tos_editor_postalcode',
//						__( 'Postcode', 'rrze-tos' ), [
//						$this,
//						'rrze_tos_textbox_callback'
//					],
//						'rrze_tos_options',
//						'rrze_tos_section_editor',
//						[
//							'name'        => 'rrze_tos_editor_postalcode',
//							'description' => __( 'Postcode', 'rrze-tos' ),
//							'required'    => 'required',
//						]
//					);
//					add_settings_field(
//						'rrze_tos_editor_city', __( 'Place', 'rrze-tos' ),
//						[ $this, 'rrze_tos_textbox_callback' ],
//						'rrze_tos_options',
//						'rrze_tos_section_editor',
//						[
//							'name'        => 'rrze_tos_editor_city',
//							'description' => __( 'City', 'rrze-tos' ),
//							'required'    => 'required',
//						]
//					);
//					add_settings_field(
//						'rrze_tos_editor_org', __( 'Faculty, institution or chair', 'rrze-tos' ),
//						[ $this, 'rrze_tos_textbox_callback' ],
//						'rrze_tos_options',
//						'rrze_tos_section_editor',
//						[
//							'name'     => 'rrze_tos_editor_org',
//							'required' => 'required',
//						]
//					);

					break;
				// --------
				// Tab data_protection
				// --------
				case 'privacy':
					// --------
					// Section Content
					// --------
					add_settings_section( 'rrze_tos_section_privacy',
						__( 'Newsletter', 'rrze-tos' ), '__return_false',
						'rrze_tos_options' );
					add_settings_field(
						'rrze_tos_protection_newsletter',
						__( 'Do you want to show the newsletter section?',
							'rrze-tos' ),
						[ $this, 'rrze_tos_radio_callback' ],
						'rrze_tos_options',
						'rrze_tos_section_privacy',
						[
							'name'    => 'rrze_tos_protection_newsletter',
							'options' =>
								[
									'1' => __( 'Yes', 'rrze-tos' ),
									'2' => __( 'No', 'rrze-tos' ),
								],
						]
					);

					add_settings_section( 'rrze_tos_section_extra', __( 'New section', 'rrze-tos' ), '__return_false', 'rrze_tos_options' );
					add_settings_field( 'rrze_tos_protectio_new_section', __( 'Type all text you want to include', 'rrze-tos' ),
						[
							$this,
							'rrze_tos_editor_callback',
						],
						'rrze_tos_options',
						'rrze_tos_section_extra',
						[
							'name'        => 'rrze_tos_protection_new_section',
							'description' => __( 'Type all text you want to include.', 'rrze-tos' ),
						]
					);
					break;
				case 'responsible':

					// --------
					// Section Responsible
					// --------
					add_settings_section( 'rrze_tos_section_responsible',
						__( 'Responsible', 'rrze-tos' ), '__return_false',
						'rrze_tos_options' );

					add_settings_field( 'rrze_tos_update_fields', __( 'Update all fields', 'rrze-tos' ),
						[ $this, 'rrze_tos_update_callback' ],
						'rrze_tos_options',
						'rrze_tos_section_responsible'
					);

					add_settings_field(
						'rrze_tos_responsible_name', __( 'Name', 'rrze-tos' ),
						[
							$this,
							'rrze_tos_textbox_callback',
						],
						'rrze_tos_options',
						'rrze_tos_section_responsible',
						[
							'name'         => 'rrze_tos_responsible_name',
							'autocomplete' => 'given-name',
							'required'     => 'required',
						]
					);
					add_settings_field(
						'rrze_tos_responsible_email',
						__( 'E-Mail', 'rrze-tos' ), [
						$this,
						'rrze_tos_textbox_callback'
					],
						'rrze_tos_options',
						'rrze_tos_section_responsible',
						[
							'name'         => 'rrze_tos_responsible_email',
							'autocomplete' => 'email',
							'required'     => 'required',
						]
					);
					add_settings_field(
						'rrze_tos_responsible_street',
						__( 'Street', 'rrze-tos' ), [
						$this,
						'rrze_tos_textbox_callback'
					],
						'rrze_tos_options',
						'rrze_tos_section_responsible',
						[
							'name'         => 'rrze_tos_responsible_street',
							'autocomplete' => 'address-line1',
							'required'     => 'required',
						]
					);
					add_settings_field(
						'rrze_tos_responsible_postalcode',
						__( 'Postcode', 'rrze-tos' ), [
						$this,
						'rrze_tos_textbox_callback'
					],
						'rrze_tos_options',
						'rrze_tos_section_responsible',
						[
							'name'     => 'rrze_tos_responsible_postalcode',
							'required' => 'required',
						]
					);
					add_settings_field(
						'rrze_tos_responsible_city', __( 'City', 'rrze-tos' ),
						[ $this, 'rrze_tos_textbox_callback' ],
						'rrze_tos_options',
						'rrze_tos_section_responsible',
						[
							'name'         => 'rrze_tos_responsible_city',
							'autocomplete' => 'address-level2',
							'required'     => 'required',
						]
					);
					add_settings_field(
						'rrze_tos_responsible_phone', __( 'Phone', 'rrze-tos' ),
						[ $this, 'rrze_tos_textbox_callback' ],
						'rrze_tos_options',
						'rrze_tos_section_responsible',
						[
							'name'         => 'rrze_tos_responsible_phone',
							'autocomplete' => 'tel',
						]
					);
					add_settings_field(
						'rrze_tos_responsible_org', __( 'Organization', 'rrze-tos' ),
						[ $this, 'rrze_tos_textbox_callback' ],
						'rrze_tos_options',
						'rrze_tos_section_responsible',
						[
							'name'         => 'rrze_tos_responsible_org'
						]
					);
					if ( is_plugin_active( 'fau-person/fau-person.php' ) ) {
						add_settings_field(
							'rrze_tos_responsible_ID',
							__( 'Person-ID', 'rrze-tos' ), [
							$this,
							'rrze_tos_textbox_callback'
						],
							'rrze_tos_options',
							'rrze_tos_section_responsible',
							[ 'name' => 'rrze_tos_responsible_ID' ]
						);
					}


					// --------
					// Section Webmaster
					// --------
					add_settings_section( 'rrze_tos_section_webmaster', __( 'Webmaster', 'rrze-tos' ), '__return_false', 'rrze_tos_options' );

					add_settings_field(
						'rrze_tos_webmaster_name', __( 'Name', 'rrze-tos' ), [
						$this,
						'rrze_tos_textbox_callback'
					],
						'rrze_tos_options',
						'rrze_tos_section_webmaster',
						[ 'name' => 'rrze_tos_webmaster_name' ]
					);
					add_settings_field(
						'rrze_tos_webmaster_street', __( 'Street', 'rrze-tos' ), [ $this, 'rrze_tos_textbox_callback' ],
						'rrze_tos_options',
						'rrze_tos_section_webmaster',
						[ 'name' => 'rrze_tos_webmaster_street' ]
					);
					add_settings_field(
						'rrze_tos_webmaster_city', __( 'City', 'rrze-tos' ), [ $this, 'rrze_tos_textbox_callback' ],
						'rrze_tos_options',
						'rrze_tos_section_webmaster',
						[ 'name' => 'rrze_tos_webmaster_city' ]
					);
					add_settings_field(
						'rrze_tos_webmaster_phone', __( 'Phone', 'rrze-tos' ), [ $this, 'rrze_tos_textbox_callback' ],
						'rrze_tos_options',
						'rrze_tos_section_webmaster',
						[ 'name' => 'rrze_tos_webmaster_phone', 'description' => __( 'Direct dialing', 'rrze-tos' ) ]
					);
					add_settings_field(
						'rrze_tos_webmaster_fax', __( 'Fax', 'rrze-tos' ),
						[ $this, 'rrze_tos_textbox_callback' ],
						'rrze_tos_options',
						'rrze_tos_section_webmaster',
						[
							'name'        => 'rrze_tos_webmaster_fax',
							'description' => __( 'Fax number, if still available', 'rrze-tos' ),
						]
					);
					add_settings_field(
						'rrze_tos_webmaster_email', __( 'E-Mail', 'rrze-tos' ), [ $this, 'rrze_tos_textbox_callback' ],
						'rrze_tos_options',
						'rrze_tos_section_webmaster',
						[ 'name' => 'rrze_tos_webmaster_email' ]
					);
					add_settings_field(
						'rrze_tos_webmaster_org', __( 'Organization', 'rrze-tos' ), [ $this, 'rrze_tos_textbox_callback' ],
						'rrze_tos_options',
						'rrze_tos_section_webmaster',
						[ 'name' => 'rrze_tos_webmaster_org' ]
					);
					if ( is_plugin_active( 'fau-person/fau-person.php' ) ) {
						add_settings_field(
							'rrze_tos_webmaster_ID', __( 'Person-ID', 'rrze-tos' ), [
							$this,
							'rrze_tos_textbox_callback'
						],
							'rrze_tos_options',
							'rrze_tos_section_webmaster',
							[ 'name' => 'rrze_tos_webmaster_ID' ]
						);
					}
//
//
//					// --------
//					// Section Content
//					// --------
//					add_settings_section( 'rrze_tos_section_content',
//						__( 'Content Manager', 'rrze-tos' ), '__return_false',
//						'rrze_tos_options' );
//					add_settings_field(
//						'rrze_tos_content_name', __( 'Name', 'rrze-tos' ),
//						[ $this, 'rrze_tos_textbox_callback' ],
//						'rrze_tos_options',
//						'rrze_tos_section_content',
//						[
//							'name'        => 'rrze_tos_content_name',
//							'description' => __( 'Full name of the content manager',
//								'rrze-tos' ),
//							'required'    => 'required',
//						]
//					);
//					add_settings_field(
//						'rrze_tos_content_email', __( 'E-mail', 'rrze-tos' ),
//						[ $this, 'rrze_tos_textbox_callback' ],
//						'rrze_tos_options',
//						'rrze_tos_section_content',
//						[
//							'name' => 'rrze_tos_content_email',
//						]
//					);
//					add_settings_field(
//						'rrze_tos_content_street', __( 'Street', 'rrze-tos' ),
//						[ $this, 'rrze_tos_textbox_callback' ],
//						'rrze_tos_options',
//						'rrze_tos_section_content',
//						[
//							'name'        => 'rrze_tos_content_street',
//							'description' => __( 'Street Number', 'rrze-tos' ),
//							'required'    => 'required',
//						]
//					);
//					add_settings_field(
//						'rrze_tos_content_postalcode',
//						__( 'Postcode', 'rrze-tos' ), [
//						$this,
//						'rrze_tos_textbox_callback'
//					],
//						'rrze_tos_options',
//						'rrze_tos_section_content',
//						[
//							'name'     => 'rrze_tos_content_postalcode',
//							'required' => 'required',
//						]
//					);
//					add_settings_field(
//						'rrze_tos_content_city', __( 'City', 'rrze-tos' ),
//						[ $this, 'rrze_tos_textbox_callback' ],
//						'rrze_tos_options',
//						'rrze_tos_section_content',
//						[
//							'name'     => 'rrze_tos_content_city',
//							'required' => 'required',
//						]
//					);
//					add_settings_field(
//						'rrze_tos_content_phone', __( 'Phone', 'rrze-tos' ),
//						[ $this, 'rrze_tos_textbox_callback' ],
//						'rrze_tos_options',
//						'rrze_tos_section_content',
//						[
//							'name'        => 'rrze_tos_content_phone',
//							'description' => __( 'Direct dialing', 'rrze-tos' ),
//						]
//					);
//					add_settings_field(
//						'rrze_tos_content_fax', __( 'Fax', 'rrze-tos' ),
//						[ $this, 'rrze_tos_textbox_callback' ],
//						'rrze_tos_options',
//						'rrze_tos_section_content',
//						[
//							'name'        => 'rrze_tos_content_fax',
//							'description' => __( 'Fax number, if still available',
//								'rrze-tos' ),
//						]
//					);
//					add_settings_field(
//						'rrze_tos_content_org', __( 'Faculty, institution or chair', 'rrze-tos' ),
//						[ $this, 'rrze_tos_textbox_callback' ],
//						'rrze_tos_options',
//						'rrze_tos_section_content',
//						[
//							'name'     => 'rrze_tos_content_org',
//							'required' => 'required',
//						]
//					);

					break;
			}

		}

		public function rrze_tos_editor_callback($args){
			if ( array_key_exists( 'name', $args ) ) {
				$editor_id = esc_attr( $args['name'] );
			}
			$content = '';
			if ( array_key_exists( $editor_id, $this->options ) ) {
				$content = $this->options->$editor_id;
			}


			$settings = [
				'media_buttons' => false,
				'textarea_name' => $this->option_name . '[' . $editor_id . ']',
			];

//			TODO:check for do_shortcode();
			wp_editor( $content, $editor_id, $settings );
		}


		public function my_error_notice() {
			?>
			<div class="notice invisible" id="ajax-response">
				<p><?php _e( 'There has been an error. Bummer!',
						'my_plugin_textdomain' ); ?></p>
			</div>
			<?php
		}

		/**
		 * Validiert die Eingabe der Optionsseite.
		 *
		 * @param array $input Each of the options to be validated.
		 *
		 * @return array
		 */
		public function options_validate( $input ) {
			if ( isset( $input ) ) {
				foreach ( $input as $key => $value ) {
					if ( preg_match( '/email/i', $key ) ) {
						$this->options->$key
							= isset( $_POST[ $this->option_name ][ $key ] )
							? sanitize_email( $_POST[ $this->option_name ][ $key ] )
							: $this->options->$key;
					} elseif ( $key != 'rrze_tos_protection_new_section' && preg_match( '/[\r\n\t ]+/', $value ) ) {
						$this->options->$key = isset( $_POST[ $this->option_name ][ $key ] )
							? sanitize_textarea_field( $_POST[ $this->option_name ][ $key ] ) : $this->options->$key;
					} elseif ( $key == 'rrze_tos_protection_new_section' ) {
						$this->options->$key = isset( $_POST[ $this->option_name ][ $key ] )
							? wp_kses_post( $_POST[ $this->option_name ][ $key ] ) : $this->options->$key;
					} else {
						$this->options->$key = isset( $_POST[ $this->option_name ][ $key ] )
							? sanitize_text_field( $_POST[ $this->option_name ][ $key ] )
							: $this->options->$key;
					}
				}
			}

			return $this->options;
		}

		/**
		 * General callback function for text input.
		 *
		 * @param array $args It contains name and description of the text field.
		 */
		public function rrze_tos_textbox_callback( $args ) {
			if ( array_key_exists( 'name', $args ) ) {
				$name = esc_attr( $args['name'] );
			}
			if ( array_key_exists( 'description', $args ) ) {
				$description = esc_attr( $args['description'] );
			}
			if ( array_key_exists( 'autocomplete', $args ) ) {
				$autocomplete = esc_attr( $args['autocomplete'] );
			}
			if ( array_key_exists( 'required', $args ) ) {
				$required = esc_attr( $args['required'] );
			}

			?>
			<?php if ( isset( $name ) ) { ?>
				<input <?php if ( isset( $required ) ) {
					echo $required;
				} ?> size="50" name="<?php printf( '%s[' . $name . ']',
					$this->option_name ); ?>" type='text'
				     title="<?php echo __( 'If the field has no data, please fill it manually',
					     'rrze-tos' ); ?>"

				     value="<?php if ( array_key_exists( $name,
					     $this->options ) ) {
					     echo $this->options->$name;
				     } ?>"
					<?php if ( isset( $autocomplete ) ) { ?>
						autocomplete="<?php echo $autocomplete; ?>"
					<?php } ?>
				>
				<br/>
				<?php if ( isset( $description ) ) { ?>
					<span
						class="description"><?php esc_html_e( $description ); ?></span>
					<?php
				}
			}
		}

		/**
		 * General callback function for text area input.
		 *
		 * @param array $args It contains name and description of the text area field.
		 */
		public function rrze_tos_textarea_callback( $args ) {
			if ( array_key_exists( 'name', $args ) ) {
				$name = esc_attr( $args['name'] );
			}
			if ( array_key_exists( 'description', $args ) ) {
				$description = esc_attr( $args['description'] );
			}
			?>
			<?php if ( isset( $name ) ) { ?>
				<textarea name="<?php printf( '%s[' . $name . ']', $this->option_name ); ?>" cols="50" rows="8"
				          title="<?php echo __( 'If the field has no data, please fill it manually',
					          'rrze-tos' ); ?>">
<?php
if ( array_key_exists( $name, $this->options ) ) {
	if ( is_array( $this->options->$name ) && count( $this->options->$name ) > 0
	     && $this->options->$name[0] !== '' ) {
		echo implode( "\n", $this->options->$name );
	} else {
		echo $this->options->$name;
	}
}
?>
</textarea><br/>
			<?php } ?>
			<?php if ( isset( $description ) ) { ?>
				<span
					class="description"><?php esc_html_e( $description ); ?></span>
				<?php
			}
		}

		/**
		 * General callback function for radio input.
		 *
		 * @param array $args All options only one can be selected.
		 */
		public function rrze_tos_radio_callback( $args ) {
			$radios = [];
			if ( array_key_exists( 'name', $args ) ) {
				$name = esc_attr( $args['name'] );
			}
			if ( array_key_exists( 'description', $args ) ) {
				$description = esc_attr( $args['description'] );
			}
			if ( array_key_exists( 'options', $args ) ) {
				$radios = $args['options'];
			}
			if ( isset( $name ) ) {
				foreach ( $radios as $_k => $_v ) {
					?>
					<label>
						<input name="<?php printf( '%s[' . $name . ']',
							$this->option_name ); ?>"
						       type='radio'
						       value='<?php print $_k; ?>'
							<?php
							if ( array_key_exists( $name, $this->options ) ) {
								checked( $this->options->$name, $_k );
							}
							?>
						>
						<?php print $_v; ?>
					</label><br/>
					<?php
				}
			}
			if ( isset( $description ) ) {
				?>
				<p class="description"><?php esc_html_e( $description ); ?></p>
				<?php
			}
		}

		/**
		 * General callback function for Select input.
		 *
		 * @param array $args All options than can be selected.
		 */
		public function rrze_tos_select_callback( $args ) {
			$limit = [];
			if ( array_key_exists( 'name', $args ) ) {
				$name = esc_attr( $args['name'] );
			}
			if ( array_key_exists( 'description', $args ) ) {
				$description = esc_attr( $args['description'] );
			}
			if ( array_key_exists( 'options', $args ) ) {
				$limit = $args['options'];
			} ?>
			<?php if ( isset( $name ) ) { ?>
				<select name="<?php printf( '%s[' . $name . ']',
					$this->option_name ); ?>"
				        title="<?php __( 'Please select one', 'rrze-tos' ) ?>">
					<?php foreach ( $limit as $_k => $_v ) { ?>
						<option value='<?php print $_k; ?>'
							<?php if ( array_key_exists( $name,
								$this->options ) ) {
								selected( $this->options->$name, $_k );
							} ?>>
							<?php print $_v; ?>
						</option>
					<?php } ?>
				</select>
			<?php } ?>
			<?php
			if ( isset( $description ) ) { ?>
				<p class="description"><?php esc_html_e( $description ); ?></p>
				<?php
			}
		}

		/**
		 * Create a button for update option object.
		 */
		public function rrze_tos_update_callback() {
			?>
<!--			<input type="button" class="spinner-demo button button-primary" name="update"-->
<!--			       value="--><?php //_e( 'Checking info from Web Master Portal', 'rrze-tos' ); ?><!--" id="update">-->

			<button class=" button button-primary " name="update" id="update">
				<span class=""><?php _e( 'Update info from Web Master Portal (WMP)', 'rrze-tos' ); ?></span>
			</button>
			<?php
		}

		/**
		 * Take the option object and update fields using ajax request from the web master portal RRZE.
		 *
		 */
		public function tos_update_ajax_handler() {

			$status_code = check_wmp();
			if ( 200 === $status_code ) {
				$wmp_option = get_json_wmp();

				foreach ( $wmp_option['verantwortlich'] as $wmp_key => $wmp_value ) {
					if ( ! is_null( $wmp_value ) ) {
						$options_key1                 = "rrze_tos_responsible_$wmp_key";
//						$options_key2                 = "rrze_tos_editor_$wmp_key";
						$this->options->$options_key1 = $wmp_value;
//						$this->options->$options_key2 = $wmp_value;
					}
				}
				foreach ( $wmp_option['webmaster'] as $wmp_key => $wmp_value ) {
					if ( ! is_null( $wmp_value ) ) {
						$options_key                 = "rrze_tos_webmaster_$wmp_key";
						$this->options->$options_key = $wmp_value;
					}
				}

				update_option( 'rrze_tos', $this->options, true );
				$wmp_option['success'] = __( 'All fields were updated!', 'rrze-tos' );
				echo json_encode( $wmp_option );
			}else{
				echo header('HTTP/1.0 404 Not Found');
				_e( 'Can not connect to the server', 'rrze-tos' );
			}

			wp_die();
		}

		/**
		 * Erstellt die Kontexthilfe der Optionsseite.
		 *
		 * @return void
		 */
		public function admin_help_menu() {

			$content = array(
				'<p>' . __( 'Here comes the Context Help content.', 'rrze-tos' )
				. '</p>',
			);


			$help_tab = array(
				'id'      => $this->admin_settings_page,
				'title'   => __( 'Overview', 'rrze-tos' ),
				'content' => implode( PHP_EOL, $content ),
			);

			$help_sidebar
				= sprintf( '<p><strong>%1$s:</strong></p><p><a href="http://blogs.fau.de/webworking">RRZE-Webworking</a></p><p><a href="https://github.com/RRZE-Webteam">%2$s</a></p>',
				__( 'For more information', 'rrze-tos' ),
				__( 'RRZE Webteam on Github', 'rrze-tos' ) );

			$screen = get_current_screen();

			if ( $screen->id != $this->admin_settings_page ) {
				return;
			}

			$screen->add_help_tab( $help_tab );

			$screen->set_help_sidebar( $help_sidebar );
		}
	}
}
