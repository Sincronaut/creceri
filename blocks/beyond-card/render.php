<?php
if ( ! defined('ABSPATH') ) { exit; }

/* -------- Helpers -------- */
if ( ! function_exists('child_beyondcard_allowed_description_tags') ) {
  function child_beyondcard_allowed_description_tags() {
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
if ( ! function_exists('child_beyondcard_image_from_attr') ) {
  function child_beyondcard_image_from_attr($raw) {
    $raw = is_array($raw ?? null) ? $raw : array();

    $defaults = array(
      'src'     => 'https://creceri.com/wp-content/uploads/2025/10/e0326943a413c071e069e6d447eb78a50ef8ed2f.png',
      'alt'     => __('UI/UX Illustration', 'vite-ttf-child-creceri'),
      'width'   => 0,
      'height'  => 0,
      'loading' => 'lazy',
      'decoding'=> 'async',
    );

    return array(
      'src'     => esc_url( ( isset($raw['src'])     && trim((string) $raw['src'])     !== '' ) ? $raw['src']     : $defaults['src'] ),
      'alt'     => esc_attr( ( isset($raw['alt'])     && trim((string) $raw['alt'])     !== '' ) ? $raw['alt']     : $defaults['alt'] ),
      'width'   => ( isset($raw['width'])  && intval($raw['width'])  > 0 ) ? intval($raw['width'])  : 0,
      'height'  => ( isset($raw['height']) && intval($raw['height']) > 0 ) ? intval($raw['height']) : 0,
      'loading' => esc_attr( ( isset($raw['loading']) && trim((string) $raw['loading']) !== '' ) ? $raw['loading'] : $defaults['loading'] ),
      'decoding'=> esc_attr( ( isset($raw['decoding']) && trim((string) $raw['decoding']) !== '' ) ? $raw['decoding'] : $defaults['decoding'] ),
    );
  }
}

/* -------- Attributes -------- */
$A = is_array($attributes ?? null) ? $attributes : array();

$anchor = sanitize_title($A['anchor'] ?? '');
$classes = array('uiux-section');

if ( ! empty($A['align']) ) {
  $align = 'align' . sanitize_html_class($A['align']);
  $classes[] = $align;
}

if ( ! empty($A['className']) ) {
  $extra = preg_split('/\s+/', $A['className']);
  foreach ( $extra as $cls ) {
    $cls = sanitize_html_class($cls);
    if ($cls) { $classes[] = $cls; }
  }
}

$title_default = __('What is Website and CMS Development?', 'vite-ttf-child-creceri');
$description_default = __('Website and CMS development is the process of creating a website and setting up the system that lets you manage it. Instead of building everything with raw code, a CMS (Content Management System) acts like the dashboard of your site where you can add pages, update content, or change the design without touching complex code. Together, website and CMS development give you both the structure (the site itself) and the tools (the CMS) to keep it running smoothly as your business grows.', 'vite-ttf-child-creceri');

$title_has_value = isset($A['title']) && is_string($A['title']) && trim(wp_strip_all_tags($A['title'])) !== '';
$description_has_value = isset($A['description']) && is_string($A['description']) && trim(wp_strip_all_tags($A['description'])) !== '';
$title = wp_kses_post($title_has_value ? $A['title'] : $title_default);
$description_raw = $description_has_value ? $A['description'] : $description_default;
$description = '';

if ( is_string($description_raw) && trim($description_raw) !== '' ) {
  $description = wpautop( wp_kses( $description_raw, child_beyondcard_allowed_description_tags() ) );
}

$image_has_value = isset($A['image']) && is_array($A['image']) && !empty(trim((string) ($A['image']['src'] ?? '')));
$image = child_beyondcard_image_from_attr($image_has_value ? $A['image'] : array());
?>
<section <?php if ($anchor) echo 'id="' . esc_attr($anchor) . '" '; ?>class="<?php echo esc_attr(implode(' ', array_unique($classes))); ?>">
  <div class="uiux-grid">
    <div class="uiux-image">
      <?php if ( ! empty($image['src']) ) : ?>
        <img
          src="<?php echo $image['src']; ?>"
          alt="<?php echo $image['alt']; ?>"
          <?php if ($image['width'])  echo ' width="'  . intval($image['width'])  . '"'; ?>
          <?php if ($image['height']) echo ' height="' . intval($image['height']) . '"'; ?>
          loading="<?php echo $image['loading']; ?>"
          decoding="<?php echo $image['decoding']; ?>" />
      <?php endif; ?>
    </div>
    <div class="uiux-content">
      <?php if ( $title !== '' ) : ?>
        <h2><?php echo $title; ?></h2>
      <?php endif; ?>

      <?php if ( $description ) : ?>
        <?php echo $description; ?>
      <?php endif; ?>
    </div>
  </div>
</section>
