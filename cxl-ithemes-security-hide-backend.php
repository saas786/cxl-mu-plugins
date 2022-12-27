<?php
/**
 * Add iThemes Security Hid Backend Module tweaks.
 *
 * @since 2022.12.27
 */

namespace CXL\ITS\HideBackEnd;

add_filter( 'itsec_hide-backend_module_config', __NAMESPACE__ . '\module_config' );

/**
 * Remove `login` from ITSEC disallowed slugs list.
 *
 * @since 2022.12.27
 * @see \iThemesSecurity\Module_Config::transform_module_config()
 * @see plugins/ithemes-security-pro/core/modules/hide-backend/module.json
 */
function module_config( array $config ): array {
    if ( ! isset( $config['settings']['properties']['slug'] ) ) {
        return $config;
    }

    $config['settings']['properties']['slug']['pattern']     = '^(?!(admin|wp-login\\.php|dashboard|wp-admin)$)[\\w\\-?&=#%]+$';
    $config['settings']['properties']['slug']['description'] = 'The login url slug cannot be “admin”, “dashboard”, or “wp-login.php” as these are use by default in WordPress.';

    return $config;
}
