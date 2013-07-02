<?php
/**
 * Plugin Name: BuddyPress Extended Friendship Request 
 * Version: 1.0.2
 * Plugin URI: http://buddydev.com/plugins/bp-extended-friendship-request/
 * Author: Brajesh Singh
 * Contributor: Anu Sharma
 * License: GPL
 * Description: Allows users to send a personalized message with the friendship request
 * 
 */

class BPExtFriendRequestHelper{
    
    private static $instance;
    
    private function __construct(){
        if(is_admin()||  is_network_admin())
            return;//we don't want anything in backend
        //load css
        add_action('wp_print_styles',array($this,'load_css'));
        //load js
        add_action('wp_print_scripts',array($this,'load_js'));
        
        //load popup template
        add_action('wp_footer',array($this,'load_template'));
       
        
        add_filter('bp_get_add_friend_button',array($this,'filter_button'));
        
        add_action('bp_friend_requests_item',array($this,'show_message'));
        
         //load text domain
        add_action ( 'bp_loaded', array($this,'load_textdomain'), 2 );
        
    }
    
    public static function get_instance(){
    
        if(!isset (self::$instance))
                self::$instance=new self();
        
        return self::$instance;
    }
    /**
     * Load plugin textdomain for translation
     */
    function load_textdomain(){
         $locale = apply_filters( 'bp-extended-friendship-request_get_locale', get_locale() );
        
      
	// if load .mo file
	if ( !empty( $locale ) ) {
		$mofile_default = sprintf( '%slanguages/%s.mo', plugin_dir_path(__FILE__), $locale );
              
		$mofile = apply_filters( 'bp-ext_fr_load_textdomain_mofile', $mofile_default );
		
                if (is_readable( $mofile ) ) {
                    // make sure file exists, and load it
			load_textdomain( 'bp-ext-friends-request', $mofile );
		}
	}
       
    }
    /**
     * We are changing the wrapper class from friendship-button to friendship-button-ext to avoild the theme's js to attach event
     * 
     * @param array $btn arraof of button fields
     * @return type array $button
     */
    function filter_button($btn){
        
        $wrapper_class=$btn['wrapper_class'];
        $wrapper_class=str_replace('friendship-button', 'friendship-button-ext', $wrapper_class);
        $btn['wrapper_class']=$wrapper_class;
        return $btn;
    }
    
    /**
     * Show message on requests page
     */
    function show_message(){
        $friendship_id=bp_get_friend_friendship_id();
        $user_id=get_current_user_id();
        $message=bp_ext_friend_request_get_message($user_id,$friendship_id);
        
        //and we need to filter the output for malicious content
        global $allowedtags;
        $message_allowed_tags = $allowedtags;
        $message_allowed_tags['span'] = array();
        $message_allowed_tags['span']['class'] = array();
        $message_allowed_tags['div'] = array();
        $message_allowed_tags['div']['class'] = array();
        $message_allowed_tags['div']['id'] = array();
        $message_allowed_tags['a']['class'] = array();
        $message_allowed_tags['img'] = array();
        $message_allowed_tags['br'] = array();
        $message_allowed_tags['p'] = array();
        $message_allowed_tags['img']['src'] = array();
        $message_allowed_tags['img']['alt'] = array();
        $message_allowed_tags['img']['class'] = array();
        $message_allowed_tags['img']['width'] = array();
        $message_allowed_tags['img']['height'] = array();
        $message_allowed_tags['img']['class'] = array();
        $message_allowed_tags['img']['id'] = array();
        $message_allowed_tags['blockquote'] = array();
        
        $message_allowed_tags =  apply_filters('bp_ext_friends_message_allowed_tags',$message_allowed_tags);
        echo wp_kses($message,$message_allowed_tags);
    }
    
    
    
    /**
     * Load required Js
     */
    public function load_js(){
        //do not load js if user is not logged in
        if(!is_user_logged_in())
            return;
       wp_enqueue_script('add-friend',  plugin_dir_url(__FILE__).'_inc/js/bp-ext-friend.js',array('jquery'));
    }
    
    //load css
    public function load_css(){
        //do not load css when user is not logged in
        if(!is_user_logged_in())
            return;
        wp_register_style('add-friend-css',  plugin_dir_url(__FILE__).'_inc/css/bp-ext-friend.css');
        wp_enqueue_style('add-friend-css');
    }
    /**
     * Load the template for the modal box
     * I know, we can handle it in js, but we may have some localization issue(not really, I just don't want to do it that way), so let us use it
     * Our popup is modeled after twitter bootstrap's pophover
     * We are not using the js of twitter because it could not suit our requirement
     * But I liked the look, so used the css/html for the popup from them
     * 
     */
    public function load_template(){
        if(!is_user_logged_in())
            return;
        ?>
        <div class="bpdev-popover top">
            <span class="bpdev-popover-close">x</span>
            <div class="arrow"></div>
            <div class="bpdev-popover-inner">
                <h3 class="bpdev-popover-title"><?php _e('Request Friendship','bp-ext-friends-request');?></h3>
                <div class="bpdev-popover-content">
                    <textarea rows="5" cols="27" name="request_friend_message" class="request_friend_message"></textarea>
                    <p>
                        <a  type="submit" class="button request-friend-ext-button" href="#"><?php _e('Send Request','bp-ext-friends-request');?></a>
                    </p>
                </div>
            </div>
        </div><!--end of popover -->
    <?php
    
        
    }

   
   
    
}

//instantiate the helper
BPExtFriendRequestHelper::get_instance();
/**
 * This class handles various ajax/non ajax actions related to the friendship
 */
class BPExtFriendShipActions{
    private static $instance;
    
    private function __construct(){
       
        
        //handle add friend
        add_action('wp_ajax_ext_friend_add_friend',array($this,'add_friend'));
        //remove friends
        add_action('wp_ajax_ext_friend_remove_friend',array($this,'remove_friend'));
        
        add_action('friends_friendship_requested',array($this,'save_friendship_request_message'),10,3);
        //clean on accepted
        add_action('friends_friendship_accepted',array($this,'clean_message'),10,3);
        //clean on rejected
        add_action('friends_friendship_rejected',array($this,'delete_message_on_withdraw'));
        //clean on withdraw
        add_action('friends_friendship_whithdrawn',array($this,'delete_message_on_withdraw'));
       
         
    }
    
    public static function get_instance(){
    
        if(!isset (self::$instance))
                self::$instance=new self();
        
        return self::$instance;
    }
    
    function clean_message( $friendship_id, $initiator_user_id, $user_id ){
        bp_ext_friend_request_delete_message($user_id, $friendship_id);
    }
    function delete_message_on_withdraw($info){
        $friendship_id=$info[0];
        $friendship=$info[1];
        bp_ext_friend_request_delete_message($friendship->friend_user_id, $friendship_id);
        
    }
    /**
     * Handle add remove friends event
     * 
     * @return type 
     */
    
    function add_friend(){
        
        //handle the request and add friend
       if ( 'POST' !== strtoupper( $_SERVER['REQUEST_METHOD'] ) )
		return;
       //we will use to echo the json data
        $messages=array();
	//validate nonce
        if(!check_ajax_referer( 'friends_add_friend' )){
                        $messages=array('message'=>__('<p>There was a problem, please try later!</p>','bp-ext-friends-request'));
                        echo $messages;
                        exit(0);
                } 
                
       
       if ( 'not_friends' == BP_Friends_Friendship::check_is_friend( bp_loggedin_user_id(), $_POST['fid'] ) ) {
                 
            //let us add the user 
            

		if ( ! friends_add_friend( bp_loggedin_user_id(), $_POST['fid'] ) )
			$messages['message']= __('<p>Friendship could not be requested.</p>', 'bp-ext-friends-request' );
		else{
                    $messages['message']=__('<p>Request sent Successfully!</p>','bp-ext-friends-request');
                    $messages['button']='<a id="friend-' . $_POST['fid'] . '" class="remove" rel="remove" title="' . __( 'Cancel Friendship Request', 'bp-ext-friends-request' ) . '" href="' . wp_nonce_url( bp_loggedin_user_domain() . bp_get_friends_slug() . '/requests/cancel/' . (int) $_POST['fid'] . '/', 'friends_withdraw_friendship' ) . '" class="requested">' . __( 'Cancel Friendship Request', 'bp-ext-friends-request') . '</a>';
                 
                        
                }        
	} else {
		$messages['message']= __( 'Request Pending', 'bp-ext-friends-request');
	}
        echo json_encode($messages);
        exit(0);
    }
    
    //since theme won't handle it, just a copy to make the theme handle it
    function remove_friend(){
        // Bail if not a POST action
	if ( 'POST' !== strtoupper( $_SERVER['REQUEST_METHOD'] ) )
		return;

	if ( 'is_friend' == BP_Friends_Friendship::check_is_friend( bp_loggedin_user_id(), $_POST['fid'] ) ) {
		check_ajax_referer( 'friends_remove_friend' );

		if ( ! friends_remove_friend( bp_loggedin_user_id(), $_POST['fid'] ) )
			echo __( 'Friendship could not be canceled.', 'bp-ext-friends-request' );
		else
			echo '<a id="friend-' . $_POST['fid'] . '" class="add" rel="add" title="' . __( 'Add Friend', 'bp-ext-friends-request' ) . '" href="' . wp_nonce_url( bp_loggedin_user_domain() . bp_get_friends_slug() . '/add-friend/' . $_POST['fid'], 'friends_add_friend' ) . '">' . __( 'Add Friend', 'bp-ext-friends-request' ) . '</a>';

	} elseif ( 'pending' == BP_Friends_Friendship::check_is_friend( bp_loggedin_user_id(), (int) $_POST['fid'] ) ) {		
		check_ajax_referer( 'friends_withdraw_friendship' );

		if ( friends_withdraw_friendship( bp_loggedin_user_id(), (int) $_POST['fid'] ) )
			echo '<a id="friend-' . $_POST['fid'] . '" class="add" rel="add" title="' . __( 'Add Friend', 'bp-ext-friends-request') . '" href="' . wp_nonce_url( bp_loggedin_user_domain() . bp_get_friends_slug() . '/add-friend/' . $_POST['fid'], 'friends_add_friend' ) . '">' . __( 'Add Friend','bp-ext-friends-request' ) . '</a>';
		else
			echo __("Friendship request could not be cancelled.", 'bp-ext-friends-request');

	} else {
		echo __( 'Request Pending', 'bp-ext-friends-request' );
	}

	exit;
    }
     
    //save the message with friendship
    function save_friendship_request_message($friendship_id, $initiator_user_id, $friend_user_id){
                
        $message=$_POST['friendship_request_message'];
       
        if(!empty($message))
            bp_ext_friend_request_update_message ($friend_user_id, $friendship_id, $message);//save message for user
      //  update_user_meta()
            
    }
    
}

BPExtFriendShipActions::get_instance();
/**
 * Functions
 */

/**
 * Get the message associated with this friendship request
 * @param type $user_id
 * @param type $friendship_id
 * @return type 
 */
function bp_ext_friend_request_get_message($user_id,$friendship_id){
    $key=bp_ext_friend_request_get_message_key();
    $messages=bp_get_user_meta($user_id, $key,true);
    if(isset ($messages[$friendship_id]))
        return $messages[$friendship_id];
    return '';

}
/**
 * Update the message associated with this friendship request
 * We are actually saving the message for the requested user
 * @param type $user_id
 * @param type $friendship_id
 * @param type $message 
 */
function bp_ext_friend_request_update_message($user_id,$friendship_id,$message){
    $key=bp_ext_friend_request_get_message_key();
    $messages=bp_get_user_meta($user_id, $key,true);
    if(empty( $messages))
         $messages=array();

    $messages[$friendship_id]=$message;

     bp_update_user_meta($user_id, $key,$messages);
}
/**
 * Delete the message for a perticular friendship id
 * @param type $user_id
 * @param type $friendship_id 
 */
function bp_ext_friend_request_delete_message($user_id,$friendship_id){
    $key=bp_ext_friend_request_get_message_key();
    $messages=bp_get_user_meta($user_id, $key,true);
    unset($messages[$friendship_id]);
    if(!empty($messages))
    $messages=array_filter($messages);//filter out empty array

    bp_update_user_meta($user_id, $key,$messages);

}
/**
 * The user meta key name which we use to store the messages
 * I just wanted to avoid hardcoding it multiple times.
 * @return string key name
 */
function bp_ext_friend_request_get_message_key(){
    return 'friendship_request_messages';
}
?>