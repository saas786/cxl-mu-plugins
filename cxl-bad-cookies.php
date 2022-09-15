<?php
/**
 * Bad Request - 400 issue fix,
 * which is caused by bad cookies.
 *
 * @package MUPlugin
 * @since 2022.09.14
 * @see https://app.clickup.com/t/28kmweq
 */

namespace CXL\MU\Plugin\BadCookies;

/**
 * @since 2022.09.14
 */
class Cookies {

    /**
     * @since 2022.09.14
     */
    private const COOKIES_ALLOWED_MAX = 50;

    /**
     * @since 2022.09.14
     */
    private const COOKIE_MAX_SIZE = 4; // KB = 4 * 1024

    /**
     * @since 2022.09.14
     */
    private const COOKIES_LIMIT = [
        'pum_alm_pages_viewed',
        'rl_trait',
        'sbjs_udata',
        'sbjs_current',
        'sbjs_first',
    ];

    /**
     * @since 2022.09.14
     */
    private const COOKIES_CLEANUP = [
        'pum_alm_pages_viewed',
        'rl_trait',
        'sbjs_udata',
        'sbjs_current',
        'sbjs_first',
    ];

    /** @since 2022.09.14 */
    private static array $cookies = [];

    /**
     * Constructor.
     *
     * @since 2022.09.14
     */
    public function __construct() {
        $this->boot();
    }

    /**
     * Boot.
     *
     * @since 2022.09.14
     */
    private function boot(): void {
        add_action( 'init', [ __CLASS__, 'process' ] );
    }

    /**
     * @since 2022.09.14
     * @see https://niamrox.com/file_get_contents-was-found-in-the-file-a-complete-solution-to-overcome-it/
     */
    public static function process(): void {
        self::setup();
        self::limit_cookies();
        self::cleanup_cookies();
    }

    /**
     * Transform `$_COOKIE` into key / value array.
     *
     * @since 2022.09.14
     */
    protected static function setup(): void {
        self::$cookies = array_map(
            static fn( $key ) => [
                'name'  => $key,
                'value' => $_COOKIE[ $key ],
            ],
            array_keys( $_COOKIE )
        );
    }

    /**
     * If cookies limit exceeds max allowed cookies,
     * remove irrelevant cookies.
     *
     * @since 2022.09.14
     */
    protected static function limit_cookies(): void {
        if ( ! ( count( self::$cookies ) > self::COOKIES_ALLOWED_MAX ) ) {
            return;
        }

        array_walk( self::$cookies, static function( $cookie ): void {
            if ( ! in_array( $cookie['name'], self::COOKIES_LIMIT, true ) ) {
                return;
            }

            \AutomateWoo\Cookies::clear( $cookie['name'] );
        });
    }

    /**
     * If some cookies become huge,
     * remove those cookies.
     *
     * @since 2022.09.14
     */
    protected static function cleanup_cookies(): void {
        $cookies = array_map( static fn( $cookie ) => self::process_cookie( $cookie ), self::$cookies );
        $cookies = array_filter( $cookies );

        $size = array_column( $cookies, 'size' );
        array_multisort( $size, SORT_DESC, $cookies );

        array_walk( $cookies, static function( $cookie ): void {
            if ( ! in_array( $cookie['name'], self::COOKIES_CLEANUP, true ) ) {
                return;
            }

            if ( $cookie['size'] <= self::COOKIE_MAX_SIZE ) {
                return;
            }

            \AutomateWoo\Cookies::clear( $cookie['name'] );
        });
    }

    /**
     * Build cookie data.
     *
     * @since 2022.09.14
     */
    protected static function process_cookie( $cookie ): array {

        $serialized_data = serialize( $cookie['value'] );
        $size            = strlen( $serialized_data );

        $length = strlen( $cookie['value'] );
        $size   = $size * 8 / 1024; // convert to KB.

        return [
            'name'   => $cookie['name'],
            'length' => $length,
            'size'   => $size,
        ];
    }

}

new Cookies();
