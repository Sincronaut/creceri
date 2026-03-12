<?php
/**
 * Dynamic render: About · Split (Mosaic + Content)
 */

$title     = isset($attributes['title']) ? $attributes['title'] : 'Who We Are?';
$content   = isset($attributes['content']) ? $attributes['content'] : '';
$ctaText   = isset($attributes['ctaText']) ? $attributes['ctaText'] : '';
$ctaUrl    = isset($attributes['ctaUrl']) ? $attributes['ctaUrl'] : '';
$btnClass  = isset($attributes['btnClass']) ? $attributes['btnClass'] : 'btn-pill';
$gallery   = (isset($attributes['gallery']) && is_array($attributes['gallery'])) ? $attributes['gallery'] : array();
$reverse   = !empty($attributes['reverse']);
$sectionId = !empty($attributes['sectionId']) ? $attributes['sectionId'] : 'who-title';

$tiles = array('tile-a','tile-b','tile-c','tile-d');

/** allow simple formatting in content */
$allowed_html = array(
  'a' => array('href'=>array(),'title'=>array(),'target'=>array(),'rel'=>array()),
  'br' => array(), 'em' => array(), 'strong' => array(), 'b' => array(), 'i' => array(),
  'span' => array(), 'p' => array(), 'ul'=>array(), 'ol'=>array(), 'li'=>array()
);
?>
<section class="about-split" aria-labelledby="<?php echo esc_attr($sectionId); ?>">
  <div class="about-split__inner<?php echo $reverse ? ' is-reversed' : ''; ?>">
    <!-- Left/Right: mosaic gallery -->
    <div class="about-split__gallery">
      <?php
      for ($i = 0; $i < 4; $i++) {
        if (empty($gallery[$i]['src'])) { continue; }
        $src  = $gallery[$i]['src'];
        $alt  = !empty($gallery[$i]['alt']) ? $gallery[$i]['alt'] : '';
        $tcls = $tiles[$i];
        ?>
        <figure class="tile <?php echo esc_attr($tcls); ?>">
          <img src="<?php echo esc_url($src); ?>" alt="<?php echo esc_attr($alt ? $alt : __('Who We Are?', 'vite-ttf-child-creceri')); ?>" loading="lazy" decoding="async" />
        </figure>
        <?php
      }
      ?>
    </div>

    <!-- Content -->
    <div class="about-split__content">
      <?php if ($title) : ?>
        <h2 id="<?php echo esc_attr($sectionId); ?>"><?php echo esc_html($title); ?></h2>
      <?php endif; ?>

      <?php if ($content) : ?>
        <div class="about-split__text">
          <?php echo wp_kses($content, $allowed_html); ?>
        </div>
      <?php endif; ?>
        <br>
      <?php if ($ctaText && $ctaUrl) : ?>
        <a class="<?php echo esc_attr($btnClass); ?>" href="<?php echo esc_url($ctaUrl); ?>" aria-label="<?php echo esc_attr( $title ? 'Learn more about ' . $title : $ctaText ); ?>">
          <?php echo esc_html($ctaText); ?>
        </a>
      <?php endif; ?>
    </div>
  </div>
</section>
