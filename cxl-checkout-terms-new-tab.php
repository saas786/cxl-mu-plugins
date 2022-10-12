<?php
/**
 * Disables opening the Terms and Conditions page in an inline form on the Checkout page.
 * The Terms and Conditions link will then open in a new tab/window.
 *
 * @since 2022.10.12
 * @see https://app.clickup.com/t/3p6d31g
 * @see https://github.com/woocommerce/woocommerce/blob/7.0.0/plugins/woocommerce/templates/checkout/terms.php#L22
 */
add_action( 'wp', static function () {
    remove_action( 'woocommerce_checkout_terms_and_conditions', 'wc_terms_and_conditions_page_content', 30 );
} );
