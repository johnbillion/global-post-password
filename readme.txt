=== Global Post Password ===
Contributors: johnbillion
Tags: post, password, privacy
Requires at least: 4.1
Tested up to: 4.5
Stable tag: trunk

Globally set a password for all password protected posts and pages.

== Description ==

If you publish many password protected posts (or even if you don't), you may end up using the same password for each one. With this plugin you can define a global password from your 'Settings -> Global Post Password' menu, and switch password protection on or off from the writing screen with just one click. Whenever you change the global password, all password protected posts are automatically updated with the new password.

Now whenever you write or edit a post or page you'll be able to switch password protection on or off with one click, instead of having to manually type in a password each time. You can change the global password whenever you like from the 'Settings -> Global Post Password' menu.

Note that this plugin **does not** automatically enable password protection on every post. It allows you to use a global password for every post that you choose to password protect.

Please note that this plugin is **no longer actively maintained**. The global post password setting does not work with the block-based editor in WordPress and it's unlikely that I'll add support for it unless someone else volunteers to develop it. It does work with the "Classic" editor and with versions of WordPress prior to 5.0.

== Frequently Asked Questions ==

= Does this plugin automatically enable password protection on every post? =

No. It allows you to use a global password for every post that you choose to password protect.

= Is the global password just for posts? =

Despite the name of the plugin, anything that is password protected on your site will use the global password - this includes posts, pages, and custom post types.

= How do I change the global password? =

Visit the 'Settings -> Global Post Password' menu in WordPress.

= Do I have to have administrator privileges to change the global password? =

Yes. Only users with the `manage_options` capability can change the global password. By default, only Administrators have this capability.

= What if I forget the password? =

Visit the 'Settings -> Global Post Password' menu in WordPress and the password will be displayed there. And don't forget it again.

= What's the correct way to remove the global password? =

If you want to remove the global password functionality and return to per-post passwords, just disable or uninstall the plugin and normal password protection will return. Password protected posts will remain protected with the password you last set under 'Settings -> Global Post Password'.

= How secure are password protected posts? =

This plugin uses WordPress' built-in password protection system and simply enables you to set the password for all password protected posts globally. There are no known ways to read a password protected post without knowing the password for it.

= Does this work with the block-based editor (Gutenberg)? =

No. It does work with the "Classic" editor and with versions of WordPress prior to 5.0.

= Can users log out of password protected posts? =

Yes. Try my <a href="https://wordpress.org/plugins/logout-password-protected-posts/">Log Out of Password Protected Posts</a> plugin which provides this functionality.

== Screenshots ==

1. Switching password protection on or off.
2. The settings screen.

== Upgrade Notice ==

= 1.5.1 =
Various bugfixes and tweaks.

== Changelog ==

= 1.5.1 =
* When passing a password via the URL parameter, ensure the password cookie's behavior matches that of core's.
* Correctly handle passwords which need to be encoded when used in the URL parameter.
* Add escaping to all translated text.
* Various code quality tweaks.

= 1.5 =
* WordPress 3.4 and 3.5 compatibility.
* WordPress 3.4 or later is now a requirement.

= 1.4.2 =
* Add a body class of 'has-post-password' to individual posts when a user has entered the correct password.

= 1.4.1 =
* Additional protection so you can't accidentally remove password protection from all your posts.

= 1.4 =
* Full compatibility with Quick Edit.
* New settings that enable using post passwords in permalinks and feed URLs.
* WordPress 2.8 or later is now a requirement.

= 1.3 =
* Introduction of (optional) multiple global passwords.
* Removal of redundant radio buttons on post editing screen.
* Support for Quick Edit.
* WordPress 2.7 or later is now a requirement.

= 1.2 =
* WordPress 2.7 compatibility.

= 1.1 =
* WordPress 2.6 compatibility.

= 1.0 =
* Initial release.
