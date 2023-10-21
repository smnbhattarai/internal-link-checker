<?php

namespace ROCKET_WP_CRAWLER;

use WP_Error;

class Rocket_Wpc_Helper {

	/**
	 * Check link on the homepage to see the match.
	 *
	 * @param string $post_content post content to check link.
	 * @param string $homepage_link link to check.
	 *
	 * @return array
	 */
	public static function check_links( $post_content, $homepage_link ) {
		$link_details = array();
		$html         = mb_convert_encoding( $post_content, 'HTML-ENTITIES', 'UTF-8' );
		$document     = new \DOMDocument();

		libxml_use_internal_errors( true );

		if ( ! empty( $html ) ) {
			$document->loadHTML( utf8_decode( $html ) );
			$links = $document->getElementsByTagName( 'a' );
			foreach ( $links as $link ) {
				$href        = $link->getAttribute( 'href' );
				$anchor_text = $link->textContent; // phpcs:ignore
				if ( rtrim( $href, '/' ) === rtrim( $homepage_link, '/' ) ) {
					$link_details[] = array(
						'href'        => $href,
						'anchor_text' => $anchor_text,
					);
				}
			}
		}

		return $link_details;
	}

	/**
	 * Checks homepage internal links.
	 *
	 * @return array
	 */
	public static function check_homepage_internal_links() {
		$link_details = array();
		$homepage_url = get_bloginfo( 'url' );
		$response     = wp_remote_get( get_bloginfo( 'url' ) );
		if ( ( ! $response instanceof WP_Error ) && 200 === $response['response']['code'] ) {

			$homepage_html = $response['body'];

			include_once ABSPATH . 'wp-admin/includes/file.php';
			WP_Filesystem();
			global $wp_filesystem;
			$wp_filesystem->put_contents( self::li_checker_upload_path( 'index.html' ), $homepage_html, FS_CHMOD_FILE );

			$html     = mb_convert_encoding( $homepage_html, 'HTML-ENTITIES', 'UTF-8' );
			$document = new \DOMDocument();

			libxml_use_internal_errors( true );

			if ( ! empty( $html ) ) {
				$document->loadHTML( utf8_decode( $html ) );
				$links = $document->getElementsByTagName( 'a' );
				foreach ( $links as $link ) {
					$href   = $link->getAttribute( 'href' );
					$anchor = $link->textContent; // phpcs:ignore
					if ( strpos( $href, $homepage_url ) === 0 ) {
						$link_details[] = array(
							'link'         => esc_url( $href ),
							'anchor'       => sanitize_text_field( $anchor ),
							'last_checked' => current_time( 'mysql' ),
						);
					}
				}
			}
		}
		return $link_details;
	}

	/**
	 * Optional file name if full path is required of the file. In other case path to folder is returned.
	 *
	 * @param string $filename optional file name.
	 *
	 * @return string
	 */
	public static function li_checker_upload_path( $filename = null ) {
		$dir_details = wp_upload_dir();
		$uploads_dir = trailingslashit( $dir_details['basedir'] );

		// check if directory exists and if not create it.
		if ( ! file_exists( $uploads_dir . DIRECTORY_SEPARATOR . 'li-checker' ) ) {
			include_once ABSPATH . 'wp-admin/includes/file.php';
			WP_Filesystem();
			global $wp_filesystem;
			$wp_filesystem->mkdir( $uploads_dir . DIRECTORY_SEPARATOR . 'li-checker', 0755 );
		}

		$path = $uploads_dir . DIRECTORY_SEPARATOR . 'li-checker' . DIRECTORY_SEPARATOR;
		if ( $filename ) {
			$path .= $filename;
		}

		return $path;
	}

	/**
	 * Generates sitemap of links and saves to uploads folder.
	 *
	 * @param array $matched_links has links from other page to homepage.
	 * @param array $homepage_links has links from homepage to other page.
	 *
	 * @return void
	 */
	public static function generate_sitemap( $matched_links, $homepage_links ) {
		$links = array();
		foreach ( $matched_links as $matched_link ) {
			$links[] = $matched_link['linked_to'];
		}
		foreach ( $homepage_links as $homepage_link ) {
			$links[] = $homepage_link['link'];
		}
		$links = array_unique( array_filter( $links ) );
		sort( $links );

		$links_html = '';
		foreach ( $links as $link ) {
			$link_only = str_replace( get_bloginfo( 'url' ), '', $link );
			$link_only = explode( '/', $link_only );
			$link_only = array_filter( $link_only );
			$link_only = array_map(
				function ( $l ) {
					return ucwords( str_replace( '-', ' ', $l ) );
				},
				$link_only
			);
			if ( ! empty( $link_only ) ) {
				$links_html .= '<li><a target="_blank" href="' . $link . '">' . implode( ' &rarr; ', $link_only ) . '</a></li>';
			}
		}

		$title = get_bloginfo( 'name' );

		$sitemap = <<<SITEMAP
		<!doctype html>
		<html lang="en">
			<head>
			    <meta charset="utf-8">
			    <meta name="viewport" content="width=device-width, initial-scale=1">
			    <title>$title</title>
			</head>
			<body>
			    <div>
				    <h2>Sitemap</h2>
				    <ul>$links_html</ul>
				</div>
			</body>
		</html>
SITEMAP;

		include_once ABSPATH . 'wp-admin/includes/file.php';
		WP_Filesystem();
		global $wp_filesystem;
		$wp_filesystem->put_contents( self::li_checker_upload_path( 'sitemap.html' ), $sitemap, FS_CHMOD_FILE );
	}
}
