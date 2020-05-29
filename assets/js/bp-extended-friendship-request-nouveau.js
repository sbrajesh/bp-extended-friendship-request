;(function ($) {
    $(document).ready(function () {

        if (typeof bp === "undefined" || typeof bp.Nouveau === "undefined" || typeof bp.Nouveau.buttonAction === "undefined") {
            return;
        }

        var friendID = 0, link = '', $buttonCurrent = null, $popover = null;

        // detach.
        $('#buddypress [data-bp-list]').unbind('click', bp.Nouveau.buttonAction);

        $('#buddypress [data-bp-list]').on('click', '[data-bp-btn-action]', function (event) {
            var target = $(event.currentTarget), action = target.data('bp-btn-action'),
                fid;
            // any action except the add friend should work as expected.
            // Proxy.
            if (action !== 'not_friends') {
                event.data = bp.Nouveau;
                bp.Nouveau.buttonAction.call(bp.Nouveau, event);
                return false;
            }

            // if we are here, It is our button.
            $buttonCurrent = null;
            link = target.attr('href');

            target.webuiPopover('destroy');

            fid = target.attr('id');
            fid = fid.split('-');
            friendID = fid[1];

            shoPoupFor(target);
            return false;
        });

        // Disable click on webui popver to hide theme toggle menus(Ib button groups are togglable, this stops issue).
        $(document).on('click', '.webui-popover-bp-extended-friendship-popup', function (event) {
            return false;
        });

        /**
         * Show popup for the given button.
         *
         * @param $button
         */
        function shoPoupFor($button) {
            // form template clone.
            var $template = $('#bp-extended-friendship-form-template .bp-extended-friendship-form-container').clone();

            $button.webuiPopover({
                title: BPExtendedFriendshipRequest.title,
                content: $template.html(),
                style: 'bp-extended-friendship-popup',
                closeable: true,
                animation: 'pop'
            });
            $button.webuiPopover('show');
            $buttonCurrent = $button;
            $popover = getPopover($button);
        }

        // bind the action to popup request button.
        // this is where we send the actual request for friendship.
        $(document).on('click', '.bpdev-ext-friendship-popover-content a.button', function () {
            var $button = $(this);

            $button.addClass('loading');

            var nonce = link.split('?_wpnonce=');
            nonce = nonce[1].split('&');
            nonce = nonce[0];

            $.post(ajaxurl, {
                    action: 'ext_friend_add_friend',
                    'friendship_request_message': $button.parents('.bpdev-ext-friendship-popover-content').find('textarea').val(),
                    'cookie': encodeURIComponent(document.cookie),
                    'item_id': friendID,
                    'nonce': nonce,
                    '_wpnonce': nonce
                },

                function (response) {
                    $button.removeClass('loading');
                    if (response.data && response.data.feedback !== undefined) {
                        if ($popover) {
                            $popover.setContent(response.data.feedback);
                            $popover.displayContent();
                        }
                    } else {
                        $popover.hide();
                    }
                    bbAction(response);

                }, 'json');

            return false;
        });

        /**
         * Get popover associated with an element.
         *
         * @param $el
         * @returns {jQuery}
         */
        function getPopover($el) {
            var pluginName = 'webuiPopover';
            return $($el).data('plugin_' + pluginName);
        }

        /**
         * BuddyBoss specific post response processing.
         *
         * @param response
         */
        function bbAction(response) {
            var target = $buttonCurrent;

            var item = target.closest('[data-bp-item-id]'),
                item_inner = target.closest('.list-wrap');

            if (false === response.success) {
                item_inner.prepend(response.data.feedback);
                target.removeClass('pending loading');
                item.find('.bp-feedback').fadeOut(6000);
            } else {
                target.parent().replaceWith(response.data.contents);
            }
        }
    });

})(jQuery);