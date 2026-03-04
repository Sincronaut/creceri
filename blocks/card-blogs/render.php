<?php 
/**
 * Tek Stories — dynamic render (right column CTA at bottom)
 * - Wide search on ?s=
 * - Optional sort: ?sort=newest|oldest|a-z|z-a|random
 * - Bottom CTA config: rightCtaText, rightCtaUrl
 */

function tek_val($arr,$key,$default=null){ return (is_array($arr)&&array_key_exists($key,$arr))?$arr[$key]:$default; }
function tek_bool($arr,$key,$default=false){ return (bool)tek_val($arr,$key,$default); }
function tek_str($arr,$key,$default=''){ $v=tek_val($arr,$key,$default); return is_string($v)?$v:((is_null($v))?'':strval($v)); }
function tek_hex2rgba($hex,$alpha=1.0){ $hex=preg_replace('/[^0-9a-fA-F]/','',(string)$hex);
  if(strlen($hex)===3){ $r=hexdec(str_repeat($hex[0],2));$g=hexdec(str_repeat($hex[1],2));$b=hexdec(str_repeat($hex[2],2)); }
  else{ $hex=str_pad($hex,6,'0',STR_PAD_RIGHT);$r=hexdec(substr($hex,0,2));$g=hexdec(substr($hex,2,2));$b=hexdec(substr($hex,4,2)); }
  $alpha=max(0,min(1,floatval($alpha))); return "rgba($r,$g,$b,$alpha)";
}
function tek_url_from($node){ if(!is_array($node))return ''; $link=tek_val($node,'link',[]); $u1=tek_str($link,'url',''); $u2=tek_str($node,'url',''); return $u1!==''?$u1:$u2; }

/* ---------- Global fallback image ---------- */
function tek_get_fallback_image() { return get_stylesheet_directory_uri() . '/assets/images/fallback-image.webp'; }

/* ---------------- Wide search plumbing ---------------- */
function tek_get_param(string $key): string {
  return isset($_GET[$key]) ? sanitize_text_field( wp_unslash($_GET[$key]) ) : '';
}
function tek_wide_search_add_filters(){
  add_filter('posts_join',     'tek_wide_search_join',     10, 2);
  add_filter('posts_search',   'tek_wide_search_where',    10, 2);
  add_filter('posts_groupby',  'tek_wide_search_groupby',  10, 2);
  add_filter('posts_distinct', 'tek_wide_search_distinct', 10, 2);
}
function tek_wide_search_remove_filters(){
  remove_filter('posts_join',     'tek_wide_search_join',     10);
  remove_filter('posts_search',   'tek_wide_search_where',    10);
  remove_filter('posts_groupby',  'tek_wide_search_groupby',  10);
  remove_filter('posts_distinct', 'tek_wide_search_distinct', 10);
}
function tek_wide_search_join($join, $q){
  if ( ! $q->get('tek_wide_search') ) return $join;
  global $wpdb;
  $join .= " LEFT JOIN {$wpdb->users} u ON u.ID = {$wpdb->posts}.post_author";
  $join .= " LEFT JOIN {$wpdb->term_relationships} tr ON tr.object_id = {$wpdb->posts}.ID";
  $join .= " LEFT JOIN {$wpdb->term_taxonomy}   tt ON tt.term_taxonomy_id = tr.term_taxonomy_id";
  $join .= " LEFT JOIN {$wpdb->terms}            t ON t.term_id = tt.term_id";
  return $join;
}
function tek_wide_search_where($search, $q){
  if ( ! $q->get('tek_wide_search') ) return $search;
  global $wpdb;
  // Use the raw ?s= to preserve multi-word queries (e.g., "digital world").
  $raw = tek_get_param('s');
  $s   = ($raw !== '') ? $raw : $q->get('s');
  if ($s === '' || $s === null) return $search;

  // Tokenize on whitespace/+ and match ANY word against:
  // - title/content/excerpt (title first priority)
  // - taxonomy terms (tags/categories)
  $terms = preg_split('/[\s\+]+/', $s, -1, PREG_SPLIT_NO_EMPTY);
  if (empty($terms)) return $search;

  $clauses = [];
  foreach ($terms as $term) {
    $like = '%' . $wpdb->esc_like($term) . '%';
    // Title / content / excerpt hit
    $clauses[] = $wpdb->prepare(
      "( {$wpdb->posts}.post_title LIKE %s
          OR {$wpdb->posts}.post_content LIKE %s
          OR {$wpdb->posts}.post_excerpt LIKE %s )",
      $like, $like, $like
    );
    // Tag / category hit
    $clauses[] = $wpdb->prepare(
      "( t.name LIKE %s OR {$wpdb->posts}.post_title LIKE %s )",
      $like, $like
    );
  }

  // OR logic: any word hit is enough, and either title/content OR term match will surface the post.
  $search = ' AND (' . implode(' OR ', $clauses) . ') ';
  return $search;
}
function tek_wide_search_groupby($groupby, $q){
  if ( ! $q->get('tek_wide_search') ) return $groupby;
  global $wpdb;
  if (empty($groupby)) return "{$wpdb->posts}.ID";
  if (strpos($groupby, "{$wpdb->posts}.ID") === false) $groupby .= ", {$wpdb->posts}.ID";
  return $groupby;
}
function tek_wide_search_distinct($distinct, $q){
  if ( ! $q->get('tek_wide_search') ) return $distinct;
  return 'DISTINCT';
}
/* ---------------------------------------------------------------- */

/* ---------- WP posts -> items mapper ---------- */
function tek_build_items_from_query(array $attributes): array {
  if ( ! function_exists('get_posts') ) return [];

  $usePosts  = (bool) tek_val($attributes,'usePosts',false);
  if ( ! $usePosts ) return [];

  $ppp       = max(1, intval(tek_val($attributes,'postsPerPage',4)));

  // Sort
  $sort_attr = strtolower(tek_str($attributes,'sort','newest'));
  $sort_qs   = strtolower(tek_get_param('sort'));
  $allowed   = ['newest','oldest','a-z','z-a','random'];
  $sort      = in_array($sort_qs,$allowed,true) ? $sort_qs : $sort_attr;

  $search    = tek_get_param('s');

  $catSlugs   = tek_val($attributes,'categorySlugs',[]);
  $catIds     = tek_val($attributes,'categoryIds',[]);
  $excludeIds = tek_val($attributes,'excludeIds',[]);

  $orderby='date'; $order='DESC';
  if ($sort==='oldest'){ $order='ASC'; }
  if ($sort==='random'){ $orderby='rand'; }
  if ($sort==='a-z'){ $orderby='title'; $order='ASC'; }
  if ($sort==='z-a'){ $orderby='title'; $order='DESC'; }

  $tax_query = [];
  if (is_array($catSlugs) && $catSlugs){
    $tax_query[] = [
      'taxonomy' => 'category',
      'field'    => 'slug',
      'terms'    => array_filter(array_map('sanitize_title',$catSlugs)),
      'operator' => 'IN',
    ];
  }

  $query_args = [
    'post_type'           => 'post',
    'post_status'         => 'publish',
    'posts_per_page'      => $ppp,
    'orderby'             => $orderby,
    'order'               => $order,
    'ignore_sticky_posts' => true,
    'no_found_rows'       => true,
    's'                   => $search,
    'tek_wide_search'     => ($search !== ''),
  ];

  if ($catIds){     $query_args['category__in']  = array_map('intval',$catIds); }
  if ($excludeIds){ $query_args['post__not_in']  = array_map('intval',$excludeIds); }
  if ($tax_query){  $query_args['tax_query']     = $tax_query; }

  if ($search !== ''){ tek_wide_search_add_filters(); }
  $posts = get_posts($query_args);
  if ($search !== ''){ tek_wide_search_remove_filters(); }
  if (!$posts) return [];

  

  $items = [];
  foreach ($posts as $p){
    $pid   = $p->ID;
    $img   = get_the_post_thumbnail_url($pid,'large')
          ?: get_the_post_thumbnail_url($pid,'medium_large')
          ?: tek_get_fallback_image();

    $excerpt = has_excerpt($pid)
      ? get_the_excerpt($pid)
      : wp_trim_words( wp_strip_all_tags( get_post_field('post_content',$pid) ), 28 );

    $cats = get_the_category($pid);
    $cat_items = [];
    if ($cats){
      foreach($cats as $c){
        $cat_items[] = ['label'=>$c->name,'url'=>get_category_link($c->term_id)];
      }
    }

    $items[] = [
      'image'      => $img ?: tek_get_fallback_image(),
      'heading'    => get_the_title($pid),
      'text'       => wp_strip_all_tags($excerpt),
      'url'        => get_permalink($pid),
      'date'       => get_the_date('', $pid),
      'author'     => get_the_author_meta('display_name', $p->post_author),
      'categories' => $cat_items,
      'overlay'    => ['enabled'=>false],
      'categoryPosition' => tek_str($attributes,'categoryButtonPosition','top'),
    ];
  }
  return $items;
}
/* --------------------------- /mapper --------------------------- */

/* ===== Attributes ===== */
$items          = is_array($attributes['items'] ?? null) ? $attributes['items'] : [];
$title          = tek_str($attributes,'title','Tek Stories');
$intro          = tek_str($attributes,'intro','');

$cta_text       = trim(tek_str($attributes,'ctaText',''));
$cta_url        = tek_str($attributes,'ctaUrl','#');

/* Right-column CTA (bottom only) */
$right_cta_text = tek_str($attributes,'rightCtaText', ($cta_text !== '' ? $cta_text : 'Browse More'));
$right_cta_url  = tek_str($attributes,'rightCtaUrl',  ($cta_url  !== '' ? $cta_url  : '/blogs'));
$right_cta_on   = ($right_cta_text !== '' && $right_cta_url !== '');

$show_read_global = tek_bool($attributes,'showReadLink',true);
$read_label_global= tek_str($attributes,'readLinkText','Read More');

$show_title_global   = tek_bool($attributes,'showTitle',true);

/* only for section intro */
$show_desc_global    = tek_bool($attributes,'showDescription',true);
$render_item_desc    = false;

$show_meta_global    = tek_bool($attributes,'showMeta',true);
$show_date_global    = tek_bool($attributes,'showDate',true);
$show_author_global  = tek_bool($attributes,'showAuthor',true);
$show_category_global= tek_bool($attributes,'showCategory',true);

$meta_sep_text       = tek_str($attributes,'metaSeparator',' | ');

$total_to_show  = intval($attributes['data_count'] ?? 4);
$belt_enabled   = (tek_str($attributes,'animation','Off') === 'On');

$cta_bg         = tek_str($attributes,'ctaBgColor','#962E2A');

$overlay_enabled= tek_bool($attributes,'overlayEnabled',true);
$ov_color       = tek_str($attributes,'overlayColor','#000000');
$ov_opacity     = floatval($attributes['overlayOpacity'] ?? 0.55);
$ov_size        = intval($attributes['overlaySize'] ?? 60);
$ov_position    = tek_str($attributes,'overlayPosition','bottom');
$image_pos      = tek_str($attributes,'imagePosition','center');

/* Right column config */
$right_title     = trim(tek_str($attributes,'rightTitle','Top Reads'));

$item_btn_pos_def     = tek_str($attributes,'itemButtonPosition','bottom');
$feature_btn_pos_def  = tek_str($attributes,'featureButtonPosition','bottom');

/* Buttons + tags */
$btn_shape = tek_str($attributes,'buttonShape','round');
$btn_bg    = tek_str($attributes,'buttonBgColor','#F5E8E6');
$btn_text  = tek_str($attributes,'buttonTextColor','#962E2A');
$btn_border= tek_str($attributes,'buttonBorderColor',$btn_text);

$category_pos_def = tek_str($attributes,'categoryButtonPosition','top');
$category_variant = tek_str($attributes,'categoryVariant','outline');
$cat_bg           = tek_str($attributes,'categoryBgColor','#F5E8E6');
$cat_text         = tek_str($attributes,'categoryTextColor','#962E2A');
$cat_border       = tek_str($attributes,'categoryBorderColor',$cat_text);

/* Hydrate from WP if enabled */
$from_query = tek_build_items_from_query($attributes);
if (!empty($from_query)) {
  $items = $from_query;
  if (!isset($attributes['data_count']) || intval($attributes['data_count'])<=0) {
    $total_to_show = count($items);
  }
}

/* Normalize / split + enforce fallback */

$items = array_values(array_filter($items,function($it){
  return is_array($it) && (trim(tek_str($it,'image'))!=='' || trim(tek_str($it,'heading'))!=='' || trim(tek_str($it,'text'))!=='' || trim(tek_str($it,'url'))!=='');
}));
$items = array_map(function($it) {
  if (trim(tek_str($it,'image',''))==='') $it['image'] = tek_get_fallback_image();
  return $it;
}, $items);
if($total_to_show>0) $items = array_slice($items,0,$total_to_show);

/* Determine emptiness before promoting feature */
$is_empty = empty($items);

/* Promote first as feature, rest as list (max 3) */
$feature = null;
if (!$is_empty) {
  $feature = array_shift($items);
  $items   = array_slice($items,0,3);
}

/* Helpers */
$media_style = function($url,$item=null) use($overlay_enabled,$ov_color,$ov_opacity,$ov_size,$ov_position,$image_pos){
  if(!$url) return '';
  $ov=(is_array($item)?tek_val($item,'overlay',[]):[]);
  $enabled=is_array($ov)&&array_key_exists('enabled',$ov)?(bool)$ov['enabled']:$overlay_enabled;
  $color  =is_array($ov)?tek_str($ov,'color',$ov_color):$ov_color;
  $opacity=is_array($ov)&&array_key_exists('opacity',$ov)?floatval($ov['opacity']):$ov_opacity;
  $size   =is_array($ov)&&array_key_exists('size',$ov)?intval($ov['size']):$ov_size;
  $position=is_array($ov)?tek_str($ov,'position',$ov_position):$ov_position;
  $dir=($position==='top')?'to bottom':'to top'; $rgba=tek_hex2rgba($color,$opacity);
  $bg='url('.esc_url($url).')';
  $layer=($enabled&&$opacity>0)?"linear-gradient($dir,$rgba 0%,rgba(0,0,0,0) {$size}%), $bg":$bg;
  return 'style="background-image:'.esc_attr($layer).';background-position:'.esc_attr($image_pos).';background-size:cover;background-repeat:no-repeat;"';
};
$visible = function(bool $global,array $item=null,string $hideKey=null){ if(!$hideKey||!is_array($item))return $global; return array_key_exists($hideKey,$item)?!(bool)$item[$hideKey]:$global; };

/* Read button - Updated to simple text link */
$build_read = function(array $item=null) use($show_read_global,$read_label_global){
  $url = tek_url_from($item ?? []);
  $label = trim(tek_str($item,'customReadText',$read_label_global));
  $hide = tek_bool($item,'hideReadLink',false);
  if(!$show_read_global || $hide || $url==='' || $label==='') return '';
  return '<a class="read-more-link" href="'.esc_url($url).'">'.esc_html($label).' &rsaquo;</a>';
};

$build_tags = function(array $item=null,string $scope='item',string $pos='top',string $variant='outline',string $bg='#F5E8E6',string $text='#962E2A',string $border='#962E2A') use($visible,$show_category_global){
  if(!$visible($show_category_global,$item,'hideCategory')) return '';
  $cats=tek_val($item,'categories',[]); $cats=is_array($cats)?array_values(array_filter($cats,fn($c)=>is_array($c)&&trim(tek_str($c,'label',''))!=='')):[]; if(!$cats) return '';
  $cls=$scope==='feature'?'feature-tags':'item-tags';
  $html='<div class="tags '.$cls.' '.$cls.'--'.esc_attr($pos).'" style="--tag-bg:'.esc_attr($bg).';--tag-text:'.esc_attr($text).';--tag-border:'.esc_attr($border).';">';
  foreach($cats as $c){ $label=esc_html(tek_str($c,'label','')); $url=esc_url(tek_str($c,'url','')); $a_cls='tag'.($variant==='outline'?' tag--outline':''); $html.=$url?'<a class="'.$a_cls.'" href="'.$url.'">'.$label.'</a>':'<span class="'.$a_cls.'">'.$label.'</span>'; }
  $html.='</div>'; return $html;
};

/* Flags */
$has_intro = ($show_desc_global && trim($intro)!=='' );
$has_cta   = $right_cta_on; /* Use right CTA values — that's where the button data is stored */
$has_rtitle= ($right_title!=='' );

$section_classes='wrap has-rtitle--list';

/* View */
?>
<section class="<?php echo esc_attr($section_classes); ?>" aria-labelledby="tek-stories-title">

  <div class="stories-header">
    <?php if($show_title_global && $title): ?>
      <h2 id="tek-stories-title" class="stories-title"><?php echo esc_html($title); ?></h2>
    <?php endif; ?>
  </div>

  <?php if($has_intro || $has_cta): ?>
    <div class="stories-intro" style="display:flex;align-items:center;justify-content:space-between;gap:2rem;">
      <?php if($has_intro): ?><p class="stories-sub" style="margin-bottom:0;"><?php echo esc_html($intro); ?></p><?php endif; ?>
      <?php if($has_cta): ?>
        <div class="stories-actions" style="flex-shrink:0;">
          <a class="btn-pill" href="<?php echo esc_url($right_cta_url); ?>"><?php echo esc_html($right_cta_text); ?></a>
        </div>
      <?php endif; ?>
    </div>
  <?php endif; ?>

  <?php if ($is_empty): ?>
    <div class="coming-soon">Coming Soon</div>
  <?php else: ?>
  <div class="grid">
    <?php
      if(is_array($feature) && !empty($feature)):
        $feature['image'] = trim(tek_str($feature,'image','')) ? $feature['image'] : tek_get_fallback_image();
        $feature_read = $build_read($feature);
        $feature_text = trim(tek_str($feature,'text',''));
    ?>
      <article class="feature">
        <div class="feature-media" <?php echo $media_style(tek_str($feature,'image',tek_get_fallback_image()),$feature); ?>></div>

        <div class="feature-body">
          <?php if(trim(tek_str($feature,'heading',''))!==''): ?>
            <h3 class="feature-title"><?php echo esc_html(tek_str($feature,'heading','Coming Soon')); ?></h3>
          <?php endif; ?>

          <?php if ($feature_text !== ''): ?>
            <p class="feature-excerpt"><?php echo esc_html($feature_text); ?></p>
          <?php endif; ?>

          <?php echo $feature_read; ?>
        </div>
      </article>
    <?php endif; ?>

    <!-- Right column -->
    <div class="list-col">

      <?php $list_id = 'blogs-track-' . uniqid(); ?>
      <div class="list<?php echo $belt_enabled ? ' belt' : ''; ?>" id="<?php echo esc_attr($list_id); ?>">
        <?php foreach($items as $item): 
              $item['image'] = trim(tek_str($item,'image','')) ? $item['image'] : tek_get_fallback_image();
              $item_read=$build_read($item);
              $item_text = trim(tek_str($item,'text','')); ?>
          <article class="item">
            <div class="thumb" aria-hidden="true" <?php echo $media_style(tek_str($item,'image',tek_get_fallback_image()),$item); ?>></div>
            <div class="item-data">
              <h3><?php echo esc_html(tek_str($item,'heading','Coming Soon')); ?></h3>
              <?php if ($item_text !== ''): ?>
                <p class="item-excerpt"><?php echo esc_html($item_text); ?></p>
              <?php endif; ?>
              <?php echo $item_read; ?>
            </div>
          </article>
        <?php endforeach; ?>
      </div>

      <?php if (count($items) > 1): ?>
        <div class="blogs-dots" aria-hidden="true">
          <?php for ( $d = 0; $d < count($items); $d++ ) : ?>
            <button class="blog-dot <?php echo $d === 0 ? 'is-active' : ''; ?>" aria-label="Go to slide <?php echo $d + 1; ?>"></button>
          <?php endfor; ?>
        </div>
      <?php endif; ?>

    </div>
  </div>
  <?php endif; ?>
  
  <script>
    document.addEventListener("DOMContentLoaded", function() {
      const sliderList = document.getElementById("<?php echo esc_js($list_id); ?>");
      if (!sliderList) return;

      const dotsContainer = sliderList.nextElementSibling;
      if (!dotsContainer || !dotsContainer.classList.contains('blogs-dots')) return;
      
      const dots = dotsContainer.querySelectorAll('.blog-dot');
      const items = sliderList.querySelectorAll('article.item');
      if (dots.length === 0 || items.length === 0) return;

      // Light up dots dynamically based on scroll position
      sliderList.addEventListener('scroll', () => {
        const scrollLeft = sliderList.scrollLeft;
        const itemWidth = items[0].offsetWidth + parseInt(window.getComputedStyle(sliderList).gap || 0);

        let index = Math.round(scrollLeft / itemWidth);

        // Fix for the last card edge cases
        const maxScrollLeft = sliderList.scrollWidth - sliderList.clientWidth;
        if (Math.ceil(scrollLeft) >= maxScrollLeft - 10) { 
          index = dots.length - 1; 
        }

        if (index >= dots.length) index = dots.length - 1;
        if (index < 0) index = 0;

        dots.forEach(d => {
          d.classList.remove('is-active');
          d.blur(); // Strips focus stuckness on scroll
        });
        if (dots[index]) dots[index].classList.add('is-active');
      }, { passive: true });

      // Scroll container cleanly when dot is clicked
      dots.forEach((dot, index) => {
        dot.addEventListener('click', (e) => {
          e.preventDefault();

          // Force state update instantly
          dots.forEach(d => { d.classList.remove('is-active'); d.blur(); });
          dot.classList.add('is-active');

          const containerLeft = sliderList.getBoundingClientRect().left;
          const offset = items[index].getBoundingClientRect().left - containerLeft + sliderList.scrollLeft;
          
          sliderList.scrollTo({ left: offset, behavior: 'smooth' });
        });
      });
    });
  </script>
</section>
