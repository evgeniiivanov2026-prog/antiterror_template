<?php
/**
 * Interactive layer: live search, journal feed, and contact dialog.
 *
 * Routes sit on the existing at/v1 namespace and reuse at_news_args()
 * so staff dossiers stay out of the journal feed.
 *
 * @package antiterror-press
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Verify the public REST nonce minted by wp_create_nonce( 'wp_rest' ).
 *
 * @param WP_REST_Request $request Request.
 * @return true|WP_Error
 */
function at_rest_nonce_permission( $request ) {
	$nonce = $request->get_header( 'X-WP-Nonce' );
	if ( ! is_string( $nonce ) || '' === $nonce ) {
		$nonce = $request->get_param( '_wpnonce' );
	}
	$nonce = is_string( $nonce ) ? sanitize_text_field( wp_unslash( $nonce ) ) : '';
	if ( ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
		return new WP_Error(
			'at_forbidden',
			'Invalid nonce.',
			array( 'status' => 403 )
		);
	}
	return true;
}

/**
 * Categories that belong in the journal filter, never the staff directory.
 *
 * @return WP_Term[]
 */
function at_feed_terms() {
	$exclude = at_staff_term_ids();
	$terms   = get_categories(
		array(
			'hide_empty' => true,
			'exclude'    => $exclude,
			'number'     => 8,
			'orderby'    => 'count',
			'order'      => 'DESC',
		)
	);
	return is_array( $terms ) ? $terms : array();
}

/**
 * Open a progressive-enhancement feed mount.
 *
 * @param array $args Feed context.
 */
function at_feed_open( $args ) {
	$args = wp_parse_args(
		$args,
		array(
			'context'  => 'journal',
			'page'     => 1,
			'pages'    => 1,
			'category' => '',
			'taxonomy' => '',
			'term'     => 0,
			'year'     => 0,
			'month'    => 0,
			'author'   => 0,
			'day'      => 0,
			'search'   => '',
		)
	);
	$context = sanitize_key( $args['context'] );
	if ( ! in_array( $context, array( 'journal', 'archive', 'search' ), true ) ) {
		$context = 'journal';
	}
	echo '<div class="feed" data-feed';
	echo ' data-feed-context="' . esc_attr( $context ) . '"';
	echo ' data-feed-page="' . esc_attr( (string) max( 1, (int) $args['page'] ) ) . '"';
	echo ' data-feed-pages="' . esc_attr( (string) max( 0, (int) $args['pages'] ) ) . '"';
	if ( 'journal' === $context ) {
		echo ' data-feed-category="' . esc_attr( sanitize_title( $args['category'] ) ) . '"';
	}
	if ( 'archive' === $context ) {
		$taxonomy = sanitize_key( $args['taxonomy'] );
		if ( in_array( $taxonomy, array( 'category', 'post_tag' ), true ) && (int) $args['term'] > 0 ) {
			echo ' data-feed-taxonomy="' . esc_attr( $taxonomy ) . '"';
			echo ' data-feed-term="' . esc_attr( (string) (int) $args['term'] ) . '"';
		}
		if ( (int) $args['year'] >= 1990 ) {
			echo ' data-feed-year="' . esc_attr( (string) (int) $args['year'] ) . '"';
		}
		if ( (int) $args['month'] >= 1 && (int) $args['month'] <= 12 ) {
			echo ' data-feed-month="' . esc_attr( (string) (int) $args['month'] ) . '"';
		}
		if ( (int) $args['day'] >= 1 && (int) $args['day'] <= 31 ) {
			echo ' data-feed-day="' . esc_attr( (string) (int) $args['day'] ) . '"';
		}
		if ( (int) $args['author'] > 0 ) {
			echo ' data-feed-author="' . esc_attr( (string) (int) $args['author'] ) . '"';
		}
	}
	if ( 'search' === $context && '' !== $args['search'] ) {
		echo ' data-feed-search="' . esc_attr( $args['search'] ) . '"';
	}
	echo '>';
}

/**
 * Category tabs for the journal feed.
 *
 * @param WP_Term[] $terms         Categories.
 * @param string    $current_slug  Active slug, empty for "all".
 */
function at_feed_tabs( $terms, $current_slug = '' ) {
	if ( ! $terms ) {
		return;
	}
	$current_slug = sanitize_title( $current_slug );
	echo '<div class="at-tabs__list" role="tablist" aria-label="' . esc_attr__( 'Рубрики', 'antiterror-press' ) . '">';
	$all_on = '' === $current_slug;
	echo '<button type="button" class="at-tabs__tab' . ( $all_on ? ' is-on' : '' ) . '" role="tab" id="at-feed-tab-all" aria-selected="' . ( $all_on ? 'true' : 'false' ) . '" aria-controls="at-feed-list" data-feed-category="" tabindex="' . ( $all_on ? '0' : '-1' ) . '">Все</button>';
	foreach ( $terms as $term ) {
		if ( ! $term instanceof WP_Term ) {
			continue;
		}
		if ( in_array( $term->slug, at_staff_slugs(), true ) ) {
			continue;
		}
		$on = $current_slug === $term->slug;
		printf(
			'<button type="button" class="at-tabs__tab%s" role="tab" id="at-feed-tab-%s" aria-selected="%s" aria-controls="at-feed-list" data-feed-category="%s" tabindex="%s">%s</button>',
			$on ? ' is-on' : '',
			esc_attr( $term->slug ),
			$on ? 'true' : 'false',
			esc_attr( $term->slug ),
			$on ? '0' : '-1',
			esc_html( $term->name )
		);
	}
	echo '</div>';
}

/**
 * Close the feed mount. The classic pagination stays in the markup for no-JS.
 *
 * @param int $page  Current page.
 * @param int $pages Total pages.
 */
function at_feed_close( $page, $pages ) {
	$more = (int) $page < (int) $pages;
	echo '<p class="empty-note" data-feed-empty hidden>В этой подборке пока пусто.</p>';
	echo '<p class="note" data-feed-status role="status" hidden></p>';
	echo '<button type="button" class="btn btn--ghost feed-more" data-feed-more' . ( $more ? '' : ' hidden' ) . '>Ещё материалы</button>';
	echo '</div>';
}

/**
 * Render post rows with the existing template part.
 *
 * @param WP_Query $query Query.
 * @return string
 */
function at_render_feed_rows( $query ) {
	ob_start();
	while ( $query->have_posts() ) {
		$query->the_post();
		get_template_part( 'template-parts/post', 'row', array( 'post_id' => get_the_ID() ) );
	}
	wp_reset_postdata();
	return (string) ob_get_clean();
}

/**
 * Whitelist feed arguments. Raw query_vars are never accepted.
 *
 * @param WP_REST_Request $request Request.
 * @return array|WP_Error
 */
function at_feed_query_args( $request ) {
	$context  = sanitize_key( (string) $request->get_param( 'context' ) );
	$page     = max( 1, min( 50, (int) $request->get_param( 'page' ) ) );
	$per_page = 12;

	if ( 'journal' === $context ) {
		$args = at_news_args(
			array(
				'posts_per_page' => $per_page,
				'paged'          => $page,
			)
		);
		$slug = sanitize_title( (string) $request->get_param( 'category' ) );
		if ( '' !== $slug ) {
			if ( in_array( $slug, at_staff_slugs(), true ) ) {
				return new WP_Error(
					'at_forbidden_category',
					'The staff directory is not part of the journal feed.',
					array( 'status' => 400 )
				);
			}
			$term = get_category_by_slug( $slug );
			if ( ! $term ) {
				return new WP_Error(
					'at_unknown_category',
					'Unknown category.',
					array( 'status' => 404 )
				);
			}
			$args['cat'] = (int) $term->term_id;
			unset( $args['category__not_in'] );
		}
		return $args;
	}

	if ( 'search' === $context ) {
		$search = sanitize_text_field( (string) $request->get_param( 's' ) );
		$search = trim( $search );
		$length = function_exists( 'mb_strlen' ) ? mb_strlen( $search ) : strlen( $search );
		if ( $length < 2 ) {
			return new WP_Error(
				'at_search_short',
				'Enter at least two characters.',
				array( 'status' => 400 )
			);
		}
		if ( $length > 80 ) {
			$search = function_exists( 'mb_substr' ) ? mb_substr( $search, 0, 80 ) : substr( $search, 0, 80 );
		}
		return array(
			'post_type'           => array( 'post', 'page', 'at_milestone', 'bpla_event', 'at_initiative' ),
			'post_status'         => 'publish',
			's'                   => $search,
			'posts_per_page'      => $per_page,
			'paged'               => $page,
			'ignore_sticky_posts' => true,
		);
	}

	if ( 'archive' === $context ) {
		$args = array(
			'post_type'           => 'post',
			'post_status'         => 'publish',
			'posts_per_page'      => $per_page,
			'paged'               => $page,
			'ignore_sticky_posts' => true,
		);
		$taxonomy = sanitize_key( (string) $request->get_param( 'taxonomy' ) );
		$term_id  = (int) $request->get_param( 'term' );
		if ( $term_id > 0 && in_array( $taxonomy, array( 'category', 'post_tag' ), true ) ) {
			$term = get_term( $term_id, $taxonomy );
			if ( ! $term || is_wp_error( $term ) ) {
				return new WP_Error(
					'at_unknown_term',
					'Unknown term.',
					array( 'status' => 404 )
				);
			}
			$args['tax_query'] = array(
				array(
					'taxonomy' => $taxonomy,
					'field'    => 'term_id',
					'terms'    => array( (int) $term->term_id ),
				),
			);
		}
		$year  = (int) $request->get_param( 'year' );
		$month = (int) $request->get_param( 'month' );
		if ( $year >= 1990 && $year <= 2100 ) {
			$args['year'] = $year;
		}
		if ( $month >= 1 && $month <= 12 ) {
			$args['monthnum'] = $month;
		}
		$day = (int) $request->get_param( 'day' );
		if ( $day >= 1 && $day <= 31 ) {
			$args['day'] = $day;
		}
		$author = (int) $request->get_param( 'author' );
		if ( $author > 0 ) {
			$args['author'] = $author;
		}
		return $args;
	}

	return new WP_Error(
		'at_bad_context',
		'Unknown feed context.',
		array( 'status' => 400 )
	);
}

/**
 * Journal, archive, and search pages as rendered rows.
 *
 * @param WP_REST_Request $request Request.
 * @return WP_REST_Response|WP_Error
 */
function at_rest_feed( $request ) {
	$args = at_feed_query_args( $request );
	if ( is_wp_error( $args ) ) {
		return $args;
	}
	$query = new WP_Query( $args );
	$page  = max( 1, (int) $query->get( 'paged' ) );
	return rest_ensure_response(
		array(
			'html'  => at_render_feed_rows( $query ),
			'page'  => $page,
			'pages' => (int) $query->max_num_pages,
			'found' => (int) $query->found_posts,
		)
	);
}

/**
 * Live search hits. Titles are plain text so the client can use textContent.
 *
 * @param WP_REST_Request $request Request.
 * @return WP_REST_Response|WP_Error
 */
function at_rest_search( $request ) {
	$search = sanitize_text_field( (string) $request->get_param( 'q' ) );
	$search = trim( $search );
	$length = function_exists( 'mb_strlen' ) ? mb_strlen( $search ) : strlen( $search );
	if ( $length < 2 ) {
		return rest_ensure_response( array( 'items' => array() ) );
	}
	if ( $length > 80 ) {
		$search = function_exists( 'mb_substr' ) ? mb_substr( $search, 0, 80 ) : substr( $search, 0, 80 );
	}

	$query = new WP_Query(
		array(
			'post_type'           => array( 'post', 'page', 'at_milestone', 'bpla_event', 'at_initiative' ),
			'post_status'         => 'publish',
			's'                   => $search,
			'posts_per_page'      => 8,
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
		)
	);

	$items = array();
	foreach ( $query->posts as $post ) {
		if ( 'post' === $post->post_type ) {
			$kicker = at_kicker( $post->ID );
		} elseif ( 'at_milestone' === $post->post_type ) {
			$kicker = 'Вехи истории';
		} elseif ( 'at_initiative' === $post->post_type ) {
			$kicker = 'Фонд инициатив';
		} elseif ( 'bpla_event' === $post->post_type ) {
			$kicker = 'БПЛА';
		} else {
			$kicker = 'Страница';
		}
		$items[] = array(
			'title'  => html_entity_decode( get_the_title( $post ), ENT_QUOTES, 'UTF-8' ),
			'url'    => esc_url_raw( get_permalink( $post ) ),
			'kicker' => $kicker ? $kicker : 'Журнал',
			'date'   => get_the_date( 'j F Y', $post ),
			'type'   => $post->post_type,
		);
	}

	return rest_ensure_response( array( 'items' => $items ) );
}

/**
 * Flatten a request field to a string.
 *
 * @param mixed $value Raw field.
 * @return string
 */
function at_contact_scalar( $value ) {
	if ( is_array( $value ) || is_object( $value ) ) {
		return '';
	}
	return (string) $value;
}

/**
 * Shared contact delivery used by admin-post and the dialog fetch.
 *
 * @param string $name     Sender name.
 * @param string $email    Sender email.
 * @param string $message  Message body.
 * @param string $topic    Subject topic.
 * @param bool   $consent  Privacy consent.
 * @param string $company  Honeypot. A filled value is treated as success and dropped.
 * @return true|WP_Error
 */

function at_contact_send( $name, $email, $message, $topic, $consent, $company = '' ) {
	$name    = at_contact_scalar( $name );
	$email   = at_contact_scalar( $email );
	$message = at_contact_scalar( $message );
	$topic   = at_contact_scalar( $topic );
	$company = sanitize_text_field( at_contact_scalar( $company ) );
	if ( '' !== $company ) {
		return true;
	}

	$name    = sanitize_text_field( (string) $name );
	$email   = sanitize_email( (string) $email );
	$message = sanitize_textarea_field( (string) $message );
	$topic   = sanitize_text_field( (string) $topic );
	if ( '' === $topic ) {
		$topic = 'Письмо в редакцию';
	}
	if ( function_exists( 'mb_substr' ) ) {
		$topic   = mb_substr( $topic, 0, 120 );
		$name    = mb_substr( $name, 0, 80 );
		$message = mb_substr( $message, 0, 5000 );
	} else {
		$topic   = substr( $topic, 0, 120 );
		$name    = substr( $name, 0, 80 );
		$message = substr( $message, 0, 5000 );
	}

	$length = function_exists( 'mb_strlen' ) ? mb_strlen( $message ) : strlen( $message );
	if ( '' === $name || ! is_email( $email ) || $length < 8 || ! $consent ) {
		return new WP_Error(
			'at_contact_invalid',
			'Check the name, email, message, and consent.',
			array( 'status' => 400 )
		);
	}
	if ( at_contact_is_limited() ) {
		return new WP_Error(
			'at_contact_limited',
			'Too many messages. Try again later.',
			array( 'status' => 429 )
		);
	}

	$subject = 'Антитеррор: ' . $topic;
	$body    = "Имя: {$name}\nEmail: {$email}\nТема: {$topic}\n\n{$message}";
	$sent    = wp_mail(
		get_option( 'admin_email' ),
		$subject,
		$body,
		array( 'Reply-To: ' . $email )
	);
	if ( ! $sent ) {
		return new WP_Error(
			'at_contact_mail',
			'The message could not be sent.',
			array( 'status' => 500 )
		);
	}
	at_contact_tick();
	return true;
}

/**
 * Transient key for the visitor contact bucket.
 *
 * @return string
 */
function at_contact_bucket() {
	$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '0';
	return 'at_contact_' . md5( $ip );
}

/**
 * Five messages an hour from one address.
 *
 * @return bool
 */
function at_contact_is_limited() {
	return (int) get_transient( at_contact_bucket() ) >= 5;
}

/**
 * Count a successful delivery.
 */
function at_contact_tick() {
	$key   = at_contact_bucket();
	$count = (int) get_transient( $key );
	set_transient( $key, $count + 1, HOUR_IN_SECONDS );
}

/**
 * Dialog / fetch contact endpoint.
 *
 * @param WP_REST_Request $request Request.
 * @return WP_REST_Response|WP_Error
 */
function at_rest_contact( $request ) {
	$result = at_contact_send(
		(string) $request->get_param( 'name' ),
		(string) $request->get_param( 'email' ),
		(string) $request->get_param( 'message' ),
		(string) $request->get_param( 'topic' ),
		(bool) $request->get_param( 'consent' ),
		(string) $request->get_param( 'company' )
	);
	if ( is_wp_error( $result ) ) {
		return $result;
	}
	return rest_ensure_response(
		array(
			'ok'      => true,
			'message' => 'Письмо отправлено. Редакция ответит на указанную почту.',
		)
	);
}

/**
 * Register interactive routes next to the existing view counter.
 */
function at_register_interact_routes() {
	register_rest_route(
		'at/v1',
		'/search',
		array(
			'methods'             => 'GET',
			'permission_callback' => 'at_rest_nonce_permission',
			'callback'            => 'at_rest_search',
			'args'                => array(
				'q' => array(
					'type'              => 'string',
					'required'          => false,
					'sanitize_callback' => 'sanitize_text_field',
				),
			),
		)
	);

	register_rest_route(
		'at/v1',
		'/feed',
		array(
			'methods'             => 'GET',
			'permission_callback' => 'at_rest_nonce_permission',
			'callback'            => 'at_rest_feed',
			'args'                => array(
				'context'  => array(
					'type'              => 'string',
					'required'          => true,
					'sanitize_callback' => 'sanitize_key',
				),
				'page'     => array(
					'type'              => 'integer',
					'required'          => false,
					'sanitize_callback' => 'absint',
				),
				'category' => array(
					'type'              => 'string',
					'required'          => false,
					'sanitize_callback' => 'sanitize_title',
				),
				'taxonomy' => array(
					'type'              => 'string',
					'required'          => false,
					'sanitize_callback' => 'sanitize_key',
				),
				'term'     => array(
					'type'              => 'integer',
					'required'          => false,
					'sanitize_callback' => 'absint',
				),
				'year'     => array(
					'type'              => 'integer',
					'required'          => false,
					'sanitize_callback' => 'absint',
				),
				'month'    => array(
					'type'              => 'integer',
					'required'          => false,
					'sanitize_callback' => 'absint',
				),
				'author'   => array(
					'type'              => 'integer',
					'required'          => false,
					'sanitize_callback' => 'absint',
				),
				'day'      => array(
					'type'              => 'integer',
					'required'          => false,
					'sanitize_callback' => 'absint',
				),
				's'        => array(
					'type'              => 'string',
					'required'          => false,
					'sanitize_callback' => 'sanitize_text_field',
				),
			),
		)
	);

	register_rest_route(
		'at/v1',
		'/contact',
		array(
			'methods'             => 'POST',
			'permission_callback' => 'at_rest_nonce_permission',
			'callback'            => 'at_rest_contact',
			'args'                => array(
				'name'    => array(
					'type'              => 'string',
					'required'          => true,
					'sanitize_callback' => 'sanitize_text_field',
				),
				'email'   => array(
					'type'              => 'string',
					'required'          => true,
					'sanitize_callback' => 'sanitize_email',
				),
				'message' => array(
					'type'              => 'string',
					'required'          => true,
					'sanitize_callback' => 'sanitize_textarea_field',
				),
				'topic'   => array(
					'type'              => 'string',
					'required'          => false,
					'sanitize_callback' => 'sanitize_text_field',
				),
				'consent' => array(
					'type'     => 'boolean',
					'required' => true,
				),
				'company' => array(
					'type'              => 'string',
					'required'          => false,
					'sanitize_callback' => 'sanitize_text_field',
				),
			),
		)
	);
}
add_action( 'rest_api_init', 'at_register_interact_routes' );
