=== Global Post Password ===
Contributors: johnbillion
Tags: post, password, privacy
Requires at least: 2.1
Tested up to: 2.6.3
Stable tag: trunk

Enables you to set a global password for all password-protected posts (and pages).

== Description ==

If you publish a lot of password-protected posts (or even if you don't), you may end up using the same password for every post. With this plugin you can define a global post password from your 'Options -> Privacy' menu, and switch password protection on or off from the writing screen with just one click. When you change the global password, all password-protected posts are automatically updated with the new password.

Note that this plugin **does not** automatically enable password-protection on every post. It allows you to use a global password for every post that you choose to password-protect.

== Installation ==

This plugin works with WordPress version 2.1 or later.

1. Unzip the ZIP file and drop the folder straight into your `wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Visit the 'Options -> Privacy' menu in WordPress and set a global post password.

Now whenever you write or edit a post (or page) you'll be able to switch password-protection on or off with one click, instead of having to manually type in a password for each post. You can change the password whenever you like.

== Frequently Asked Questions ==

= Does this plugin automatically enable password-protection on every post? =

No. It allows you to use a global password for every post that you choose to password-protect.

= How do I change the global post password? =

Visit the 'Options -> Privacy' menu in WordPress and you'll see the option to change the password there.

= Do I have to have administrator privileges to change the global post password? =

Yes. Only users with the 'manage_options' capability can change the global post password. By default, only administrators have this capability.

= What if I forget the password? =

Visit the 'Options -> Privacy' menu in WordPress and the password will be displayed there. And don't forget it again.

= What's the correct way to remove the global post password? =

Be careful here. If you want to remove the global post password functionality, **do not** set a blank password under 'Options -> Privacy' as this will remove password protection from every post on your blog! (Unless of course that *is* what you want to do.) All you have to do is disable or uninstall the plugin and normal password-protection functionality will return (password-protected posts will remain protected with the password you last set under 'Options -> Privacy').

= How secure are password-protected posts? / Can people find out my password? / etc =

This plugin uses Wordpress' built-in password protection system and simply enables you to set the password for all password-protected posts globally. There are no known ways to read a password-protected post without knowing the password for it.

Your password will be stored in exactly the same manner as it would be if you set the password on a per-post basis - as unencrypted plain text in your database. It is no less secure than the normal password-protection system.

**Note:** When you visit the 'Options -> Privacy' menu in WordPress the password will be displayed to you in plain text, so make sure no-one's looking over your shoulder.

== Screenshots ==

1. The post writing screen (WordPress 2.3) where you can switch a post's password-protection on or off.

2. The post writing screen (WordPress 2.5) where you can switch a post's password-protection on or off.
