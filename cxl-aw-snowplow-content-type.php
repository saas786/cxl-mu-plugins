<?php
/**
 * Plugin name: CXL SnowPlow add content type header.
 * Description: Add <code>content-type</code> header to SnowPlow API requests.
 * Version: 2022.10.19
 *
 * @since 2022.10.19
 * @see https://app.clickup.com/t/2xc9y4z
 */

defined( 'ABSPATH' ) || exit;

/**
 * @since 2022.10.19
 */
add_filter('http_request_args', static function( array $args, string $url = '' ): array {

    $snowplow_sandbox_url    = 'https://data-sandbox-product.cxl.com/com.snowplowanalytics.iglu/v1?aid=cxl-webhooks-sandbox';
    $snowplow_production_url = 'https://data-product.cxl.com/com.snowplowanalytics.iglu/v1?aid=cxl-webhooks';

    if ( strpos( $url, $snowplow_sandbox_url ) !== false || strpos( $url, $snowplow_production_url ) !== false ) {
        $args['headers']['content-type'] = 'application/json';
    }

    return $args;
}, 10, 2);
