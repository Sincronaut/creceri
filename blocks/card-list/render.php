<?php
if ( ! defined('ABSPATH') ) { exit; }

/**
 * Dynamic render for child/benefits
 * Attributes:
 * - anchor, align, className
 * - title, intro, subhead, titleId
 * - media: {src,alt,width,height,loading,decoding}
 * - check: {icon, items: [string|{text,icon}]}
 */

/* ---------- Helpers ---------- */
if ( ! function_exists('child_benefits_allowed_text_tags') ) {
  function child_benefits_allowed_text_tags() {
    return array(
      'a'      => array('href'=>true,'title'=>true,'target'=>true,'rel'=>true),
      'strong' => array(),
      'b'      => array(),
      'em'     => array(),
      'i'      => array(),
      'br'     => array(),
      'span'   => array('class'=>true)
    );
  }
}

if ( ! function_exists('child_benefits_image_from_attr') ) {
  function child_benefits_image_from_attr( $raw ) {
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

$anchor  = sanitize_title( $A['anchor'] ?? '' );
$classes = array('benefits');

if ( ! empty($A['align']) )    { $classes[] = 'align' . sanitize_html_class($A['align']); }
if ( ! empty($A['className']) ) {
  $extra = preg_split('/\s+/', $A['className']);
  foreach ($extra as $cls) { $cls = sanitize_html_class($cls); if ($cls) { $classes[] = $cls; } }
}

$title_default   = __('Benefits of Thoughtful UI/UX & App Design', 'vite-ttf-child-creceri');
$intro_default   = __('Design that feels good, works well, and keeps users coming back.', 'vite-ttf-child-creceri');
$subhead_default = __('Why It Matters', 'vite-ttf-child-creceri');

$title   = isset($A['title'])   && is_string($A['title'])   ? trim($A['title'])   : $title_default;
$intro   = isset($A['intro'])   && is_string($A['intro'])   ? trim($A['intro'])   : $intro_default;
$subhead = isset($A['subhead']) && is_string($A['subhead']) ? trim($A['subhead']) : $subhead_default;

$title_id = sanitize_html_class( $A['titleId'] ?? ( $anchor ? "{$anchor}-title" : 'benefits-title' ) );

/* ---------- Media ---------- */
$media = child_benefits_image_from_attr( $A['media'] ?? array() );

/* ---------- Checklist ---------- */
$check = is_array($A['check'] ?? null) ? $A['check'] : array();
$check_icon  = isset($check['icon']) ? esc_url($check['icon']) : '';
$check_items = array();

if ( isset($check['items']) && is_array($check['items']) ) {
  foreach ($check['items'] as $it) {
    if ( is_string($it) ) {
      $check_items[] = array('text' => $it, 'icon' => $check_icon);
    } elseif ( is_array($it) ) {
      $text = isset($it['text']) ? (string)$it['text'] : '';
      $icon = isset($it['icon']) ? (string)$it['icon'] : $check_icon;
      if ( trim($text) !== '' ) {
        $check_items[] = array('text' => $text, 'icon' => $icon);
      }
    }
  }
}

?>
<section
  <?php echo $anchor ? 'id="' . esc_attr($anchor) . '" ' : ''; ?>
  class="<?php echo esc_attr(implode(' ', array_unique($classes))); ?>"
  aria-labelledby="<?php echo esc_attr($title_id); ?>"
>
  <div class="benefits__container">
    <div class="benefits__content">
      <?php if ($title !== ''): ?>
        <h2 id="<?php echo esc_attr($title_id); ?>" class="benefits__title"><?php echo esc_html($title); ?></h2>
      <?php endif; ?>

      <?php if ($intro !== ''): ?>
        <p class="benefits__intro"><?php echo wp_kses($intro, child_benefits_allowed_text_tags()); ?></p>
      <?php endif; ?>

      <?php if ($subhead !== ''): ?>
        <div class="benefits__subhead"><?php echo wp_kses($subhead, child_benefits_allowed_text_tags()); ?></div>
      <?php endif; ?>

      <?php if ( ! empty($check_items) ): ?>
        <ul class="checklist">
          <?php foreach ($check_items as $item): ?>
            <li>
              <?php if ( ! empty($item['icon']) ): ?>
                <img src="<?php echo esc_url($item['icon']); ?>" alt="" aria-hidden="true" />
              <?php endif; ?>
              <?php echo esc_html($item['text']); ?>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </div>

    <div class="benefits__media">
      <?php if ( ! empty($media['src']) ): ?>
        <img
          class="benefits__img"
          src="<?php echo $media['src']; ?>"
          alt="<?php echo esc_attr($media['alt']); ?>"
          <?php echo $media['width']  ? ' width="'  . intval($media['width'])  . '"' : ''; ?>
          <?php echo $media['height'] ? ' height="' . intval($media['height']) . '"' : ''; ?>
          loading="<?php echo $media['loading']; ?>"
          decoding="<?php echo $media['decoding']; ?>"
        />
      <?php endif; ?>
    </div>
  </div>
</section>
