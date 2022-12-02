<?php
/**
 * Add iThemes Security Re-captcha.
 *
 * @since 2022.12.02
 * @see https://help.ithemes.com/hc/en-us/articles/360001137034-How-Do-I-Integrate-My-Plugin-with-iThemes-Security-reCAPTCHA-
 */

namespace CXL\ITS\Recaptcha;

use ITSEC_Recaptcha_API;

add_action( 'woocommerce_add_payment_method_form_bottom', __NAMESPACE__ . '\display_recaptcha' );
add_action( 'woocommerce_review_order_before_submit', __NAMESPACE__ . '\display_recaptcha', 11 );

/**
 * Display iThemes Security Re-captcha.
 *
 * @since 2022.12.02
 */
function display_recaptcha(): void {
    if ( ! did_action( 'itsec_recaptcha_api_ready' ) ) {
        return;
    }

    ITSEC_Recaptcha_API::display( [ 'margin' => [ 'top' => '20' ] ] );
}

add_action( 'woocommerce_after_checkout_validation', __NAMESPACE__ . '\checkout_validate_captcha', 10, 2 );

/**
 * Checkout: Check if iThemes Security Re-captcha is valid.
 *
 * @since 2022.12.02
 * @param $data
 * @param $errors
 */
function checkout_validate_captcha( $data, $errors ): void {
    if ( ! did_action( 'itsec_recaptcha_api_ready' ) ) {
        return;
    }

    $valid = ITSEC_Recaptcha_API::validate();

    if ( is_wp_error( $valid ) ) {
        $errors->add( 'itsec_recaptcha_validation', 'Re-captcha Spam.' );
    }
}

add_action( 'woocommerce_add_payment_method_form_is_valid', __NAMESPACE__ . '\add_payment_method_validate_captcha' );

/**
 * Add payment method: Check if iThemes Security Re-captcha is valid.
 *
 * @since 2022.12.06
 * @see \WC_Form_Handler::add_payment_method_action()
 */
function add_payment_method_validate_captcha(): bool {
    if ( ! did_action( 'itsec_recaptcha_api_ready' ) ) {
        return true;
    }

    $valid = ITSEC_Recaptcha_API::validate();

    if ( is_wp_error( $valid ) ) {
        return false;
    }

    return true;
}

add_action( 'plugins_loaded', __NAMESPACE__ . '\init', 0 );

/**
 * Init.
 *
 * @since 2022.12.07
 */
function init(): void {
    if ( ! in_array( 'woocommerce/woocommerce.php', get_option( 'active_plugins' ) ) ) {
        return;
    }

    add_action( 'wc_ajax_wc_stripe_create_setup_intent', __NAMESPACE__ . '\create_setup_intent', \PHP_INT_MIN );
}

/**
 * Creates a Setup Intent through AJAX while adding cards.
 */
function create_setup_intent(): void {

    if ( ! did_action( 'itsec_recaptcha_api_ready' )
        || ! is_user_logged_in()
        || ! isset( $_POST['stripe_source_id'] )
        || ! isset( $_POST['nonce'] )
    ) {
        return;
    }

    $valid = ITSEC_Recaptcha_API::validate();

    if ( is_wp_error( $valid ) ) {
        // 1. Verify.
        $response = [
            'status' => 'error',
            'error'  => [
                'type'    => 'setup_intent_error',
                'message' => 'Unable to verify the captcha.',
            ],
        ];

        echo wp_json_encode( $response );
        exit;
    }

}
