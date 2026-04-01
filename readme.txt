=== SRKP Live Support ===
Contributors: srkp
Donate link: https://github.com/keshavsharma262001-ops/srkp-live-support
Tags: live chat, support chat, customer support, real-time chat, helpdesk
Requires at least: 5.0
Requires PHP: 7.4
Tested up to: 6.9
Stable tag: 1.0.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A real-time live chat system for WordPress. Provide instant support to your users using Pusher-powered live messaging.

== Description ==

**SRKP Live Support** is a lightweight and powerful real-time chat plugin for WordPress.  
It allows website owners to communicate instantly with their visitors using a clean, responsive chat widget.

The plugin includes:

* Real-time chat using Pusher API  
* Admin dashboard chat management  
* Customizable widget colors  
* Custom support icon upload  
* Header/footer color control  
* Enable/Disable chat switch  
* Mobile responsive floating chat button  
* Secure architecture using WordPress database

This plugin is perfect for businesses that want to improve customer engagement directly from their website with zero coding required.

== External Services ==

This plugin connects to the Pusher Channels service to deliver real-time chat updates between visitors and the WordPress admin panel.

It sends:

* chat event payloads needed to deliver new messages in real time
* your configured Pusher app credentials from the site settings when the server authenticates API requests to Pusher

It does not automatically create a Pusher account. You must provide your own Pusher credentials in the plugin settings.

Service links:

* Pusher: https://pusher.com/
* Terms of Service: https://pusher.com/legal/terms
* Privacy Policy: https://pusher.com/legal/privacy-policy

== Features ==

*  Real-time Chat (Pusher integrated)  
*  Customizable chat UI (colors, icons, text)  
*  Live unread message count  
*  Fully mobile responsive  
*  Lightweight and optimized  
*  Works with any WordPress theme  
*  Admin can manage all user messages  
*  Secure – credentials stored safely in database  

== Installation ==

1. Upload the plugin folder `srkp-live-support` to the `/wp-content/plugins/` directory.  
2. Activate the plugin from **Plugins → Installed Plugins**.  
3. Go to **Settings → SRKP Live Support**.  
4. Enable chat support from the switch.  
5. Enter your Pusher App credentials.  
6. Customize colors, icon, widget text, and save changes.  
7. The floating chat widget will now appear on your website.

== Frequently Asked Questions ==

= Do I need Pusher credentials? =  
Yes. The plugin uses the free Pusher plan for real-time updates.

= Is Pusher free? =  
Yes. Pusher offers a **free plan** suitable for small websites and low chat traffic.

= Is bundled third-party JavaScript included? =
Yes. The plugin bundles Pusher JavaScript client files for runtime use. Readable source copies are included in:

* `admin/js/pusher.js`
* `public/js/pusher.js`

= Can I customize chat colors? =  
Absolutely — you can change header, footer, button, and chat colors.

= Does the plugin store chat history? =  
Yes, all chats are safely stored in your WordPress database.

== Screenshots ==

1. Admin settings screen for configuring live chat.
2. Frontend chat widget opened by the visitor.
3. Floating chat icon on the website.
4. Admin live messaging panel.

== Changelog ==

= 1.0.0 =
* Initial release.
* Real-time chat via Pusher.
* Admin message management.
* Chat widget settings (colors, text, icon).
* Floating chat support button.

== Upgrade Notice ==

= 1.0.0 =
Major first stable release of SRKP Live Support. Update recommended for full functionality.

== Credits ==

SRKP Live Support is created and maintained by SRKP.
It bundles the Pusher JavaScript client library under its upstream MIT license.

== A brief Markdown Example ==

Ordered list example:

1. Enable chat
2. Add Pusher credentials
3. Customize widget
4. Start supporting your customers

Unordered list example:

* Fast
* Secure
* Real-time
