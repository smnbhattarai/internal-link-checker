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

		// Register link check admin page.
		add_action( 'admin_menu', array( $this, 'il_checker_settings_page' ) );

		// Handle link checker form submit.
		add_action( 'admin_init', array( $this, 'start_link_checker_process' ) );

		// Add cron job for checking links.
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
		$il_checker_link_results   = get_transient( ROCKET_CRWL_IL_CHECKER_RESULT );
		$il_checker_homepage_links = get_transient( ROCKET_CRWL_HOMEPAGE_INTERNAL_LINKS );

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

				// Set the cron hook if not already set. If its set run it to update result.
				if ( ! wp_next_scheduled( 'il_checker_cron_job' ) ) {
					wp_schedule_event( time(), 'hourly', 'il_checker_cron_job' );
				} else {
					wp_schedule_single_event( time(), 'il_checker_cron_job' );
				}

				new Rocket_Wpc_Admin_Notice( 'Internal link check started', 'success' );

			}
		}
	}

	/**
	 * Cron method to generate the link records.
	 *
	 * @return void
	 */
	public function start_link_checker_cron() {

		$homepage_url = get_bloginfo( 'url' );

		/**
		 * Start checking homepage link from internal pages
		 */
		$matched_links = array();
		// Since this is a new request to check link, delete old transient data.
		delete_transient( ROCKET_CRWL_IL_CHECKER_RESULT );

		$query = new WP_Query(
			array(
				'post_type'      => array( 'post', 'page' ), // Probably an option to add custom posts type later.
				'post_status'    => 'publish',
				'posts_per_page' => -1, // Not ideal to fetch all --- might be slow if there are lots of posts.
			)
		);

		foreach ( $query->posts as $post ) {
			$content       = $post->post_content;
			$matched_links = Rocket_Wpc_Helper::check_links( $content, $homepage_url );

			if ( ! empty( $matched_links ) ) {
				foreach ( $matched_links as $matched_link ) {
					// Set transient for matched link.
					$checker_result = get_transient( ROCKET_CRWL_IL_CHECKER_RESULT );
					if ( ! $checker_result ) {
						$checker_result = array();
					}

					$checker_result[] = array(
						'page_link'    => get_permalink( $post->ID ),
						'anchor_text'  => sanitize_text_field( $matched_link['anchor_text'] ),
						'linked_to'    => sanitize_url( $matched_link['href'] ),
						'last_checked' => current_time( 'mysql' ),
					);

					set_transient( ROCKET_CRWL_IL_CHECKER_RESULT, $checker_result, 60 * MINUTE_IN_SECONDS );
				}
			}
		}

		wp_reset_postdata();

		/**
		 * Start Crawling homepage for other inter links
		 */
		delete_transient( ROCKET_CRWL_HOMEPAGE_INTERNAL_LINKS );

		$homepage_links = Rocket_Wpc_Helper::check_homepage_internal_links();
		if ( ! empty( $homepage_links ) ) {
			set_transient( ROCKET_CRWL_HOMEPAGE_INTERNAL_LINKS, $homepage_links, 60 * MINUTE_IN_SECONDS );
		}

		Rocket_Wpc_Helper::generate_sitemap( $matched_links, $homepage_links );
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

		// Remove cron on deactivation.
		wp_unschedule_event( wp_next_scheduled( 'il_checker_cron_job' ), 'il_checker_cron_job' );

		// Delete transient data.
		delete_transient( ROCKET_CRWL_IL_CHECKER_RESULT );
		delete_transient( ROCKET_CRWL_HOMEPAGE_INTERNAL_LINKS );

		// Delete uploads folder.
		$dir_details = wp_upload_dir();
		$uploads_dir = trailingslashit( $dir_details['basedir'] );
		include_once ABSPATH . 'wp-admin/includes/file.php';
		WP_Filesystem();
		global $wp_filesystem;
		$wp_filesystem->rmdir( $uploads_dir . DIRECTORY_SEPARATOR . 'li-checker', true );
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
