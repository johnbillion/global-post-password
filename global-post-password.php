<?php
/*
Plugin Name: Global Post Password
Description: Enables you to define a global password for all password-protected posts. <a href="options-privacy.php">Click here to change the password</a>.
Plugin URI:  http://lud.icro.us/wordpress-plugin-global-post-password/
Version:     1.2
License:     GNU General Public License
Author:      John Blackbourn
Author URI:  http://johnblackbourn.com/

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 2 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

*/

class GlobalPostPassword {

	function GlobalPostPassword() {
		if ( strpos($_SERVER['REQUEST_URI'], 'options-privacy.php') ) {
			add_action('admin_head',   array(&$this, 'start_buffer'));
			add_action('admin_footer', array(&$this, 'end_buffer'));
			add_action('blog_privacy_selector', array(&$this, 'options_screen'));
		}
		elseif ( strpos($_SERVER['REQUEST_URI'], 'edit.php') or
		         strpos($_SERVER['REQUEST_URI'], 'post-new.php') or
		         strpos($_SERVER['REQUEST_URI'], 'page-new.php') or
		         strpos($_SERVER['REQUEST_URI'], 'post.php'    ) or
		         strpos($_SERVER['REQUEST_URI'], 'page.php'    ) ) {
			add_action('admin_head',   array(&$this, 'start_buffer'));
			add_action('admin_footer', array(&$this, 'end_buffer'));
		}
		add_action('update_option_globalpostpassword', array(&$this, 'update_passwords'));
		# WP 2.7 specific:
		add_filter('whitelist_options', array( &$this, 'whitelist_options' ));
	}

	function whitelist_options( $list ) {
		$list['privacy'][] = 'globalpostpassword';
		return $list;
	}

	function buffer( $content ) {
		global $post_ID;
		if ( strpos($_SERVER['REQUEST_URI'], 'options-privacy.php') )
			$content = str_replace('name="page_options" value="', 'name="page_options" value="globalpostpassword,', $content);
		else {
			$p = get_post($post_ID);
			$pw = get_option('globalpostpassword');
			$chk = ( $p->post_password != '' ) ? 'checked="checked"' : '';
			$replace = "<label class='selectit'><input type='radio' name='post_password' value='$pw' $chk />" . __(' On ', 'g_p_p') . '</label>';
			$chk = ( $chk ? '' : 'checked="checked"' );
			$replace .= "<label class='selectit'><input type='radio' name='post_password' value='' $chk />" . __(' Off ', 'g_p_p') . '</label>';
			$content = preg_replace('/<input (type="text" )?name="post_password".*?\/>/i', $replace, $content);
			# WP 2.3 specific:
			$content = str_replace('<h3 class="dbx-handle">' . __('Post Password') . '</h3>', '<h3 class="dbx-handle">' . __('Password Protection', 'g_p_p') . '</h3>', $content);
			# WP 2.5 specific:
			$content = str_replace('<h3>' . __('Password Protect This Post') . '</h3>', '<h3 class="dbx-handle">' . __('Password Protection', 'g_p_p') . '</h3>', $content);
			$content = str_replace(__('Setting a password will require people who visit your blog to enter the above password to view this post and its comments.'), __('Enabling password protection will require people who visit your blog to enter the global post password (<a href="options-privacy.php">set here</a>) to view this post and its comments.', 'g_p_p'), $content);
			}
		return $content;
	}

	function start_buffer() {
		?>
		<style type="text/css"><!--

		#passworddiv input {
			margin-top: inherit;
			width: inherit;
		}

		#passworddiv label {
			display: block;
		}

		--></style>
		<?php
		ob_start(array(&$this, 'buffer'));
	}

	function end_buffer() {
		ob_end_flush();
	}

	function options_screen() {
		?>
	</td>
	</tr>
	<tr valign="top">
	<th scope="row"><?php _e('Global Post Password:', 'g_p_p'); ?></th>
	<td><input name="globalpostpassword" type="text" value="<?php form_option('globalpostpassword'); ?>" />
	<p><?php _e('Any posts which are password-protected will use this password', 'g_p_p'); ?></p>
	<p><?php _e('<strong>Warning:</strong> Setting the password to a blank value will switch off password protection on all posts! If you wish to remove the global password functionality and return to per-post passwords, simply disable or uninstall the Global Post Password plugin.', 'g_p_p'); ?></p>
		<?php
	}

	function update_passwords() {
		global $wpdb;
		$pw = get_option('globalpostpassword');
		$wpdb->query("UPDATE {$wpdb->posts} SET `post_password` = '$pw' WHERE `post_password` <> ''");
	}

}

$g_p_p = new GlobalPostPassword();

if ( function_exists('load_plugin_textdomain') )
	load_plugin_textdomain('g_p_p', PLUGINDIR);

?>