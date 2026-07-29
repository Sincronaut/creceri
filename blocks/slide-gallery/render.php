<?php
/**
 * Slide Gallery block: keep original design, populate from posts.
 */
if (!defined('ABSPATH')) {
  exit;
}

$attrs = is_array($attributes ?? null) ? $attributes : array();
$section_id = !empty($attrs['sectionId']) ? $attrs['sectionId'] : 'sea-local-leagues-' . wp_generate_password(6, false, false);
$title = isset($attrs['title']) && $attrs['title'] !== '' ? $attrs['title'] : 'Beyond the Game: Southeast Asia Sports';
$subtitle = isset($attrs['content']) && $attrs['content'] !== '' ? $attrs['content'] : 'Discover the sports shaping culture, community, and competition across Southeast Asia.';
$posts_to_show = isset($attrs['postsToShow']) ? min(10, max(1, intval($attrs['postsToShow']))) : 6;

$exclude_ids = array();
$tag_ids = array();
if (is_singular()) {
  $exclude_ids[] = get_the_ID();
  $post_tags = wp_get_post_tags(get_the_ID(), array('fields' => 'ids'));
  if (!empty($post_tags)) {
    $tag_ids = $post_tags;
  }
}

$query_args = array(
  'post_type' => 'post',
  'post_status' => 'publish',
  'posts_per_page' => $posts_to_show,
  'ignore_sticky_posts' => true,
  'post__not_in' => $exclude_ids,
  'orderby' => 'date',
  'order' => 'DESC',
);

// Filter by the current post's tags so only genuinely related posts appear.
// Falls back to recent posts if the current post has no tags.
if (!empty($tag_ids)) {
  $query_args['tag__in'] = $tag_ids;
}

$query = new WP_Query($query_args);
$fallback_img = get_stylesheet_directory_uri() . '/assets/images/fallback-image.webp';
?>
<section class="sea-local-leagues" aria-labelledby="<?php echo esc_attr($section_id); ?>">
  <style>
    .sea-local-leagues {
      background:#f9fafb;
      padding:clamp(28px,4vw,48px) 0 48px;
      margin:0 auto;
      font-family:system-ui,-apple-system,BlinkMacSystemFont,"Inter","Segoe UI",sans-serif;
      color:#111827;
    }

    /* Header */
    .sea-local-leagues__header {
      padding-left: max(20px, calc((100% - 1280px) / 2));
      padding-right: max(20px, 4vw);
      margin:0 auto 24px;
    }
    .sea-local-leagues__title {
      margin:0 0 6px;
      font-weight:700;
      font-size:clamp(22px,2.4vw,36px);
      line-height:1.2;
      letter-spacing:-.01em;
    }
    .sea-local-leagues__subtitle {
      margin:0;
      font-size:clamp(13px,1.3vw,20px);
      font-weight: 400;
      color:#6b7280;
    }

    /* SCROLL TRACK (no visible scrollbar) */
    .sea-local-leagues__track {
      width:100%;
      overflow-x:auto;
      overflow-y:hidden;
      padding-bottom:10px;
      padding-left: max(20px, calc((100% - 1280px) / 2));
      padding-right: max(20px, calc((100% - 1280px) / 2));
      scrollbar-width:none;       /* Firefox */
      -ms-overflow-style:none;    /* IE/Edge */
    }
    .sea-local-leagues__track::-webkit-scrollbar {
      display:none;
    }
    /* Touch drag only on mobile/tablet */
    @media (max-width:1024px) {
      .sea-local-leagues__track {
        cursor:grab;
        user-select:none;
      }
      .sea-local-leagues__track.is-dragging {
        cursor:grabbing;
      }
    }

    .sea-local-leagues__row {
      display:flex;
      flex-wrap:nowrap;
      gap:20px;
      justify-content:flex-start;
    }

    /* Card: max ~5 visible on wide screens */
    .sea-local-leagues__card {
      position:relative;
      background:#ffffff;
      border-radius:18px;
      box-shadow:0 2px 0 rgba(15,23,42,.02),0 10px 24px rgba(15,23,42,.06);
      padding:14px 16px 16px;
      display:flex;
      flex-direction:column;
      gap:10px;
      flex:0 0 auto;
      width:calc((100vw - clamp(32px,8vw,128px) - 80px) / 5);
      min-width:220px;
      cursor:pointer;
      transition:box-shadow .18s ease, transform .18s ease;
    }
    .sea-local-leagues__card:hover {
      box-shadow:0 4px 0 rgba(15,23,42,.03),0 16px 36px rgba(15,23,42,.12);
      transform:translateY(-2px);
    }
    /* Stretched link pattern applied to CTA covers the whole card */
    .sea-local-leagues__cta::after {
      content: "";
      position:absolute;
      inset:0;
      z-index:10;
      border-radius:18px;
    }

    @media (max-width:1024px){
      .sea-local-leagues__card{
        flex:0 0 min(260px,70vw);
      }
    }

    /* Card internals */
    .sea-local-leagues__media {
      border-radius:14px;
      background:#e5e7eb;
      height:160px;
      width:100%;
      background-size:cover;         /* ensure featured image fills the frame */
      background-position:center;
      background-repeat:no-repeat;
      margin-bottom:6px;
    }
    .sea-local-leagues__sport {
      margin:0;
      font-size:24px;
      font-weight:600;
      color:#962E2A;
      position:relative;
      z-index:1;
    }
    .sea-local-leagues__desc {
      margin:0;
      font-weight:400;
      font-size:18px;
      line-height:1.5;
      color:#6b7280;
      position:relative;
      z-index:1;
    }
    .sea-local-leagues__cta {
      margin-top:auto;
      font-size:20px;
      font-weight:500;
      color:#962E2A;
      text-decoration:none;
      display:inline-flex;
      align-items:center;
      gap:6px;
      padding-top:6px;
      position:static;
    }
    .sea-local-leagues__cta-icon {
      font-size:13px;
      transform:translateY(1px);
    }
    .sea-local-leagues__cta:hover {
      text-decoration:underline;
    }

    /* Header layout: title+subtitle left, nav buttons right (desktop only) */
    .sea-local-leagues__header {
      display:flex;
      align-items:center;
      justify-content:space-between;
      gap:16px;
    }
    .sea-local-leagues__header-text {
      flex:1 1 auto;
    }
    .sea-local-leagues__nav-btns {
      display:none;   /* hidden on mobile — buttons unused there */
      gap:12px;
      flex-shrink:0;
      padding-right:0;
    }
    @media (min-width:1025px) {
      .sea-local-leagues__nav-btns {
        display:flex;
      }
    }
    .sea-local-leagues__nav-btn {
      width:36px;
      height:36px;
      border-radius:50%;
      border:2px solid #962E2A;
      background:#fff;
      color:#962E2A;
      cursor:pointer;
      display:inline-flex;
      align-items:center;
      justify-content:center;
      transition:background .15s ease, color .15s ease;
    }
    .sea-local-leagues__nav-btn:hover {
      background:#962E2A;
      color:#fff;
    }
    .sea-local-leagues__nav-btn:disabled {
      opacity:.35;
      cursor:default;
    }
  </style>

  <header class="sea-local-leagues__header">
    <div class="sea-local-leagues__header-text">
      <h2 id="<?php echo esc_attr($section_id); ?>" class="sea-local-leagues__title">
        <?php echo esc_html($title); ?>
      </h2>
      <p class="sea-local-leagues__subtitle">
        <?php echo esc_html($subtitle); ?>
      </p>
    </div>
    <div class="sea-local-leagues__nav-btns">
      <button class="sea-local-leagues__nav-btn sea-local-leagues__nav-prev" aria-label="Previous slides">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M15 18l-6-6 6-6"/></svg>
      </button>
      <button class="sea-local-leagues__nav-btn sea-local-leagues__nav-next" aria-label="Next slides">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18l6-6-6-6"/></svg>
      </button>
    </div>
  </header>

  <!-- Card row -->
  <div class="sea-local-leagues__track">
    <div class="sea-local-leagues__row">
      <?php
if ($query->have_posts()):
  while ($query->have_posts()):
    $query->the_post();
    $thumb = get_the_post_thumbnail_url(get_the_ID(), 'large');
    $title_p = get_the_title();
    $excerpt = get_the_excerpt();
?>
          <article class="sea-local-leagues__card">
            <div class="sea-local-leagues__media" style="background-image: url('<?php echo esc_url($thumb ? $thumb : $fallback_img); ?>');"></div>
            <h3 class="sea-local-leagues__sport"><?php echo esc_html($title_p); ?></h3>
            <p class="sea-local-leagues__desc">
              <?php echo esc_html(wp_trim_words($excerpt, 20, '…')); ?>
            </p>
            <a href="<?php the_permalink(); ?>" class="sea-local-leagues__cta">
             Read More <span class="sea-local-leagues__cta-icon">➜</span>
            </a>
          </article>
          <?php
  endwhile;
  wp_reset_postdata();
else:
?>
        <article class="sea-local-leagues__card">
          <div class="sea-local-leagues__media" style="background-image:url('<?php echo esc_url($fallback_img); ?>');"></div>
          <h3 class="sea-local-leagues__sport">No posts yet</h3>
          <p class="sea-local-leagues__desc">Publish some posts to see them here.</p>
        </article>
      <?php
endif; ?>
    </div>
  </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const track   = document.querySelector('.sea-local-leagues__track');
  const row     = document.querySelector('.sea-local-leagues__row');
  const btnPrev = document.querySelector('.sea-local-leagues__nav-prev');
  const btnNext = document.querySelector('.sea-local-leagues__nav-next');
  if (!track || !row) return;

  // Scroll by roughly one card width + gap
  const cardScrollAmount = () => {
    const card = row.querySelector('.sea-local-leagues__card');
    return card ? card.offsetWidth + 20 : 260;
  };

  // Desktop: prev/next button navigation
  btnPrev?.addEventListener('click', () => {
    track.scrollBy({ left: -cardScrollAmount(), behavior: 'smooth' });
  });
  btnNext?.addEventListener('click', () => {
    track.scrollBy({ left: cardScrollAmount(), behavior: 'smooth' });
  });

  // Update disabled state on buttons
  const updateBtns = () => {
    if (!btnPrev || !btnNext) return;
    btnPrev.disabled = track.scrollLeft <= 0;
    btnNext.disabled = track.scrollLeft + track.clientWidth >= track.scrollWidth - 2;
  };
  track.addEventListener('scroll', updateBtns, { passive: true });
  updateBtns();

  // Mobile/Tablet: touch swipe
  let touchStartX     = 0;
  let touchScrollLeft = 0;

  track.addEventListener('touchstart', (e) => {
    touchStartX     = e.touches[0].clientX;
    touchScrollLeft = track.scrollLeft;
    track.classList.add('is-dragging');
  }, { passive: true });

  track.addEventListener('touchmove', (e) => {
    const dx = touchStartX - e.touches[0].clientX;
    track.scrollLeft = touchScrollLeft + dx;
  }, { passive: true });

  track.addEventListener('touchend', () => {
    track.classList.remove('is-dragging');
  }, { passive: true });
});
</script>
