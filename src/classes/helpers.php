<?php

namespace ROCKET_WP_CRAWLER;

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
}
