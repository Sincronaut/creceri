<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Image ALT helpers for blocks and content.
 *
 * Allows per‑page/template customization via post meta while
 * preserving explicit alt text set by blocks/editors.
 */

if ( ! function_exists( 'creceri_resolve_page_alt_text' ) ) {
  /**
   * Resolve a context-aware ALT text for current queried object.
   * Priority:
   * 1) Post meta: 'template_image_alt' (template-level override)
   * 2) Post meta: 'page_image_alt' or 'image_alt'
   * 3) Current post/page title
   * 4) Site name
   * 5) Provided fallback
   */
  function creceri_resolve_page_alt_text( $fallback = '' ) {
    $alt = '';

    if ( is_singular() ) {
      $post_id = get_the_ID();
      if ( $post_id ) {
        $candidates = array( 'template_image_alt', 'page_image_alt', 'image_alt' );
        foreach ( $candidates as $key ) {
          $val = get_post_meta( $post_id, $key, true );
          if ( is_string( $val ) && trim( $val ) !== '' ) {
            $alt = $val;
            break;
          }
        }
        if ( $alt === '' ) {
          $title = get_the_title( $post_id );
          if ( is_string( $title ) && trim( $title ) !== '' ) {
            $alt = $title;
          }
        }
      }
    }

    if ( $alt === '' ) {
      $blogname = get_bloginfo( 'name', 'display' );
      if ( is_string( $blogname ) && trim( $blogname ) !== '' ) {
        $alt = $blogname;
      }
    }

    if ( $alt === '' && is_string( $fallback ) ) {
      $alt = $fallback;
    }

    return esc_html( wp_strip_all_tags( $alt ) );
  }
}

if ( ! function_exists( 'creceri_inject_missing_img_alt' ) ) {
  /**
   * Ensure all <img> elements have a non-empty alt.
   * Does NOT override existing non-empty alt attributes.
   */
  function creceri_inject_missing_img_alt( $html ) {
    if ( ! is_string( $html ) || $html === '' ) return $html;

    // Quick check to avoid DOM work when no <img> present
    if ( stripos( $html, '<img' ) === false ) return $html;

    $alt_fallback = creceri_resolve_page_alt_text();
    $template_override = '';
    if ( is_singular() ) {
      $pid = get_the_ID();
      if ( $pid ) {
        $to = get_post_meta( $pid, 'template_image_alt', true );
        if ( is_string( $to ) && trim( $to ) !== '' ) {
          $template_override = esc_html( wp_strip_all_tags( $to ) );
        }
      }
    }

    // Use DOMDocument to safely edit attributes
    $libxml_prev = libxml_use_internal_errors( true );
    $doc = new DOMDocument();
    $loaded = $doc->loadHTML( '<?xml encoding="utf-8" ?>' . $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );
    if ( ! $loaded ) {
      libxml_clear_errors();
      libxml_use_internal_errors( $libxml_prev );
      return $html;
    }

    $imgs = $doc->getElementsByTagName( 'img' );
    /** @var DOMElement $img */
    foreach ( $imgs as $img ) {
      $has = $img->hasAttribute( 'alt' );
      $val = $has ? $img->getAttribute( 'alt' ) : '';
      if ( $template_override !== '' ) {
        // Explicit template override takes precedence site-wide on the page
        $img->setAttribute( 'alt', $template_override );
      } else if ( ! $has || trim( $val ) === '' ) {
        // Otherwise, only fill missing/empty alts
        $img->setAttribute( 'alt', $alt_fallback );
      }
    }

    $out = $doc->saveHTML();
    libxml_clear_errors();
    libxml_use_internal_errors( $libxml_prev );

    // Remove XML prolog if present from our trick
    if ( strpos( $out, '<?xml' ) === 0 ) {
      $pos = strpos( $out, '?>' );
      if ( $pos !== false ) {
        $out = substr( $out, $pos + 2 );
      }
    }

    return $out;
  }
}

if ( ! function_exists( 'creceri_block_alt_from_attrs' ) ) {
  /**
   * Compute a descriptive alt based on block name + attributes.
   * Returns empty string when not applicable.
   */
  function creceri_block_alt_from_attrs( $block ) {
    $name = isset( $block['blockName'] ) ? (string) $block['blockName'] : '';
    $attrs = isset( $block['attrs'] ) && is_array( $block['attrs'] ) ? $block['attrs'] : array();

    $mk = function( $parts ) {
      $txt = trim( preg_replace( '/\s+/', ' ', wp_strip_all_tags( implode( ' — ', array_filter( array_map( 'strval', $parts ) ) ) ) ) );
      return $txt;
    };

    // Banner variants: use brand + line + lead unless image.alt is provided
    if ( in_array( $name, array( 'child/banner', 'child/banner-2', 'child/banner-3' ), true ) ) {
      $img = isset( $attrs['image'] ) && is_array( $attrs['image'] ) ? $attrs['image'] : array();
      if ( ! empty( $img['alt'] ) ) return (string) $img['alt'];
      $brand = isset( $attrs['brand'] ) ? $attrs['brand'] : '';
      $line  = isset( $attrs['line'] ) ? $attrs['line'] : '';
      $lead  = isset( $attrs['lead'] ) ? $attrs['lead'] : '';
      return $mk( array( $brand, $line, $lead ) );
    }

    // Cards: map each image to card title/text if possible; return section-level description
    if ( $name === 'child/card' ) {
      $title = isset( $attrs['title'] ) ? $attrs['title'] : '';
      $intro = isset( $attrs['intro'] ) ? $attrs['intro'] : '';
      return $mk( array( $title, $intro ) );
    }

    return '';
  }
}

if ( ! function_exists( 'creceri_filter_rendered_block_for_alt' ) ) {
  /** Hook for render_block to add missing alt attributes, with block-aware descriptions */
  function creceri_filter_rendered_block_for_alt( $block_content, $block ) {
    if ( ! is_string( $block_content ) || $block_content === '' ) return $block_content;

    $block_alt = creceri_block_alt_from_attrs( $block );

    // Global template override (forces fixed alt on the page)
    $template_override = '';
    if ( is_singular() ) {
      $pid = get_the_ID();
      if ( $pid ) {
        $to = get_post_meta( $pid, 'template_image_alt', true );
        if ( is_string( $to ) && trim( $to ) !== '' ) {
          $template_override = esc_html( wp_strip_all_tags( $to ) );
        }
      }
    }
    if ( $block_alt === '' && (!isset($block['blockName']) || $block['blockName'] !== 'child/card') ) {
      // No block-specific context; do generic injection
      return creceri_inject_missing_img_alt( $block_content );
    }

    // When we have a block description, prefer it over page fallback for this block's content
    $libxml_prev = libxml_use_internal_errors( true );
    $doc = new DOMDocument();
    $loaded = $doc->loadHTML( '<?xml encoding="utf-8" ?>' . $block_content, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );
    if ( ! $loaded ) {
      libxml_clear_errors();
      libxml_use_internal_errors( $libxml_prev );
      return $block_content;
    }

    $imgs = $doc->getElementsByTagName( 'img' );

    // Special handling for child/card: map each image to its card title/text
    if ( isset( $block['blockName'] ) && $block['blockName'] === 'child/card' ) {
      $attrs = isset( $block['attrs'] ) && is_array( $block['attrs'] ) ? $block['attrs'] : array();
      $cards = isset( $attrs['cards'] ) && is_array( $attrs['cards'] ) ? $attrs['cards'] : array();
      $alts  = array();
      foreach ( $cards as $c ) {
        $t = isset( $c['title'] ) ? $c['title'] : '';
        $x = isset( $c['text'] )  ? $c['text']  : '';
        $alts[] = trim( preg_replace( '/\s+/', ' ', wp_strip_all_tags( $t !== '' ? $t : $x ) ) );
      }
      $i = 0;
      foreach ( $imgs as $img ) {
        $has = $img->hasAttribute( 'alt' );
        $val = $has ? $img->getAttribute( 'alt' ) : '';
        if ( trim( (string) $val ) === '' ) {
          $candidate = isset( $alts[$i] ) && $alts[$i] !== '' ? $alts[$i] : $block_alt;
          if ( $candidate === '' ) $candidate = creceri_resolve_page_alt_text();
          $img->setAttribute( 'alt', $candidate );
        }
        $i++;
      }
      $out = $doc->saveHTML();
      libxml_clear_errors();
      libxml_use_internal_errors( $libxml_prev );
      if ( strpos( $out, '<?xml' ) === 0 ) {
        $pos = strpos( $out, '?>' );
        if ( $pos !== false ) $out = substr( $out, $pos + 2 );
      }
      return $out;
    }
    /** @var DOMElement $img */
    foreach ( $imgs as $img ) {
      $has = $img->hasAttribute( 'alt' );
      $val = $has ? $img->getAttribute( 'alt' ) : '';
      $target_alt = $template_override !== '' ? $template_override : $block_alt;
      if ( in_array( $block['blockName'] ?? '', array( 'child/banner', 'child/banner-2', 'child/banner-3' ), true ) ) {
        // For banner blocks, always set a fixed, descriptive alt from block content (or template override)
        $img->setAttribute( 'alt', $target_alt !== '' ? $target_alt : creceri_resolve_page_alt_text() );
      } else if ( trim( (string) $val ) === '' ) {
        // Other blocks: only fill when empty
        $img->setAttribute( 'alt', $target_alt !== '' ? $target_alt : creceri_resolve_page_alt_text() );
      }
    }

    $out = $doc->saveHTML();
    libxml_clear_errors();
    libxml_use_internal_errors( $libxml_prev );
    if ( strpos( $out, '<?xml' ) === 0 ) {
      $pos = strpos( $out, '?>' );
      if ( $pos !== false ) $out = substr( $out, $pos + 2 );
    }
    return $out;
  }
}

// Hook into rendered blocks and content
add_filter( 'render_block', 'creceri_filter_rendered_block_for_alt', 20, 2 );
add_filter( 'the_content', 'creceri_inject_missing_img_alt', 20 );
