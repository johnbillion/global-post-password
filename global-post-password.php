<?php
/*
	Plugin Name: Global Post Password
	Description: Enables you to define a global password for all password-protected posts. <a href="options-privacy.php">Click here to change the password</a>.
	Plugin URI:  http://lud.icro.us/wordpress-plugin-global-post-password/
	Version:     1.3
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
		if ( $this->is_edit_screen() ) {
			add_action( 'admin_menu',        array( &$this, 'start_buffer_edit' ) );
			add_action( 'admin_footer',      array( &$this, 'end_buffer' ) );
		} else if ( $this->is_manage_screen() ) {
			add_action( 'admin_menu',        array( &$this, 'start_buffer_manage' ) );
			add_action( 'admin_head',        array( &$this, 'js_manage' ) );
			add_action( 'admin_footer',      array( &$this, 'end_buffer' ) );
		} else if ( $this->is_privacy_screen() ) {
			add_action( 'admin_head',        array( &$this, 'js_privacy' ) );
		} else if ( $this->is_wp_pass() ) {
			add_action( 'init',              array( &$this, 'check_password' ) );
		}
		add_filter( 'whitelist_options',     array( &$this, 'whitelist_options' ) );
		add_action( 'blog_privacy_selector', array( &$this, 'settings' ) );
		add_action( 'admin_init',            array( &$this, 'register' ) );
	}

	function register() {
		register_setting( 'globalpostpassword', 'globalpostpassword', array( $this, 'update' ) );
	}

	function whitelist_options( $list ) {
		$list['privacy'][] = 'globalpostpassword';
		return $list;
	}

	function buffer_edit( $content ) {

		$passes  = (array) get_option( 'globalpostpassword' );
		$search  = '|<span id="password-span">.*?</span>|i';
		$replace = '<input type="hidden" name="post_password" value="' . attribute_escape( $passes[0] ) . '" />';

		if ( !$passes[0] ) {
			$replace .= '<div style="background:#FFFFE0;padding:5px;border:1px solid #E6DB55;-moz-border-radius:3px">';
			$replace .= __( '<strong>Warning:</strong> No global post password set.<br /><a href="options-privacy.php">Set one here &raquo;</a>', 'g_p_p' );
			$replace .= '</div>';
		}

		$replace = '<span id="password-span">' . $replace . '</span>';

		return preg_replace( $search, $replace, $content );

	}

	function buffer_manage( $content ) {

		$search  = '<input type="text" name="post_password"';
		$replace = '<input name="post_password" type="hidden" value="" /><input type="checkbox" name="post_password"';

		return str_replace( $search, $replace, $content );

	}

	function js_manage() {
		$passes = (array) get_option( 'globalpostpassword' );
		?>
		<script type="text/javascript">

		jQuery(function($) {
			$('a.editinline').click(function() {
				$('.inline-edit-password-input').attr('checked',function(){
					return $(this).val() ? 'checked' : '';
				}).val('<?php echo attribute_escape( $passes[0] ); ?>').css({
					margin : '0.3em',
					width  : 'auto'
				});
				$('.inline-edit-group [name=post_password]:hidden').val('');
				return false;
			});
		});

		</script>
		<?php
	}

	function js_privacy() {
		?>
		<script type="text/javascript">

		jQuery(function($) {
			$('#mgpp h4').show();
			if ( $('#mgpp input').length > 1 )
				$('#mgpp input:last, #mgpp br:last').hide();
			$('#mgpp h4 a').click(function(){
				$('#mgpp input:last').after($('#mgpp input:last').clone().val('').show()).after('<br />');
				return false;
			});
		});

		</script>
		<?php
	}

	function check_password() {
		if ( !isset( $_POST['post_password'] ) or empty( $_POST['post_password'] ) )
			return;
		if ( get_magic_quotes_gpc() )
			$_POST['post_password'] = stripslashes( $_POST['post_password'] );
		if ( in_array( $_POST['post_password'], $passes = (array) get_option( 'globalpostpassword' ) ) )
			$_POST['post_password'] = $passes[0];
		if ( get_magic_quotes_gpc() )
			$_POST['post_password'] = addslashes( $_POST['post_password'] );
	}

	function settings() {
		$passes = (array) get_option( 'globalpostpassword' );
		?>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><?php _e('Global Post Password', 'g_p_p'); ?></th>
			<td><input name="globalpostpassword[0]" type="text" value="<?php echo attribute_escape( $passes[0] ); ?>" class="regular-text" />
			    <p><?php _e('Any posts which are password-protected will use this password.', 'g_p_p'); ?></p>
			    <p class="setting-description description"><?php _e('<strong>Warning:</strong> Setting this password to a blank value will remove password protection from all posts! If you wish to return to per-post passwords, deactivate or uninstall the Global Post Password plugin.', 'g_p_p'); ?></p>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><?php _e('Additional Global Post Passwords', 'g_p_p'); ?></th>
			<td id="mgpp"><?php

			foreach ( $passes as $key => $pass ) {
				if ( !$key )
					continue;
				echo '<input name="globalpostpassword[]" type="text" value="' . attribute_escape( $pass ) . '" class="regular-text" /><br />';
			}

			?>
			<input name="globalpostpassword[]" type="text" value="" class="regular-text" />
			<h4 style="display:none"><a href="#"><?php _e('+ Add', 'g_p_p'); ?></a></h4>
			<p><?php _e('Optionally, any number of additional global post passwords can be set here.', 'g_p_p'); ?></p>
			<p class="setting-description description"><?php _e( 'These work just like the main global post password, but can be changed or removed whenever necessary. Users can enter the global post password or any one of the additional global post passwords to gain access to any password protected post. This gives you the ability to have temporary passwords which can be given to certain users and revoked at a later date.', 'g_p_p'); ?></p>
		<?php
	}

	function update( $passes ) {
		global $wpdb;
		$passes = (array) $passes;
		foreach ( $passes as $key => $pass ) {
			if ( !trim( $pass ) )
				unset( $passes[$key] );
		}
		$wpdb->query( $wpdb->prepare( "
			UPDATE {$wpdb->posts}
			SET `post_password` = %s
			WHERE `post_password` <> ''
		", $passes[0] ) );
		return array_values( $passes );
	}

	function start_buffer_edit() {
		ob_start( array( &$this, 'buffer_edit' ) );
	}

	function start_buffer_manage() {
		ob_start( array( &$this, 'buffer_manage' ) );
	}

	function end_buffer() {
		ob_end_flush();
	}

	function is_edit_screen() {
		if ( strpos( $_SERVER['REQUEST_URI'], 'post-new.php' )
		  or strpos( $_SERVER['REQUEST_URI'], 'page-new.php')
		  or strpos( $_SERVER['REQUEST_URI'], 'post.php' )
		  or strpos( $_SERVER['REQUEST_URI'], 'page.php' ) )
			return true;
		return false;
	}

	function is_manage_screen() {
		if ( strpos( $_SERVER['REQUEST_URI'], 'edit.php' )
		  or strpos( $_SERVER['REQUEST_URI'], 'edit-pages.php' ) )
			return true;
		return false;
	}

	function is_privacy_screen() {
		if ( strpos( $_SERVER['REQUEST_URI'], 'options-privacy.php' ) )
			return true;
		return false;
	}

	function is_wp_pass() {
		if ( strpos( $_SERVER['REQUEST_URI'], 'wp-pass.php' ) )
			return true;
		return false;
	}

}

$g_p_p = new GlobalPostPassword();

load_plugin_textdomain( 'g_p_p', PLUGINDIR . '/' . dirname( plugin_basename( __FILE__ ) ), dirname( plugin_basename( __FILE__ ) ) );

?>