;( function ( $ ) {

    var friend_id = 0, link = '', $button_friend = null, $popover = null;

    // Disable click on webui popver to hide theme toggle menus(Ib button groups are togglable, this stops issue).
    $(document).on('click', '.webui-popover-bp-extended-friendship-popup', function (event) {
        return false;
    });

    /* Add / Remove friendship buttons */
    $( document ).on( 'click', '.friendship-button-ext a', function () {
        var $btn = $( this );
        // reset.
        $button_friend = null;
        link = $btn.attr( 'href' );

        $btn.webuiPopover('destroy');

        var fid = $btn.attr( 'id' );
        fid = fid.split( '-' );
        friend_id = fid[1];

        var action = $btn.attr( 'rel' );
        // form template clone.
        var $template = $('#bp-extended-friendship-form-template .bp-extended-friendship-form-container').clone();

        // if this is add friend action
        // we will setup the popup box and show it.
        if (action === 'add') {
            $btn.webuiPopover({title: BPExtendedFriendshipRequest.title, content: $template.html(), style: 'bp-extended-friendship-popup', closeable:true,  animation: 'pop'});
            $btn.webuiPopover('show');
            $button_friend = $btn;
            $popover = getPopover( $btn);
            return false;
        }

        // if we are here, It is most probably a cancel friend request or withdraw friend request.
        $btn.parent().addClass( 'loading' );

        // this handles the cancel friendship request.
        var nonce = $btn.attr( 'href' );

        nonce = nonce.split( '?_wpnonce=' );
        nonce = nonce[1].split( '&' );
        nonce = nonce[0];

        $.post(ajaxurl, {
                action: 'ext_friend_remove_friend',
                'cookie': encodeURIComponent(document.cookie),
                'fid': friend_id,
                '_wpnonce': nonce
            },
            function (response) {

                var parentdiv = $btn.parent();

                if (action === 'remove') {
                    $( parentdiv ).fadeOut(200,
                        function () {
                            parentdiv.removeClass( 'remove_friend' );
                            parentdiv.removeClass( 'loading' );
                            parentdiv.addClass( 'add' );
                            parentdiv.fadeIn( 200 ).html( response );
                        }
                    );
                }
            });
        return false;
    });

    // bind the action to popup request button.
    // this is where we send the actual request for friendship.
    $( document ).on( 'click', '.bpdev-ext-friendship-popover-content a.button', function () {
        var $btn = $( this );

        $btn.addClass( 'loading' );

        var nonce = link.split( '?_wpnonce=' );
        nonce = nonce[1].split( '&' );
        nonce = nonce[0];

        $.post(ajaxurl, {
                action: 'ext_friend_add_friend',
                'friendship_request_message': $btn.parents('.bpdev-ext-friendship-popover-content').find('textarea').val(),
                'cookie': encodeURIComponent(document.cookie),
                'fid': friend_id,
                '_wpnonce': nonce
            },

            function (response) {
                $btn.removeClass( 'loading' );
                if (response.message !== undefined) {
                    if( $popover ) {
                        $popover.setContent(response.message);
                        $popover.displayContent();
                    }
                    if ( response.button !== undefined ) {
                     //   $btn.html( response.button );
                    }
                }

            }, 'json' );

        return false;
    });

    function getPopover($el) {
        var pluginName = 'webuiPopover';
        return $($el).data('plugin_' + pluginName);
    }

})(jQuery);