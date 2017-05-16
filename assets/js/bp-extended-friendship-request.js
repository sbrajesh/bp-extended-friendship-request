/* 
 * I am in hurry and I could not find a popup box to suit my need
 * Twitter bootstrap popover looked great but it attaches to individual element, so I could not use it
 * Instead, I used their look and feel, so the css part is taken from them
 * Someday, I plan to write the below code in a jquery plugin, hoping that someday will come soon :)
 * 
 */

jQuery(document).ready(function () {

    var jq = jQuery;

    var popup = null;

    //this is a fallback template which we never expect to use
    var template = '<div class="bpdev-popover top"><span class="bpdev-popover-close">x</span><div class="arrow"></div><div class="bpdev-popover-inner"><h3 class="bpdev-popover-title"></h3><div class="bpdev-popover-content"><textarea name="request_friend_message" cols="27" rows="5" class="request_friend_message"></textarea><p><a class="button request-friend-ext-button" type="submit" herf="#">Send Request</a></p></div></div></div>';
    //
    //
    //initialize    
    //let us check if the popup exists
    if (jq('.bpdev-popover').get(0)) {

        popup = jq('.bpdev-popover');

        template = popup.find('.bpdev-popover-content').html();

    } else {

        jq('body').append(template);

        popup = jq('.bpdev-popover');

    }

    popup.hide();//by default, let us keep it hidden


    // Some Utility Functions //
    // 
    //sets the title of the popup
    function set_title(title) {

        popup.find('.bpdev-popover-title').text(title);

    }

    //sets the content of the modal
    function set_content(content) {

        popup.find('.bpdev-popover-content').html(content);

    }

    //repositions the popup box
    function set_position(left, top) {

        popup.animate({left: left, top: top}, 300, 'swing');

    }

    //just resets the state of the popup to the initial state
    function init_popup() {
        //reset the popup data
        set_content(template);
    }

    /**
     * realigns popup box with element
     */
    function realign_with_element(e) {
        var left = 0, top = 0;

        var offset = jq(e).offset();
        //offset left of the button-half the width of the popu box+half the width of the button

        left = offset.left - ( jq(popup).width() / 2 ) + jq(e).width() / 2 + 10;//+'px';

        top = offset.top - ( jq(popup).height() + 20 ) + 'px';

        var right = 0;
        right = left + jq(popup).width();

        var win_width = jq(document).width();


        if (win_width <= right) {
            //we need to adjust our left coordinate
            //how much?
            left = left - ( right - win_width + 20 );

            //make sure to add a class to popup to account for the resizing
            popup.addClass('bpdev-popover-aligned-right')
        }

        if (left < 0)
            left = 0;

        left = left + 'px';

        popup.css('left', left);

        popup.show();
        //animate the popup box and make visible
        set_position(left, top);
        //save the element
    }

    //close on clicking the close btn
    jq(document).on('click', '.bpdev-popover-close', function () {
        popup.hide(300, 'swing');
        popup.css({top: 0});

    });


    /* Add / Remove friendship buttons */
    jq(document).on('click', '.friendship-button-ext a', function () {

        init_popup();//reset any data we had earlier

        popup.hide();//hide the popup

        var fid = jq(this).attr('id');
        fid = fid.split('-');
        fid = fid[1];

        var thelink = jq(this);
        var action = thelink.attr('rel');
        //if this is add friend action
        //we will setup the popup box and show it
        if (action == 'add') {
            //setup the modal box

            popup.find('a.button').attr('href', jq(this).attr('href'));//save the link
            realign_with_element(this);//realign popup
            popup.data('btn', jq(this).parent().attr('id'));//save the id of the parent elemtn
            popup.data('fid', fid);   //save button id
            return false;//prevent propagation
        }

        //if we are here, It is most probably a cancel friend request or withdraw friend request

        jq(this).parent().addClass('loading');

        //this handles the cancel friendship request
        var nonce = jq(this).attr('href');

        nonce = nonce.split('?_wpnonce=');
        nonce = nonce[1].split('&');
        nonce = nonce[0];

        jq.post(ajaxurl, {
                action: 'ext_friend_remove_friend',
                'cookie': encodeURIComponent(document.cookie),
                'fid': fid,
                '_wpnonce': nonce
            },
            function (response) {

                var parentdiv = thelink.parent();

                if (action == 'remove') {
                    jq(parentdiv).fadeOut(200,
                        function () {
                            parentdiv.removeClass('remove_friend');
                            parentdiv.removeClass('loading');
                            parentdiv.addClass('add');
                            parentdiv.fadeIn(200).html(response);
                        }
                    );
                }
            });
        return false;
    });


    //bind the action to popup request button
    //this is where we send the actual request for friendship
    jq(document).on('click', '.bpdev-popover a.button', function () {

        jq(this).addClass('loading');

        var link = jq(this);

        var btn = jq(popup).data('btn');
        var fid = jq(popup).data('fid');

        var nonce = jq(this).attr('href');

        nonce = nonce.split('?_wpnonce=');
        nonce = nonce[1].split('&');
        nonce = nonce[0];


        jq.post(ajaxurl, {
                action: 'ext_friend_add_friend',
                'friendship_request_message': popup.find('textarea').val(),
                'cookie': encodeURIComponent(document.cookie),
                'fid': fid,
                '_wpnonce': nonce
            },

            function (response) {

                link.removeClass('loading');

                if (response.message != 'undefined') {

                    set_content(response.message);

                    if (response.button != undefined) {

                        jq('#' + btn).html(response.button);
                        realign_with_element('#' + btn);//realign the popup

                    }
                }

            }, 'json');

        return false;

    });

    jq( document ).on( "bp-ext-friendship-popover:close", function() {
       //close any active popover
        popup.hide(300, 'swing');
        popup.css({top: 0});
    });
    //bind the event to send request button

    /*
     jq(window).on('resize', function(){
     console.log('resized');
     })
     */
});//have a great day :)