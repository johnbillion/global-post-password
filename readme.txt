=== Global Post Password ===
Contributors: johnbillion
Donate link: http://lud.icro.us/donations/
Tags: post, password, privacy
Requires at least: 2.8
Tested up to: 3.2
Stable tag: trunk

Enables you to globally set a password for all password protected posts (and pages).

== Description ==

If you publish a lot of password protected posts (or even if you don't), you may end up using the same password for every post. With this plugin you can define a global post password from your 'Settings -> Privacy' menu, and switch password protection on or off from the writing screen with just one click. When you change the global password, all password protected posts are automatically updated with the new password.

Note that this plugin **does not** automatically enable password protection on every post. It allows you to use a global password for every post that you choose to password protect.

== Installation ==

This plugin works with WordPress version 2.8 or later.

1. Unzip the ZIP file and drop the folder straight into your `wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Visit the 'Settings -> Privacy' menu in WordPress and set a global post password.

Now whenever you write or edit a post (or page) you'll be able to switch password protection on or off with one click, instead of having to manually type in a password for each post. You can change the password whenever you like from the 'Settings -> Privacy' menu.

== Frequently Asked Questions ==

= Does this plugin automatically enable password protection on every post? =

No. It allows you to use a global password for every post that you choose to password protect.

= Is the global password just for posts? =

Despite the name of the plugin, anything that is password protected on your blog will use the global password - this includes pages and custom post types as well as regular posts.

= How do I change the global post password? =

Visit the 'Settings -> Privacy' menu in WordPress and you'll see the option to change the password there.

= Do I have to have administrator privileges to change the global post password? =

Yes. Only users with the 'manage_options' capability can change the global post password. By default, only administrators have this capability.

= What if I forget the password? =

Visit the 'Settings -> Privacy' menu in WordPress and the password will be displayed there. And don't forget it again.

= What's the correct way to remove the global post password? =

If you want to remove the global post password functionality and return to per-post passwords, just disable or uninstall the plugin and normal password protection will return. Password protected posts will remain protected with the password you last set under 'Settings -> Privacy'.

= How secure are password protected posts? =

This plugin uses WordPress' built-in password protection system and simply enables you to set the password for all password protected posts globally. There are no known ways to read a password protected post without knowing the password for it.

= Can users log out of password protected posts? =

Yes. Try my <a href="http://wordpress.org/extend/plugins/logout-password-protected-posts/">Logout of Password Protected Posts</a> plugin which provides this functionality.

== Screenshots ==

1. Switching a post's password protection on or off.
2. The settings screen.

== Changelog ==

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

== Upgrade Notice ==

= 1.4.2 =
Add a body class of 'has-post-password' to individual posts when a user has entered the correct password.
