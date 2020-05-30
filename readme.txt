=== BuddyPress Extended Friendship Request ===
Contributors: buddydev,sbrajesh, anusharma
Tags: buddypress, social, friends, friendship
Requires at least: 5.0
Tested up to: 5.5
Stable tag: 1.2.1
License: GPLv2 
License URI: http://www.gnu.org/licenses/gpl-2.0.html

BuddyPress Extended Friendship Request plugin allows users to send a personalized message with the friendship requests.

== Description ==

BuddyPress Extended Friendship Request plugin allows users to send a personalized message with the friendship request on BuddyPress based Social Networks.

= How it works:- =

When a users clicks on Add friend, It shows him/her a small popup to enter some personalized message.
The user can enter a personalized message and click on the Send request to send the request.

= Credit =
 Version 1.2+ uses [WebUI-Popover](https://github.com/sandywalker/webui-popover) by Sandy Duan.

= More Plugins =
We love BuddyPress and we have created 100+ BuddyPress plugins.
Please take a look at our
 1. [Free BuddyPress Plugins](https://buddydev.com/plugins/  "Best BuddyPress Plugins")
 1. [Premium BuddyPress plugins](https://buddydev.com/plugins/category/buddypress-premium-plugins/ "Best BuddyPress Premium Plugins")
 We hope that it will help you take your BuddyPress network to the next level.


= BuddyPress Custom development & Maintenance Service =
If you need any assistance with setting up or adding new features to BuddyPress or this plugin, Our team is available for hire.
Please use our [BuddyPress Development Services](https://buddydev.com/buddypress-custom-plugin-development-service/) for any custom development needs.

== Installation ==

This section describes how to install the plugin and get it working.

1. Download the zip file and extract
1. Upload `buddypress-extended-friendship-request` directory to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress( Network activate if you are on multisite )
1. Enjoy

== Frequently Asked Questions ==

= Does This plugin works without BuddyPress =
No, It needs you to have BuddyPress Installed and activated

= What is the supported BuddyPress Version? =
2.9+, Tested with 4.2.

= Where do I get support? =
Please use [BuddyDev support](https://buddydev.com/support/forums/) forums.

= Can I hire you for BuddyPress development? =
We will love to work with you. Please let us know if you need any of our [services](https://buddydev.com/services/).

== Screenshots ==

1. This shows sending a friendship request screenshot-1.png
2. This shows successful friendship request screenshot-2.png

== Changelog ==

= 1.2.1 =
 * Added compatibility with BP Nouveau & BuddyBoss platform.

= 1.2.0 =
 * Used webui popover. Fixes various issues related to placement of the popup.

= 1.1.1 =
 * Allow using custom js event "bp-ext-friendship-popover:close" to close any active popover.
    You can trigger it like jQuery(document).trigger("bp-ext-friendship-popover:close" );

= 1.1.0 =
 * Fix issue with friendship request email notification.

= 1.0.9 =
 * add filter 'bp_ext_friendship_default_message' to allow adding default message.

= 1.0.8 =
 * Make translation files loading inline with wp standard
 * Partial Code refactoring

= 1.0.7 =
 * Fixed notice when wrapper-class is not set on buttons
 * A small bit of code cleanup again

= 1.0.6 =
 * Fixed to show popup even on reloading via ajax
 * Remove the easeOutQuad easing with swing to avoid js error
 
= 1.0.5 =
 * Fixed Fatal error: Cannot unset string offsets in bp-extended-friendship-request.php
 * Removes javascript debug info in the console
 

= 1.0.4 =
 * Fixes translation problems on ajax responses. Now the translations will work fine.
 * Removes javascript debug info in the console
 
= 1.0.3 =
 * Fix a notice message when request was removed
 * Adds basic support for mobile devices

= 1.0.2 =
 * Fix a possible security issue with displaying message
= 1.0.1 =
 * Added Support for localization, thanks to the efforts of Anu Sharma(@anusharma).
= 1.0 =
* Initial release for BuddyPress 1.6+

== Other Notes ==
We appreciate your thoughts and suggestions. Please leave a comment on [BuddyDev](https://buddydev.com/buddypress/introducing-buddypress-extended-friendship-request-plugin/)