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
			return at_trim_meta( $excerpt );
		}
		return 'Архив фото и видео: портреты сотрудников и кадры материалов по темам журнала.';
	}
	if ( is_singular() ) {
		$post = get_queried_object();
		if ( $post instanceof WP_Post && has_excerpt( $post ) ) {
			return at_trim_meta( get_the_excerpt( $post ) );
		}
		if ( $post instanceof WP_Post ) {
			return at_trim_meta( $post->post_content );
		}
	}
	if ( is_category() || is_tag() || is_tax() ) {
		$desc = term_description();
		if ( $desc ) {
			return at_trim_meta( $desc );
		}
		return at_trim_meta( sprintf( 'Материалы раздела «%s» в журнале «%s».', single_term_title( '', false ), get_bloginfo( 'name' ) ) );
	}
	$description = get_bloginfo( 'description' );
	if ( $description ) {
		return at_trim_meta( $description );
	}
	return 'Журнал о борьбе с терроризмом и экстремизмом. За межнациональную дружбу.';
}

function at_share_image() {
	if ( is_singular() ) {
		$id = get_queried_object_id();
		if ( has_post_thumbnail( $id ) ) {
			$url = get_the_post_thumbnail_url( $id, 'large' );
			if ( $url ) {
				return $url;
			}
		}
		$sources = at_lead_sources( $id );
		if ( ! empty( $sources['image'] ) ) {
			return $sources['image'];
		}
	}
	$logo = (int) get_theme_mod( 'custom_logo' );
	if ( $logo ) {
		$url = wp_get_attachment_image_url( $logo, 'full' );
		if ( $url ) {
			return $url;
		}
	}
	return '';
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
	if ( is_search() ) {
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
		$logo_url = wp_get_attachment_image_url( $logo, 'full' );
		if ( $logo_url ) {
			$org['logo'] = array(
				'@type' => 'ImageObject',
				'url'   => $logo_url,
			);
		}
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
	$type  = is_singular( 'post' ) ? 'article' : 'website';
	$image = at_share_image();
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
	}
	if ( is_singular( 'post' ) ) {
		echo '<meta property="article:published_time" content="' . esc_attr( get_the_date( 'c' ) ) . '">' . "\n";
		echo '<meta property="article:modified_time" content="' . esc_attr( get_the_modified_date( 'c' ) ) . '">' . "\n";
		$cat = at_primary_category();
		if ( $cat ) {
			echo '<meta property="article:section" content="' . esc_attr( $cat->name ) . '">' . "\n";
		}
	}
	echo '<meta name="twitter:card" content="' . esc_attr( $image ? 'summary_large_image' : 'summary' ) . '">' . "\n";
	echo '<meta name="twitter:title" content="' . esc_attr( $title ) . '">' . "\n";
	if ( $description ) {
		echo '<meta name="twitter:description" content="' . esc_attr( $description ) . '">' . "\n";
	}
	if ( $image ) {
		echo '<meta name="twitter:image" content="' . esc_url( $image ) . '">' . "\n";
	}

	$schema = wp_json_encode( at_schema_graph(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
	if ( $schema ) {
		echo '<script type="application/ld+json">' . $schema . '</script>' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}
add_action( 'wp_head', 'at_seo_head', 1 );
