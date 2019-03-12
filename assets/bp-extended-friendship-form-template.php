<?php
// Do not allow direct access over web.
defined( 'ABSPATH' ) || exit;

?>

<div id="bp-extended-friendship-form-template" style="display: none;">

	<div class="bp-extended-friendship-form-container">
		<div class="bpdev-ext-friendship-popover-content">
			<textarea rows="5" cols="30" name="request_friend_message" class="request_friend_message" style="resize: none; height: 128px; max-width: 100%;"></textarea>
			<p class="request-friend-ext-button-wrap">
				<a class="btn button request-friend-ext-button" href="#"><?php _e('Send Request', 'bp-extended-friendship-request' );?></a>
			</p>
		</div>
	</div>
</div>
