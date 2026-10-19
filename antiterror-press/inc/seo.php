<?php
/**
 * Заголовок, описание, соцсети и разметка для поиска.
 *
 * @package antiterror-press
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function at_trim_meta( $text ) {
	$text = trim( preg_replace( '/\s+/u', ' ', wp_strip_all_tags( (string) $text ) ) );
	if ( function_exists( 'mb_strlen' ) && mb_strlen( $text, 'UTF-8' ) > 180 ) {
		$text = rtrim( mb_substr( $text, 0, 177, 'UTF-8' ) ) . '…';
	}
	return $text;
}

function at_meta_description() {
	if ( is_search() || is_404() ) {
		return '';
	}
	if ( is_page_template( 'page-templates/template-media.php' ) ) {
		$excerpt = get_the_excerpt();
		if ( $excerpt ) {
			return at_paged_description( at_trim_meta( $excerpt ) );
		}
		return at_paged_description( 'Архив фото и видео: портреты сотрудников и кадры материалов по темам журнала.' );
	}
	if ( is_singular() ) {
		$post = get_queried_object();
		if ( $post instanceof WP_Post && has_excerpt( $post ) ) {
			return at_paged_description( at_trim_meta( get_the_excerpt( $post ) ) );
		}
		if ( $post instanceof WP_Post ) {
			return at_paged_description( at_trim_meta( $post->post_content ) );
		}
	}
	if ( is_category() || is_tag() || is_tax() ) {
		$desc = term_description();
		if ( $desc ) {
			return at_paged_description( at_trim_meta( $desc ) );
		}
		return at_paged_description( at_trim_meta( sprintf( 'Материалы раздела «%s» в журнале «%s».', single_term_title( '', false ), get_bloginfo( 'name' ) ) ) );
	}
	$description = get_bloginfo( 'description' );
	if ( $description ) {
		return at_paged_description( at_trim_meta( $description ) );
	}
	return at_paged_description( 'Журнал о борьбе с терроризмом и экстремизмом. За межнациональную дружбу.' );
}

/**
 * Distinguish paged archives so descriptions are not identical.
 *
 * @param string $text Description.
 * @return string
 */
function at_paged_description( $text ) {
	$text  = (string) $text;
	$paged = max( (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ) );
	if ( $paged > 1 && '' !== $text ) {
		return at_trim_meta( $text . ' Страница ' . $paged . '.' );
	}
	return $text;
}

function at_share_media() {
	$media = array(
		'url'    => '',
		'width'  => 0,
		'height' => 0,
		'alt'    => '',
	);
	if ( is_singular() ) {
		$id = get_queried_object_id();
		$thumb = get_post_thumbnail_id( $id );
		if ( $thumb ) {
			$src = wp_get_attachment_image_src( $thumb, 'large' );
			if ( $src ) {
				$media['url']    = at_public_url( $src[0] );
				$media['width']  = (int) $src[1];
				$media['height'] = (int) $src[2];
				$media['alt']    = (string) get_post_meta( $thumb, '_wp_attachment_image_alt', true );
				if ( '' === $media['alt'] ) {
					$media['alt'] = get_the_title( $id );
				}
			}
		}
		if ( ! $media['url'] ) {
			$sources = at_lead_sources( $id );
			if ( ! empty( $sources['image'] ) ) {
				$media['url'] = at_public_url( $sources['image'] );
				$media['alt'] = get_the_title( $id );
			}
		}
	}
	if ( ! $media['url'] ) {
		$logo = (int) get_theme_mod( 'custom_logo' );
		if ( $logo ) {
			$src = wp_get_attachment_image_src( $logo, 'full' );
			if ( $src ) {
				$media['url']    = at_public_url( $src[0] );
				$media['width']  = (int) $src[1];
				$media['height'] = (int) $src[2];
				$media['alt']    = get_bloginfo( 'name' );
			}
		}
	}
	return $media;
}

function at_share_image() {
	$media = at_share_media();
	return $media['url'];
}

function at_current_url() {
	if ( is_singular() ) {
		return get_permalink();
	}
	if ( is_front_page() ) {
		return home_url( '/' );
	}
	$paged = max( 1, (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ) );
	$link  = get_pagenum_link( $paged );
	return $link ? $link : home_url( '/' );
}

function at_document_title_separator() {
	return '—';
}
add_filter( 'document_title_separator', 'at_document_title_separator' );

function at_robots( $robots ) {
	if ( is_404() ) {
		$robots['noindex'] = true;
		$robots['follow']  = true;
		return $robots;
	}
	if ( is_search() || is_preview() ) {
		$robots['noindex'] = true;
		$robots['follow']  = true;
		return $robots;
	}
	$robots['max-image-preview'] = 'large';
	$robots['max-snippet']       = '-1';
	$robots['max-video-preview'] = '-1';
	return $robots;
}
add_filter( 'wp_robots', 'at_robots' );

function at_image_alt( $attr, $attachment ) {
	if ( ! empty( $attr['alt'] ) ) {
		return $attr;
	}
	$parent = $attachment->post_parent ? wp_strip_all_tags( get_the_title( $attachment->post_parent ) ) : '';
	$attr['alt'] = $parent ? $parent : get_bloginfo( 'name' );
	return $attr;
}
add_filter( 'wp_get_attachment_image_attributes', 'at_image_alt', 10, 2 );

function at_schema_graph() {
	$name = get_bloginfo( 'name' );
	$home = home_url( '/' );
	$logo = (int) get_theme_mod( 'custom_logo' );
	$org  = array(
		'@type' => 'NewsMediaOrganization',
		'@id'   => $home . '#org',
		'name'  => $name,
		'url'   => $home,
	);
	if ( $logo ) {
		$logo_url = at_public_url( (string) wp_get_attachment_image_url( $logo, 'full' ) );
		if ( $logo_url ) {
			$org['logo'] = array(
				'@type' => 'ImageObject',
				'url'   => $logo_url,
			);
		}
	}
	$phone   = trim( (string) get_theme_mod( 'at_org_phone', '' ) );
	$address = trim( (string) get_theme_mod( 'at_org_address', '' ) );
	if ( $phone ) {
		$org['telephone'] = $phone;
	}
	if ( $address ) {
		$org['address'] = array(
			'@type'         => 'PostalAddress',
			'streetAddress' => $address,
		);
	}
	$same = array_filter(
		array(
			get_theme_mod( 'at_vk', 'https://vk.com/antiterror_rossii' ),
			get_theme_mod( 'at_tg', 'https://t.me/terrorismunet' ),
		)
	);
	if ( $same ) {
		$org['sameAs'] = array_values( $same );
	}

	$graph = array(
		$org,
		array(
			'@type'           => 'WebSite',
			'@id'             => $home . '#website',
			'url'             => $home,
			'name'            => $name,
			'description'     => at_meta_description() ? at_meta_description() : get_bloginfo( 'description' ),
			'publisher'       => array( '@id' => $home . '#org' ),
			'inLanguage'      => get_locale(),
			'potentialAction' => array(
				'@type'       => 'SearchAction',
				'target'      => home_url( '/?s={search_term_string}' ),
				'query-input' => 'required name=search_term_string',
			),
		),
	);

	$trail = at_crumb_trail();
	if ( $trail ) {
		$here  = at_current_url();
		$items = array();
		$last  = count( $trail ) - 1;
		foreach ( $trail as $index => $crumb ) {
			$url = $crumb[1];
			if ( ! $url && $index === $last ) {
				$url = $here;
			}
			$entry = array(
				'@type'    => 'ListItem',
				'position' => $index + 1,
				'name'     => $crumb[0],
			);
			if ( $url ) {
				$entry['item'] = $url;
			}
			$items[] = $entry;
		}
		$graph[] = array(
			'@type'           => 'BreadcrumbList',
			'@id'             => $here . '#breadcrumbs',
			'itemListElement' => $items,
		);
	}

	if ( is_singular( 'post' ) ) {
		$post    = get_queried_object();
		$is_staff = $post instanceof WP_Post && at_is_staff_post( $post->ID );
		$image   = at_share_image();
		$entry   = array(
			'@type'            => $is_staff ? 'ProfilePage' : 'NewsArticle',
			'@id'              => get_permalink( $post ) . '#entry',
			'headline'         => get_the_title( $post ),
			'url'              => get_permalink( $post ),
			'datePublished'    => get_the_date( 'c', $post ),
			'dateModified'     => get_the_modified_date( 'c', $post ),
			'description'      => at_meta_description(),
			'inLanguage'       => get_locale(),
			'mainEntityOfPage' => get_permalink( $post ),
			'publisher'        => array( '@id' => $home . '#org' ),
			'author'           => array(
				'@type' => 'Person',
				'name'  => get_the_author_meta( 'display_name', $post->post_author ),
			),
		);
		if ( $image ) {
			$entry['image'] = array( $image );
		}
		$stats = at_reading_stats( $post->ID );
		if ( $stats['words'] > 0 ) {
			$entry['wordCount']    = $stats['words'];
			$entry['timeRequired'] = 'PT' . $stats['minutes'] . 'M';
		}
		$entry['isAccessibleForFree'] = true;
		$tags = get_the_tags( $post->ID );
		if ( $tags && ! is_wp_error( $tags ) ) {
			$entry['keywords'] = implode( ', ', wp_list_pluck( $tags, 'name' ) );
		}
		$cat = at_primary_category( $post->ID );
		if ( $cat ) {
			$entry['articleSection'] = $cat->name;
		}
		if ( $is_staff ) {
			$person = array(
				'@type' => 'Person',
				'name'  => get_the_title( $post ),
				'url'   => get_permalink( $post ),
			);
			$rank = get_post_meta( $post->ID, '_at_rank', true );
			$unit = get_post_meta( $post->ID, '_at_unit', true );
			if ( $rank ) {
				$person['jobTitle'] = $rank;
			}
			if ( $unit ) {
				$person['affiliation'] = $unit;
			}
			if ( $image ) {
				$person['image'] = $image;
			}
			$entry['mainEntity'] = $person;
		}
		$graph[] = $entry;
	} elseif ( is_singular( array( 'at_milestone', 'bpla_event' ) ) ) {
		$post  = get_queried_object();
		$image = at_share_image();
		$entry = array(
			'@type'            => 'Article',
			'@id'              => get_permalink( $post ) . '#entry',
			'headline'         => get_the_title( $post ),
			'url'              => get_permalink( $post ),
			'datePublished'    => get_the_date( 'c', $post ),
			'dateModified'     => get_the_modified_date( 'c', $post ),
			'description'      => at_meta_description(),
			'inLanguage'       => get_locale(),
			'mainEntityOfPage' => get_permalink( $post ),
			'publisher'        => array( '@id' => $home . '#org' ),
		);
		if ( $image ) {
			$entry['image'] = array( $image );
		}
		$graph[] = $entry;
	} elseif ( is_page_template( 'page-templates/template-media.php' ) ) {
		$graph[] = array(
			'@type'       => 'CollectionPage',
			'@id'         => get_permalink() . '#archive',
			'name'        => get_the_title(),
			'url'         => get_permalink(),
			'description' => at_meta_description(),
			'isPartOf'    => array( '@id' => $home . '#website' ),
		);
	}

	$url         = at_current_url();
	$description = at_meta_description();
	$graph[]     = array(
		'@type'       => 'WebPage',
		'@id'         => $url . '#webpage',
		'url'         => $url,
		'name'        => wp_get_document_title(),
		'description' => $description,
		'isPartOf'    => array( '@id' => $home . '#website' ),
		'inLanguage'  => get_locale(),
	);

	if ( is_home() || is_archive() || is_search() ) {
		global $wp_query;
		$elements = array();
		$position = 1;
		$posts    = ( $wp_query instanceof WP_Query ) ? array_slice( $wp_query->posts, 0, 12 ) : array();
		foreach ( $posts as $list_post ) {
			if ( ! $list_post instanceof WP_Post || 'publish' !== $list_post->post_status ) {
				continue;
			}
			$elements[] = array(
				'@type'    => 'ListItem',
				'position' => $position,
				'url'      => get_permalink( $list_post ),
				'name'     => get_the_title( $list_post ),
			);
			$position++;
		}
		if ( $elements ) {
			$graph[] = array(
				'@type'           => 'ItemList',
				'@id'             => $url . '#list',
				'itemListElement' => $elements,
			);
		}
	}

	return array(
		'@context' => 'https://schema.org',
		'@graph'   => $graph,
	);
}

function at_seo_head() {
	if ( is_feed() || is_embed() ) {
		return;
	}
	$description = at_meta_description();
	if ( $description ) {
		echo '<meta name="description" content="' . esc_attr( $description ) . '">' . "\n";
	}
	$title = wp_get_document_title();
	$url   = at_current_url();
	$type  = is_singular( array( 'post', 'at_milestone', 'bpla_event' ) ) ? 'article' : 'website';
	$media = at_share_media();
	$image = $media['url'];
	echo '<meta property="og:locale" content="' . esc_attr( get_locale() ) . '">' . "\n";
	echo '<meta property="og:site_name" content="' . esc_attr( get_bloginfo( 'name' ) ) . '">' . "\n";
	echo '<meta property="og:title" content="' . esc_attr( $title ) . '">' . "\n";
	echo '<meta property="og:url" content="' . esc_url( $url ) . '">' . "\n";
	echo '<meta property="og:type" content="' . esc_attr( $type ) . '">' . "\n";
	if ( $description ) {
		echo '<meta property="og:description" content="' . esc_attr( $description ) . '">' . "\n";
	}
	if ( $image ) {
		echo '<meta property="og:image" content="' . esc_url( $image ) . '">' . "\n";
		echo '<meta property="og:image:alt" content="' . esc_attr( $media['alt'] ) . '">' . "\n";
		if ( $media['width'] && $media['height'] ) {
			echo '<meta property="og:image:width" content="' . esc_attr( (string) $media['width'] ) . '">' . "\n";
			echo '<meta property="og:image:height" content="' . esc_attr( (string) $media['height'] ) . '">' . "\n";
		}
	}
	if ( is_singular( array( 'post', 'at_milestone', 'bpla_event' ) ) ) {
		echo '<meta property="article:published_time" content="' . esc_attr( get_the_date( 'c' ) ) . '">' . "\n";
		echo '<meta property="article:modified_time" content="' . esc_attr( get_the_modified_date( 'c' ) ) . '">' . "\n";
		$cat = at_primary_category();
		if ( $cat ) {
			echo '<meta property="article:section" content="' . esc_attr( $cat->name ) . '">' . "\n";
		}
		$tags = get_the_tags();
		if ( $tags && ! is_wp_error( $tags ) ) {
			foreach ( array_slice( $tags, 0, 6 ) as $tag ) {
				echo '<meta property="article:tag" content="' . esc_attr( $tag->name ) . '">' . "\n";
			}
		}
	}
	echo '<meta name="twitter:card" content="' . esc_attr( $image ? 'summary_large_image' : 'summary' ) . '">' . "\n";
	echo '<meta name="twitter:title" content="' . esc_attr( $title ) . '">' . "\n";
	if ( $description ) {
		echo '<meta name="twitter:description" content="' . esc_attr( $description ) . '">' . "\n";
	}
	if ( $image ) {
		echo '<meta name="twitter:image" content="' . esc_url( $image ) . '">' . "\n";
		echo '<meta name="twitter:image:alt" content="' . esc_attr( $media['alt'] ) . '">' . "\n";
	}

	$schema = wp_json_encode(
		at_schema_graph(),
		JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
	);
	if ( $schema ) {
		echo '<script type="application/ld+json">' . $schema . '</script>' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}
add_action( 'wp_head', 'at_seo_head', 1 );
