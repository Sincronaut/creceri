<?php
if ( ! defined('ABSPATH') ) { exit; }

/* ───────── Helpers ───────── */
if ( ! function_exists('child_ecomdev_allowed_lede_tags') ) {
  function child_ecomdev_allowed_lede_tags() {
    return array(
      'a' => array('href'=>true,'title'=>true,'target'=>true,'rel'=>true),
      'strong'=>array(),'b'=>array(),'em'=>array(),'i'=>array(),
      'br'=>array(),'span'=>array('class'=>true)
    );
  }
}

if ( ! function_exists('child_ecomdev_image_from_attr') ) {
  function child_ecomdev_image_from_attr($img){
    $img = is_array($img ?? null) ? $img : array();
    return array(
      'src'     => esc_url($img['src'] ?? ''),
      'alt'     => esc_attr($img['alt'] ?? ''),
      'width'   => !empty($img['width'])  ? intval($img['width'])  : 0,
      'height'  => !empty($img['height']) ? intval($img['height']) : 0,
      'loading' => esc_attr($img['loading'] ?? 'lazy'),
      'decoding'=> esc_attr($img['decoding'] ?? 'async'),
    );
  }
}

/* Render description as <p> or <ul> */
if ( ! function_exists('child_ecomdev_render_desc') ) {
  function child_ecomdev_render_desc($desc){
    if (!is_array($desc)) {
      $text = trim( wp_strip_all_tags( (string) $desc ) );
      if ($text === '') { return ''; }
      return '<p class="ecom-dev__phase-desc">'. wp_kses_post($desc) .'</p>';
    }
    $clean = array();
    foreach ($desc as $d) {
      $t = trim( wp_strip_all_tags( is_array($d) ? ($d['text'] ?? '') : (string) $d ) );
      if ($t !== '') { $clean[] = $t; }
    }
    if (empty($clean)) { return ''; }
    if (count($clean) === 1) {
      return '<p class="ecom-dev__phase-desc">'. wp_kses_post($clean[0]) .'</p>';
    }
    $out = '<ul class="ecom-dev__phase-desc ecom-dev__phase-desc--list">';
    foreach ($clean as $li) { $out .= '<li>'. wp_kses_post($li) .'</li>'; }
    $out .= '</ul>';
    return $out;
  }
}

/* ───────── Attributes ───────── */
$A = is_array($attributes ?? null) ? $attributes : array();

$section_id   = sanitize_title($A['sectionId'] ?? 'ecom-dev');
$className    = sanitize_html_class($A['className'] ?? '');

$title        = wp_kses_post($A['title'] ?? 'What is E-commerce development?');
$description  = wp_kses_post($A['description'] ?? '');
$lede_raw     = $A['lede'] ?? ($A['intro'] ?? '');

$lineStatus   = (isset($A['lineStatus']) && strtolower($A['lineStatus']) === 'off') ? 'Off' : 'On';
$items        = (isset($A['items']) && is_array($A['items'])) ? $A['items'] : array();

/* Fallback content */
if (empty($items)) {
  $items = array(
    array('type'=>'phase','title'=>'Strategy & Planning','label'=>'1',
      'img'=>array('src'=>'https://creceri.com/wp-content/uploads/2025/10/1.png','alt'=>'Strategy & Planning icon','width'=>150,'height'=>150),
      'desc'=>array('Market and competitor analysis','Requirements gathering','Roadmapping & KPIs')),
    array('type'=>'connector','img'=>array('src'=>'https://creceri.com/wp-content/uploads/2025/10/Screenshot-2025-10-13-082149-1.png','alt'=>'arrow','width'=>160,'height'=>60)),
    array('type'=>'phase','title'=>'Design & User Experience','label'=>'2',
      'img'=>array('src'=>'https://creceri.com/wp-content/uploads/2025/10/2.png','alt'=>'Design & UX icon','width'=>96,'height'=>96),
      'desc'=>'Wireframes, UI, and interaction patterns that reduce friction.'),
    array('type'=>'connector','img'=>array('src'=>'https://creceri.com/wp-content/uploads/2025/10/output-onlinepngtools-lines.png','alt'=>'arrow','width'=>160,'height'=>60)),
    array('type'=>'phase','title'=>'Technical Development','label'=>'3',
      'img'=>array('src'=>'https://creceri.com/wp-content/uploads/2025/10/3.png','alt'=>'Technical Development icon','width'=>96,'height'=>96),
      'desc'=>array('Platform setup & integrations','Payments, tax, and shipping','Performance & security baselines')),
    array('type'=>'connector','img'=>array('src'=>'https://creceri.com/wp-content/uploads/2025/10/Screenshot-2025-10-13-082149-1.png','alt'=>'arrow','width'=>160,'height'=>60)),
    array('type'=>'phase','title'=>'Launch & Optimization','label'=>'4',
      'img'=>array('src'=>'https://creceri.com/wp-content/uploads/2025/10/4.png','alt'=>'Launch & Optimization icon','width'=>96,'height'=>96),
      'desc'=>'Go-live, QA, analytics, and ongoing CRO.')
  );
}

/* Classes */
$classes = array('ecom-dev');
if ($className) { $classes[] = $className; }

/* Flags */
$has_connector = false;
foreach ($items as $t) {
  if (sanitize_key($t['type'] ?? '') === 'connector') { $has_connector = true; break; }
}

/* Track connector count to target the 2nd connector */
$connector_index = 0;
?>
<section class="<?php echo esc_attr(implode(' ', $classes)); ?>" aria-labelledby="<?php echo esc_attr($section_id); ?>">
  <header class="ecom-dev__header">
    <h2 id="<?php echo esc_attr($section_id); ?>"><?php echo wp_kses_post($title); ?></h2>

    <?php if (trim( wp_strip_all_tags($description) ) !== ''): ?>
      <p class="ecom-dev__description"><?php echo wp_kses_post($description); ?></p>
    <?php endif; ?>

    <?php if ( ! empty($lede_raw) && trim( wp_strip_all_tags($lede_raw) ) !== '' ) : ?>
      <div class="ecom-dev__lede">
        <?php echo do_shortcode( wpautop( wp_kses( $lede_raw, child_ecomdev_allowed_lede_tags() ) ) ); ?>
      </div>
    <?php endif; ?>
  </header>

  <ol class="ecom-dev__phases" role="list">
    <?php foreach ($items as $idx => $raw) :
      $type        = sanitize_key($raw['type'] ?? 'phase');
      $item_title  = wp_kses_post($raw['title'] ?? '');
      $item_desc   = $raw['desc'] ?? '';
      $label       = sanitize_text_field($raw['label'] ?? '');
      $img         = child_ecomdev_image_from_attr($raw['img'] ?? array());

      if ($type === 'connector') :

        $connector_index++;
        $is_second_connector = ($connector_index === 2);
        $second_mt_style     = $is_second_connector ? 'margin-top:120px;padding-right:20px;' : '';

        if ($lineStatus !== 'On') :
          $w = $img['width']  ?: 160;
          $h = $img['height'] ?: 60; ?>
          <li class="ecom-dev__phase ecom-dev__connector ecom-dev__connector--blank"
              aria-hidden="true" 
              style="width:<?php echo intval($w); ?>px; min-width:<?php echo intval($w); ?>px; height:<?php echo intval($h); ?>px; <?php echo $second_mt_style; ?>">
          </li>
        <?php else : ?>
          <li class="ecom-dev__phase ecom-dev__connector<?php echo (!empty($raw['position']) && $raw['position']==='middle') ? ' middle' : ''; ?>"
              <?php echo $second_mt_style ? 'style="'.$second_mt_style.'"' : ''; ?>>
            <figure class="ecom-dev__figure" <?php if (empty($img['src'])) echo 'aria-hidden="true"'; ?>>
              <?php if (!empty($img['src'])): ?>
                <img src="<?php echo $img['src']; ?>" alt="<?php echo $img['alt']; ?>" class="image_data"
                     <?php if($img['width'])  echo 'width="'.intval($img['width']).'"'; ?>
                     <?php if($img['height']) echo 'height="'.intval($img['height']).'"'; ?>
                     loading="<?php echo $img['loading']; ?>" decoding="<?php echo $img['decoding']; ?>" />
              <?php endif; ?>
            </figure>
            <?php if ($label !== ''): ?>
              <span class="ecom-dev__connector-label"></span>
            <?php endif; ?>
          </li>
        <?php endif;

      else : ?>
        <li class="ecom-dev__phase">
          <figure class="ecom-dev__figure">
            <?php if (!empty($img['src'])): ?>
              <img src="<?php echo $img['src']; ?>" alt="<?php echo $img['alt']; ?>"
                   <?php if($img['width'])  echo 'width="'.intval($img['width']).'"'; ?>
                   <?php if($img['height']) echo 'height="'.intval($img['height']).'"'; ?>
                   loading="<?php echo $img['loading']; ?>" decoding="<?php echo $img['decoding']; ?>" />
            <?php endif; ?>
            <?php if ($label !== ''): ?>
              <figcaption class="ecom-dev__label"></figcaption>
            <?php endif; ?>
          </figure>

          <?php if ($item_title !== ''): ?>
            <h3 class="ecom-dev__phase-title"><?php echo $item_title; ?></h3>
          <?php endif; ?>

          <?php
            $desc_html = child_ecomdev_render_desc($item_desc);
            if ($desc_html !== '') { echo $desc_html; }
          ?>
        </li>

        <?php
          if (!$has_connector) {
            $next = $items[$idx + 1] ?? null;
            $next_type = is_array($next) ? sanitize_key($next['type'] ?? 'phase') : 'phase';
            if ($next && $next_type === 'phase') {
              echo '<li class="ecom-dev__phase ecom-dev__connector ecom-dev__gap" aria-hidden="true"></li>';
            }
          }
        ?>

      <?php endif; endforeach; ?>
  </ol>
</section>
