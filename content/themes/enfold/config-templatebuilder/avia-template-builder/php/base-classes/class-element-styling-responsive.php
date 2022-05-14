<?php
namespace aviaBuilder\base;

/**
 * This class implements support for responsive styling rules in post css files
 * used to replace calls to AviaHelper::av_mobile_sizes( $atts ) in shortcode handler
 *
 *
 * @author		Günter
 * @since 4.8.8
 */
if( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly


if( ! class_exists( __NAMESPACE__ . '\aviaElementStylinResponsive' ) )
{

	class aviaElementStylingResponsive extends \aviaBuilder\base\aviaElementStylingRules
	{

		/**
		 * Containes sizes for media queries
		 *
		 * @since 4.8.8
		 * @var array
		 */
		private $media_sizes;

		/**
		 *
		 * @since 4.8.8
		 * @param \aviaShortcodeTemplate $shortcode
		 * @param string $element_id
		 */
		protected function __construct( \aviaShortcodeTemplate $shortcode = null, $element_id = '' )
		{
			parent::__construct( $shortcode, $element_id );

			/**
			 * @since 4.8.8
			 * @param array $limits
			 * @return array
			 */
			$this->media_sizes = apply_filters( 'avf_responsive_media_sizes', array(
									'av-medium'	=> array( 768, 989 ),
									'av-small'	=> array( 480, 767 ),
									'av-mini'	=> array( 0, 479 )
							) );
		}

		/**
		 * @since 4.8.8
		 */
		public function __destruct()
		{
			parent::__destruct();

			unset( $this->media_sizes );
		}

		/**
		 * Adds classes to given container to hide element depending on screen width
		 *
		 * @since 4.8.8
		 * @since 4.8.9							added $font_id and 'font_sizes' for av-...-font-size-overwrite
		 * @param string $container
		 * @param string $what					'hide_element' | 'columns' | 'font_sizes'
		 * @param array $atts
		 * @param string $font_id
		 */
		public function add_responsive_classes( $container, $what = 'hide_element', array $atts = array(), $font_id = '' )
		{
			$classes = array();

			switch( $what )
			{
				case 'columns':
					$this->responsive_columns_classes( $classes, $atts );
					break;
				case 'hide_element':
					$this->responsive_hide_element_classes( $classes, $atts );
					break;
				case 'font_sizes':
					$this->responsive_font_sizes_classes( $classes, $atts, $font_id );
					break;
			}

			if( ! empty( $classes ) )
			{
				$this->add_classes( $container, $classes );
			}
		}

		/**
		 * Return the class strings
		 *
		 * @since 4.8.8
		 * @param array $atts
		 * @return string
		 */
		public function responsive_classes_string( $what = 'hide_element', array $atts = array() )
		{
			$classes = array();

			switch( $what )
			{
				case 'columns':
					$this->responsive_columns_classes( $classes, $atts );
					break;
				case 'hide_element':
					$this->responsive_hide_element_classes( $classes, $atts );
					break;
			}

			return ! empty( $classes ) ? trim( implode( ' ', $classes ) ) : '';
		}

		/**
		 * Add responsive font sizes media queries to container
		 *
		 * @since 4.8.8
		 * @since 4.8.8.1						added $sc_context
		 * @since 4.8.9							added $important
		 * @param string $container
		 * @param string $font_id
		 * @param array $atts
		 * @param \aviaShortcodeTemplate|null $sc_context
		 * @param string $important
		 */
		public function add_responsive_font_sizes( $container, $font_id, array $atts = array(), \aviaShortcodeTemplate $sc_context = null, $important = '' )
		{
			/**
			 * Allow to skip responsive font handling on element basis
			 *
			 * @since 4.8.8.1
			 * @param boolean $skip
			 * @param array $atts
			 * @param \aviaShortcodeTemplate|null $sc_context
			 * @param string $font_id
			 * @param string $container
			 */
			if( false !== apply_filters( 'avf_el_styling_responsive_font_size_skip', false, $atts, $sc_context, $font_id, $container ) )
			{
				return;
			}

			$prefixes = array( '', 'av-medium-font-', 'av-small-font-', 'av-mini-font-' );

			foreach( $prefixes as $prefix )
			{
				$key = $prefix . $font_id;
				$value = isset( $atts[ $key ] ) ? $atts[ $key ] : '';

				if( '' == trim( $value ) )
				{
					continue;
				}

				if( is_numeric( $value ) )
				{
					$value .= 'px';
				}

				$css_val = trim( "$value $important" );

				if( '' == $prefix )
				{
					if( $value != 'hidden')
					{
						$this->add_styles( $container, array( 'font-size' => $css_val ) );
					}
					else
					{
						$this->add_styles( $container, array( 'display' => 'none' ) );
					}
				}
				else
				{
					$media = $this->media_sizes[ str_replace( '-font-', '', $prefix ) ];

					if( $value != 'hidden')
					{
						$rule = array( 'font-size' => $css_val );
					}
					else
					{
						$rule = array( 'display' => 'none' );
					}

					$mq_rule = array( 'screen' => array( "$media[0];$media[1]" => $rule ) );
					$this->add_media_queries( $container, $mq_rule );
				}
			}
		}

		/**
		 * Adds classes to change number of columns depending on screen width
		 *
		 * @since 4.8.8
		 * @param array &$classes
		 * @param array $atts
		 */
		protected function responsive_columns_classes( array &$classes, array $atts = array() )
		{
			$column_atts = array( 'av-medium-columns', 'av-small-columns', 'av-mini-columns' );

			foreach( $column_atts as $key )
			{
				if( ! empty( $atts[ $key ] ) )
				{
					$classes[] = "{$key}-overwrite";
					$classes[] = "{$key}-{$atts[ $key ]}";
				}
			}
		}

		/**
		 * Adds classes to array $classes to hide element depending on screen width
		 *
		 * @since 4.8.8
		 * @param array &$classes
		 * @param array $atts
		 * @return string
		 */
		protected function responsive_hide_element_classes( array &$classes, array $atts = array() )
		{
			$display_atts = array( 'av-desktop-hide', 'av-medium-hide', 'av-small-hide', 'av-mini-hide' );

			foreach( $display_atts as $key )
			{
				if( ! empty( $atts[ $key ] ) )
				{
					$classes[] = $key;
				}
			}
		}

		/**
		 * Add a class
		 * @since 4.8.9
		 * @param array $classes
		 * @param array $atts
		 * @param string $font_id
		 */
		protected function responsive_font_sizes_classes( array &$classes, array $atts = array(), $font_id = '' )
		{
			if( empty( $font_id ) )
			{
				return;
			}

			$prefixes = array( '', 'av-medium-font-', 'av-small-font-', 'av-mini-font-' );

			foreach( $prefixes as $key )
			{
				if( ! empty( $atts[ $key . $font_id ] ) )
				{
					if( '' == $key )
					{
						$classes[] = 'av-font-size-overwrite-css';
					}
					else
					{
						$classes[] = $key . 'size-overwrite-css';
					}
				}
			}
		}

	}
}
