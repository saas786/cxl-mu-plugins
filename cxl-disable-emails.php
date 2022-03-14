<?php
/**
 * Filters whether to send the email change email.
 *
 * Note: $user['user_email'] is original email and $userdata['user_email'] is new email.
 *
 * @see wp_insert_user() For `$user` and `$userdata` fields.
 * @see https://app.clickup.com/t/2698qxf
 * @since 2022.03.14
 *
 * @param bool  $send     Whether to send the email.
 * @param array $user     The original user array.
 * @param array $userdata The updated user array.
 */
add_filter( 'send_email_change_email', static function( $send, $user, $userdata ) {

    /**
     * If email is changed, we verify if email has `+fraud` appended.
     */
    if (
        isset( $userdata['user_email'] )
        && $user['user_email'] !== $userdata['user_email']
        && strpos( $userdata['user_email'], '+fraud' ) !== false
     ) {
        $send = false;
    }

    return $send;
}, 99, 3 );


