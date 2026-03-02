<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

function ccar_val($arr, $key, $default = null){
  return (is_array($arr) && array_key_exists($key, $arr)) ? $arr[$key] : $default;
}
function ccar_str($arr, $key, $default = ''){
  $v = ccar_val($arr, $key, $default);
  return is_string($v) ? $v : (is_null($v) ? '' : (string)$v);
}
function ccar_bool($arr, $key, $default = false){
  return (bool)ccar_val($arr, $key, $default);
}
function ccar_array($arr, $key){
  $v = ccar_val($arr, $key, array());
  return is_array($v) ? $v : array();
}

$A = is_array($attributes ?? null) ? $attributes : array();

$title   = ccar_str($A, 'title', '');
$intro   = ccar_str($A, 'intro', '');
$items   = ccar_array($A, 'items');
$usePosts = ccar_bool($A, 'usePosts', false);
$related  = ccar_bool($A, 'relatedToCurrent', false);
$cols     = ccar_val($A, 'cols', array('xs'=>1,'sm'=>2,'md'=>3,'lg'=>4,'xl'=>5));
$controls = ccar_bool($A, 'showControls', true);

$cardLinkBehavior = ccar_str($A, 'cardLinkBehavior', 'stretched');
$ariaLabel = ccar_str($A, 'ariaLabel', 'Carousel');
$backgroundType  = ccar_str($A, 'backgroundType', 'none');
$backgroundValue = ccar_str($A, 'backgroundValue', '');

$source_defaults = array(
  'postType' => 'post',
  'taxonomy' => 'category',
  'terms' => array(),
  'tags' => array(),
  'perPage' => 10,
  'orderby' => 'date',
  'order' => 'DESC',
  'offset' => 0,
  'includeChildren' => true,
  'thumbSize' => 'large',
  'search' => '',
  'searchFromUrl' => false,
);
$source = array_merge($source_defaults, array_filter(ccar_val($A, 'source', array()), function($v){ return $v !== null; }));

$instance_id = ccar_str($A, 'instanceId', '');
if ($instance_id === '') {
  $instance_id = 'carousel-' . wp_generate_password(6, false, false);
}
$track_id = $instance_id . '-track';
$prev_id  = $instance_id . '-prev';
$next_id  = $instance_id . '-next';

if ($usePosts) {
  $post_type = post_type_exists($source['postType']) ? $source['postType'] : 'post';
  $per_page = max(1, intval($source['perPage']));
  $order = strtoupper($source['order']) === 'ASC' ? 'ASC' : 'DESC';
  $valid_orderby = array('date','title','modified','rand','menu_order');
  $orderby = in_array($source['orderby'], $valid_orderby, true) ? $source['orderby'] : 'date';
  $offset = max(0, intval($source['offset']));
  $thumb_size = $source['thumbSize'] ?: 'large';

  $tax_query = array();
  $terms = ccar_array($source, 'terms');
  $taxonomy = $source['taxonomy'] ?: 'category';

  $exclude_ids = array();
  if ($related && is_singular($post_type)) {
    $current_id = get_the_ID();
    if ($current_id) {
      $exclude_ids[] = $current_id;
      $related_terms = wp_get_object_terms($current_id, $taxonomy, array('fields' => 'ids'));
      if (!is_wp_error($related_terms) && !empty($related_terms)) {
        $terms = $related_terms;
      }
    }
  }

  if (!empty($terms)) {
    $taxonomy_args = array(
      'taxonomy' => $taxonomy,
      'terms'    => array_map('intval', $terms),
      'field'    => 'term_id',
      'include_children' => !empty($source['includeChildren']),
    );
    $tax_query[] = $taxonomy_args;
  }

  $tags = ccar_array($source, 'tags');
  if (!empty($tags)) {
    $tax_query[] = array(
      'taxonomy' => 'post_tag',
      'terms'    => array_map('intval', $tags),
      'field'    => 'term_id',
      'include_children' => false,
    );
  }

  $search = $source['search'] ?: '';
  if ($source['searchFromUrl'] && isset($_GET['s'])) {
    $search = sanitize_text_field(wp_unslash($_GET['s']));
  }

  $query_args = array(
    'post_type'           => $post_type,
    'post_status'         => 'publish',
    'posts_per_page'      => $per_page,
    'orderby'             => $orderby,
    'order'               => $order,
    'offset'              => $offset,
    'ignore_sticky_posts' => true,
    'post__not_in'        => $exclude_ids,
  );

  if (!empty($tax_query)) {
    if (count($tax_query) > 1) {
      $tax_query['relation'] = 'AND';
    }
    $query_args['tax_query'] = $tax_query;
  }

  if ($search !== '') {
    $query_args['s'] = $search;
  }

  $loop = new WP_Query($query_args);
  $items = array();

  if ($loop->have_posts()) {
    while ($loop->have_posts()) {
      $loop->the_post();

      $thumb_id  = get_post_thumbnail_id();
      $image_url = $thumb_id ? wp_get_attachment_image_url($thumb_id, $thumb_size) : '';
      $image_alt = $thumb_id ? get_post_meta($thumb_id, '_wp_attachment_image_alt', true) : '';
      if ($image_alt === '') {
        $image_alt = get_the_title();
      }

      $terms_list = get_the_terms(get_the_ID(), $taxonomy);
      $primary_term = (is_array($terms_list) && !empty($terms_list)) ? $terms_list[0] : null;
      $tag_label = $primary_term && !is_wp_error($primary_term) ? $primary_term->name : '';

      $items[] = array(
        'imageURL'     => $image_url ?: '',
        'imageAlt'     => $image_alt ?: '',
        'heading'      => get_the_title(),
        'text'         => wp_strip_all_tags(get_the_excerpt()),
        'buttonLabel'  => __('Read More', 'vite-ttf-child-creceri'),
        'buttonURL'    => get_permalink(),
        'tag'          => $tag_label,
        'meta'         => get_the_author(),
        'date'         => get_the_date('F j, Y'),
        'datetime'     => get_the_date(DATE_W3C),
      );
    }
    wp_reset_postdata();
  }
}

if (empty($items)) {
  return;
}

$style_attr = '';
if ($backgroundType === 'color' && $backgroundValue !== '') {
  $style_attr = 'style="background:' . esc_attr($backgroundValue) . '"';
} elseif ($backgroundType === 'gradient' && $backgroundValue !== '') {
  $style_attr = 'style="background:' . esc_attr($backgroundValue) . '"';
}
?>
<section class="section full-bleed" aria-label="<?php echo esc_attr($ariaLabel); ?>" <?php echo $style_attr; ?>>
  <div class="container">
    <div class="header">
      <h2><?php echo esc_html($title !== '' ? $title : __('Related Posts', 'vite-ttf-child-creceri')); ?></h2>
      <?php if ($controls) : ?>
      <div class="controls">
        <button id="<?php echo esc_attr($prev_id); ?>" class="prev" aria-label="<?php esc_attr_e('Previous', 'vite-ttf-child-creceri'); ?>">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 18l-6-6 6-6"/></svg>
        </button>
        <button id="<?php echo esc_attr($next_id); ?>" class="next" aria-label="<?php esc_attr_e('Next', 'vite-ttf-child-creceri'); ?>">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 6l6 6-6 6"/></svg>
        </button>
      </div>
      <?php endif; ?>
    </div>
    <?php if ($intro !== '') : ?>
      <p class="carousel-intro"><?php echo esc_html($intro); ?></p>
    <?php endif; ?>

    <div class="carousel">
      <div class="carousel__track" id="<?php echo esc_attr($track_id); ?>">
        <?php foreach ($items as $item) :
          $img = ccar_str($item, 'imageURL');
          $alt = ccar_str($item, 'imageAlt');
          $heading = ccar_str($item, 'heading');
          $text = ccar_str($item, 'text');
          $btnLabel = ccar_str($item, 'buttonLabel', __('Read More', 'vite-ttf-child-creceri'));
          $btnUrl   = ccar_str($item, 'buttonURL', '#');
          $meta     = ccar_str($item, 'meta');
          $tag      = ccar_str($item, 'tag');
          $date     = ccar_str($item, 'date');
          $datetime = ccar_str($item, 'datetime');

          $thumb_style = $img ? ' style="background-image:url(' . esc_url($img) . ');"' : '';
          $thumb_aria  = $img ? ' aria-label="' . esc_attr($alt) . '"' : '';
          ?>
        <article class="card">
          <div class="thumb" role="img"<?php echo $thumb_style . $thumb_aria; ?>></div>
          <?php if ($tag !== '') : ?>
            <span class="badge"><?php echo esc_html($tag); ?></span>
          <?php endif; ?>
          <h3><?php echo esc_html($heading); ?></h3>
          <div class="meta">
            <span class="dot" aria-hidden="true"></span>
            <span><?php echo esc_html($meta); ?></span>
          </div>
          <p><?php echo esc_html($text); ?></p>
          <div class="footer">
            <span class="date">
              <?php if ($datetime !== '') : ?>
                <time datetime="<?php echo esc_attr($datetime); ?>"><?php echo esc_html($date); ?></time>
              <?php else : ?>
                <?php echo esc_html($date); ?>
              <?php endif; ?>
            </span>
            <?php if ($cardLinkBehavior !== 'none') : ?>
              <a class="btn btn-sm" href="<?php echo esc_url($btnUrl); ?>">
                <?php echo esc_html($btnLabel); ?>
              </a>
            <?php endif; ?>
          </div>
        </article>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>

<script>
  (function(){
    const track = document.getElementById('<?php echo esc_js($track_id); ?>');
    if(!track) return;
    const prev  = document.getElementById('<?php echo esc_js($prev_id); ?>');
    const next  = document.getElementById('<?php echo esc_js($next_id); ?>');

    function step(){
      const card = track.querySelector('.card');
      if(!card) return 0;
      const style = window.getComputedStyle(track);
      const gap = parseFloat(style.columnGap || style.gap || 0);
      return card.getBoundingClientRect().width + gap;
    }

    function updateButtons(){
      if(!prev || !next) return;
      const max = track.scrollWidth - track.clientWidth - 1;
      prev.disabled = track.scrollLeft <= 0;
      next.disabled = track.scrollLeft >= max;
    }

    if(prev){
      prev.addEventListener('click', function(){
        track.scrollBy({ left: -step(), behavior: 'smooth' });
      });
    }
    if(next){
      next.addEventListener('click', function(){
        track.scrollBy({ left: step(), behavior: 'smooth' });
      });
    }

    track.addEventListener('scroll', updateButtons, { passive: true });
    window.addEventListener('resize', updateButtons);

    track.tabIndex = 0;
    track.addEventListener('keydown', function(e){
      if (e.key === 'ArrowRight' && next) { next.click(); }
      if (e.key === 'ArrowLeft' && prev) { prev.click(); }
    });

    updateButtons();
  })();
</script>
