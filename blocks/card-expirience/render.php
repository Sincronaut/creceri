<?php
if ( ! defined('ABSPATH') ) { exit; }

/**
 * Dynamic render for child/ux-cards
 * Accepts:
 * - anchor (string)
 * - align (string)    e.g., "wide"
 * - className (string)
 * - title (string)
 * - titleId (string)  aria-labelledby target; falls back to "{$anchor}-title" or "ux-cards-title"
 * - intro (string)    supports minimal HTML via wp_kses
 * - items (array)     each: img:{src,alt,width,height,loading,decoding}, title, copy, ctaText, ctaUrl
 */

/* ---------- Helpers ---------- */
if ( ! function_exists('child_uxcards_allowed_intro_tags') ) {
  function child_uxcards_allowed_intro_tags() {
    return array(
      'a'      => array( 'href' => true, 'title' => true, 'target' => true, 'rel' => true ),
      'strong' => array(),
      'b'      => array(),
      'em'     => array(),
      'i'      => array(),
      'br'     => array(),
      'span'   => array( 'class' => true ),
    );
  }
}

if ( ! function_exists('child_uxcards_image_from_attr') ) {
  function child_uxcards_image_from_attr( $raw ) {
    $raw = is_array($raw ?? null) ? $raw : array();
    $defaults = array(
      'src'      => '',
      'alt'      => '',
      'width'    => 0,
      'height'   => 0,
      'loading'  => 'lazy',
      'decoding' => 'async',
    );
    return array(
      'src'      => esc_url( ( isset($raw['src']) && trim((string)$raw['src']) !== '' ) ? $raw['src'] : $defaults['src'] ),
      'alt'      => esc_attr( ( isset($raw['alt']) && trim((string)$raw['alt']) !== '' ) ? $raw['alt'] : $defaults['alt'] ),
      'width'    => ( isset($raw['width'])  && intval($raw['width'])  > 0 ) ? intval($raw['width'])  : 0,
      'height'   => ( isset($raw['height']) && intval($raw['height']) > 0 ) ? intval($raw['height']) : 0,
      'loading'  => esc_attr( ( isset($raw['loading'])  && trim((string)$raw['loading'])  !== '' ) ? $raw['loading']  : $defaults['loading'] ),
      'decoding' => esc_attr( ( isset($raw['decoding']) && trim((string)$raw['decoding']) !== '' ) ? $raw['decoding'] : $defaults['decoding'] ),
    );
  }
}

/* ---------- Attributes ---------- */
$A = is_array($attributes ?? null) ? $attributes : array();

$anchor   = sanitize_title( $A['anchor'] ?? '' );
$classes  = array( 'ux-cards' );

if ( ! empty($A['align']) ) {
  $classes[] = 'align' . sanitize_html_class( $A['align'] );
}
if ( ! empty($A['className']) ) {
  $extra = preg_split('/\s+/', $A['className']);
  foreach ( $extra as $cls ) {
    $cls = sanitize_html_class($cls);
    if ($cls) { $classes[] = $cls; }
  }
}

$title_default = __('Design That Shapes Experiences', 'vite-ttf-child-creceri');
$intro_default = __('Every click, scroll, and tap is a touchpoint. Build outcomes users value with focused UX investments. Explore the core workstreams that move the needle:', 'vite-ttf-child-creceri');

$title_has_value = isset($A['title']) && is_string($A['title']) && trim(wp_strip_all_tags($A['title'])) !== '';
$intro_has_value = isset($A['intro']) && is_string($A['intro']) && trim(wp_strip_all_tags($A['intro'])) !== '';

$title = wp_kses_post( $title_has_value ? $A['title'] : $title_default );
$intro_raw = $intro_has_value ? $A['intro'] : $intro_default;
$intro = $intro_raw !== '' ? wpautop( wp_kses( $intro_raw, child_uxcards_allowed_intro_tags() ) ) : '';

$title_id = sanitize_html_class( $A['titleId'] ?? ( $anchor ? $anchor . '-title' : 'ux-cards-title' ) );

/* ---------- Items ---------- */
$items_default = array(
  array(
    'img'     => array(
      'src'      => 'https://creceri.com/wp-content/uploads/2025/10/1b524548540f27eccf61558865254cc79734dd74.png',
      'alt'      => __('Illustration for UI/UX Design & Prototyping', 'vite-ttf-child-creceri'),
      'width'    => 600,
      'height'   => 600,
      'loading'  => 'lazy',
      'decoding' => 'async',
    ),
    'title'   => __('UI/UX Design & Prototyping', 'vite-ttf-child-creceri'),
    'copy'    => __('Turn ideas into interactive journeys with wireframes and prototypes that surface real user behavior.', 'vite-ttf-child-creceri'),
    'ctaText' => __('Discover More →', 'vite-ttf-child-creceri'),
    'ctaUrl'  => '#',
  ),
  array(
    'img'     => array(
      'src'      => 'https://creceri.com/wp-content/uploads/2025/10/200964812f2b5d91038acba1950b7d9838dd7f4e.png',
      'alt'      => __('Illustration for UI/UX Design', 'vite-ttf-child-creceri'),
      'width'    => 600,
      'height'   => 600,
      'loading'  => 'lazy',
      'decoding' => 'async',
    ),
    'title'   => __('UI/UX Design', 'vite-ttf-child-creceri'),
    'copy'    => __('Ship interfaces that are clean, intuitive, and user-first. Reduce friction and make navigation effortless.', 'vite-ttf-child-creceri'),
    'ctaText' => __('Discover More →', 'vite-ttf-child-creceri'),
    'ctaUrl'  => '#',
  ),
  array(
    'img'     => array(
      'src'      => 'https://creceri.com/wp-content/uploads/2025/10/1284980c789e21b3c4aa17767d4da504cabc39df.png',
      'alt'      => __('Illustration for Figma Prototyping', 'vite-ttf-child-creceri'),
      'width'    => 600,
      'height'   => 600,
      'loading'  => 'lazy',
      'decoding' => 'async',
    ),
    'title'   => __('Figma Prototyping', 'vite-ttf-child-creceri'),
    'copy'    => __('Test, iterate, and collaborate in real time. Share concepts, collect feedback, and align design with development.', 'vite-ttf-child-creceri'),
    'ctaText' => __('Discover More →', 'vite-ttf-child-creceri'),
    'ctaUrl'  => '#',
  ),
  array(
    'img'     => array(
      'src'      => 'https://creceri.com/wp-content/uploads/2025/10/1cbb6ba7007944eeef755d1bd24b0e75475e9d9d.png',
      'alt'      => __('Illustration for App Design', 'vite-ttf-child-creceri'),
      'width'    => 600,
      'height'   => 600,
      'loading'  => 'lazy',
      'decoding' => 'async',
    ),
    'title'   => __('App Design', 'vite-ttf-child-creceri'),
    'copy'    => __('Balance usability with style across mobile and web apps. Keep users engaged and coming back.', 'vite-ttf-child-creceri'),
    'ctaText' => __('Discover More →', 'vite-ttf-child-creceri'),
    'ctaUrl'  => '#',
  ),
);

$items = isset($A['items']) && is_array($A['items']) && !empty($A['items'])
  ? $A['items']
  : $items_default;

?>
<section
  <?php echo $anchor ? 'id="' . esc_attr($anchor) . '" ' : ''; ?>
  class="<?php echo esc_attr( implode(' ', array_unique($classes)) ); ?>"
  aria-labelledby="<?php echo esc_attr($title_id); ?>"
>
  <div class="ux-cards__container">
    <?php if ( $title !== '' ) : ?>
      <h2 id="<?php echo esc_attr($title_id); ?>" class="ux-cards__title"><?php echo $title; ?></h2>
    <?php endif; ?>

    <?php if ( $intro ) : ?>
      <p class="ux-cards__intro"><?php echo $intro; ?></p>
    <?php endif; ?>

    <div class="ux-cards__grid">
      <?php foreach ( $items as $card ) :
        $media = child_uxcards_image_from_attr( $card['img'] ?? array() );
        $card_title = isset($card['title']) ? wp_kses_post($card['title']) : '';
        $card_copy  = isset($card['copy'])  ? wp_kses_post($card['copy'])  : '';
        $cta_text   = isset($card['ctaText']) ? wp_strip_all_tags($card['ctaText']) : '';
        $cta_url    = isset($card['ctaUrl'])  ? trim((string)$card['ctaUrl']) : '';
      ?>
        <article class="ux-card">
          <figure class="ux-card__figure">
            <?php if ( ! empty($media['src']) ) : ?>
              <img
                class="ux-card__img"
                src="<?php echo $media['src']; ?>"
                alt="<?php echo esc_attr( $media['alt'] !== '' ? $media['alt'] : wp_strip_all_tags($card_title) ); ?>"
                <?php echo $media['width']  ? ' width="'  . intval($media['width'])  . '"' : ''; ?>
                <?php echo $media['height'] ? ' height="' . intval($media['height']) . '"' : ''; ?>
                loading="<?php echo $media['loading']; ?>"
                decoding="<?php echo $media['decoding']; ?>"
              />
            <?php endif; ?>
          </figure>

          <?php if ( $card_title !== '' ) : ?>
            <h3 class="ux-card__title"><?php echo $card_title; ?></h3>
          <?php endif; ?>

          <?php if ( $card_copy !== '' ) : ?>
            <p class="ux-card__copy"><?php echo $card_copy; ?></p>
          <?php endif; ?>

          <?php if ( $cta_text !== '' && $cta_url !== '' ) : ?>
            <a class="ux-card__cta" href="<?php echo esc_url($cta_url); ?>">
              <?php echo esc_html($cta_text); ?>
            </a>
          <?php endif; ?>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>
