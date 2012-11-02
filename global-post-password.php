<?php
/*
Plugin Name: Global Post Password
Description: Define a global password for all your password-protected posts. <a href="options-general.php?page=globalpostpassword">Set the password here</a>.
Plugin URI:  http://lud.icro.us/wordpress-plugin-global-post-password/
Version:     1.5
Author:      John Blackbourn
Author URI:  http://johnblackbourn.com/

Copyright © 2012 John Blackbourn

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

	public $has_buffer = false;
	public $wp_hasher  = null;

	function __construct() {

		$this->settings = (array) get_option( 'globalpostpassword_settings' );
		$this->passes   = (array) get_option( 'globalpostpassword' );

		add_action( 'init', array( $this, 'init' ) );

		if ( is_admin() ) {

			add_action( 'admin_init',        array( $this, 'register' ) );
			add_action( 'admin_menu',        array( $this, 'admin_menu' ) );
			add_action( 'admin_footer',      array( $this, 'end_buffer' ) );
			add_action( 'load-post.php',     array( $this, 'start_buffer' ) );
			add_action( 'load-post-new.php', array( $this, 'start_buffer' ) );
			add_action( 'load-edit.php',     array( $this, 'assets' ) );
			add_action( 'load-settings_page_globalpostpassword', array( $this, 'assets' ) );

		} else {

			add_action( 'body_class', array( $this, 'body_class' ) );

			if ( isset( $_GET['pass'] ) and $this->check_password( stripslashes( $_GET['pass'] ) ) ) {
				if ( $this->settings['in_permalinks'] ) {
					add_action( 'template_redirect', array( $this, 'redirect' ) );
					add_filter( 'the_permalink_rss', array( $this, 'permalink_rss' ) );
				}
				if ( $this->settings['in_feed'] ) {
					add_filter( 'the_content_feed',  array( $this, 'content_rss' ) );
					add_filter( 'the_excerpt_rss',   array( $this, 'excerpt_rss' ) );
				}
			}

		}

	}

	function admin_menu() {
		add_options_page(
			__( 'Global Post Password', 'globalpostpassword' ),
			__( 'Global Post Password', 'globalpostpassword' ),
			'manage_options',
			'globalpostpassword',
			array( $this, 'settings' )
		);
	}

	function redirect() {
		if ( !is_singular() )
			return;
		$password = $this->check_password( stripslashes( $_GET['pass'] ) );

		if ( empty( $this->wp_hasher ) ) {
			require_once( ABSPATH . 'wp-includes/class-phpass.php' );
			$this->wp_hasher = new PasswordHash( 8, true );
		}

		setcookie( 'wp-postpass_' . COOKIEHASH, $this->wp_hasher->HashPassword( $password ), time() + 864000, COOKIEPATH );

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
		return add_query_arg( 'pass', stripslashes( $_GET['pass'] ), $permalink );
	}

	function register() {

		register_setting( 'globalpostpassword', 'globalpostpassword', array( $this, 'update' ) );
		register_setting( 'globalpostpassword', 'globalpostpassword_settings' );

		add_settings_section(
			'default',
			'',
			'__return_false',
			'globalpostpassword'
		);
		add_settings_field(
			'globalpostpassword_global',
			__( 'Global Post Password', 'globalpostpassword' ),
			array( $this, 'settings_global' ),
			'globalpostpassword'
		);
		add_settings_field(
			'globalpostpassword_additional',
			__( 'Additional Global Post Passwords', 'globalpostpassword' ),
			array( $this, 'settings_additional' ),
			'globalpostpassword'
		);
		add_settings_field(
			'globalpostpassword_urls',
			__( 'Passwords in URLs', 'globalpostpassword' ),
			array( $this, 'settings_urls' ),
			'globalpostpassword'
		);

	}

	function buffer_edit( $content ) {

		$search  = '|<span id="password-span">.*?</span>|i';
		$replace = '<input type="hidden" name="post_password" value="' . esc_attr( $this->passes[0] ) . '" />';

		if ( !$this->passes[0] ) {
			$replace .= '<div style="background:#FFFFE0;padding:5px;border:1px solid #E6DB55;-moz-border-radius:3px">';
			$replace .= __( '<strong>Warning:</strong> No global post password set.<br /><a href="options-general.php?page=globalpostpassword">Set one here &raquo;</a>', 'globalpostpassword' );
			$replace .= '</div>';
		}

		$replace = '<span id="password-span">' . $replace . '</span>';

		return preg_replace( $search, $replace, $content );

	}

	function assets() {

		wp_enqueue_script(
			'globalpostpassword',
			$this->plugin_url( 'admin.js' ),
			array( 'jquery' ),
			$this->plugin_ver( 'admin.js' )
		);

		wp_localize_script(
			'globalpostpassword',
			'gpp',
			array(
				'password'           => $this->passes[0],
				'password_protected' => __( 'Password protected', 'globalpostpassword' ),
				'not_blank'          => __( 'The global post password cannot be blank. If you wish to return to per-post passwords, you should deactivate or uninstall the Global Post Password plugin.', 'globalpostpassword' )
			)
		);

	}

	function init() {

		load_plugin_textdomain(
			'globalpostpassword',
			false,
			dirname( $this->plugin_base() ) . '/languages'
		);

		if ( isset( $_POST['post_password'] ) and isset( $_GET['action'] ) and ( 'postpass' == $_GET['action'] ) ) {
			if ( $password = $this->check_password( stripslashes( $_POST['post_password'] ) ) )
				$_POST['post_password'] = addslashes( $password );
		}

	}

	function check_password( $password ) {

		if ( in_array( $password, $this->passes ) )
			return $this->passes[0];
		else
			return false;

	}

	function check_hashed_password( $hash ) {

		if ( empty( $this->wp_hasher ) ) {
			require_once( ABSPATH . 'wp-includes/class-phpass.php');
			$this->wp_hasher = new PasswordHash( 8, true );
		}

		foreach ( $this->passes as $pass ) {
			if ( $this->wp_hasher->CheckPassword( $pass, $hash ) )
				return $this->passes[0];
		}

		return false;

	}

	function settings() {

		?>
		<div class="wrap">
			<?php screen_icon(); ?>
			<h2><?php _e( 'Global Post Password', 'globalpostpassword' ); ?></h2>

			<form method="post" action="options.php" id="globalpostpassword_form">
			<?php
				settings_fields( 'globalpostpassword' );
				do_settings_sections( 'globalpostpassword' );
				submit_button();
			?>
			</form>

		</div>
		<?php

	}

	function settings_global() {
		?>
		<input name="globalpostpassword[0]" id="globalpostpassword" type="text" value="<?php echo esc_attr( $this->passes[0] ); ?>" class="regular-text" maxlength="20" />
		<p><?php _e( 'All posts which are password-protected will use this password.', 'globalpostpassword' ); ?></p>
		<?php
	}

	function settings_additional() {

		foreach ( $this->passes as $key => $pass ) {
			if ( !$key )
				continue;
			echo '<div><input name="globalpostpassword[]" type="text" value="' . esc_attr( $pass ) . '" class="regular-text" maxlength="20" /></div>';
		}

		?>
		<div class="hide-if-js globalpostpassword"><input name="globalpostpassword[]" type="text" value="" class="regular-text" maxlength="20" /></div>
		<strong class="hide-if-no-js"><a href="#" id="add_gpp"><?php _e( '+ Add', 'globalpostpassword' ); ?></a></strong>
		<p>
			<?php _e( 'Optionally, any number of additional global post passwords can be set here.', 'globalpostpassword' ); ?>
		</p>
		<p class="description">
			<?php _e( 'These work just like the main global post password, but can be removed at any time. Users can enter any of the global post passwords to gain access to any password protected post. This gives you the ability to have temporary passwords which can be revoked.', 'globalpostpassword'); ?>
		</p>
		<?php
	}

	function settings_urls() {
		?>
		<p>
			<label>
				<input name="globalpostpassword_settings[in_permalinks]" value="1" type="checkbox" <?php checked( @$this->settings['in_permalinks'] ); ?> />
				<?php _e( 'Allow post password in permalinks', 'globalpostpassword' ); ?>
			</label><br />
			<span class="description"><?php printf( __( 'With this setting enabled, appending <code>?pass=%s</code> to a post permalink will display the post without asking for the password.', 'globalpostpassword' ), esc_html( $this->passes[0] ) ); ?></span>
		</p>
		<p>
			<label>
				<input name="globalpostpassword_settings[in_feed]" value="1" type="checkbox" <?php checked( @$this->settings['in_feed'] ); ?> />
				<?php _e( 'Allow post password in your feed URL', 'globalpostpassword' ); ?>
			</label><br />
			<span class="description"><?php printf( __( 'With this setting enabled, appending <code>?pass=%s</code> to your blog&rsquo;s feed URL will display password protected posts in your feed without asking for passwords.', 'globalpostpassword' ), esc_html( $this->passes[0] ) ); ?></span>
		</p>
		<p class="description">
			<?php _e( '<strong>Tip:</strong> With either of these settings enabled, you can use any of your global post passwords in the URL.', 'globalpostpassword' ); ?>
		</p>
		<?php
	}

	function body_class( $classes ) {

		global $post;

		$cookie = 'wp-postpass_' . COOKIEHASH;

		if ( !is_singular() )
			return $classes;
		if ( !$post->post_password )
			return $classes;
		if ( !isset( $_COOKIE[$cookie] ) )
			return $classes;

		if ( $this->check_hashed_password( stripslashes( $_COOKIE[$cookie] ) ) )
			$classes[] = 'has-post-password';

		return $classes;

	}

	function update( $passes ) {
		global $wpdb;
		$passes = (array) $passes;
		if ( '' === trim( $passes[0] ) )
			return $this->passes;
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
		$this->has_buffer = ob_start( array( $this, 'buffer_edit' ) );
	}

	function end_buffer() {
		if ( $this->has_buffer )
			ob_end_flush();
	}

	function plugin_url( $file = '' ) {
		return $this->plugin( 'url', $file );
	}

	function plugin_path( $file = '' ) {
		return $this->plugin( 'path', $file );
	}

	function plugin_ver( $file ) {
		return filemtime( $this->plugin_path( $file ) );
	}

	function plugin_base() {
		return $this->plugin( 'base' );
	}

	function plugin( $item, $file = '' ) {
		if ( !isset( $this->plugin ) ) {
			$this->plugin = array(
				'url'  => plugin_dir_url( __FILE__ ),
				'path' => plugin_dir_path( __FILE__ ),
				'base' => plugin_basename( __FILE__ )
			);
		}
		return $this->plugin[$item] . ltrim( $file, '/' );
	}

}

global $globalpostpassword;

$globalpostpassword = new GlobalPostPassword;
