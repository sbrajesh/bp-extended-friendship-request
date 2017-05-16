<?php

/**
 * Plugin Name: BuddyPress Extended Friendship Request
 * Version: 1.1.1
 * Plugin URI: https://buddydev.com/plugins/bp-extended-friendship-request/
 * Author: BuddyDev Team
 * Author URI: https://buddydev.com
 * Contributor: Anu Sharma, Brajesh Singh
 * License: GPL
 * Description: Allows users to send a personalized message with the friendship request
 *
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit( 0 );
}
class BPExtFriendRequestHelper {

	private static $instance;

	private $path;
	private $url;

	/**
	 * Singleton
	 *
	 * @return BPExtFriendRequestHelper
	 */
	public static function get_instance() {

		if ( ! isset ( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	private function __construct() {

		$this->path = plugin_dir_path( __FILE__ );
		$this->url  = plugin_dir_url( __FILE__ );

		$this->setup();
	}

	private function setup() {

		add_action( 'bp_loaded', array( $this, 'load' ) );

		add_action( 'bp_init', array( $this, 'load_text_domain' ), 2 );

		add_filter( 'bp_get_add_friend_button', array( $this, 'filter_button' ) );

		add_action( 'bp_friend_requests_item', array( $this, 'show_message' ) );

		//load css
		add_action( 'bp_enqueue_scripts', array( $this, 'load_css' ) );
		//load js
		add_action( 'bp_enqueue_scripts', array( $this, 'load_js' ) );

		//load popup template
		add_action( 'wp_footer', array( $this, 'load_template' ) );
	}

	/**
	 * Load core files
	 *
	 */
	public function load() {

		$path = $this->path;

		$files = array(
			'core/bp-extended-friendship-request-functions.php',
			'core/class-bp-extended-friendship-request-action-handler.php',
		);

		foreach ( $files as $file ) {
			require_once $path . $file;
		}
	}

	/**
	 * Load plugin text domain for translation
	 */
	public function load_text_domain() {
		load_plugin_textdomain( 'bp-extended-friendship-request', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	}

	/**
	 * We are changing the wrapper class from friendship-button to friendship-button-ext to avoid the theme's js to attach event
	 *
	 * @param array $btn array of of button fields
	 *
	 * @return array $button
	 */
	public function filter_button( $btn ) {

		$wrapper_class = isset( $btn['wrapper_class'] ) ? $btn['wrapper_class'] : '';

		if ( $wrapper_class ) {
			$wrapper_class = str_replace( 'friendship-button', 'friendship-button-ext', $wrapper_class );
		}

		$btn['wrapper_class'] = $wrapper_class;

		return $btn;
	}

	/**
	 * Show message on requests page
	 */
	public function show_message() {

		$friendship_id = bp_get_friend_friendship_id();
		$user_id       = get_current_user_id();
		$message       = bp_ext_friend_request_get_message( $user_id, $friendship_id );

		//and we need to filter the output for malicious content
		global $allowedtags;

		$message_allowed_tags                  = $allowedtags;
		$message_allowed_tags['span']          = array();
		$message_allowed_tags['span']['class'] = array();
		$message_allowed_tags['div']           = array();
		$message_allowed_tags['div']['class']  = array();
		$message_allowed_tags['div']['id']     = array();
		$message_allowed_tags['a']['class']    = array();
		$message_allowed_tags['img']           = array();
		$message_allowed_tags['br']            = array();
		$message_allowed_tags['p']             = array();
		$message_allowed_tags['img']['src']    = array();
		$message_allowed_tags['img']['alt']    = array();
		$message_allowed_tags['img']['class']  = array();
		$message_allowed_tags['img']['width']  = array();
		$message_allowed_tags['img']['height'] = array();
		$message_allowed_tags['img']['class']  = array();
		$message_allowed_tags['img']['id']     = array();
		$message_allowed_tags['blockquote']    = array();

		$message_allowed_tags = apply_filters( 'bp_ext_friends_message_allowed_tags', $message_allowed_tags );
		echo wp_kses( $message, $message_allowed_tags );
	}

	/**
	 * Load required Js
	 */
	public function load_js() {
		//do not load js if user is not logged in
		if ( ! is_user_logged_in() ) {
			return;
		}

		wp_enqueue_script( 'bp-extended-friendship-request', $this->url . 'assets/js/bp-extended-friendship-request.js', array( 'jquery' ) );
	}

	//load css
	public function load_css() {
		//do not load css when user is not logged in
		if ( ! is_user_logged_in() ) {
			return;
		}

		wp_register_style( 'bp-extended-friendship-request', $this->url . 'assets/css/bp-extended-friendship-request.css' );

		wp_enqueue_style( 'bp-extended-friendship-request' );
	}

	/**
	 * Load the template for the modal box
	 * I know, we can handle it in js, but we may have some localization issue(not really, I just don't want to do it that way), so let us use it
	 * Our popup is modeled after twitter bootstrap's popover
	 * We are not using the js of twitter because it could not suit our requirement
	 * But I liked the look, so used the css/html for the popup from them
	 *
	 */
	public function load_template() {

		if ( ! is_user_logged_in() ) {
			return;
		}
		/**
		 * @todo let site admins define a template in dashboard
		 */
		$default_template = apply_filters( 'bp_ext_friendship_default_message', '' );
		?>
		<div class="bpdev-popover top">
			<span class="bpdev-popover-close">x</span>
			<div class="arrow"></div>
			<div class="bpdev-popover-inner">
				<h3 class="bpdev-popover-title"><?php _e( 'Request Friendship', 'bp-extended-friendship-request' ); ?></h3>
				<div class="bpdev-popover-content">
					<textarea rows="5" cols="27" name="request_friend_message"
					          class="request_friend_message"><?php echo $default_template; ?></textarea>
					<p class="request-friend-ext-button-wrap">
						<a  class="btn button request-friend-ext-button"
						   href="#"><?php _e( 'Send Request', 'bp-extended-friendship-request' ); ?>
						</a>
					</p>
				</div>
			</div>
		</div><!--end of popover -->
		<?php
	}
}

//instantiate the helper
BPExtFriendRequestHelper::get_instance();

