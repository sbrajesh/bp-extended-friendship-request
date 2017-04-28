<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit( 0 );
}

/**
 * This class handles various ajax/non ajax actions related to the friendship
 */
class BPExtFriendShipActions {

	private static $instance;

	private function __construct() {

		//handle add friend
		add_action( 'wp_ajax_ext_friend_add_friend', array( $this, 'add_friend' ) );
		//remove friends
		add_action( 'wp_ajax_ext_friend_remove_friend', array( $this, 'remove_friend' ) );

		add_action( 'friends_friendship_requested', array( $this, 'save_friendship_request_message' ), 10, 3 );
		//clean on accepted
		add_action( 'friends_friendship_accepted', array( $this, 'clean_message' ), 10, 3 );
		//clean on rejected
		add_action( 'friends_friendship_rejected', array( $this, 'delete_message_on_withdraw' ), 10, 2 );
		//clean on withdraw
		add_action( 'friends_friendship_withdrawn', array( $this, 'delete_message_on_withdraw' ), 10, 2 );
	}

	public static function get_instance() {

		if ( ! isset ( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Delete associated message
	 *
	 * @param $friendship_id
	 * @param $initiator_user_id
	 * @param $user_id
	 */
	public function clean_message( $friendship_id, $initiator_user_id, $user_id ) {
		bp_ext_friend_request_delete_message( $user_id, $friendship_id );
	}

	/**
	 * Delete message on request withdraw
	 *
	 * @param $friendship_id
	 * @param $friendship
	 */
	public function delete_message_on_withdraw( $friendship_id, $friendship ) {
		// $friendship_id=$info[0];
		//$friendship=$info[1];
		bp_ext_friend_request_delete_message( $friendship->friend_user_id, $friendship_id );
	}

	/**
	 * Handle add remove friends event
	 *
	 */
	public function add_friend() {

		//handle the request and add friend
		if ( 'POST' !== strtoupper( $_SERVER['REQUEST_METHOD'] ) ) {
			return;
		}

		//we will use to echo the json data
		$messages = array();

		//validate nonce

		if ( ! check_ajax_referer( 'friends_add_friend' ) ) {

			$messages = array( 'message' => __( '<p>There was a problem, please try later!</p>', 'bp-extended-friendship-request' ) );
			echo $messages;
			exit( 0 );
		}

		if ( 'not_friends' == BP_Friends_Friendship::check_is_friend( bp_loggedin_user_id(), $_POST['fid'] ) ) {
			$friend_id = absint( $_POST['fid'] );
			//let us add the user
			if ( ! friends_add_friend( bp_loggedin_user_id(), $friend_id ) ) {
				$messages['message'] = __( '<p>Friendship could not be requested.</p>', 'bp-extended-friendship-request' );
			} else {
				$messages['message'] = __( '<p>Request sent Successfully!</p>', 'bp-extended-friendship-request' );
				$messages['button']  = '<a id="friend-' . $_POST['fid'] . '" class="remove" rel="remove" title="' . __( 'Cancel Friendship Request', 'bp-extended-friendship-request' ) . '" href="' . wp_nonce_url( bp_loggedin_user_domain() . bp_get_friends_slug() . '/requests/cancel/' . (int) $_POST['fid'] . '/', 'friends_withdraw_friendship' ) . '" class="requested">' . __( 'Cancel Friendship Request', 'bp-extended-friendship-request' ) . '</a>';
			}

		} else {
			$messages['message'] = __( 'Request Pending', 'bp-extended-friendship-request' );
		}

		echo json_encode( $messages );
		exit( 0 );
	}

	//since theme won't handle it, just a copy to make the theme handle it
	public function remove_friend() {
		// Bail if not a POST action
		if ( 'POST' !== strtoupper( $_SERVER['REQUEST_METHOD'] ) ) {
			return;
		}

		if ( 'is_friend' == BP_Friends_Friendship::check_is_friend( bp_loggedin_user_id(), $_POST['fid'] ) ) {
			check_ajax_referer( 'friends_remove_friend' );

			if ( ! friends_remove_friend( bp_loggedin_user_id(), $_POST['fid'] ) ) {
				echo __( 'Friendship could not be canceled.', 'bp-extended-friendship-request' );
			} else {
				echo '<a id="friend-' . $_POST['fid'] . '" class="add" rel="add" title="' . __( 'Add Friend', 'bp-extended-friendship-request' ) . '" href="' . wp_nonce_url( bp_loggedin_user_domain() . bp_get_friends_slug() . '/add-friend/' . $_POST['fid'], 'friends_add_friend' ) . '">' . __( 'Add Friend', 'bp-extended-friendship-request' ) . '</a>';
			}

		} elseif ( 'pending' == BP_Friends_Friendship::check_is_friend( bp_loggedin_user_id(), (int) $_POST['fid'] ) ) {
			check_ajax_referer( 'friends_withdraw_friendship' );

			if ( friends_withdraw_friendship( bp_loggedin_user_id(), (int) $_POST['fid'] ) ) {
				echo '<a id="friend-' . $_POST['fid'] . '" class="add" rel="add" title="' . __( 'Add Friend', 'bp-extended-friendship-request' ) . '" href="' . wp_nonce_url( bp_loggedin_user_domain() . bp_get_friends_slug() . '/add-friend/' . $_POST['fid'], 'friends_add_friend' ) . '">' . __( 'Add Friend', 'bp-extended-friendship-request' ) . '</a>';
			} else {
				echo __( "Friendship request could not be cancelled.", 'bp-extended-friendship-request' );
			}

		} else {
			echo __( 'Request Pending', 'bp-extended-friendship-request' );
		}

		exit;
	}

	//save the message with friendship
	public function save_friendship_request_message( $friendship_id, $initiator_user_id, $friend_user_id ) {
		$message = $_POST['friendship_request_message'];

		if ( ! empty( $message ) ) {
			bp_ext_friend_request_update_message( $friend_user_id, $friendship_id, $message );//save message for user
		}

	}
}

BPExtFriendShipActions::get_instance();