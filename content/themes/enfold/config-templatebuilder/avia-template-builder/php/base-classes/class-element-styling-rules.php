<?php
namespace aviaBuilder\base;

/**
 * This base class implements special styling rules for compatibility with all browsers
 *
 *
 * @author		Günter
 * @since 4.8.4
 */
if( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly


if( ! class_exists( __NAMESPACE__ . '\aviaElementStylingRules' ) )
{

	class aviaElementStylingRules extends \aviaBuilder\base\aviaElementStylingBase
	{
		/**
		 *
		 * @since 4.8.4
		 * @param \aviaShortcodeTemplate $shortcode
		 * @param string $element_id
		 */
		protected function __construct( \aviaShortcodeTemplate $shortcode = null, $element_id = '' )
		{
			parent::__construct( $shortcode, $element_id );
		}

		/**
		 * @since 4.8.4
		 */
		public function __destruct()
		{
			parent::__destruct();
		}

		/**
		 * Returns all transition rules for a combined string
		 *
		 * @since 4.8.4
		 * @param string $rule_string
		 * @return array
		 */
		public function transition_rules( $rule_string )
		{
			$transition = array(
						'transition'			=> $rule_string,
						'-webkit-transition'	=> $rule_string,
						'-moz-transition'		=> $rule_string,
						'-ms-transition'		=> $rule_string,
						'-o-transition'			=> $rule_string,
					);

			/**
			 * @since 4.8.4
			 * @param array $transition
			 * @param string $rule_string
			 * @return array
			 */
			return apply_filters( 'avf_css_rules_transition', $transition, $rule_string );
		}

		/**
		 * Returns all transform rules for a rule string
		 *
		 * @since 4.8.4
		 * @param string $rule_string
		 * @return array
		 */
		public function transform_rules( $rule_string )
		{
			$transform = array(
						'transform'			=> $rule_string,
						'-webkit-transform'	=> $rule_string,
						'-ms-transform'		=> $rule_string
					);

			/**
			 * @since 4.8.4
			 * @param array $transform
			 * @param string $rule_string
			 * @return array
			 */
			return apply_filters( 'avf_css_rules_transform', $transform, $rule_string );
		}



		/**
		 * Returns all animation rules for a combined string
		 *
		 * @since 4.8.4
		 * @param string $rule_string
		 * @return array
		 */
		public function animation_rules( $rule_string )
		{
			$animation = array(
						'animation'			=> $rule_string,
						'-webkit-animation'	=> $rule_string,
						'-moz-animation'	=> $rule_string,
						'-o-animation'		=> $rule_string
					);

			/**
			 * @since 4.8.4
			 * @param array $animation
			 * @param string $rule_string
			 * @return array
			 */
			return apply_filters( 'avf_css_rules_animation', $animation, $rule_string );
		}

		/**
		 * Returns all box-shadow rules for a combined string
		 *
		 * @since 4.8.4
		 * @param string $rule_string
		 * @return array
		 */
		public function box_shadow_rules( $rule_string )
		{
			$box_shadow = array(
						'box-shadow'			=> $rule_string,
						'-webkit-box-shadow'	=> $rule_string,
						'-moz-box-shadow'		=> $rule_string,
					);

			/**
			 * @since 4.8.4
			 * @param array $box_shadow
			 * @param string $rule_string
			 * @return array
			 */
			return apply_filters( 'avf_css_rules_box_shadow', $box_shadow, $rule_string );
		}

		/**
		 * Returns all box-shadow rules for a combined string
		 *
		 * @since 4.8.4
		 * @param string $rule_string
		 * @return array
		 */
		public function border_radius_rules( $rule_string )
		{
			$border_radius = array(
						'border-radius'			=> $rule_string,
						'-webkit-border-radius'	=> $rule_string,
						'-moz-border-radius'	=> $rule_string,
					);

			/**
			 * @since 4.8.4
			 * @param array $border_radius
			 * @param string $rule_string
			 * @return array
			 */
			return apply_filters( 'avf_css_rules_border_radius', $border_radius, $rule_string );
		}

		/**
		 * Returns all gradient color rules rules for a combined string
		 * Currently we do not add support for browser compatibility:
		 *		-moz-....
		 *		-webkit-...
		 *
		 * Seperate with , does not work in FF
		 *
		 * @since 4.8.4
		 * @param string $rule_prefix
		 * @param string $rule_colors
		 * @param string $fallback_color		for older browsers only
		 * @return array
		 */
		public function gradient_color_rules( $rule_prefix, $rule_colors, $fallback_color = '' )
		{
			$background = array();

			//	MUST be placed before background for transparent rgba colors to work
			if( ! empty( $fallback_color ) )
			{
				$background['background-color'] = $fallback_color;
			}

			$background['background'] = "$rule_prefix( {$rule_colors} )";

			/**
			 * @since 4.8.4
			 * @param array $background
			 * @param string $rule_prefix
			 * @param string $rule_colors
			 * @param string $fallback_color
			 * @return array
			 */
			return apply_filters( 'avf_css_rules_gradient_color', $background, $rule_prefix, $rule_colors, $fallback_color );
		}

		/**
		 * @since 4.8.4
		 * @param string $id
		 * @param string $animation
		 * @return array
		 */
		public function sonar_keyframes( $id, $animation )
		{
			$keyframes = array(
					"@keyframes $id {" . $this->new_ln . $animation . $this->new_ln . '}',
					"@-webkit-keyframes $id {" . $this->new_ln . $animation . $this->new_ln . '}'
				);

			/**
			 * @since 4.8.4
			 * @param array $keyframes
			 * @param string $rule_string
			 * @return array
			 */
			return apply_filters( 'avf_css_rules_sonar_keyframes', $keyframes, $id, $animation );
		}

		/**
		 * Translate option value string for background position to % value and reorder to get compatible with
		 * minification plugins only translating position strings to % value without checking logic resulting in broken layout
		 * (e.g. https://wordpress.org/plugins/sg-cachepress/ reported https://kriesi.at/support/topic/color-section-disappeared-after-update/)
		 *
		 * Currently we only translate rules defined in Avia_Popup_Templates::background_image_position()
		 * Logic might need to be extended if necessary
		 *
		 * @since 4.8.6.1
		 * @param string $bg_pos_string
		 * @param string $default
		 * @return string
		 */
		public function background_position_string( $bg_pos_string, $default = '' )
		{
			if( ! is_string( $bg_pos_string ) )
			{
				return '';
			}

			if( empty( $bg_pos_string ) )
			{
				if( empty( $default ) )
				{
					return '';
				}

				$bg_pos_string = $default;
			}

			switch( $bg_pos_string )
			{
				case 'top left':
					$pos = '0% 0%';
					break;
				case 'top center':
					$pos = '50% 0%';
					break;
				case 'top right':
					$pos = '100% 0%';
					break;
				case 'bottom left':
					$pos = '0% 100%';
					break;
				case 'bottom center':
					$pos = '50% 100%';
					break;
				case 'bottom right':
					$pos = '100% 100%';
					break;
				case 'center left':
					$pos = '0% 50%';
					break;
				case 'center center':
					$pos = '50% 50%';
					break;
				case 'center right':
					$pos = '100% 50%';
					break;
				default:
					$pos = '0% 0%';
					break;
			}

			return $pos;
		}

		/**
		 * Returns rules for grayscale
		 *
		 * @since 4.8.7
		 * @param string $grayscale_value
		 * @return array
		 */
		public function grayscale_rule( $grayscale_value )
		{
			if( ! is_numeric( $grayscale_value ) || empty( $grayscale_value ) )
			{
				$grayscale_value = 0;
			}

			$grayscale = array(
						'filter'			=>  "grayscale($grayscale_value%)",
						'-webkit-filter'	=>  "grayscale($grayscale_value%)"
					);


			/**
			 * @since 4.8.7
			 * @param array $grayscale
			 * @param string $grayscale_value
			 * @return array
			 */
			return apply_filters( 'avf_css_rules_grayscale', $grayscale, $grayscale_value );
		}

		/**
		 * Returns all column count rules for a textblock
		 *
		 * @since 4.8.8
		 * @param string $rule_string
		 * @return array
		 */
		public function textblock_column_count_rules( $rule_string )
		{
			$column_count = array(
						'column-count'			=> $rule_string,
						'-webkit-column-count'	=> $rule_string,
						'-moz-column-count'		=> $rule_string,
					);

			/**
			 * @since 4.8.8
			 * @param array $column_count
			 * @param string $rule_string
			 * @return array
			 */
			return apply_filters( 'avf_css_rules_textblock_column_count', $column_count, $rule_string );
		}

		/**
		 * Returns all column gap rules for a textblock
		 *
		 * @since 4.8.8
		 * @param string $rule_string
		 * @return array
		 */
		public function textblock_column_gap_rules( $rule_string )
		{
			$column_gap = array(
						'column-gap'			=> $rule_string,
						'-webkit-column-gap'	=> $rule_string,
						'-moz-column-gap'		=> $rule_string,
					);

			/**
			 * @since 4.8.8
			 * @param array $column_gap
			 * @param string $rule_string
			 * @return array
			 */
			return apply_filters( 'avf_css_rules_textblock_column_gap', $column_gap, $rule_string );
		}

	}
}
