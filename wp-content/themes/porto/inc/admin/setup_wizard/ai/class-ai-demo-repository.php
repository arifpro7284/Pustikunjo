<?php
/**
 * Porto AI — Demo Repository.
 *
 * Single source of truth for demo metadata consumed by the AI Demo Import
 * Assistant. It does NOT redefine the demo list: the canonical list lives in
 * Porto_Theme_Setup_Wizard::porto_demo_types(). This class:
 *
 *   1. DERIVES structured metadata (name, builders, categories, woocommerce,
 *      features, thumbnail, preview URL) from the existing `filter` tokens and
 *      `plugins` arrays each demo already carries — zero AI needed.
 *   2. MERGES a thin AI-enriched OVERLAY (data/demos.json: industry, style,
 *      colors, keywords, richer description) on top, keyed by demo id.
 *
 * A demo with no overlay entry still works — it simply exposes its derived
 * fields and empty enrichment. New demos added to porto_demo_types() therefore
 * never break the assistant.
 *
 * The derivation helpers (derive(), feature_map(), … ) are PURE and WP-free so
 * the build-time generator (tools/generate-demos-json.php) and the WP runtime
 * share ONE implementation.
 *
 * @package Porto
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || defined( 'PORTO_AI_CLI' ) || exit;

if ( ! class_exists( 'Porto_AI_Demo_Repository' ) ) {

	class Porto_AI_Demo_Repository {

		/**
		 * Canonical live-preview base (WPBakery variant). Builder-specific
		 * variants prefix the id, e.g. `elementor-<id>` — see preview_urls().
		 */
		const PREVIEW_BASE = 'https://www.portotheme.com/wordpress/porto/';

		/**
		 * Overlay file holding ONLY AI-enriched fields, keyed by demo id.
		 *
		 * @var string
		 */
		const OVERLAY_RELATIVE = 'data/demos.json';

		/**
		 * @var self|null
		 */
		private static $instance = null;

		/**
		 * Merged demo list cache (runtime).
		 *
		 * @var array<string,array>|null
		 */
		private $merged = null;

		/**
		 * Overlay cache (runtime).
		 *
		 * @var array<string,array>|null
		 */
		private $overlay = null;

		/**
		 * @return self
		 */
		public static function instance() {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/* ---------------------------------------------------------------------
		 * Pure derivation (no WordPress, no overlay) — shared with the generator.
		 * ------------------------------------------------------------------- */

		/**
		 * Category tokens → human label. These are the same tokens the wizard's
		 * porto_demo_filters() exposes, minus meta tokens (all/has-badge/builder).
		 *
		 * @return array<string,string>
		 */
		public static function category_tokens() {
			return array(
				'shop'      => 'WooCommerce / Shop',
				'business'  => 'Business',
				'portfolio' => 'Portfolio',
				'classic'   => 'Classic / Multipurpose',
				'blog'      => 'Blog',
				'onepage'   => 'One Page',
			);
		}

		/**
		 * Builder tokens that may appear in a demo `filter` string. WPBakery
		 * (js_composer) is the always-available base, so it is implicit.
		 *
		 * @return array<string,string>
		 */
		public static function builder_tokens() {
			return array(
				'elementor'  => 'elementor',
				'vc'         => 'visualcomposer',
				'gutenberg'  => 'gutenberg',
			);
		}

		/**
		 * Required-plugin slug → user-facing feature label. Plugins that are
		 * purely structural (ACF, post-types-unlimited, …) are intentionally
		 * omitted: they are not features a buyer chooses a demo for.
		 *
		 * @return array<string,string>
		 */
		public static function feature_map() {
			return array(
				'woocommerce'                                 => 'shopping cart',
				'yith-woocommerce-wishlist'                   => 'wishlist',
				'yith-woocommerce-ajax-navigation'            => 'product filters',
				'customer-reviews-woocommerce'                => 'product reviews',
				'yith-woocommerce-frequently-bought-together' => 'frequently bought together',
				'yith-woocommerce-compare'                    => 'product compare',
				'wpc-buy-now-button'                          => 'buy now button',
				'instagram-slider-widget'                     => 'instagram feed',
				'contact-form-7'                              => 'contact form',
				'dokan-lite'                                  => 'multivendor marketplace',
				'wc-frontend-manager'                         => 'multivendor marketplace',
				'wc-multivendor-marketplace'                  => 'multivendor marketplace',
				'leadin'                                      => 'hubspot crm',
			);
		}

		/**
		 * Whether a raw porto_demo_types() entry is a category GROUP header
		 * (the "Main/Shop/Blog Demo (N variations)" tiles) rather than an
		 * importable demo. These are excluded from the recommendable set.
		 *
		 * @param array $details Raw entry.
		 * @return bool
		 */
		public static function is_group_header( array $details ) {
			return ! empty( $details['grouped'] );
		}

		/**
		 * Derive structured metadata from a single raw porto_demo_types() entry.
		 * PURE: no WordPress calls, no overlay. Safe to run at build time.
		 *
		 * @param string $id      Demo id (array key in porto_demo_types()).
		 * @param array  $details Raw entry { alt, img, filter, plugins, … }.
		 * @return array Normalized derived metadata.
		 */
		public static function derive( $id, array $details ) {
			$filter  = isset( $details['filter'] ) ? (string) $details['filter'] : '';
			$tokens  = array_filter( preg_split( '/\s+/', $filter ) );
			$plugins = isset( $details['plugins'] ) && is_array( $details['plugins'] ) ? $details['plugins'] : array();

			// Name: strip the "<small>(N VARIATIONS)</small>" markup from alt.
			$name = isset( $details['alt'] ) ? (string) $details['alt'] : $id;
			$name = trim( preg_replace( '/\s+/', ' ', wp_strip_all_tags_safe( $name ) ) );

			// Categories.
			$categories = array();
			foreach ( self::category_tokens() as $token => $label ) {
				if ( in_array( $token, $tokens, true ) ) {
					$categories[] = $token;
				}
			}

			// Builders: WPBakery is always available; add detected variants.
			$builders = array( 'wpbakery' );
			foreach ( self::builder_tokens() as $token => $slug ) {
				if ( in_array( $token, $tokens, true ) ) {
					$builders[] = $slug;
				}
			}
			$builders = array_values( array_unique( $builders ) );
			// Elementor demos are marketed as such → primary builder.
			$primary_builder = in_array( 'elementor', $tokens, true ) ? 'elementor' : 'wpbakery';

			// WooCommerce: explicit plugin OR shop category.
			$woocommerce = in_array( 'woocommerce', $plugins, true ) || in_array( 'shop', $tokens, true );

			// Features from required plugins.
			$feature_map = self::feature_map();
			$features    = array();
			foreach ( $plugins as $slug ) {
				if ( isset( $feature_map[ $slug ] ) ) {
					$features[] = $feature_map[ $slug ];
				}
			}
			$features = array_values( array_unique( $features ) );

			return array(
				'id'              => $id,
				'name'            => $name,
				'categories'      => $categories,
				'primary_builder' => $primary_builder,
				'builders'        => $builders,
				'woocommerce'     => $woocommerce,
				'fse'             => in_array( 'soft', $tokens, true ),
				'is_new'          => in_array( 'badge-new', $tokens, true ) || in_array( 'has-badge', $tokens, true ),
				'features'        => $features,
				'thumbnail'       => self::normalize_url( isset( $details['img'] ) ? (string) $details['img'] : '' ),
				'preview_url'     => self::PREVIEW_BASE . rawurlencode( $id ),
				'filter_tokens'   => array_values( $tokens ),
			);
		}

		/**
		 * Empty AI-enrichment skeleton (the overlay shape).
		 *
		 * @return array
		 */
		public static function empty_enrichment() {
			return array(
				'keywords'    => array(),
				'style'       => array(),
				'colors'      => array(),
				'description' => '',
			);
		}

		/**
		 * Normalize a protocol-relative or bare URL to https.
		 *
		 * @param string $url URL.
		 * @return string
		 */
		public static function normalize_url( $url ) {
			$url = trim( (string) $url );
			if ( '' === $url ) {
				return '';
			}
			if ( 0 === strpos( $url, '//' ) ) {
				return 'https:' . $url;
			}
			return $url;
		}

		/* ---------------------------------------------------------------------
		 * Runtime (WordPress) — merge + lookups.
		 * ------------------------------------------------------------------- */

		/**
		 * The canonical raw demo list from the setup wizard.
		 *
		 * @return array<string,array>
		 */
		public function get_raw_types() {
			// On REST requests the wizard file isn't loaded (it's behind an
			// is_admin()/admin_init gate), so the canonical demo list is absent.
			// Load it on demand — porto_demo_types() is the single source of truth.
			if ( ! class_exists( 'Porto_Theme_Setup_Wizard' ) && defined( 'PORTO_ADMIN' ) ) {
				$wizard_file = PORTO_ADMIN . '/setup_wizard/setup_wizard.php';
				if ( is_readable( $wizard_file ) ) {
					require_once $wizard_file;
				}
			}
			if ( ! class_exists( 'Porto_Theme_Setup_Wizard' ) ) {
				return array();
			}
			$wizard = Porto_Theme_Setup_Wizard::get_instance();
			if ( ! $wizard || ! method_exists( $wizard, 'porto_demo_types' ) ) {
				return array();
			}
			$types = $wizard->porto_demo_types();
			return is_array( $types ) ? $types : array();
		}

		/**
		 * Load (and cache) the AI overlay file.
		 *
		 * @return array<string,array>
		 */
		public function get_overlay() {
			if ( null !== $this->overlay ) {
				return $this->overlay;
			}
			$this->overlay = self::read_overlay_file( __DIR__ . '/' . self::OVERLAY_RELATIVE );
			return $this->overlay;
		}

		/**
		 * Read + decode an overlay JSON file. Static so the generator can reuse
		 * it. Returns [] on any error (missing file / invalid JSON).
		 *
		 * @param string $path Absolute path.
		 * @return array<string,array>
		 */
		public static function read_overlay_file( $path ) {
			if ( ! is_readable( $path ) ) {
				return array();
			}
			$raw = file_get_contents( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions
			if ( ! $raw ) {
				return array();
			}
			$data = json_decode( $raw, true );
			if ( ! is_array( $data ) ) {
				return array();
			}
			// Support both { "demos": { id: {...} } } and a flat { id: {...} } map.
			if ( isset( $data['demos'] ) && is_array( $data['demos'] ) ) {
				return $data['demos'];
			}
			return $data;
		}

		/**
		 * Full merged demo list: derived metadata + overlay enrichment.
		 * Group headers are excluded.
		 *
		 * @return array<string,array> Keyed by demo id.
		 */
		public function get_all() {
			if ( null !== $this->merged ) {
				return $this->merged;
			}

			$overlay = $this->get_overlay();
			$merged  = array();

			foreach ( $this->get_raw_types() as $id => $details ) {
				if ( ! is_array( $details ) || self::is_group_header( $details ) ) {
					continue;
				}
				$merged[ $id ] = self::merge_one( $id, $details, isset( $overlay[ $id ] ) ? $overlay[ $id ] : array() );
			}

			$this->merged = $merged;
			return $this->merged;
		}

		/**
		 * Merge a single demo's derived metadata with its overlay enrichment.
		 * Static + pure so the generator can preview merged output.
		 *
		 * @param string $id      Demo id.
		 * @param array  $details Raw porto_demo_types() entry.
		 * @param array  $enrich  Overlay enrichment for this id (may be empty).
		 * @return array
		 */
		public static function merge_one( $id, array $details, array $enrich = array() ) {
			$derived = self::derive( $id, $details );
			$enrich  = array_merge( self::empty_enrichment(), array_intersect_key( $enrich, self::empty_enrichment() ) );

			// Description falls back to the demo name when not enriched.
			if ( '' === trim( (string) $enrich['description'] ) ) {
				$enrich['description'] = $derived['name'];
			}

			return array_merge( $derived, $enrich );
		}

		/**
		 * Fetch one merged demo by id.
		 *
		 * @param string $id Demo id.
		 * @return array|null
		 */
		public function get( $id ) {
			$all = $this->get_all();
			return isset( $all[ $id ] ) ? $all[ $id ] : null;
		}

		/**
		 * Whitelist check — is $id a real, importable demo? Use this to validate
		 * any demo id returned by the AI before turning it into an action.
		 *
		 * @param string $id Demo id.
		 * @return bool
		 */
		public function is_valid_demo_id( $id ) {
			$all = $this->get_all();
			return is_string( $id ) && isset( $all[ $id ] );
		}

		/**
		 * Compact representation for sending to the model — only the fields the
		 * recommendation prompt needs, to minimize tokens.
		 *
		 * @param array<string,array>|null $demos Subset of merged demos; defaults to all.
		 * @return array<int,array>
		 */
		public function to_ai_payload( $demos = null ) {
			if ( null === $demos ) {
				$demos = $this->get_all();
			}
			$out = array();
			foreach ( $demos as $demo ) {
				// Keep the payload lean: PHP has already ranked these candidates, so
				// the model only needs enough signal to pick the top few and write a
				// short reason. Full descriptions + long keyword lists balloon the
				// token count (and TPM usage) for no ranking benefit, so we cap them.
				$keywords = is_array( $demo['keywords'] ) ? array_slice( $demo['keywords'], 0, 12 ) : $demo['keywords'];
				$out[]    = array(
					'id'          => $demo['id'],
					'name'        => $demo['name'],
					'builder'     => $demo['primary_builder'],
					'categories'  => $demo['categories'],
					'keywords'    => $keywords,
					'style'       => $demo['style'],
					'woocommerce' => $demo['woocommerce'],
					'features'    => $demo['features'],
					'description' => $this->trim_words( $demo['description'], 24 ),
				);
			}
			return $out;
		}

		/**
		 * Shorten prose to at most $limit words, adding an ellipsis when cut.
		 * Keeps the candidate payload (and OpenAI TPM usage) small.
		 *
		 * @param string $text  Source text.
		 * @param int    $limit Max words.
		 * @return string
		 */
		private function trim_words( $text, $limit = 24 ) {
			$text  = trim( (string) $text );
			if ( '' === $text ) {
				return '';
			}
			$words = preg_split( '/\s+/', $text );
			if ( count( $words ) <= $limit ) {
				return $text;
			}
			return implode( ' ', array_slice( $words, 0, $limit ) ) . '…';
		}
	}
}

/**
 * wp_strip_all_tags() is unavailable at build time (no WP). Thin shim that
 * prefers the core function when present and falls back to a safe local strip.
 *
 * @param string $text Text.
 * @return string
 */
if ( ! function_exists( 'wp_strip_all_tags_safe' ) ) {
	function wp_strip_all_tags_safe( $text ) {
		if ( function_exists( 'wp_strip_all_tags' ) ) {
			return wp_strip_all_tags( $text );
		}
		$text = preg_replace( '@<(script|style)[^>]*?>.*?</\\1>@si', '', (string) $text );
		return trim( strip_tags( $text ) );
	}
}