<?php

namespace CXL\Bot\Blocker;

add_action( 'plugins_loaded', __NAMESPACE__ . '\init', 0 );

/**
 * Init.
 *
 * @since 2022.12.01
 */
function init(): void {
    if ( ! in_array( 'woocommerce/woocommerce.php', get_option( 'active_plugins' ) ) ) {
        return;
    }

    // Add our custom checks to the built-in WooCommerce checkout validation.
    add_action( 'woocommerce_after_checkout_validation', __NAMESPACE__ . '\validate_spam_checkout', 10, 2 );
}

/**
 * Check if billing name or email domain is a specific string, if it is we'll decline the order.
 *
 * @since 2022.12.01
 * @param $data
 * @param $errors
 */
function validate_spam_checkout( $data, $errors ): void {
    if ( has_a_blocked_domain( $data['billing_email'] ) || is_disposable_email_address( $data['billing_email'] ) ) {
        $errors->add( 'billing_email_validation', 'Email Spam.' );
    }
}

/**
 * Check over a list of provided domains, to see if $user_email matches any of them.
 *
 * @since 2022.12.01
 */
function has_a_blocked_domain( string $user_email ): bool {

    if ( ! $user_email ) {
        return false;
    }

    // Provide our list of domains we want to block.
    $list_of_blocked_domains = [ 'abbuzz.com', 'fakemail.com' ];

    $has_blocked_domain = array_filter( $list_of_blocked_domains, static fn( string $blocked_domain ): bool => strpos( $user_email, $blocked_domain ) !== false );

    return count( $has_blocked_domain ) > 0;
}

/**
 * Check if email is disposable or not.
 *
 * @since 2022.12.07
 */
function is_disposable_email_address( string $email ): bool {

    if ( ! $email || ( defined( 'CXL_KICKBOX_API_ENABLED' ) && ! CXL_KICKBOX_API_ENABLED ) || ! defined( 'CXL_KICKBOX_API_URL' ) || ! defined( 'CXL_KICKBOX_API_KEY' ) ) {
        return false;
    }

    $api_params = [
        'timeout' => 6000, // milliseconds
        'email'   => urlencode( $email ),
        'apikey'  => CXL_KICKBOX_API_KEY,
    ];

    $api_url = add_query_arg( $api_params, CXL_KICKBOX_API_URL );

    $response = wp_remote_get(
        $api_url,
        [
            'timeout'   => 6,
            'sslverify' => false,
        ]
    );

    // Make sure the response came back okay.
    if ( is_wp_error( $response ) ) {
        return false;
    }

    $response_code = wp_remote_retrieve_response_code( $response );
    $response_data = json_decode( wp_remote_retrieve_body( $response ), true );

    return $response_code === 200 && array_key_exists( 'disposable', $response_data ) && $response_data['disposable'];
}
