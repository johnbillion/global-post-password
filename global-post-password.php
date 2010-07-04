<?php
/*
	Plugin Name: Global Post Password
	Description: Enables you to define a global password for all password-protected posts. <a href="options-privacy.php">Click here to change the password</a>.
	Plugin URI:  http://lud.icro.us/wordpress-plugin-global-post-password/
	Version:     1.4.1
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
		$settings = (array) get_option( 'globalpostpassword_settings' );
		if ( $this->is_edit_screen() ) {
			add_action( 'admin_menu',        array( &$this, 'start_buffer' ) );
			add_action( 'admin_footer',      array( &$this, 'end_buffer' ) );
		} else if ( $this->is_manage_screen() ) {
			add_action( 'admin_footer',      array( &$this, 'js_manage' ) );
		} else if ( $this->is_privacy_screen() ) {
			add_action( 'admin_head',        array( &$this, 'js_privacy' ) );
		} else if ( $this->is_wp_pass() ) {
			add_action( 'init',              array( &$this, 'set_password' ) );
		}
		if ( isset( $_GET['pass'] ) and $this->check_password( $_GET['pass'] ) ) {
			if ( $settings['in_permalinks'] ) {
				add_action( 'template_redirect', array( &$this, 'redirect' ) );
			}
			if ( $settings['in_permalinks'] ) {
				add_filter( 'the_permalink_rss', array( &$this, 'permalink_rss' ) );
			}
			if ( $settings['in_feed'] ) {
				add_filter( 'the_content_feed',  array( &$this, 'content_rss' ) );
				add_filter( 'the_excerpt_rss',   array( &$this, 'excerpt_rss' ) );
			}
		}
		add_filter( 'whitelist_options',     array( &$this, 'whitelist_options' ) );
		add_action( 'blog_privacy_selector', array( &$this, 'settings' ) );
		add_action( 'admin_init',            array( &$this, 'register' ) );
	}

	function redirect() {
		if ( !is_single() )
			return;
		$password = $this->check_password( $_GET['pass'] );
		if ( get_magic_quotes_gpc() )
			$password = stripslashes( $password );

		setcookie( 'wp-postpass_' . COOKIEHASH, $password, time() + 864000, COOKIEPATH );

		wp_safe_redirect( remove_query_arg( 'pass' ) );
		die();
	}

	function content_rss( $content ) {
		global $post;
		if ( !$post->post_password )
			return $content;
		return apply_filters( 'the_content', $post->post_content );
	}

	function excerpt_rss( $excerpt ) {
		global $post;
		if ( !$post->post_password )
			return $excerpt;
		return apply_filters( 'get_the_excerpt', $post->post_content );
	}

	function permalink_rss( $permalink ) {
		global $post;
		if ( !$post->post_password )
			return $permalink;
		$permalink = add_query_arg( 'pass', esc_attr( stripslashes( $_GET['pass'] ) ), $permalink );
		return str_replace( '&', '&amp;', $permalink );
	}

	function register() {
		register_setting( 'globalpostpassword', 'globalpostpassword', array( $this, 'update' ) );
		register_setting( 'globalpostpassword_settings', 'globalpostpassword_settings' );
	}

	function whitelist_options( $list ) {
		$list['privacy'][] = 'globalpostpassword';
		$list['privacy'][] = 'globalpostpassword_settings';
		return $list;
	}

	function buffer_edit( $content ) {

		$passes  = (array) get_option( 'globalpostpassword' );
		$search  = '|<span id="password-span">.*?</span>|i';
		$replace = '<input type="hidden" name="post_password" value="' . esc_attr( $passes[0] ) . '" />';

		if ( !$passes[0] ) {
			$replace .= '<div style="background:#FFFFE0;padding:5px;border:1px solid #E6DB55;-moz-border-radius:3px">';
			$replace .= __( '<strong>Warning:</strong> No global post password set.<br /><a href="options-privacy.php">Set one here &raquo;</a>', 'g_p_p' );
			$replace .= '</div>';
		}

		$replace = '<span id="password-span">' . $replace . '</span>';

		return preg_replace( $search, $replace, $content );

	}

	function js_manage() {
		$passes = (array) get_option( 'globalpostpassword' );
		?>
		<script type="text/javascript">

		function set_password_switch() {
			$ = window.jQuery;
			$('.inline-edit-password-switch').attr('checked',function(){
				return $('.inline-edit-password-input').val() ? 'checked' : '';
			});
		}

		jQuery(document).ready(function($) {
			$('.editinline').click(function() {
				setTimeout( 'set_password_switch()', 1 ); // hacky
			});
			$('<label><input type="checkbox" class="inline-edit-password-switch" /> <span class="checkbox-title"><?php _e('Password protected', 'g_p_p' ); ?></span></label>').insertAfter('.inline-edit-password-input').click(function(){
				val = $('input',this).attr('checked') ? '<?php echo esc_attr( $passes[0] ); ?>' : '';
				$('.inline-edit-password-input').val(val);
			});
			$('.inline-edit-password-input').hide();
		});

		</script>
		<?php
	}

	function js_privacy() {
		?>
		<script type="text/javascript">

		jQuery(function($) {

			$('#g_p_p h4').show();
			if ( $('#g_p_p input').length > 1 )
				$('#g_p_p input:last, #g_p_p br:last').hide();
			$('#g_p_p h4 a').click(function(){
				$('#g_p_p input:last').after($('#g_p_p input:last').clone().val('').show()).after('<br />');
				return false;
			});

			$('#globalpostpassword').change(function(){
				if ( '' == $(this).val() )
					$(this).parents('tr').addClass('form-invalid');
				else
					$(this).parents('tr').removeClass('form-invalid');
			});

			$('form[action="options.php"]').submit(function(){
				if ( '' != $('#globalpostpassword').val() )
					return true;
				alert( '<?php _e('The global post password cannot be blank. If you wish to return to per-post passwords, just deactivate or uninstall the Global Post Password plugin.', 'g_p_p' ); ?>' );
				return false;
			});

		});

		</script>
		<?php
	}

	function set_password() {
		if ( !isset( $_POST['post_password'] ) or empty( $_POST['post_password'] ) )
			return;
		if ( $password = $this->check_password( $_POST['post_password'] ) )
			$_POST['post_password'] = $password;
	}

	function check_password( $password, $handleslashes = true ) {
		if ( $handleslashes and get_magic_quotes_gpc() )
			$password = stripslashes( $password );
		if ( in_array( $password, $passes = (array) get_option( 'globalpostpassword' ) ) )
			$password = $passes[0];
		else
			return false;
		if ( $handleslashes and get_magic_quotes_gpc() )
			$password = addslashes( $password );
		return $password;
	}

	function settings() {
		$passes   = (array) get_option( 'globalpostpassword' );
		$settings = (array) get_option( 'globalpostpassword_settings' );
		?>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><?php _e('Global Post Password', 'g_p_p'); ?></th>
			<td><input name="globalpostpassword[0]" id="globalpostpassword" type="text" value="<?php echo esc_attr( $passes[0] ); ?>" class="regular-text" maxlength="20" />
			    <p><?php _e('All posts which are password-protected will use this password.', 'g_p_p'); ?></p>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><?php _e('Additional Global Post Passwords', 'g_p_p'); ?></th>
			<td id="g_p_p"><?php

			foreach ( $passes as $key => $pass ) {
				if ( !$key )
					continue;
				echo '<input name="globalpostpassword[]" type="text" value="' . esc_attr( $pass ) . '" class="regular-text" maxlength="20" /><br />';
			}

			?>
			<input name="globalpostpassword[]" type="text" value="" class="regular-text" maxlength="20" />
			<h4 style="display:none"><a href="#"><?php _e('+ Add', 'g_p_p'); ?></a></h4>
			<p><?php _e('Optionally, any number of additional global post passwords can be set here.', 'g_p_p'); ?></p>
			<p class="setting-description description"><?php _e( 'These work just like the main global post password, but can be removed at any time. Users can enter any of the global post passwords to gain access to any password protected post. This gives you the ability to have temporary passwords which can be revoked.', 'g_p_p'); ?></p>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><?php _e('Passwords in URLs', 'g_p_p'); ?></th>
			<td>
				<p><label><input name="globalpostpassword_settings[in_permalinks]" value="1" type="checkbox" <?php checked( $settings['in_permalinks'] ); ?> /> <?php _e('Allow post password in permalinks', 'g_p_p'); ?></label><br />
				<span class="setting-description description"><?php printf( __( 'With this setting enabled, appending <code>?pass=%s</code> to a post permalink will display the post without asking for the password.', 'g_p_p' ), esc_attr( $passes[0] ) ); ?></span></p>
				<p><label><input name="globalpostpassword_settings[in_feed]" value="1" type="checkbox" <?php checked( $settings['in_feed'] ); ?> /> <?php _e('Allow post password in your feed URL', 'g_p_p'); ?></label><br />
				<span class="setting-description description"><?php printf( __( 'With this setting enabled, appending <code>?pass=%s</code> to your blog&rsquo;s feed URL will display password protected posts in your feed without asking for passwords.', 'g_p_p' ), esc_attr( $passes[0] ) ); ?></span></p>
				<p class="setting-description description"><?php _e( '<strong>Tip:</strong> With either of these settings enabled, you can use any of your global post passwords in the URL.', 'g_p_p' ); ?></p>
				<p class="setting-description description"><?php _e( '<strong>Tip:</strong> If you enable both these options, permalinks in your feed will automatically have the password appended so users can click through to the post without needing to enter the password.', 'g_p_p' ); ?></p>
		<?php
	}

	function update( $passes ) {
		global $wpdb;
		$passes = (array) $passes;
		if ( '' == trim( $passes[0] ) )
			return get_option('globalpostpassword');
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

	function start_buffer() {
		ob_start( array( &$this, 'buffer_edit' ) );
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

load_plugin_textdomain(
	'g_p_p',
	PLUGINDIR . '/' . dirname( plugin_basename( __FILE__ ) ),
	dirname( plugin_basename( __FILE__ ) )
);

$g_p_p = new GlobalPostPassword();

?>