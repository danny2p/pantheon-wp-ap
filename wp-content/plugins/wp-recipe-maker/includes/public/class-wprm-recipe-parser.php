<?php
/**
 * Responsible for parsing recipe (parts) into our recipe format.
 *
 * @link       http://bootstrapped.ventures
 * @since      1.0.0
 *
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 */

/**
 * Responsible for parsing recipe (parts) into our recipe format.
 *
 * @since      1.0.0
 * @package    WP_Recipe_Maker
 * @subpackage WP_Recipe_Maker/includes/public
 * @author     Brecht Vandersmissen <brecht@bootstrapped.ventures>
 */
class WPRM_Recipe_Parser {

	private static $fraction_symbols_map = array(
		'00BC' => '1/4', '00BD' => '1/2', '00BE' => '3/4', '2150' => '1/7',
		'2151' => '1/9', '2152' => '1/10', '2153' => '1/3', '2154' => '2/3',
		'2155' => '1/5', '2156' => '2/5', '2157' => '3/5', '2158' => '4/5',
		'2159' => '1/6', '215A' => '5/6', '215B' => '1/8', '215C' => '3/8',
		'215D' => '5/8', '215E' => '7/8'
	);

	/**
	 * Replace fractions with their symbol, if available.
	 *
	 * @since	7.2.0
	 * @param	mixed $text Text to find fractions in.
	 */
	public static function replace_any_fractions_with_symbol( $text ) {
		$text = ' ' . $text . ' ';

		foreach ( self::$fraction_symbols_map as $symbol => $fraction ) {
			$text = str_replace( ' ' . $fraction . ' ', ' ' . json_decode( '"\u' . $symbol . '"' ) . ' ', $text );
		}

		// We added 1 space in front and at the end, remove again.
		$text = substr( $text, 1, strlen( $text ) - 2 );

		return $text;
	}

	/**
	 * Parse text to ingredient fields.
	 *
	 * @since    1.0.0
	 * @param	mixed $raw Text to parse into an ingredient.
	 */
	public static function parse_ingredient( $raw ) {
		// Replace non-breaking spaces with regular ones and trim.
		$raw = trim( preg_replace( '/\xc2\xa0/', ' ', $raw ) );

		// Backup raw.
		$raw_original = $raw;

		// Amount.
		$amount = '';

		$unicode_regex = '';
		foreach ( self::$fraction_symbols_map as $unicode => $normal ) {
			$unicode_regex .= '\x{' . $unicode . '}';
		}

		$range_keyword = trim( WPRM_Settings::get( 'import_range_keyword' ) );
		$amount_regex = '/^\s*([\d0-9' . $unicode_regex . ']([\s\/\-\d0-9.,' . $unicode_regex . ']|' . $range_keyword . '\s)*)(.*)/u';

		preg_match( $amount_regex, $raw, $match );
		if ( isset( $match[0] ) ) {
			$amount = trim( $match[1] );
			$raw = trim( $match[3] );
		}

		// Units.
		$unit = '';

		$possible_units = WPRM_Settings::get( 'import_units' );

		// Can't use array_map because of parameter.
		foreach ( $possible_units as $index => $value ) {
			$possible_units[ $index ] = preg_quote( $value, '/' );
			$possible_units[ $index ] .= '\.?'; // With or without dot to cover abbreviations.
		}

		$pattern = '/^(\b' . implode( '\s|\b', $possible_units ) . '\s)/ui';
		preg_match( $pattern, $raw, $match );
		if ( isset( $match[0] ) ) {
			$unit = trim( $match[0] );
			$raw = trim( preg_replace( $pattern, '', $raw, 1 ) );
		}

		// Notes 2.0.
		$notes = '';
		$notes_start = array();

		// Check for comma.
		if ( in_array( WPRM_Settings::get( 'import_notes_identifier' ), array( 'comma', 'both' ), true ) ) {
			if ( strpos( $raw, ',' ) ) {
				$notes_start[] = strpos( $raw, ',' );
			}
		}

		// Check for ().
		if ( in_array( WPRM_Settings::get( 'import_notes_identifier' ), array( 'parentheses', 'both' ), true ) ) {
			preg_match( '/\((.*?)\)/i', $raw, $match );
			if ( ! empty( $match ) ) {
				$notes_start[] = strpos( $raw, '(' );
			}
		}

		// Take out notes.
		if ( ! empty( $notes_start ) ) {
			$start = min( array_map( 'intval', $notes_start ) );

			$notes = trim( substr( $raw, $start ) );
			$raw = trim( substr( $raw, 0, $start ) );

			if ( WPRM_Settings::get( 'import_notes_remove_identifier' ) ) {
				$type = substr( $notes, 0, 1 );
				$notes = substr( $notes, 1 );

				if ( '(' === $type ) {
					$notes = preg_replace( '/\)/', '', $notes, 1);
				}
			}

			// Make sure the name is not empty.
			if ( ! $raw ) {
				$raw = $notes;
				$notes = '';
			}
		}

		// Name.
		$name = trim( $raw );

		// Make sure name is always filled in.
		if ( ! $name ) {
			$amount = '';
			$unit = '';
			$name = $raw_original;
			$notes = '';
		}

		return array(
			'amount' => trim( $amount ),
			'unit' => trim( $unit ),
			'name' => trim( $name ),
			'notes' => trim( $notes ),
		);
	}

	/**
	 * Get an array of possible ingredient units.
	 *
	 * @since    1.0.0
	 */
	public static function parse_ingredient_units() {
		$units = array(
			// Weight.
			__( 'kilograms', 'wp-recipe-maker' ),
			__( 'kilogram', 'wp-recipe-maker' ),
			__( 'kg', 'wp-recipe-maker' ),
			__( 'grams', 'wp-recipe-maker' ),
			__( 'gram', 'wp-recipe-maker' ),
			__( 'gr', 'wp-recipe-maker' ),
			__( 'g', 'wp-recipe-maker' ),
			__( 'milligrams', 'wp-recipe-maker' ),
			__( 'milligram', 'wp-recipe-maker' ),
			__( 'mg', 'wp-recipe-maker' ),
			__( 'pounds', 'wp-recipe-maker' ),
			__( 'pound', 'wp-recipe-maker' ),
			__( 'lbs', 'wp-recipe-maker' ),
			__( 'lb', 'wp-recipe-maker' ),
			__( 'ounces', 'wp-recipe-maker' ),
			__( 'ounce', 'wp-recipe-maker' ),
			__( 'oz', 'wp-recipe-maker' ),
			// Volume.
			__( 'liters', 'wp-recipe-maker' ),
			__( 'liter', 'wp-recipe-maker' ),
			__( 'l', 'wp-recipe-maker' ),
			__( 'deciliters', 'wp-recipe-maker' ),
			__( 'deciliter', 'wp-recipe-maker' ),
			__( 'dl', 'wp-recipe-maker' ),
			__( 'centiliters', 'wp-recipe-maker' ),
			__( 'centiliter', 'wp-recipe-maker' ),
			__( 'cl', 'wp-recipe-maker' ),
			__( 'milliliters', 'wp-recipe-maker' ),
			__( 'milliliter', 'wp-recipe-maker' ),
			__( 'ml', 'wp-recipe-maker' ),
			__( 'gallons', 'wp-recipe-maker' ),
			__( 'gallon', 'wp-recipe-maker' ),
			__( 'gal', 'wp-recipe-maker' ),
			__( 'quarts', 'wp-recipe-maker' ),
			__( 'quart', 'wp-recipe-maker' ),
			__( 'qt', 'wp-recipe-maker' ),
			__( 'pints', 'wp-recipe-maker' ),
			__( 'pint', 'wp-recipe-maker' ),
			__( 'pt', 'wp-recipe-maker' ),
			__( 'cups', 'wp-recipe-maker' ),
			__( 'cup', 'wp-recipe-maker' ),
			__( 'cu', 'wp-recipe-maker' ),
			__( 'c', 'wp-recipe-maker' ),
			__( 'fluid ounces', 'wp-recipe-maker' ),
			__( 'fluid ounce', 'wp-recipe-maker' ),
			__( 'fl ounces', 'wp-recipe-maker' ),
			__( 'fl ounce', 'wp-recipe-maker' ),
			__( 'floz', 'wp-recipe-maker' ),
			__( 'tablespoons', 'wp-recipe-maker' ),
			__( 'tablespoon', 'wp-recipe-maker' ),
			__( 'tbsps', 'wp-recipe-maker' ),
			__( 'tbsp', 'wp-recipe-maker' ),
			__( 'tbls', 'wp-recipe-maker' ),
			__( 'tbs', 'wp-recipe-maker' ),
			__( 'tb', 'wp-recipe-maker' ),
			__( 'T', 'wp-recipe-maker' ),
			__( 'teaspoons', 'wp-recipe-maker' ),
			__( 'teaspoon', 'wp-recipe-maker' ),
			__( 'tsps', 'wp-recipe-maker' ),
			__( 'tsp', 'wp-recipe-maker' ),
			__( 'ts', 'wp-recipe-maker' ),
			__( 't', 'wp-recipe-maker' ),
			// Length.
			__( 'meters', 'wp-recipe-maker' ),
			__( 'meter', 'wp-recipe-maker' ),
			__( 'm', 'wp-recipe-maker' ),
			__( 'centimeters', 'wp-recipe-maker' ),
			__( 'centimeter', 'wp-recipe-maker' ),
			__( 'cm', 'wp-recipe-maker' ),
			__( 'millimeters', 'wp-recipe-maker' ),
			__( 'millimeter', 'wp-recipe-maker' ),
			__( 'mm', 'wp-recipe-maker' ),
			__( 'yards', 'wp-recipe-maker' ),
			__( 'yard', 'wp-recipe-maker' ),
			__( 'yd', 'wp-recipe-maker' ),
			__( 'feet', 'wp-recipe-maker' ),
			__( 'foot', 'wp-recipe-maker' ),
			__( 'ft', 'wp-recipe-maker' ),
			__( 'inches', 'wp-recipe-maker' ),
			__( 'inch', 'wp-recipe-maker' ),
			__( 'in', 'wp-recipe-maker' ),
			// General.
			__( 'cloves', 'wp-recipe-maker' ),
			__( 'clove', 'wp-recipe-maker' ),
			__( 'leaves', 'wp-recipe-maker' ),
			__( 'leave', 'wp-recipe-maker' ),
			__( 'slices', 'wp-recipe-maker' ),
			__( 'slice', 'wp-recipe-maker' ),
			__( 'pieces', 'wp-recipe-maker' ),
			__( 'piece', 'wp-recipe-maker' ),
			__( 'pinches', 'wp-recipe-maker' ),
			__( 'pinch', 'wp-recipe-maker' ),
		);

		$units = apply_filters( 'wprm_parse_ingredient_units', $units );

		return array_map( 'sanitize_text_field', $units );
	}
	
	/**
	 * Parse ingredient amount.
	 *
	 * @since	6.3.0
	 * @param	mixed $raw Text to parse into an ingredient amount.
	 */
	public static function parse_quantity( $raw ) {
		// Should mimic the JS parseQuantity function.

		// Make sure to start out with string.
		$raw = '' . $raw;

		// Strip HTML and shortcodes.
		$raw = wp_strip_all_tags( strip_shortcodes( $raw ) );

		// Use . for decimals.
		$raw = str_replace( ',', '.', $raw );

		// Replace range keyword by dash.
		$range_keyword = trim( WPRM_Settings::get( 'import_range_keyword' ) );
		$range_keyword = $range_keyword ? $range_keyword : 'to';
		$raw = str_replace( ' ' . $range_keyword . ' ', '-', $raw );
		$raw = str_replace( ' - ', '-', $raw );

		// Replace fraction symbols.
		foreach ( self::$fraction_symbols_map as $unicode => $normal ) {
			$raw = preg_replace( '/\x{' . $unicode . '}/ u', ' ' . $normal, $raw );
		}

		// Remove any leftover characters we're not expecting.
		$raw = preg_replace( '/[^\d\s\.\/-]/', '', $raw );

		// Split by spaces.
		$raw = trim( $raw );
		$parts = explode( ' ', $raw );

		$quantity = 0.0;

		// Loop over parts and add values.
		foreach ( $parts as $part ) {
			$part = trim( $part );

			if ( '' !== $part ) {
				$division_parts = explode( '/', $part, 2 );
				$part_quantity = floatval( $division_parts[0] );

				if ( isset( $division_parts[1] ) ) {
					$divisor = floatval( $division_parts[1] );

					if ( $divisor ) {
						$part_quantity /= $divisor;
					}
				}

				if ( is_float( $part_quantity ) ) {
					$quantity += $part_quantity;
				}
			}
		}

		return $quantity;
	}

	/**
	 * Format ingredient amount.
	 *
	 * @since	6.3.0
	 * @param	mixed $raw Ingredient amount to format.
	 */
	public static function format_quantity( $raw, $decimals = 2, $allow_fractions = false ) {
		$formatted = '';

		if ( $raw ) {
			$display_as_fraction = false;

			// Check if fractions are enabled.
			if ( $allow_fractions && WPRM_Settings::get( 'fractions_enabled' ) ) {
				$max_denominator = intval( WPRM_Settings::get( 'fractions_max_denominator' ) );
				$max_denominator = 1 < $max_denominator ? $max_denominator : 8;

				$fraction_parts = self::frac( $raw, $max_denominator, WPRM_Settings::get( 'fractions_use_mixed' ) );

				if ( $fraction_parts && 3 === count( $fraction_parts ) && is_numeric( $fraction_parts[0] ) && is_numeric( $fraction_parts[1] ) && is_numeric( $fraction_parts[2] ) ) {
					$formatted_fraction = '';

					if ( 0 < $fraction_parts[0] ) {
						$formatted_fraction .= $fraction_parts[0] . ' ';
					}

					if ( 0 < $fraction_parts[1] ) {
						if ( 0 < $fraction_parts[2] ) {
							$formatted_fraction .= 1 === $fraction_parts[2] ? $fraction_parts[1] : $fraction_parts[1] . '/' . $fraction_parts[2];
						}
					} else {
						// End result should not be 0.
						if ( 0 === $fraction_parts[0] ) {
							$formatted_fraction .= '1/' . $max_denominator;
						}
					}
				}

				if ( $formatted_fraction ) {
					if ( WPRM_Settings::get( 'fractions_use_symbols' ) ) {
						$formatted_fraction = self::replace_any_fractions_with_symbol( $formatted_fraction );
					}

					$formatted = trim( $formatted_fraction );
					$display_as_fraction = true;
				}
			}

			// Not using fractions, round to x decimals.
			if ( ! $display_as_fraction ) {
				$decimals = intval( $decimals );

				$formatted = number_format( $raw, $decimals );
				if ( 0.0 === floatval( $formatted ) ) {
					$formatted .= pow( 10, -1 * $decimals );
				}

				// No unnecessary trailing zeroes.
				$test_formatted = str_replace( ',', '', $formatted ); // Strip thousands.
				if ( 0 < intval( $test_formatted ) && intval( $test_formatted ) == floatval( $test_formatted ) ) {
					$formatted = number_format( $raw, 0 );
				}

				// Optionally use comma as decimal separator (point is default).
				if ( 'comma' === WPRM_Settings::get( 'decimal_separator' ) ) {
					$formatted = str_replace( '.', '|', $formatted );
					$formatted = str_replace( ',', '.', $formatted );
					$formatted = str_replace( '|', ',', $formatted );
				}
			}
		}

		return $formatted;
	}

	/**
	 * Convert to fraction based on JS library used in frontend.
	 * https://github.com/SheetJS/frac
	 *
	 * @since	7.2.0
	 */
	private static function frac( $x, $D, $mixed ) {
		$x = floatval( $x );

		$n1 = floor( $x );
		$d1 = 1;
		$n2 = $n1 + 1;
		$d2 = 1;

		if ( $x != $n1 ) {
			while ( $d1 <= $D && $d2 <= $D ) {
				$m = ( $n1 + $n2 ) / ( $d1 + $d2 );

				if ( $x == $m ) {
					if ( $d1 + $d2 <= $D ) {
						$d1 += $d2;
						$n1 += $n2;

						$d2 = $D + 1;
					} elseif ( $d1 > $d2 ) {
						$d2 = $D + 1;
					} else {
						$d1 = $D + 1;
					}
					break;
				} elseif ( $x < $m ) {
					$n2 = $n1 + $n2;
					$d2 = $d1 + $d2;
				} else {
					$n1 = $n1 + $n2;
					$d1 = $d1 + $d2;
				}
			}
		}

		if ( $d1 > $D ) {
			$d1 = $d2;
			$n1 = $n2;
		}

		if ( ! $mixed ) {
			return array( 0, intval( $n1 ), intval( $d1 ) );
		}

		$q = floor( $n1 / $d1 );
		return array( intval( $q ), intval( $n1 - $q * $d1 ), intval( $d1 ) );
	}
}