<?php
/**
 * Porto AI — candidate pre-filter / payload builder.
 *
 * Reduces the full catalog (~100 demos) to a small, relevant candidate set
 * BEFORE anything is sent to the model. This is the "smart filtering" step:
 * it applies hard intent filters (shop → WooCommerce only, builder preference)
 * and a lightweight relevance score over the enriched metadata, then returns
 * the compact AI payload for the top N. Cuts tokens, cost, and latency.
 *
 * Pure PHP, no network — easy to unit test.
 *
 * @package Porto
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Porto_AI_Prompt_Builder' ) ) {

	class Porto_AI_Prompt_Builder {

		/** @var self|null */
		private static $instance = null;

		/** @return self */
		public static function instance() {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * Build the candidate payload for a user request.
		 *
		 * @param string $message Free-text user message.
		 * @param array  $answers Structured follow-up answers (business_type, style, builder…).
		 * @param int    $max     Max candidates to return.
		 * @return array Compact AI payload (Porto_AI_Demo_Repository::to_ai_payload shape).
		 */
		public function build_candidates( $message, $answers = array(), $max = 15 ) {
			$repo = Porto_AI_Demo_Repository::instance();
			$all  = $repo->get_all();
			if ( empty( $all ) ) {
				return array();
			}

			$answers = is_array( $answers ) ? $answers : array();
			$text    = mb_strtolower( trim( (string) $message . ' ' . implode( ' ', array_map( 'strval', $answers ) ) ) );

			$wants_shop    = $this->matches( $text, array( 'shop', 'store', 'ecommerce', 'e-commerce', 'sell', 'selling', 'product', 'products', 'woocommerce', 'cart', 'checkout', 'boutique' ) );
			$wants_builder = $this->detect_builder( $text, $answers );

			$tokens = $this->tokenize( $text );
			$scored = array();

			foreach ( $all as $id => $demo ) {
				// Hard filters.
				if ( $wants_shop && empty( $demo['woocommerce'] ) ) {
					continue;
				}
				if ( '' !== $wants_builder
					&& $wants_builder !== $demo['primary_builder']
					&& ! in_array( $wants_builder, $demo['builders'], true ) ) {
					continue;
				}

				$scored[ $id ] = $this->score( $tokens, $answers, $demo );
			}

			if ( empty( $scored ) ) {
				return array();
			}

			// Vague request (everything scored 0): prefer New, then keep order.
			$has_signal = false;
			foreach ( $scored as $s ) {
				if ( $s > 0 ) {
					$has_signal = true;
					break;
				}
			}
			if ( ! $has_signal ) {
				foreach ( $scored as $id => $s ) {
					$scored[ $id ] = ! empty( $all[ $id ]['is_new'] ) ? 1 : 0;
				}
			}

			arsort( $scored );
			$top_ids = array_slice( array_keys( $scored ), 0, max( 1, (int) $max ) );

			$subset = array();
			foreach ( $top_ids as $id ) {
				$subset[ $id ] = $all[ $id ];
			}

			return $repo->to_ai_payload( $subset );
		}

		/* ----------------------------------------------------------------- */

		/**
		 * Relevance score: token hits across the demo's searchable fields,
		 * plus small boosts for matching structured answers and "new" badges.
		 *
		 * @param string[] $tokens  Query tokens.
		 * @param array    $answers Structured answers.
		 * @param array    $demo    Merged demo record.
		 * @return int
		 */
		private function score( $tokens, $answers, $demo ) {
			// Stemmed word sets: curated fields (name/categories/keywords) are a
			// strong signal; prose (style/features/description) is a weak one. This
			// keeps stray words in a description (e.g. "...store selling...") from
			// outranking a real keyword match.
			$strong = $this->stem_set( array_merge( array( $demo['name'] ), $demo['categories'], $demo['keywords'] ) );
			$weak   = $this->stem_set( array_merge( $demo['style'], $demo['features'], array( $demo['description'] ) ) );

			$score = 0;
			foreach ( array_unique( $tokens ) as $t ) {
				$st = $this->stem( $t );
				if ( '' === $st ) {
					continue;
				}
				if ( isset( $strong[ $st ] ) ) {
					$score += 3;
				} elseif ( isset( $weak[ $st ] ) ) {
					$score += 1;
				}
			}

			// Structured answer boosts.
			if ( ! empty( $answers['style'] ) ) {
				$style = mb_strtolower( (string) $answers['style'] );
				if ( in_array( $style, array_map( 'mb_strtolower', $demo['style'] ), true ) ) {
					$score += 3;
				}
			}
			if ( ! empty( $answers['industry'] ) ) {
				if ( isset( $strong[ $this->stem( (string) $answers['industry'] ) ] ) ) {
					$score += 3;
				}
			}
			if ( ! empty( $demo['is_new'] ) ) {
				$score += 1;
			}

			return $score;
		}

		/**
		 * @param string $text    Query text.
		 * @param array  $answers Structured answers.
		 * @return string '' | elementor | wpbakery | visualcomposer | gutenberg
		 */
		private function detect_builder( $text, $answers ) {
			if ( ! empty( $answers['builder'] ) ) {
				return sanitize_key( $answers['builder'] );
			}
			if ( $this->matches( $text, array( 'elementor' ) ) ) {
				return 'elementor';
			}
			if ( $this->matches( $text, array( 'wpbakery', 'wp bakery', 'visual composer', 'js_composer' ) ) ) {
				return 'wpbakery';
			}
			if ( $this->matches( $text, array( 'gutenberg', 'block editor' ) ) ) {
				return 'gutenberg';
			}
			return '';
		}

		/**
		 * @param string $text Query text.
		 * @return string[]
		 */
		private function tokenize( $text ) {
			// Generic intent / commerce words carry no discriminating signal (they
			// already drive the hard filters and appear in most demo prose), so they
			// are dropped. Stored as stems so plurals/gerunds are covered too.
			$stop = array(
				'the', 'and', 'for', 'with', 'want', 'would', 'like', 'have', 'about',
				'using', 'use', 'that', 'this', 'your', 'from', 'our', 'their', 'need',
				'website', 'site', 'web', 'online', 'page', 'build', 'create', 'make',
				'please', 'looking', 'store', 'shop', 'ecommerce', 'commerce', 'buy',
				'sell', 'purchase', 'sale', 'price', 'best', 'top', 'good', 'great',
				'find', 'show', 'some', 'any', 'all', 'new',
			);
			$parts = preg_split( '/[^a-z0-9]+/i', mb_strtolower( (string) $text ) );
			$out   = array();
			foreach ( (array) $parts as $w ) {
				if ( strlen( $w ) <= 2 ) {
					continue;
				}
				if ( in_array( $w, $stop, true ) || in_array( $this->stem( $w ), $stop, true ) ) {
					continue;
				}
				$out[] = $w;
			}
			return $out;
		}

		/**
		 * Split strings into a set of stemmed words: { stem => true }.
		 *
		 * @param string[] $strings Strings.
		 * @return array<string,bool>
		 */
		private function stem_set( $strings ) {
			$set = array();
			foreach ( $strings as $s ) {
				foreach ( preg_split( '/[^a-z0-9]+/i', mb_strtolower( (string) $s ) ) as $w ) {
					if ( strlen( $w ) > 2 ) {
						$set[ $this->stem( $w ) ] = true;
					}
				}
			}
			return $set;
		}

		/**
		 * Light suffix stemmer so singular/plural/gerund forms match
		 * (clothes↔clothing→"cloth", childrens→children, bikes→bike).
		 *
		 * @param string $w Word.
		 * @return string
		 */
		private function stem( $w ) {
			$w = mb_strtolower( trim( (string) $w ) );
			$w = preg_replace( "/['\x{2019}]s$/u", '', $w );
			// Plural → singular only. Deliberately conservative: aggressive -es/-ing
			// stripping mangles stems (e.g. "bicycles" → "bicycl"), so avoid it.
			if ( mb_strlen( $w ) > 4 && 'ies' === substr( $w, -3 ) ) {
				return substr( $w, 0, -3 ) . 'y';
			}
			if ( mb_strlen( $w ) > 3 && 's' === substr( $w, -1 )
				&& 'ss' !== substr( $w, -2 ) && 'us' !== substr( $w, -2 ) ) {
				return substr( $w, 0, -1 );
			}
			return $w;
		}

		/**
		 * @param string   $text    Haystack.
		 * @param string[] $needles Needles.
		 * @return bool
		 */
		private function matches( $text, $needles ) {
			foreach ( $needles as $n ) {
				if ( false !== mb_strpos( $text, $n ) ) {
					return true;
				}
			}
			return false;
		}
	}
}