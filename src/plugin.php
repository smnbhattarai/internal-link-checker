<?php
/**
 * Plugin main class
 *
 * @package     IL Checker
 * @since       1.0.0
 * @author      Mathieu Lamiot
 * @license     GPL-2.0-or-later
 */

namespace ROCKET_WP_CRAWLER;

use WP_Query;

/**
 * Main plugin class. It manages initialization, install, and activations.
 */
class Rocket_Wpc_Plugin_Class {
	/**
	 * Manages plugin initialization
	 *
	 * @return void
	 */
	public function __construct() {

		// Register link check admin page
		add_action( 'admin_menu', array( $this, 'il_checker_settings_page' ) );

		// Handle link checker form submit
		add_action( 'admin_init', array( $this, 'start_link_checker_process' ) );

		// Add cron job for checking links
		add_action( 'il_checker_cron_job', array( $this, 'start_link_checker_cron' ) );

		// Register plugin lifecycle hooks.
		register_deactivation_hook( ROCKET_CRWL_PLUGIN_FILENAME, array( $this, 'wpc_deactivate' ) );
	}

	/**
	 * Handle plugin settings page
	 */
	public function il_checker_settings_page() {
		add_submenu_page(
			'options-general.php',
			__( 'IL Checker', 'il-checker' ),
			__( 'IL Checker', 'il-checker' ),
			'manage_options',
			'il-checker-settings',
			array( $this, 'il_checker_render_settings_page' )
		);
	}

	/**
	 * Registers a setting page to view link checker result and initiate the cron job
	 *
	 * @return void
	 */
	public function il_checker_render_settings_page() {
		$link_results = get_transient( ROCKET_CRWL_IL_CHECKER_RESULT );
		require_once 'partials/settings.php';
	}

	/**
	 * Process Start link checker form submit.
	 * This action adds cron and processes request for first time
	 *
	 * @return void
	 */
	public function start_link_checker_process() {
		if ( isset( $_POST['il-checker-submit'] ) ) {
			if ( check_admin_referer( 'il-checker' ) ) {

				// Set the cron hook if not already set. If its set run it to update result
				if ( ! wp_next_scheduled( 'il_checker_cron_job' ) ) {
					wp_schedule_event( time(), 'hourly', 'il_checker_cron_job' );
				} else {
					do_action( 'il_checker_cron_job' );
				}

				add_action( 'admin_notices', array( $this, 'admin_notice_check_started' ) );
			}
		}
	}

	/**
	 * Admin success message when form is submitted
	 * Link check started
	 *
	 * @return void
	 */
	public function admin_notice_check_started() {
		printf(
			'<div class="notice notice-success is-dismissible"><p>%s: %s</p></div>',
			'Success',
			__( 'Internal link check started', 'il-checker' )
		);
	}

	public function start_link_checker_cron() {

		// Since this is a new request to check link, delete old transient data
		delete_transient( ROCKET_CRWL_IL_CHECKER_RESULT );

		$homepage_url = get_bloginfo( 'url' );

		$args = array(
			'post_type'      => array( 'post', 'page' ), // Probably an option to add custom posts type later
			'post_status'    => 'publish',
			'posts_per_page' => -1, // Not ideal to fetch all --- might be slow if there are lots of posts
		);

		$query = new WP_Query( $args );

		foreach ( $query->posts as $post ) {
			$content       = $post->post_content;
			$matched_links = Rocket_Wpc_Helper::check_links( $content, $homepage_url );

			if ( ! empty( $matched_links ) ) {
				foreach ( $matched_links as $matched_link ) {
					// Set transient for matched link
					$checker_result = get_transient( ROCKET_CRWL_IL_CHECKER_RESULT );
					if ( ! $checker_result ) {
						$checker_result = array();
					}

					$checker_result[] = array(
						'page_link'    => get_permalink( $post->ID ),
						'anchor_text'  => $matched_link['anchor_text'],
						'linked_to'    => $matched_link['href'],
						'last_checked' => current_time( 'mysql' ),
					);

					set_transient( ROCKET_CRWL_IL_CHECKER_RESULT, $checker_result, 30 * MINUTE_IN_SECONDS );
				}
			}
		}

		wp_reset_postdata();
	}

	/**
	 * Handles plugin activation:
	 *
	 * @return void
	 */
	public static function wpc_activate() {
		// Security checks.
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}
		$plugin = isset( $_REQUEST['plugin'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['plugin'] ) ) : '';
		check_admin_referer( "activate-plugin_{$plugin}" );
	}

	/**
	 * Handles plugin deactivation
	 *
	 * @return void
	 */
	public function wpc_deactivate() {
		// Security checks.
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}
		$plugin = isset( $_REQUEST['plugin'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['plugin'] ) ) : '';
		check_admin_referer( "deactivate-plugin_{$plugin}" );

		// Remove cron on deactivation
		wp_unschedule_event( wp_next_scheduled( 'il_checker_cron_job' ), 'il_checker_cron_job' );
	}

	/**
	 * Handles plugin uninstall
	 *
	 * @return void
	 */
	public static function wpc_uninstall() {

		// Security checks.
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}
	}
}
