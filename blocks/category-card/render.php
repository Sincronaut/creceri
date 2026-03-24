<?php
if (!defined('ABSPATH')) {
  exit;
}

function cc_val($a, $k, $d = null)
{
  return (is_array($a) && array_key_exists($k, $a)) ? $a[$k] : $d;
}
function cc_str($a, $k, $d = '')
{
  $v = cc_val($a, $k, $d);
  return is_string($v) ? $v : ((is_null($v)) ? '' : strval($v));
}
function cc_bool($a, $k, $d = false)
{
  return (bool)cc_val($a, $k, $d);
}
function cc_clean($v)
{
  return html_entity_decode(wp_specialchars_decode((string)$v), ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

$A = is_array($attributes ?? null) ? $attributes : array();
$uri = $_SERVER['REQUEST_URI'] ?? '';

function cc_i18n($A, $key, $fallback) {
  $val = cc_val($A, $key);
  return cc_clean($val ?: $fallback);
}

$is_ko = (strpos($uri, '/ko/') !== false) || (function_exists('get_locale') && strpos(get_locale(), 'ko') === 0);

$i18n = [
  'no_results'      => cc_i18n($A, 'noResultsText',     $is_ko ? '결과를 찾을 수 없습니다' : 'No results found'),
  'no_results_desc' => cc_i18n($A, 'noResultsDescText', $is_ko ? '검색어와 일치하는 내용을 찾을 수 없습니다. 다른 키워드를 입력해 보세요.' : "We couldn't find anything matching your search. Please try a different keyword."),
  'all'             => cc_i18n($A, 'allText',           $is_ko ? '전체' : 'All'),
  'all_cats'        => cc_i18n($A, 'allCatsText',      $is_ko ? '전체 카테고리' : 'All Categories'),
  'sort_label'      => $is_ko ? cc_clean('정렬:') : 'Sort:',
  'newest'          => $is_ko ? cc_clean('최신순') : 'Newest',
  'oldest'          => $is_ko ? cc_clean('오래된순') : 'Oldest',
  'title_az'        => $is_ko ? cc_clean('제목 (A - Z)') : 'Title A - Z',
  'title_za'        => $is_ko ? cc_clean('제목 (Z - A)') : 'Title Z - A',
  'read_more'       => cc_i18n($A, 'readLinkText',      $is_ko ? '자세히 보기' : 'Read More'),
  'prev'            => cc_i18n($A, 'prevText',          $is_ko ? '이전' : 'Prev'),
  'next'            => cc_i18n($A, 'nextText',          $is_ko ? '다음' : 'Next'),
  'goto'            => cc_i18n($A, 'gotoText',          $is_ko ? '이동:' : 'Go to:'),
  'lang'            => $is_ko ? 'ko-KR' : 'en-US'
];

$title = cc_str($A, 'title', 'Who We Are?');
$content = cc_str($A, 'content', '');
$cta_text = cc_str($A, 'ctaText', 'Learn More');
$cta_url = cc_str($A, 'ctaUrl', '#');
$btn_class = cc_str($A, 'btnClass', 'btn-primary');
$reverse = cc_bool($A, 'reverse', false);
$sectionId = cc_str($A, 'sectionId', 'who-title');

$items = is_array($A['items'] ?? null) ? $A['items'] : array();
$use_posts = cc_bool($A, 'usePosts', false);
$posts_per_page = intval($A['postsPerPage'] ?? 12);
$order_by_attr = cc_str($A, 'orderBy', 'date');
$order_attr = strtolower(cc_str($A, 'order', 'desc'));
$cat_attr = $A['categories'] ?? array();

$pageSize = intval($A['pageSize'] ?? 6);
$sort = cc_str($A, 'sort', 'newest');

$brand = cc_str($A, 'brand', '#962E2A');
$bg = cc_str($A, 'bg', '#ffffff');
$line = cc_str($A, 'line', '#E5E7EB');
$chip = cc_str($A, 'chip', '#F5E8E6');
$radius = floatval($A['radius'] ?? 14);

$cardW = max(260, floatval($A['cardWidth'] ?? 380));
$gap = max(12, floatval($A['gap'] ?? 22));

$allowed_order_by = array('date', 'title');
$order_by = in_array($order_by_attr, $allowed_order_by, true) ? $order_by_attr : 'date';
$order = ($order_attr === 'asc') ? 'ASC' : 'DESC';
$posts_per_page = max(1, $posts_per_page);
$cat_ids = array_filter(array_map('intval', is_array($cat_attr) ? $cat_attr : array()));

/* Query + hydrate all categories for badges/filters */
if ($use_posts || empty($items)) {
  $qargs = array(
    'post_type' => 'post',
    'post_status' => 'publish',
    'posts_per_page' => -1, // Use -1 to get all posts so client-side pagination functions fully
    'orderby' => $order_by,
    'order' => $order,
    'ignore_sticky_posts' => true,
  );
  if (!empty($cat_ids)) {
    $qargs['tax_query'] = array(array(
        'taxonomy' => 'category', 'field' => 'term_id', 'terms' => $cat_ids,
      ));
  }

  // Inject search term if on a search page
  if (is_search()) {
    $qargs['s'] = get_search_query();

    if (!function_exists('cc_custom_search_filter')) {
      function cc_custom_search_filter($search, $wp_query)
      {
        if (empty($search))
          return $search;
        $q = $wp_query->query_vars;
        if (empty($q['search_terms']))
          return $search;
        global $wpdb;

        $n = !empty($q['exact']) ? '' : '%';
        $new_search = '';
        $searchand = '';
        foreach ((array)$q['search_terms'] as $term) {
          $like = $n . $wpdb->esc_like($term) . $n;
          $title_sql = $wpdb->prepare("{$wpdb->posts}.post_title LIKE %s", $like);
          $author_sql = $wpdb->prepare("{$wpdb->posts}.post_author IN (SELECT ID FROM {$wpdb->users} WHERE display_name LIKE %s)", $like);
          $new_search .= "{$searchand}({$title_sql} OR {$author_sql})";
          $searchand = ' AND ';
        }
        if (!empty($new_search)) {
          $search = " AND ({$new_search}) ";
          if (!is_user_logged_in()) {
            $search .= " AND ({$wpdb->posts}.post_password = '') ";
          }
        }
        return $search;
      }
    }
    add_filter('posts_search', 'cc_custom_search_filter', 10, 2);
  }

  $loop = new WP_Query($qargs);

  if (is_search()) {
    remove_filter('posts_search', 'cc_custom_search_filter', 10);
  }
  $items = array();

  if ($loop->have_posts()) {
    while ($loop->have_posts()) {
      $loop->the_post();

      $p_title = cc_clean(wp_strip_all_tags(get_the_title()));

      $terms = get_the_category();
      $primary = (is_array($terms) && $terms) ? $terms[0] : null;
      if (!$primary) {
        $pid = (int)get_option('default_category');
        $primary = $pid ? get_term($pid, 'category') : null;
      }

      $badges = array();
      if (is_array($terms)) {
        foreach ($terms as $t) {
          if (is_wp_error($t))
            continue;
          $cat_label = cc_clean($t->name);
          if ($is_ko) {
            $cat_label = trim(preg_replace('/\s*\(.*?\)/', '', $cat_label));
          }
          $badges[] = array(
            'slug' => $t->slug,
            'label' => $cat_label,
            'url' => '?filter=' . urlencode($t->slug) . '#category-list',
          );
        }
      }

      $thumb_id = get_post_thumbnail_id();
      $image_src = $thumb_id ? wp_get_attachment_image_url($thumb_id, 'large') : get_stylesheet_directory_uri() . '/assets/images/fallback-image.webp';
      $image_alt = $thumb_id ? get_post_meta($thumb_id, '_wp_attachment_image_alt', true) : '';
      if ($image_alt === '')
        $image_alt = $p_title;

      $primary_label = ($primary && !is_wp_error($primary)) ? cc_clean($primary->name) : 'Uncategorized';
      if ($is_ko && $primary_label !== 'Uncategorized') {
        $primary_label = trim(preg_replace('/\s*\(.*?\)/', '', $primary_label));
      }

      $items[] = array(
        'title' => $p_title,
        'category' => ($primary && !is_wp_error($primary)) ? $primary->slug : 'uncategorized',
        'categoryLabel' => $primary_label,
        'categories' => $badges,
        'author' => cc_clean(get_the_author()),
        'date' => get_post_time('c'),
        'url' => get_permalink(),
        'snippet' => cc_clean(wp_strip_all_tags(get_the_excerpt())),
        'image' => array('src' => $image_src ?: get_stylesheet_directory_uri() . '/assets/images/fallback-image.webp', 'alt' => $image_alt ?: ''),
      );
    }
    wp_reset_postdata();
  }
}

/* Build filter set from ALL categories */
$category_labels = array();
foreach ($items as $it) {
  if (!empty($it['category'])) {
    $category_labels[$it['category']] = $it['categoryLabel'] ?: ucwords(str_replace('-', ' ', $it['category']));
  }
  if (!empty($it['categories'])) {
    foreach ($it['categories'] as $c) {
      $s = $c['slug'] ?? '';
      if ($s === '')
        continue;
      $l = $c['label'] ?? ucwords(str_replace('-', ' ', $s));
      $category_labels[$s] = $l;
    }
  }
}
if ($category_labels) {
  uasort($category_labels, fn($a, $b) => strcasecmp($a, $b));
}

$sec_id = $attributes['anchor'] ?? 'category-list';
$tpl_id = $sec_id . '-tpl';
$data_id = $sec_id . '-data';
$cfg_id = $sec_id . '-cfg';

$payload = array_map(function ($r) {
  $cats = array();
  if (!empty($r['categories'])) {
    foreach ($r['categories'] as $c) {
      $cats[] = array(
        'slug' => cc_str($c, 'slug', ''),
        'label' => cc_str($c, 'label', ''),
        'url' => cc_str($c, 'url', ''),
      );
    }
  }
  return array(
  'title' => cc_clean(wp_strip_all_tags(cc_str($r, 'title', ''))),
  'category' => cc_str($r, 'category', ''),
  'categoryLabel' => cc_clean(cc_str($r, 'categoryLabel', '')),
  'categories' => $cats,
  'author' => cc_clean(cc_str($r, 'author', '')),
  'date' => cc_str($r, 'date', ''),
  'url' => cc_str($r, 'url', '#'),
  'snippet' => cc_clean(cc_str($r, 'snippet', '')),
  'image' => array(
  'src' => cc_str(cc_val($r, 'image', []), 'src', ''),
  'alt' => cc_clean(cc_str(cc_val($r, 'image', []), 'alt', ''))
  )
  );
}, $items);

$config = array(
  'pageSize' => max(1, $pageSize),
  'sort' => in_array($sort, ['newest', 'oldest', 'title-az', 'title-za'], true) ? $sort : 'newest',
  'is_ko' => $is_ko,
  'i18n' => $i18n
);
?>
<section
  id="<?php echo esc_attr($sec_id); ?>"
  class="ccard<?php echo $reverse ? ' ccard--reverse' : ''; ?>"
  style="--brand: <?php echo esc_attr($brand); ?>; --bg: <?php echo esc_attr($bg); ?>; --line: <?php echo esc_attr($line); ?>; --chip: <?php echo esc_attr($chip); ?>; --radius: <?php echo esc_attr($radius); ?>px; --card-w: <?php echo esc_attr($cardW); ?>px; --gap: <?php echo esc_attr($gap); ?>px;"
>
  <!-- Hardening: z-index/flex-wrap so multiple tags never sit behind the image -->
  <style>
    #<?php echo esc_attr($sec_id); ?> .card .media{position:relative; display:block; border-radius:12px; overflow:hidden;}
    /* Case A: CSS background layer (if you use it) */
    #<?php echo esc_attr($sec_id); ?> .card .media-bg{position:absolute; inset:0; background-size:cover; background-position:center; z-index:1;}
    /* Case B: <img> tag (Bootstrap .card-img-top) */
    #<?php echo esc_attr($sec_id); ?> .card .media img,
    #<?php echo esc_attr($sec_id); ?> .card .card-img-top{position:relative; z-index:1; display:block; width:100%; height:auto; margin: 10 auto; object-fit: cover; object-position: center center;}
    /* Badges overlay – always above image/background */
    #<?php echo esc_attr($sec_id); ?> .card .badges{
      position:absolute; top:10px; left:10px; right:10px;
      z-index:3; display:flex; flex-wrap:wrap; gap:8px; max-width:calc(100% - 20px);
    }
    #<?php echo esc_attr($sec_id); ?> .card .badges a,
    #<?php echo esc_attr($sec_id); ?> .card .badges span{
      background:var(--rose-100); color:#962E2A; border:1px solid #962E2A;
      border-radius:999px; padding:.25rem .6rem; font-size:.8rem; line-height:1;
      box-shadow:0 1px 2px rgba(0,0,0,.08); position:relative; z-index:4;
      white-space:nowrap;text-decoration:none;font-weight:700;
    }
    /* Optional top gradient for legibility */
    #<?php echo esc_attr($sec_id); ?> .card .media::after{
      content:""; position:absolute; left:0; right:0; top:0; height:56px;
      background:linear-gradient(180deg, rgba(0,0,0,.22), rgba(0,0,0,0));
      z-index:2; pointer-events:none;
    }
  </style>

  <div class="ccard__toolbar">
    <div class="filters-container">
      <div class="filters-desktop" role="tablist" aria-label="<?php echo $is_ko ? '기사 필터' : 'Filter articles'; ?>">
        <button class="filter" data-filter="all" aria-pressed="true"><?php echo esc_html($i18n['all']); ?></button>
        <?php foreach ($category_labels as $cat_slug => $cat_label): ?>
          <button class="filter" data-filter="<?php echo esc_attr($cat_slug); ?>"><?php echo esc_html($cat_label); ?></button>
        <?php endforeach; ?>
      </div>
      <div class="filters-mobile">
        <label for="<?php echo esc_attr($sec_id); ?>-category" class="sr-only"><?php echo $is_ko ? '카테고리:' : 'Category:'; ?></label>
        <select id="<?php echo esc_attr($sec_id); ?>-category" class="category-select" aria-label="<?php echo $is_ko ? '카테고리별 필터' : 'Filter by category'; ?>">
          <option value="all"><?php echo esc_html($i18n['all_cats']); ?></option>
          <?php foreach ($category_labels as $cat_slug => $cat_label): ?>
            <option value="<?php echo esc_attr($cat_slug); ?>"><?php echo esc_html($cat_label); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>
    <div class="sort">
      <label for="<?php echo esc_attr($sec_id); ?>-sort"><?php echo esc_html($i18n['sort_label']); ?></label>
      <select id="<?php echo esc_attr($sec_id); ?>-sort" class="sort-select" aria-label="<?php echo $is_ko ? '기사 정렬' : 'Sort articles'; ?>">
        <option value="newest" <?php selected($config['sort'], 'newest'); ?>><?php echo esc_html($i18n['newest']); ?></option>
        <option value="oldest" <?php selected($config['sort'], 'oldest'); ?>><?php echo esc_html($i18n['oldest']); ?></option>
        <option value="title-az" <?php selected($config['sort'], 'title-az'); ?>><?php echo esc_html($i18n['title_az']); ?></option>
        <option value="title-za" <?php selected($config['sort'], 'title-za'); ?>><?php echo esc_html($i18n['title_za']); ?></option>
      </select>
    </div>
  </div>

  <div class="grid_category" aria-live="polite"></div>
  <div class="pagination" aria-label="Pagination"></div>

  <!-- Template: supports either <img> or CSS background -->
  <template id="<?php echo esc_attr($tpl_id); ?>">
    <article class="card">
      <div class="media">
        <span class="media-bg" aria-hidden="true" style="position: absolute; inset: 0; background-size: cover; background-position: center;"></span>
      </div>
      <div class="content">
        <h3 class="title"></h3>
        <div class="meta">
          <div class="avatar" aria-hidden="true"></div>
          <div class="byline"><span class="author"></span></div>
        </div>
        <p class="snippet"></p>
        <div class="footer">
          <span class="date">&bull; <time></time></span>
          <a class="cta cta-stretched-link" href="#"><?php echo esc_html($i18n['read_more']); ?></a>
        </div>
      </div>
    </article>
  </template>

  <script type="application/json" id="<?php echo esc_attr($data_id); ?>" class="ccard-data"><?php echo wp_json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?></script>
  <script type="application/json" id="<?php echo esc_attr($cfg_id); ?>" class="ccard-config"><?php echo wp_json_encode($config, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?></script>
  <script>
    (function(){
      const dec = (str) => {
        if (!str) return '';
        const t = document.createElement('textarea');
        t.innerHTML = str;
        return t.value;
      };

      function buildFilters(root, items){
        const holder = root.querySelector('.filters-desktop'); if(!holder) return;
        const existing = new Set(Array.from(holder.querySelectorAll('.filter')).map(b=>b.dataset.filter));
        const labels = new Map();
        items.forEach(item=>{
          if ((item.category||'').trim()){ const s=item.category.trim(); const l=item.categoryLabel||s.replace(/-/g,' ').replace(/\b\w/g,m=>m.toUpperCase()); labels.set(s,l); }
          if (Array.isArray(item.categories)){ item.categories.forEach(c=>{ const s=(c.slug||'').trim(); if(!s) return; const l=c.label||s.replace(/-/g,' ').replace(/\b\w/g,m=>m.toUpperCase()); labels.set(s,l); }); }
        });
        labels.forEach((l,s)=>{ 
          if(!existing.has(s)) {
            const b=document.createElement('button'); b.className='filter'; b.dataset.filter=s; b.textContent=l; holder.appendChild(b); 
          }
          const mobileSel = root.querySelector('.category-select');
          if (mobileSel && !mobileSel.querySelector(`option[value="${s}"]`)) {
            const opt = document.createElement('option'); opt.value = s; opt.textContent = l;
            mobileSel.appendChild(opt);
          }
        });
      }

      function bindBlock(root){
        const rawData = JSON.parse((root.querySelector('.ccard-data')?.textContent||'[]'));
        const data = rawData.map((row)=> {
          const cats = Array.isArray(row.categories) ? row.categories.map(c => ({
            ...c,
            label: dec(c.label || '')
          })) : [];
          return {
            ...row,
            title: dec(row.title || ''),
            categoryLabel: dec(row.categoryLabel || ''),
            author: dec(row.author || ''),
            snippet: dec(row.snippet || ''),
            categories: cats,
            image: {
              ...(row.image || {}),
              alt: dec((row.image && row.image.alt) || '')
            }
          };
        });
        const cfg  = JSON.parse((root.querySelector('.ccard-config')?.textContent||'{}'));
        const i18n = cfg.i18n || {};

        const fmtDate = (d) => {
          try {
            return new Date(d).toLocaleDateString(i18n.lang || undefined, {
              year: 'numeric',
              month: 'long',
              day: 'numeric'
            });
          } catch (e) {
            return d || '';
          }
        };
        const urlParams = new URLSearchParams(window.location.search);
        const state = { filter: urlParams.get('filter') || 'all', sort:cfg.sort||'newest', page:1, pageSize: Math.max(1, cfg.pageSize||6) };

        const grid = root.querySelector('.grid_category');
        const pagination = root.querySelector('.pagination');
        const tpl = root.querySelector('template');

        buildFilters(root, data);

        root.addEventListener('click', e=>{
          const f = e.target.closest('.filters-desktop .filter');
          if(f) {
            root.querySelectorAll('.filters-desktop .filter').forEach(x=>x.setAttribute('aria-pressed','false'));
            f.setAttribute('aria-pressed','true');
            state.filter = f.dataset.filter; state.page = 1; 
            
            const mobileSel = root.querySelector('.category-select');
            if(mobileSel) mobileSel.value = state.filter;
            
            render();
            const newUrl = new URL(window.location);
            newUrl.searchParams.set('filter', state.filter);
            window.history.replaceState({}, '', newUrl);
            return;
          }

          // Intercept badge clicks within the cards
          const badge = e.target.closest('.badge, a[href*="?filter="]');
          if(badge && badge.href && badge.href.includes('?filter=')) {
            e.preventDefault();
            const filterMatch = badge.href.match(/[\?&]filter=([^&#]+)/);
            if(filterMatch) {
              const filterSlug = decodeURIComponent(filterMatch[1]);
              state.filter = filterSlug; state.page = 1;
              
              // Sync UI components
              const filterBtn = root.querySelector(`.filters-desktop .filter[data-filter="${filterSlug}"]`);
              root.querySelectorAll('.filters-desktop .filter').forEach(x=>x.setAttribute('aria-pressed','false'));
              if(filterBtn) filterBtn.setAttribute('aria-pressed','true');
              
              const mobileSel = root.querySelector('.category-select');
              if(mobileSel) mobileSel.value = filterSlug;
              
              render();
              
              const newUrl = new URL(window.location);
              newUrl.searchParams.set('filter', state.filter);
              newUrl.hash = 'category-list';
              window.history.replaceState({}, '', newUrl);
              
              root.scrollIntoView({behavior: 'smooth', block: 'start'});
            }
          }
        });

        const categorySelect = root.querySelector('.category-select');
        if (categorySelect) {
          categorySelect.addEventListener('change', e => {
            state.filter = e.target.value;
            state.page = 1;
            
            // Sync desktop buttons
            const filterBtn = root.querySelector(`.filters-desktop .filter[data-filter="${state.filter}"]`);
            root.querySelectorAll('.filters-desktop .filter').forEach(x=>x.setAttribute('aria-pressed','false'));
            if(filterBtn) filterBtn.setAttribute('aria-pressed','true');
            
            render();
            
            const newUrl = new URL(window.location);
            newUrl.searchParams.set('filter', state.filter);
            window.history.replaceState({}, '', newUrl);
          });
        }

        const sortSelect = root.querySelector('.sort-select');
        if (sortSelect){ sortSelect.value = state.sort; sortSelect.addEventListener('change', e=>{ state.sort=e.target.value; render(); }); }

        function matchesFilter(row){
          if (state.filter==='all') return true;
          if ((row.category||'')===state.filter) return true;
          if (Array.isArray(row.categories)) return row.categories.some(c=> (c.slug||'')===state.filter);
          return false;
        }

        function workingSet(){
          let rows = data.slice().filter(matchesFilter);
          switch(state.sort){
            case 'newest': rows.sort((a,b)=> new Date(b.date)-new Date(a.date)); break;
            case 'oldest': rows.sort((a,b)=> new Date(a.date)-new Date(b.date)); break;
            case 'title-az': rows.sort((a,b)=> (a.title||'').trim().localeCompare((b.title||'').trim(), undefined, { sensitivity: 'base' })); break;
            case 'title-za': rows.sort((a,b)=> (b.title||'').trim().localeCompare((a.title||'').trim(), undefined, { sensitivity: 'base' })); break;
          }
          return rows;
        }

        function buildCard(row){
          const node = tpl.content.firstElementChild.cloneNode(true);
          const media = node.querySelector('.media');
          const mediaBg = node.querySelector('.media-bg');
          const imgEl = node.querySelector('img.card-img-top');
          const badgesWrap = node.querySelector('.badges');

          const img = (row.image && row.image.src) ? row.image.src : '';
          const alt = (row.image && row.image.alt) ? row.image.alt : (row.title||'');

          const cta = node.querySelector('.cta');

          // Set both; CSS ensures either works and stays under badges
          if (mediaBg) mediaBg.style.backgroundImage = img ? `url("${img}")` : 'none';

          if (cta) {
            cta.href = row.url || '#';
            cta.setAttribute('aria-label', row.title || 'Read ' + (row.title || 'post'));
          }

          node.querySelector('.title').textContent = row.title || '';
          node.querySelector('.author').textContent = row.author || '';
          node.querySelector('.snippet').textContent = row.snippet || '';
          const t = node.querySelector('time'); t.setAttribute('datetime', row.date||''); t.textContent = fmtDate(row.date);
          node.querySelector('.cta').href = row.url || '#';
          return node;
        }

        function render(){
          const rows = workingSet();
          
          grid.innerHTML = '';
          
          if (rows.length === 0) {
            grid.innerHTML = `<div class="cc-no-results" style="grid-column: 1 / -1; text-align: center; padding: 80px 20px; font-size: 1.1rem; color: #6b6f75; background: #fff; border-radius: 16px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);"><h3 style="margin-bottom: 10px; color: var(--brand, #962E2A); font-size: 1.5rem; font-weight: 700;">${dec(i18n.no_results)}</h3><p style="margin: 0;">${dec(i18n.no_results_desc)}</p></div>`;
            pagination.innerHTML = '';
            return;
          }

          const totalPages = Math.max(1, Math.ceil(rows.length / state.pageSize));
          state.page = Math.min(state.page, totalPages);

          const start = (state.page - 1) * state.pageSize;
          rows.slice(start, start + state.pageSize).forEach(r=> grid.appendChild(buildCard(r)));
          renderPagination(totalPages);
        }

        function renderPagination(totalPages){
          const mkBtn=(label,page,active=false,disabled=false)=>{const el=document.createElement('button'); el.className='page-btn'+(active?' active':''); el.textContent=dec(label); el.disabled=disabled; el.addEventListener('click',()=>{state.page=page; render();}); return el;};
          const mkGhost=(t='...')=>{const s=document.createElement('span'); s.className='page-ghost'; s.textContent=t; return s;};
          pagination.innerHTML=''; pagination.appendChild(mkBtn(i18n.prev || 'Prev', Math.max(1,state.page-1), false, state.page===1));
          const windowSize=5; const start=Math.max(1, state.page-Math.floor(windowSize/2)); const end=Math.min(totalPages, start+windowSize-1); const s=Math.max(1, Math.min(start, end-windowSize+1));
          if(s>1){ pagination.appendChild(mkBtn('1',1,state.page===1)); if(s>2) pagination.appendChild(mkGhost()); }
          for(let p=s;p<=end;p++){ pagination.appendChild(mkBtn(String(p),p,p===state.page)); }
          if(end<totalPages){ if(end<totalPages-1) pagination.appendChild(mkGhost()); pagination.appendChild(mkBtn(String(totalPages), totalPages, state.page===totalPages)); }
          pagination.appendChild(mkBtn(i18n.next || 'Next', Math.min(totalPages, state.page+1), false, state.page===totalPages));
          const goto=document.createElement('span'); goto.className='goto-wrap'; const inp=document.createElement('input'); inp.type='number'; inp.min='1'; inp.max=String(totalPages); inp.placeholder='e.g. 2'; inp.addEventListener('change', ()=>{ const v=Math.min(totalPages, Math.max(1, Number(inp.value||1))); state.page=v; render(); }); goto.append(dec(i18n.goto || 'Go to:'), inp); pagination.appendChild(goto);
        }

        // Sync initial filter button state
        const initialBtn = root.querySelector(`.filters-desktop .filter[data-filter="${state.filter}"]`);
        if (initialBtn) {
          root.querySelectorAll('.filters-desktop .filter').forEach(x=>x.setAttribute('aria-pressed','false'));
          initialBtn.setAttribute('aria-pressed','true');
        }
        const initialMobileSel = root.querySelector('.category-select');
        if (initialMobileSel) {
          initialMobileSel.value = state.filter;
        }

        render();
      }

      document.addEventListener('DOMContentLoaded', function(){
        document.querySelectorAll('.ccard').forEach(bindBlock);
      });
    })();
  </script>
</section>
