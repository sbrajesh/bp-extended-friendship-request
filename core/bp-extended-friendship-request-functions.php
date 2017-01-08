<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit( 0 );
}

/**
 * Get the message associated with this friendship request
 *
 * @param int $user_id
 * @param int $friendship_id
 *
 * @return string
 */
function bp_ext_friend_request_get_message( $user_id, $friendship_id ) {

	$key = bp_ext_friend_request_get_message_key();

	$messages = bp_get_user_meta( $user_id, $key, true );

	if ( isset ( $messages[ $friendship_id ] ) ) {
		return $messages[ $friendship_id ];
	}

	return '';
}

/**
 * Update the message associated with this friendship request
 * We are actually saving the message for the requested user
 *
 * @param int $user_id
 * @param int $friendship_id
 * @param string $message
 */
function bp_ext_friend_request_update_message( $user_id, $friendship_id, $message ) {

	$key = bp_ext_friend_request_get_message_key();

	$messages = bp_get_user_meta( $user_id, $key, true );

	if ( empty( $messages ) ) {
		$messages = array();
	}

	$messages[ $friendship_id ] = $message;

	bp_update_user_meta( $user_id, $key, $messages );
}

/**
 * Delete the message for a particular friendship id
 *
 * @param int $user_id
 * @param int $friendship_id
 */
function bp_ext_friend_request_delete_message( $user_id, $friendship_id ) {

	$key = bp_ext_friend_request_get_message_key();

	$messages = bp_get_user_meta( $user_id, $key, true );

	if ( ! empty( $messages ) && is_array( $messages ) ) {
		unset( $messages[ $friendship_id ] );
	}

	if ( ! empty( $messages ) ) {
		$messages = array_filter( $messages );//filter out empty array
	}

	bp_update_user_meta( $user_id, $key, $messages );
}

/**
 * The user meta key name which we use to store the messages
 * I just wanted to avoid hard coding it multiple times.
 *
 * @return string key name
 */
function bp_ext_friend_request_get_message_key() {
	return 'friendship_request_messages';
}
