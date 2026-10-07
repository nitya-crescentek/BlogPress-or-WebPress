<?php
/**
 * Load necessary Customizer controls and functions.
 *
 * @package WebPress
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Add fields.
require_once trailingslashit( dirname( __FILE__ ) ) . 'class-customize-field.php';

// Controls.
require_once trailingslashit( dirname( __FILE__ ) ) . 'controls/class-react-control.php';
require_once trailingslashit( dirname( __FILE__ ) ) . 'controls/class-color-control.php';
require_once trailingslashit( dirname( __FILE__ ) ) . 'controls/class-range-control.php';
require_once trailingslashit( dirname( __FILE__ ) ) . 'controls/class-wrapper-control.php';
require_once trailingslashit( dirname( __FILE__ ) ) . 'controls/class-radio-image-control.php';

// Helper functions.
require_once trailingslashit( dirname( __FILE__ ) ) . 'helpers.php';
