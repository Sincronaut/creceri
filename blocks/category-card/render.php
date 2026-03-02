<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

function cc_val($a,$k,$d=null){ return (is_array($a)&&array_key_exists($k,$a))?$a[$k]:$d; }
function cc_str($a,$k,$d=''){ $v=cc_val($a,$k,$d); return is_string($v)?$v:((is_null($v))?'':strval($v)); }
function cc_bool($a,$k,$d=false){ return (bool)cc_val($a,$k,$d); }
function cc_clean($v){
  return html_entity_decode( wp_specialchars_decode( (string) $v ), ENT_QUOTES | ENT_HTML5, 'UTF-8' );
}

$A = is_array($attributes ?? null) ? $attributes : array();

$title     = cc_str($A,'title','Who We Are?');
$content   = cc_str($A,'content','');
$cta_text  = cc_str($A,'ctaText','Learn More');
$cta_url   = cc_str($A,'ctaUrl','#');
$btn_class = cc_str($A,'btnClass','btn-primary');
$reverse   = cc_bool($A,'reverse',false);
$sectionId = cc_str($A,'sectionId','who-title');

$items          = is_array($A['items'] ?? null) ? $A['items'] : array();
$use_posts      = cc_bool($A,'usePosts',false);
$posts_per_page = intval($A['postsPerPage'] ?? 12);
$order_by_attr  = cc_str($A,'orderBy','date');
$order_attr     = strtolower(cc_str($A,'order','desc'));
$cat_attr       = $A['categories'] ?? array();

$pageSize  = intval($A['pageSize'] ?? 6);
$sort      = cc_str($A,'sort','newest');

$brand     = cc_str($A,'brand','#962E2A');
$bg        = cc_str($A,'bg','#ffffff');
$line      = cc_str($A,'line','#E5E7EB');
$chip      = cc_str($A,'chip','#F5E8E6');
$radius    = floatval($A['radius'] ?? 14);

$cardW     = max(260, floatval($A['cardWidth'] ?? 380));
$gap       = max(12, floatval($A['gap'] ?? 22));

$allowed_order_by = array('date','title');
$order_by = in_array($order_by_attr, $allowed_order_by, true) ? $order_by_attr : 'date';
$order    = ($order_attr === 'asc') ? 'ASC' : 'DESC';
$posts_per_page = max(1, $posts_per_page);
$cat_ids = array_filter(array_map('intval', is_array($cat_attr) ? $cat_attr : array()));

/* Query + hydrate all categories for badges/filters */
if ($use_posts || empty($items)) {
  $qargs = array(
    'post_type'           => 'post',
    'post_status'         => 'publish',
    'posts_per_page'      => $posts_per_page,
    'orderby'             => $order_by,
    'order'               => $order,
    'ignore_sticky_posts' => true,
  );
  if (!empty($cat_ids)) {
    $qargs['tax_query'] = array(array(
      'taxonomy' => 'category','field'=>'term_id','terms'=>$cat_ids,
    ));
  }

  $loop = new WP_Query($qargs);
  $items = array();

  if ($loop->have_posts()) {
    while ($loop->have_posts()) {
      $loop->the_post();

      $p_title = cc_clean( get_the_title() );

      $terms = get_the_category();
      $primary = (is_array($terms) && $terms) ? $terms[0] : null;
      if (!$primary) {
        $pid = (int) get_option('default_category');
        $primary = $pid ? get_term($pid,'category') : null;
      }

      $badges = array();
      if (is_array($terms)) {
        foreach ($terms as $t) {
          if (is_wp_error($t)) continue;
          $badges[] = array(
            'slug'  => $t->slug,
            'label' => cc_clean( $t->name ),
            'url'   => get_term_link($t),
          );
        }
      }

      $thumb_id  = get_post_thumbnail_id();
      $image_src = $thumb_id ? wp_get_attachment_image_url($thumb_id,'large') : '';
      $image_alt = $thumb_id ? get_post_meta($thumb_id,'_wp_attachment_image_alt',true) : '';
      if ($image_alt==='') $image_alt = $p_title;

      $items[] = array(
        'title'         => $p_title,
        'category'      => ($primary && !is_wp_error($primary)) ? $primary->slug : 'uncategorized',
        'categoryLabel' => ($primary && !is_wp_error($primary)) ? cc_clean( $primary->name ) : 'Uncategorized',
        'categories'    => $badges,
        'author'        => cc_clean( get_the_author() ),
        'date'          => get_post_time('c'),
        'url'           => get_permalink(),
        'snippet'       => cc_clean( wp_strip_all_tags(get_the_excerpt()) ),
        'image'         => array('src'=>$image_src ?: '','alt'=>$image_alt ?: ''),
      );
    }
    wp_reset_postdata();
  }
}

/* Build filter set from ALL categories */
$category_labels = array();
foreach ($items as $it) {
  if (!empty($it['category'])) {
    $category_labels[$it['category']] = $it['categoryLabel'] ?: ucwords(str_replace('-',' ',$it['category']));
  }
  if (!empty($it['categories'])) {
    foreach ($it['categories'] as $c) {
      $s = $c['slug'] ?? ''; if ($s==='') continue;
      $l = $c['label'] ?? ucwords(str_replace('-',' ',$s));
      $category_labels[$s] = $l;
    }
  }
}
if ($category_labels) {
  uasort($category_labels, fn($a,$b)=>strcasecmp($a,$b));
}

$sec_id  = $attributes['anchor'] ?? ('ccard-' . wp_generate_password(6,false,false));
$tpl_id  = $sec_id . '-tpl';
$data_id = $sec_id . '-data';
$cfg_id  = $sec_id . '-cfg';

$payload = array_map(function($r){
  $cats = array();
  if (!empty($r['categories'])) {
    foreach ($r['categories'] as $c) {
      $cats[] = array(
        'slug'  => cc_str($c,'slug',''),
        'label' => cc_str($c,'label',''),
        'url'   => cc_str($c,'url',''),
      );
    }
  }
  return array(
    'title'         => cc_clean(cc_str($r,'title','')),
    'category'      => cc_str($r,'category',''),
    'categoryLabel' => cc_clean(cc_str($r,'categoryLabel','')),
    'categories'    => $cats,
    'author'        => cc_clean(cc_str($r,'author','')),
    'date'          => cc_str($r,'date',''),
    'url'           => cc_str($r,'url','#'),
    'snippet'       => cc_clean(cc_str($r,'snippet','')),
    'image'         => array(
      'src' => cc_str(cc_val($r,'image',[]),'src',''),
      'alt' => cc_clean(cc_str(cc_val($r,'image',[]),'alt',''))
    )
  );
}, $items);

$config = array(
  'pageSize' => max(1,$pageSize),
  'sort'     => in_array($sort,['newest','oldest','title-az','title-za'],true)?$sort:'newest'
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
    <div class="filters" role="tablist" aria-label="Filter articles">
      <button class="filter" data-filter="all" aria-pressed="true">All</button>
      <?php foreach($category_labels as $cat_slug => $cat_label): ?>
        <button class="filter" data-filter="<?php echo esc_attr($cat_slug); ?>"><?php echo esc_html($cat_label); ?></button>
      <?php endforeach; ?>
    </div>
    <div class="sort">
      <label for="<?php echo esc_attr($sec_id); ?>-sort">Sort:</label>
      <select id="<?php echo esc_attr($sec_id); ?>-sort" class="sort-select" aria-label="Sort articles">
        <option value="newest" <?php selected($config['sort'],'newest'); ?>>Newest</option>
        <option value="oldest" <?php selected($config['sort'],'oldest'); ?>>Oldest</option>
        <option value="title-az" <?php selected($config['sort'],'title-az'); ?>>Title A - Z</option>
        <option value="title-za" <?php selected($config['sort'],'title-za'); ?>>Title Z - A</option>
      </select>
    </div>
  </div>

  <div class="grid_category" aria-live="polite"></div>
  <div class="pagination" aria-label="Pagination"></div>

  <!-- Template: supports either <img> or CSS background -->
  <template id="<?php echo esc_attr($tpl_id); ?>">
    <article class="card">
      <a class="media" href="#" aria-label="">
        <span class="media-bg" aria-hidden="true"></span>
        <div class="badges"></div>
        <!-- If you prefer <img>, we inject it too; CSS above keeps it under badges -->
        <img alt="" class="card-img-top" loading="lazy" decoding="async" />
      </a>
      <div class="content">
        <h3 class="title"></h3>
        <div class="meta">
          <div class="avatar" aria-hidden="true"></div>
          <div class="byline"><span class="author"></span></div>
        </div>
        <p class="snippet"></p>
        <div class="footer">
          <span class="date">&bull; <time></time></span>
          <a class="cta" href="#">Read More</a>
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

      function fmtDate(d){ try{return new Date(d).toLocaleDateString(undefined,{year:'numeric',month:'long',day:'numeric'});}catch(e){return d||'';} }

      function buildFilters(root, items){
        const holder = root.querySelector('.filters'); if(!holder) return;
        const existing = new Set(Array.from(holder.querySelectorAll('.filter')).map(b=>b.dataset.filter));
        const labels = new Map();
        items.forEach(item=>{
          if ((item.category||'').trim()){ const s=item.category.trim(); const l=item.categoryLabel||s.replace(/-/g,' ').replace(/\b\w/g,m=>m.toUpperCase()); labels.set(s,l); }
          if (Array.isArray(item.categories)){ item.categories.forEach(c=>{ const s=(c.slug||'').trim(); if(!s) return; const l=c.label||s.replace(/-/g,' ').replace(/\b\w/g,m=>m.toUpperCase()); labels.set(s,l); }); }
        });
        labels.forEach((l,s)=>{ if(existing.has(s)) return; const b=document.createElement('button'); b.className='filter'; b.dataset.filter=s; b.textContent=l; holder.appendChild(b); });
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
        const state = { filter:'all', sort:cfg.sort||'newest', page:1, pageSize: Math.max(1, cfg.pageSize||6) };

        const grid = root.querySelector('.grid_category');
        const pagination = root.querySelector('.pagination');
        const tpl = root.querySelector('template');

        buildFilters(root, data);

        root.addEventListener('click', e=>{
          const f = e.target.closest('.filters .filter'); if(!f) return;
          root.querySelectorAll('.filters .filter').forEach(x=>x.setAttribute('aria-pressed','false'));
          f.setAttribute('aria-pressed','true');
          state.filter = f.dataset.filter; state.page = 1; render();
        });

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
            case 'title-az': rows.sort((a,b)=> (a.title||'').localeCompare(b.title||'')); break;
            case 'title-za': rows.sort((a,b)=> (b.title||'').localeCompare(a.title||'')); break;
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

          // Set both; CSS ensures either works and stays under badges
          if (mediaBg) mediaBg.style.backgroundImage = img ? `url("${img}")` : 'none';
          if (imgEl) { imgEl.src = img || ''; imgEl.alt = alt; }

          media.href = row.url || '#';
          media.setAttribute('aria-label', row.title || '');

          // badges
          badgesWrap.innerHTML = '';
          const badges = Array.isArray(row.categories) ? row.categories : [];
          if (badges.length){
            badges.forEach(b=>{
              const label = (b.label||b.slug||'').trim(); if(!label) return;
              const el = document.createElement(b.url ? 'a' : 'span');
              el.className = 'badge'; el.textContent = label;
              if (b.url) el.href = b.url;
              badgesWrap.appendChild(el);
            });
          } else {
            const single = (row.categoryLabel||row.category||'').trim();
            if (single){ const s=document.createElement('span'); s.className='badge'; s.textContent=single; badgesWrap.appendChild(s); }
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
          const totalPages = Math.max(1, Math.ceil(rows.length / state.pageSize));
          state.page = Math.min(state.page, totalPages);

          grid.innerHTML = '';
          const start = (state.page - 1) * state.pageSize;
          rows.slice(start, start + state.pageSize).forEach(r=> grid.appendChild(buildCard(r)));
          renderPagination(totalPages);
        }

        function renderPagination(totalPages){
          const mkBtn=(label,page,active=false,disabled=false)=>{const el=document.createElement('button'); el.className='page-btn'+(active?' active':''); el.textContent=label; el.disabled=disabled; el.addEventListener('click',()=>{state.page=page; render();}); return el;};
          const mkGhost=(t='...')=>{const s=document.createElement('span'); s.className='page-ghost'; s.textContent=t; return s;};
          pagination.innerHTML=''; pagination.appendChild(mkBtn('Prev', Math.max(1,state.page-1), false, state.page===1));
          const windowSize=5; const start=Math.max(1, state.page-Math.floor(windowSize/2)); const end=Math.min(totalPages, start+windowSize-1); const s=Math.max(1, Math.min(start, end-windowSize+1));
          if(s>1){ pagination.appendChild(mkBtn('1',1,state.page===1)); if(s>2) pagination.appendChild(mkGhost()); }
          for(let p=s;p<=end;p++){ pagination.appendChild(mkBtn(String(p),p,p===state.page)); }
          if(end<totalPages){ if(end<totalPages-1) pagination.appendChild(mkGhost()); pagination.appendChild(mkBtn(String(totalPages), totalPages, state.page===totalPages)); }
          pagination.appendChild(mkBtn('Next', Math.min(totalPages, state.page+1), false, state.page===totalPages));
          const goto=document.createElement('span'); goto.className='goto-wrap'; const inp=document.createElement('input'); inp.type='number'; inp.min='1'; inp.max=String(totalPages); inp.placeholder='e.g. 2'; inp.addEventListener('change', ()=>{ const v=Math.min(totalPages, Math.max(1, Number(inp.value||1))); state.page=v; render(); }); goto.append('Go to:', inp); pagination.appendChild(goto);
        }

        render();
      }

      document.addEventListener('DOMContentLoaded', function(){
        document.querySelectorAll('.ccard').forEach(bindBlock);
      });
    })();
  </script>
</section>
