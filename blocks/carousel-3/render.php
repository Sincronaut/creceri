<?php
if ( ! defined('ABSPATH') ) { exit; }

/**
 * Dynamic render for child/carousel-3
 * Attributes (supported):
 * - anchor, align, className
 * - regionLabel (string)  aria-label for <section>
 * - title (string), intro (string)
 * - showIndicators (bool), showArrows (bool)
 * - dataIndex (int)       starting slide index
 * - items (array)         [{title, text}]
 */

/* ---------- Helpers ---------- */
if ( ! function_exists('child_carousel_allowed_text_tags') ) {
  function child_carousel_allowed_text_tags() {
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

/* ---------- Attributes ---------- */
$A = is_array($attributes ?? null) ? $attributes : array();

$anchor   = sanitize_title( $A['anchor'] ?? '' );
$classes  = array('carousel');
if ( ! empty($A['align']) )    { $classes[] = 'align' . sanitize_html_class($A['align']); }
if ( ! empty($A['className']) ) {
  $extra = preg_split('/\s+/', $A['className']);
  foreach ($extra as $cls) { $cls = sanitize_html_class($cls); if ($cls) { $classes[] = $cls; } }
}

$region_label = isset($A['regionLabel']) && is_string($A['regionLabel']) && $A['regionLabel'] !== ''
  ? $A['regionLabel']
  : __('Current trends carousel', 'vite-ttf-child-creceri');

$title_default = __('Core Elements of Effective UI/UX & App Design', 'vite-ttf-child-creceri');
$intro_default = __('The building blocks that turn good ideas into smooth, user-friendly experiences', 'vite-ttf-child-creceri');

$title = isset($A['title']) && is_string($A['title']) && trim(wp_strip_all_tags($A['title'])) !== '' ? $A['title'] : $title_default;
$intro = isset($A['intro']) && is_string($A['intro']) && trim(wp_strip_all_tags($A['intro'])) !== '' ? $A['intro'] : $intro_default;

$show_arrows    = isset($A['showArrows'])     ? (bool)$A['showArrows']     : true;
$show_indicators= isset($A['showIndicators']) ? (bool)$A['showIndicators'] : true;
$start_index    = isset($A['dataIndex'])      ? max(0, intval($A['dataIndex'])) : 0;

$items = array();
if ( isset($A['items']) && is_array($A['items']) ) {
  foreach ( $A['items'] as $it ) {
    $t = is_array($it ?? null) ? $it : array();
    $title_i = isset($t['title']) ? trim((string)$t['title']) : '';
    $text_i  = isset($t['text'])  ? trim((string)$t['text'])  : '';
    if ( $title_i !== '' || $text_i !== '' ) {
      $items[] = array('title' => $title_i, 'text' => $text_i);
    }
  }
}

/* Sensible defaults if no items provided */
if ( empty($items) ) {
  $items = array(
    array(
      'title' => __('User-Centric Design', 'vite-ttf-child-creceri'),
      'text'  => __('Design starts with empathy, understanding your users and their goals.', 'vite-ttf-child-creceri'),
    ),
  );
}

$instance_id = function_exists('wp_unique_id') ? wp_unique_id('carousel-') : ('carousel-' . uniqid());

/* ---------- Render ---------- */
?>
<section
  <?php echo $anchor ? 'id="' . esc_attr($anchor) . '" ' : ''; ?>
  class="<?php echo esc_attr(implode(' ', array_unique($classes))); ?>"
  role="region"
  aria-label="<?php echo esc_attr($region_label); ?>"
  data-carousel
  data-start="<?php echo esc_attr($start_index); ?>"
  data-instance="<?php echo esc_attr($instance_id); ?>"
>
  <div class="carousel__container">
    <?php if ( $title !== '' ) : ?>
      <h2 class="carousel__title"><?php echo wp_kses($title, child_carousel_allowed_text_tags()); ?></h2>
    <?php endif; ?>

    <?php if ( $intro !== '' ) : ?>
      <p class="carousel__desc"><?php echo wp_kses($intro, child_carousel_allowed_text_tags()); ?></p>
    <?php endif; ?>

    <div class="carousel__wrap">
      <?php if ( $show_arrows ) : ?>
        <button class="carousel__arrow carousel__arrow--prev" type="button" aria-label="<?php echo esc_attr__('Previous slide', 'vite-ttf-child-creceri'); ?>">❮</button>
      <?php endif; ?>

      <div class="carousel__track" data-track>
        <?php foreach ( $items as $card ) : ?>
          <article class="carousel__card">
            <?php if ( ! empty($card['title']) ) : ?>
              <h3 class="carousel__card-title"><?php echo wp_kses($card['title'], child_carousel_allowed_text_tags()); ?></h3>
            <?php endif; ?>
            <?php if ( ! empty($card['text']) ) : ?>
              <p class="carousel__card-copy"><?php echo wp_kses($card['text'], child_carousel_allowed_text_tags()); ?></p>
            <?php endif; ?>
          </article>
        <?php endforeach; ?>
      </div>

      <?php if ( $show_arrows ) : ?>
        <button class="carousel__arrow carousel__arrow--next" type="button" aria-label="<?php echo esc_attr__('Next slide', 'vite-ttf-child-creceri'); ?>">❯</button>
      <?php endif; ?>

      <?php if ( $show_indicators ) : ?>
        <div class="carousel__dots" role="tablist" aria-label="<?php echo esc_attr__('Slide indicators', 'vite-ttf-child-creceri'); ?>">
          <?php for ( $i = 0; $i < count($items); $i++ ) : ?>
            <button
              class="carousel__dot<?php echo $i === max(0, min(count($items)-1, $start_index)) ? ' is-active' : ''; ?>"
              aria-label="<?php echo esc_attr( sprintf( __('Slide %d', 'vite-ttf-child-creceri'), $i+1 ) ); ?>">
            </button>
          <?php endfor; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <script>
  (function() {
    var root = (function() {
      var scripts = document.getElementsByTagName('script');
      return scripts[scripts.length - 1].closest('[data-instance]');
    })();
    if (!root) return;
    var track = root.querySelector('[data-track]');
    if (!track) return;

    var prev = root.querySelector('.carousel__arrow--prev');
    var next = root.querySelector('.carousel__arrow--next');
    var dots = Array.prototype.slice.call(root.querySelectorAll('.carousel__dot'));

    function getStep() {
      var first = track.firstElementChild;
      if (!first) return track.clientWidth || 0;
      var style = window.getComputedStyle(track);
      var gap = parseFloat(style.gap || style.columnGap || 16);
      return first.getBoundingClientRect().width + gap;
    }

    function setActive(i) {
      dots.forEach(function(d, idx){ d.classList.toggle('is-active', idx === i); });
    }

    function updateDots() {
      if (!dots.length) return;
      var step = getStep();
      var page = Math.round(track.scrollLeft / step);
      var max  = Math.max(0, dots.length - 1);
      setActive(Math.max(0, Math.min(max, page)));
    }

    function scrollByStep(dir) {
      track.scrollBy({ left: dir * getStep(), behavior: 'smooth' });
    }

    if (prev) prev.addEventListener('click', function(){ scrollByStep(-1); });
    if (next) next.addEventListener('click', function(){ scrollByStep(1); });
    track.addEventListener('scroll', function(){ window.requestAnimationFrame(updateDots); });
    dots.forEach(function(d, i){
      d.addEventListener('click', function(){
        track.scrollTo({ left: i * getStep(), behavior: 'smooth' });
      });
    });

    var start = parseInt(root.getAttribute('data-start') || '0', 10);
    if (start > 0) {
      window.requestAnimationFrame(function(){
        track.scrollTo({ left: start * getStep(), behavior: 'auto' });
        setActive(start);
      });
    } else {
      updateDots();
    }
  })();
  </script>
</section>
