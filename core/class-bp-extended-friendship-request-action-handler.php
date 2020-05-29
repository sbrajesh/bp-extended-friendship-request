<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit( 0 );
}

/**
 * This class handles various ajax/non ajax actions related to the friendship
 *
 * The classname is a mistake from past, unable to change due to Back compatibility with codes shared on our forums.
 */
class BPExtFriendShipActions {

	/**
	 * Singleton instance.
	 *
	 * @var BPExtFriendShipActions
	 */
	private static $instance;

	/**
	 * Constructor.
	 */
	private function __construct() {

		// handle add friend.
		add_action( 'wp_ajax_ext_friend_add_friend', array( $this, 'add_friend' ) );
		// remove friends.
		add_action( 'wp_ajax_ext_friend_remove_friend', array( $this, 'remove_friend' ) );

		add_action( 'friends_friendship_requested', array( $this, 'save_friendship_request_message' ), 10, 3 );
		// clean on accepted.
		add_action( 'friends_friendship_accepted', array( $this, 'clean_message' ), 10, 3 );
		// clean on rejected.
		add_action( 'friends_friendship_rejected', array( $this, 'delete_message_on_withdraw' ), 10, 2 );
		// clean on withdraw.
		add_action( 'friends_friendship_withdrawn', array( $this, 'delete_message_on_withdraw' ), 10, 2 );
	}

	/**
	 * Get the singleton instance.
	 *
	 * @return BPExtFriendShipActions
	 */
	public static function get_instance() {

		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Delete associated message
	 *
	 * @param int $friendship_id friendship id.
	 * @param int $initiator_user_id initiator user id.
	 * @param int $user_id friend use id.
	 */
	public function clean_message( $friendship_id, $initiator_user_id, $user_id ) {
		bp_ext_friend_request_delete_message( $user_id, $friendship_id );
	}

	/**
	 * Delete message on request withdraw
	 *
	 * @param int                   $friendship_id friendship id.
	 * @param BP_Friends_Friendship $friendship Friendship object.
	 */
	public function delete_message_on_withdraw( $friendship_id, $friendship ) {
		bp_ext_friend_request_delete_message( $friendship->friend_user_id, $friendship_id );
	}

	/**
	 * Handle add remove friends event
	 */
	public function add_friend() {
		//echo  did_action( 'admin_init');
		//exit(0);
		//if ( function_exists( 'bp_nouveau_ajax_addremove_friend' ) ) {

		if ( function_exists( 'bp_nouveau' ) ) {
			if ( ! function_exists( 'bp_nouveau_ajax_addremove_friend' ) ) {
				require bp_nouveau()->friends->dir . 'ajax.php';
			}

			$_POST['action'] = 'friends_add_friend';
			bp_nouveau_ajax_addremove_friend();
		}

		// handle the request and add friend.
		if ( 'POST' !== strtoupper( $_SERVER['REQUEST_METHOD'] ) ) {
			return;
		}

		// we will use to echo the json data.
		$messages = array();

		if ( ! check_ajax_referer( 'friends_add_friend' ) ) {

			$messages = array(
				'error'   => true,
				'message' => __( '<p>There was a problem, please try later!</p>', 'bp-extended-friendship-request' ),
			);
			echo json_encode( $messages );
			exit( 0 );
		}


		if ( 'not_friends' == BP_Friends_Friendship::check_is_friend( bp_loggedin_user_id(), $_POST['fid'] ) ) {
			$friend_id = absint( $_POST['fid'] );
			// let us add the user.
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

	// since theme won't handle it, just a copy to make the theme handle it.

	/**
	 * Handle remove friend.
	 */
	public function remove_friend() {
		// Bail if not a POST action.
		if ( 'POST' !== strtoupper( $_SERVER['REQUEST_METHOD'] ) ) {
			return;
		}

		$friend_id = isset( $_POST['fid'] ) ? absint( $_POST['fid'] ) : 0;

		if ( 'is_friend' === BP_Friends_Friendship::check_is_friend( bp_loggedin_user_id(), $friend_id ) ) {
			check_ajax_referer( 'friends_remove_friend' );

			if ( ! friends_remove_friend( bp_loggedin_user_id(), $friend_id ) ) {
				echo __( 'Friendship could not be canceled.', 'bp-extended-friendship-request' );
			} else {
				echo '<a id="friend-' . $friend_id . '" class="add" rel="add" title="' . __( 'Add Friend', 'bp-extended-friendship-request' ) . '" href="' . wp_nonce_url( bp_loggedin_user_domain() . bp_get_friends_slug() . '/add-friend/' . $friend_id, 'friends_add_friend' ) . '">' . __( 'Add Friend', 'bp-extended-friendship-request' ) . '</a>';
			}
		} elseif ( 'pending' === BP_Friends_Friendship::check_is_friend( bp_loggedin_user_id(), $friend_id ) ) {
			check_ajax_referer( 'friends_withdraw_friendship' );

			if ( friends_withdraw_friendship( bp_loggedin_user_id(), $friend_id ) ) {
				echo '<a id="friend-' . $friend_id . '" class="add" rel="add" title="' . __( 'Add Friend', 'bp-extended-friendship-request' ) . '" href="' . wp_nonce_url( bp_loggedin_user_domain() . bp_get_friends_slug() . '/add-friend/' . $friend_id, 'friends_add_friend' ) . '">' . __( 'Add Friend', 'bp-extended-friendship-request' ) . '</a>';
			} else {
				echo __( 'Friendship request could not be cancelled.', 'bp-extended-friendship-request' );
			}
		} else {
			echo __( 'Request Pending', 'bp-extended-friendship-request' );
		}

		exit;
	}

	/**
	 * Save message with request.
	 *
	 * @param int $friendship_id friendship id.
	 * @param int $initiator_user_id user id.
	 * @param int $friend_user_id friend id.
	 */
	public function save_friendship_request_message( $friendship_id, $initiator_user_id, $friend_user_id ) {
		$message = isset( $_POST['friendship_request_message'] ) ? $_POST['friendship_request_message'] : '';

		if ( ! empty( $message ) ) {
			// save message for user.
			bp_ext_friend_request_update_message( $friend_user_id, $friendship_id, $message );
		}

	}
}

BPExtFriendShipActions::get_instance();