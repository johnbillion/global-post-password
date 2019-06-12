<?php
/**
Plugin Name: Global Post Password
Description: Define a global password for all your password-protected posts and pages.
Plugin URI:  https://johnblackbourn.com/wordpress-plugin-global-post-password/
Version:     1.5.2
Author:      John Blackbourn
Author URI:  https://johnblackbourn.com/
Text Domain: global-post-password
Domain Path: /languages/
License:     GPL v2 or later

Copyright 2007-2019 John Blackbourn

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

	public function __construct() {
		$this->settings = (array) get_option( 'globalpostpassword_settings', array() );
		$this->passes   = (array) get_option( 'globalpostpassword', array() );

		add_action( 'init', array( $this, 'init' ) );

		if ( is_admin() ) {
			$plugin_file = $this->plugin_base();

			add_action( 'admin_init',                            array( $this, 'register' ) );
			add_action( 'admin_menu',                            array( $this, 'admin_menu' ) );
			add_action( 'admin_footer',                          array( $this, 'end_buffer' ) );
			add_action( 'load-post.php',                         array( $this, 'start_buffer' ) );
			add_action( 'load-post-new.php',                     array( $this, 'start_buffer' ) );
			add_action( 'load-edit.php',                         array( $this, 'assets' ) );
			add_action( 'load-settings_page_globalpostpassword', array( $this, 'assets' ) );
			add_filter( "plugin_action_links_{$plugin_file}",    array( $this, 'plugin_action_links' ), 10, 4 );
		} else {
			add_action( 'body_class', array( $this, 'body_class' ) );

			if ( isset( $_GET['pass'] ) && $this->check_password( wp_unslash( $_GET['pass'] ) ) ) {
				if ( ! empty( $this->settings['in_permalinks'] ) ) {
					add_action( 'template_redirect', array( $this, 'redirect' ) );
					add_filter( 'the_permalink_rss', array( $this, 'permalink_rss' ) );
				}
				if ( ! empty( $this->settings['in_feed'] ) ) {
					add_filter( 'the_content_feed',  array( $this, 'content_rss' ) );
					add_filter( 'the_excerpt_rss',   array( $this, 'excerpt_rss' ) );
				}
			}
		}
	}

	public function admin_menu() {
		add_options_page(
			esc_html__( 'Global Post Password', 'global-post-password' ),
			esc_html__( 'Global Post Password', 'global-post-password' ),
			'manage_options',
			'globalpostpassword',
			array( $this, 'settings' )
		);
	}

	public function redirect() {
		if ( ! is_singular() ) {
			return;
		}
		$password = $this->check_password( wp_unslash( $_GET['pass'] ) );

		if ( empty( $this->wp_hasher ) ) {
			require_once ABSPATH . 'wp-includes/class-phpass.php';
			$this->wp_hasher = new PasswordHash( 8, true );
		}

		/** This filter is documented in wp-login.php */
		$expire    = apply_filters( 'post_password_expires', time() + 10 * DAY_IN_SECONDS );
		$permalink = get_permalink( get_the_ID() );
		$secure    = ( 'https' === parse_url( $permalink, PHP_URL_SCHEME ) );
		setcookie( 'wp-postpass_' . COOKIEHASH, $this->wp_hasher->HashPassword( $password ), $expire, COOKIEPATH, COOKIE_DOMAIN, $secure );

		wp_redirect( get_permalink( get_the_ID() ) );
		exit;
	}

	public function content_rss( $content ) {
		global $post;
		if ( ! $post->post_password ) {
			return $content;
		}
		/** This filter is documented in wp-includes/post-template.php */
		return apply_filters( 'the_content', $post->post_content );
	}

	public function excerpt_rss( $excerpt ) {
		global $post;
		if ( ! $post->post_password ) {
			return $excerpt;
		}
		/** This filter is documented in wp-includes/post-template.php */
		return apply_filters( 'get_the_excerpt', $post->post_content );
	}

	public function permalink_rss( $permalink ) {
		global $post;
		if ( ! $post->post_password ) {
			return $permalink;
		}
		return add_query_arg( 'pass', urlencode( wp_unslash( $_GET['pass'] ) ), $permalink );
	}

	public function register() {
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
			esc_html__( 'Global Password', 'global-post-password' ),
			array( $this, 'settings_global' ),
			'globalpostpassword'
		);
		add_settings_field(
			'globalpostpassword_additional',
			esc_html__( 'Additional Global Passwords', 'global-post-password' ),
			array( $this, 'settings_additional' ),
			'globalpostpassword'
		);
		add_settings_field(
			'globalpostpassword_urls',
			esc_html__( 'Passwords in URLs', 'global-post-password' ),
			array( $this, 'settings_urls' ),
			'globalpostpassword'
		);
	}

	public function buffer_edit( $content ) {
		$search  = '|<span id="password-span">.*?</span>|i';
		$replace = '';

		if ( isset( $this->passes[0] ) ) {
			$replace .= '<input type="hidden" name="post_password" value="' . esc_attr( $this->passes[0] ) . '" />';
		} else {
			$replace .= '<div style="background:#FFFFE0;padding:5px;border:1px solid #E6DB55;">';
			$replace .= __( '<strong>Warning:</strong> No global password set.<br /><a href="options-general.php?page=globalpostpassword">Set one here &raquo;</a>', 'global-post-password' );
			$replace .= '</div>';
		}

		$replace = '<span id="password-span">' . $replace . '</span>';

		return preg_replace( $search, $replace, $content );
	}

	public function assets() {
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
				'password'           => isset( $this->passes[0] ) ? $this->passes[0] : '',
				'password_protected' => __( 'Password protected', 'global-post-password' ),
				'not_blank'          => __( 'The global password cannot be blank. If you wish to return to per-post passwords, you should deactivate or uninstall the Global Post Password plugin.', 'global-post-password' ),
			)
		);
	}

	public function init() {
		load_plugin_textdomain(
			'global-post-password',
			false,
			dirname( $this->plugin_base() ) . '/languages'
		);

		// phpcs:disable WordPress.Security.NonceVerification
		if ( isset( $_POST['post_password'] ) && isset( $_GET['action'] ) && ( 'postpass' === $_GET['action'] ) ) {
			$password = $this->check_password( wp_unslash( $_POST['post_password'] ) );
			if ( $password ) {
				$_POST['post_password'] = wp_slash( $password );
			}
		}
		// phpcs:enable WordPress.Security.NonceVerification
	}

	public function check_password( $password ) {
		if ( in_array( $password, $this->passes, true ) ) {
			return $this->passes[0];
		} else {
			return false;
		}
	}

	public function check_hashed_password( $hash ) {
		if ( empty( $this->wp_hasher ) ) {
			require_once ABSPATH . 'wp-includes/class-phpass.php';
			$this->wp_hasher = new PasswordHash( 8, true );
		}

		foreach ( $this->passes as $pass ) {
			if ( $this->wp_hasher->CheckPassword( $pass, $hash ) ) {
				return $this->passes[0];
			}
		}

		return false;
	}

	public function settings() {
		?>
		<div class="wrap">
			<h2><?php esc_html_e( 'Global Post Password', 'global-post-password' ); ?></h2>

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

	public function settings_global() {
		$pass = isset( $this->passes[0] ) ? $this->passes[0] : '';
		?>
		<input name="globalpostpassword[0]" id="globalpostpassword" type="text" value="<?php echo esc_attr( $pass ); ?>" class="regular-text" maxlength="20" />
		<p><?php esc_html_e( 'All password protected posts, pages, and custom post types will use this password.', 'global-post-password' ); ?></p>
		<?php
	}

	public function settings_additional() {
		foreach ( $this->passes as $key => $pass ) {
			if ( ! $key ) {
				continue;
			}
			echo '<div><input name="globalpostpassword[]" type="text" value="' . esc_attr( $pass ) . '" class="regular-text" maxlength="20" /></div>';
		}

		?>
		<div class="hide-if-js globalpostpassword"><input name="globalpostpassword[]" type="text" value="" class="regular-text" maxlength="20" /></div>
		<strong class="hide-if-no-js"><a href="#" id="add_gpp"><?php esc_html_e( '+ Add', 'global-post-password' ); ?></a></strong>
		<p>
			<?php esc_html_e( 'Optionally, any number of additional global passwords can be set here.', 'global-post-password' ); ?>
		</p>
		<p class="description">
			<?php esc_html_e( 'These function just like the main global password, but can be removed at any time. Any of the global passwords can be used to gain access to any password protected post. This enables the ability to provide temporary passwords which can be revoked.', 'global-post-password' ); ?>
		</p>
		<?php
	}

	public function settings_urls() {
		$pass = isset( $this->passes[0] ) ? urlencode( $this->passes[0] ) : '<password>';
		?>
		<p>
			<label>
				<input name="globalpostpassword_settings[in_permalinks]" value="1" type="checkbox" <?php checked( ! empty( $this->settings['in_permalinks'] ) ); ?> />
				<?php esc_html_e( 'Allow password in permalinks', 'global-post-password' ); ?>
			</label><br />
			<span class="description">
				<?php
				printf(
					/* translators: %s: The URL query variable */
					esc_html__( 'With this setting enabled, appending %s to a permalink will display the content without prompting for a password.', 'global-post-password' ),
					'<code>?pass=' . esc_html( $pass ) . '</code>'
				);
				?>
			</span>
		</p>
		<p>
			<label>
				<input name="globalpostpassword_settings[in_feed]" value="1" type="checkbox" <?php checked( ! empty( $this->settings['in_feed'] ) ); ?> />
				<?php esc_html_e( 'Allow password in feed URLs', 'global-post-password' ); ?>
			</label><br />
			<span class="description">
				<?php
				printf(
					/* translators: %s: The URL query variable */
					esc_html__( 'With this setting enabled, appending %s to a feed URL will display password protected content in the feed without prompting for passwords.', 'global-post-password' ),
					'<code>?pass=' . esc_html( $pass ) . '</code>'
				);
				?>
			</span>
		</p>
		<p class="description">
			<?php esc_html_e( 'Tip: With either of these settings enabled, any global password can be used in the URL.', 'global-post-password' ); ?>
		</p>
		<?php
	}

	public function body_class( $classes ) {
		global $post;

		$cookie = 'wp-postpass_' . COOKIEHASH;

		if ( ! is_singular() ) {
			return $classes;
		}
		if ( ! $post->post_password ) {
			return $classes;
		}
		if ( ! isset( $_COOKIE[ $cookie ] ) ) {
			return $classes;
		}

		if ( $this->check_hashed_password( wp_unslash( $_COOKIE[ $cookie ] ) ) ) {
			$classes[] = 'has-post-password';
		}

		return $classes;
	}

	public function update( $passes ) {
		global $wpdb;
		$passes = (array) $passes;
		if ( '' === trim( $passes[0] ) ) {
			return $this->passes;
		}
		foreach ( $passes as $key => $pass ) {
			if ( ! trim( $pass ) ) {
				unset( $passes[ $key ] );
			}
		}
		$wpdb->query( $wpdb->prepare( "
			UPDATE {$wpdb->posts}
			SET post_password = %s
			WHERE post_password <> ''
		", $passes[0] ) );
		return array_values( $passes );
	}

	public function start_buffer() {
		$this->has_buffer = ob_start( array( $this, 'buffer_edit' ) );
	}

	public function end_buffer() {
		if ( $this->has_buffer ) {
			ob_end_flush();
		}
	}

	public function plugin_action_links( $actions, $plugin_file, $plugin_data, $context ) {
		$actions['global-post-password'] = sprintf(
			'<a href="%s">%s</a>',
			admin_url( 'options-general.php?page=globalpostpassword' ),
			esc_html__( 'Settings', 'global-post-password' )
		);
		return $actions;
	}

	protected function plugin_url( $file = '' ) {
		return $this->plugin( 'url', $file );
	}

	protected function plugin_path( $file = '' ) {
		return $this->plugin( 'path', $file );
	}

	protected function plugin_ver( $file ) {
		return filemtime( $this->plugin_path( $file ) );
	}

	protected function plugin_base() {
		return $this->plugin( 'base' );
	}

	protected function plugin( $item, $file = '' ) {
		if ( ! isset( $this->plugin ) ) {
			$this->plugin = array(
				'url'  => plugin_dir_url( __FILE__ ),
				'path' => plugin_dir_path( __FILE__ ),
				'base' => plugin_basename( __FILE__ ),
			);
		}
		return $this->plugin[ $item ] . ltrim( $file, '/' );
	}

}

$GLOBALS['globalpostpassword'] = new GlobalPostPassword();
