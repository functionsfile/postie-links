<?php
/**
 * Core plugin functionality
 *
 * @package PostieLinksAddOn
 */

namespace FunctionsFile\PostieLinksAddOn;

defined( 'ABSPATH' ) || exit;

// Update the post if the content is deemed to only contain a URL.
add_filter( 'postie_post_before', __NAMESPACE__ . '\maybe_update_post', 999 );

/**
 * Is the string a URL?
 *
 * @todo Some mail programs create a hyperlink when inserting a URL. We'll
 *       probably also want to check if the email body contains a hyperlink.
 *
 * @param string $string String of text.
 * @return bool
 */
function is_url( $string ) {

	if ( filter_var( $string, FILTER_VALIDATE_URL ) ) {
		return true;
	}

	require_once( FUFI_POSTIE_LINKS_ADDON_INC . 'class-idna-convert.php' );

	$idna = new \idna_convert( array( 'idn_version' => '2008' ) );

	if ( filter_var( $idna->encode( $string, 'utf8' ), FILTER_VALIDATE_URL ) ) {
		return true;
	}

	return false;
}

/**
 * Update the post if the content is deemed to only contain a URL.
 *
 * 1. Strip 'utm' query parameters.
 * 2. Save the URL in the `fufi_postie_links_url` meta field.
 * 3. Set the post format to `link`
 * 4. Update the post content to contain a paragraph block with a link element.
 *
 * @param array $post An array of elements that make up a post to insert.
 * @return array
 */
function maybe_update_post( $post ) {

	$post_content = trim( wp_strip_all_tags( $post['post_content'], true ) );

	if ( strpos( $post_content, ' ' ) ) {
		return $post;
	}

	$post_content = esc_url( $post_content );
	$post_content = str_replace( 'http://', 'https://', $post_content );

	if ( ! is_url( $post_content ) ) {
		return $post;
	}

	/**
	 * Allows disabling the removal of 'utm' query parameters from the URL.
	 *
	 * @var bool   $strip_utm    Whether to remove the 'utm' parameters.
	 * @var string $post_content URL.
	 */
	if ( apply_filters( 'fufi_postie_links_remove_utm', true, $post_content ) ) {
		/*
		 * Remove 'utm' query parameters.
		 *
		 * @see https://en.wikipedia.org/wiki/UTM_parameters
		 */
		$post_content = preg_replace( "/&?utm_(.*?)\=[^&]+/", '', $post_content );
	}

	add_post_meta( $post['ID'], 'fufi_postie_links_url', $post_content );

	/**
	 * Allows disabling or overriding the post format.
	 *
	 * @var bool   $post_format Post format, return false to not set a post format.
	 * @var string $url         URL.
	 * @var array  $post        List of post fields.
	 */
	$post_format = apply_filters( 'fufi_postie_links_post_format', 'link', $post_content, $post );

	if ( $post_format ) {

		set_post_format( $post['ID'], (string) $post_format );
	}

	/**
	 * Allows filtering the URL before the post is updated.
	 *
	 * @var string $post_content URL.
	 */
	$post_content = apply_filters( 'fufi_postie_links_url', $post_content );
	$post_content = sprintf(
		'<!-- wp:paragraph --><p><a href="%s">%s</a></p><!-- /wp:paragraph -->',
		esc_url( $post_content ),
		wp_kses_post( $post_content )
	);

	$post['post_content'] = $post_content;

	/**
	 * Allows filtering the post array before the post is updated.
	 *
	 * @var array  $post         List of post fields with updated 'post_content' item.
	 * @var string $post_content URL.
	 */
	$post = apply_filters(
		'fufi_postie_links_post',
		$post,
		$post_content
	);

	return $post;
}
