<?php

namespace ROCKET_WP_CRAWLER;

use WP_Error;

class Rocket_Wpc_Helper {

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
				$anchor_text = $link->textContent;
				if ( rtrim( $href, '/' ) == rtrim( $homepage_link, '/' ) ) {
					$link_details[] = array(
						'href'        => $href,
						'anchor_text' => $anchor_text,
					);
				}
			}
		}

		return $link_details;
	}

	public static function check_homepage_internal_links() {
		$link_details = array();
		$homepage_url = get_bloginfo( 'url' );
		$response     = wp_remote_get( get_bloginfo( 'url' ) );
		if ( ( ! $response instanceof WP_Error ) && 200 === $response['response']['code'] ) {

			$homepage_html = $response['body'];

			file_put_contents( self::li_checker_upload_path( 'index.html' ), $homepage_html );

			$html     = mb_convert_encoding( $homepage_html, 'HTML-ENTITIES', 'UTF-8' );
			$document = new \DOMDocument();

			libxml_use_internal_errors( true );

			if ( ! empty( $html ) ) {
				$document->loadHTML( utf8_decode( $html ) );
				$links = $document->getElementsByTagName( 'a' );
				foreach ( $links as $link ) {
					$href   = $link->getAttribute( 'href' );
					$anchor = $link->textContent;
					if ( strpos( $href, $homepage_url ) === 0 ) {
						$link_details[] = array(
							'link'         => $href,
							'anchor'       => $anchor,
							'last_checked' => current_time( 'mysql' ),
						);
					}
				}
			}
		}
		return $link_details;
	}

	public static function li_checker_upload_path( $filename = null ) {
		$dirDetails  = wp_upload_dir();
		$uploads_dir = trailingslashit( $dirDetails['basedir'] );

		// check if directory exists and if not create it
		if ( ! file_exists( $uploads_dir . DIRECTORY_SEPARATOR . 'li-checker' ) ) {
			mkdir( $uploads_dir . DIRECTORY_SEPARATOR . 'li-checker', 0755 );
		}

		$path = $uploads_dir . DIRECTORY_SEPARATOR . 'li-checker' . DIRECTORY_SEPARATOR;
		if ( $filename ) {
			$path .= $filename;
		}

		return $path;
	}

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
				$links_html .= '<li class="list-group-item"><a target="_blank" href="' . $link . '">' . implode( ' &rarr; ', $link_only ) . '</a></li>';
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
		    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
		  </head>
		  <body>
		  	<div class="container">
		  		<div class="row">
		  			<div class="col">
		  				<h1 class="my-4">Sitemap</h1>
		    			<ul class="list-group">$links_html</ul>
					</div>
				</div>
			</div>
		  </body>
		</html>
		SITEMAP;

		file_put_contents( self::li_checker_upload_path( 'sitemap.html' ), $sitemap );
	}
}
