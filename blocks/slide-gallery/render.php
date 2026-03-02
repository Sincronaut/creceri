<?php
/**
 * Slide Gallery block: keep original design, populate from posts.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$attrs         = is_array( $attributes ?? null ) ? $attributes : array();
$section_id    = ! empty( $attrs['sectionId'] ) ? $attrs['sectionId'] : 'sea-local-leagues-' . wp_generate_password( 6, false, false );
$title         = isset( $attrs['title'] ) && $attrs['title'] !== '' ? $attrs['title'] : 'Beyond the Game: Southeast Asia Sports';
$subtitle      = isset( $attrs['content'] ) && $attrs['content'] !== '' ? $attrs['content'] : 'Discover the sports shaping culture, community, and competition across Southeast Asia.';
$posts_to_show = isset( $attrs['postsToShow'] ) ? max( 1, intval( $attrs['postsToShow'] ) ) : 6;

$exclude_ids = array();
if ( is_singular() ) {
	$exclude_ids[] = get_the_ID();
}

$query = new WP_Query(
	array(
		'post_type'           => 'post',
		'post_status'         => 'publish',
		'posts_per_page'      => $posts_to_show,
		'ignore_sticky_posts' => true,
		'post__not_in'        => $exclude_ids,
	)
);
?>
<section class="sea-local-leagues" aria-labelledby="<?php echo esc_attr( $section_id ); ?>">
 <section class="sea-local-leagues" aria-labelledby="sea-local-leagues-heading">
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
      padding-inline:clamp(16px,4vw,64px);
      max-width:1390px;
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

    /* DRAG TRACK (no scrollbar, drag to move) */
    .sea-local-leagues__track {
      width:100%;
      overflow:hidden;          /* hide scrollbars */
      padding-bottom:10px;
      cursor:grab;
      user-select:none;
    }
    .sea-local-leagues__track.is-dragging {
      cursor:grabbing;
    }

    .sea-local-leagues__row {
      display:flex;
      flex-wrap:nowrap;
      gap:20px;
      justify-content:flex-start;

      /*
       * START POSITION OF FIRST CARD
       * -------------------------------------------------
       * Change this value to move where the first card
       * appears on initial load.
       *
       * Examples:
       *   900px   → row starts closer to the middle
       *   1250px  → row pushed further to the right
       *   1600px  → row starts even further off to the right
       */
      --sea-leagues-offset:320px;

      transform:translateX(var(--sea-leagues-offset));
      transition:transform 0.18s ease-out;
    }
    .sea-local-leagues__row.is-dragging {
      transition:none;
    }

    /* Card: max ~5 visible on wide screens */
    .sea-local-leagues__card {
      background:#ffffff;
      border-radius:18px;
      box-shadow:0 2px 0 rgba(15,23,42,.02),0 10px 24px rgba(15,23,42,.06);
      padding:14px 16px 16px;
      display:flex;
      flex-direction:column;
      gap:10px;
      min-width:220px;
      flex:0 0 calc((100% - 4*20px)/5);   /* 5 cards + 4 gaps in view */
      user-select:none;
    }

    @media (max-width:1400px){
      /* on smaller screens, start closer to the left edge */
      .sea-local-leagues__row{
        --sea-leagues-offset:24px;       /* you can tweak this too if needed */
      }
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
      color:#111827;
    }
    .sea-local-leagues__desc {
      margin:0;
      font-weight:400;
      font-size:18px;
      line-height:1.5;
      color:#6b7280;
    }
    .sea-local-leagues__cta {
      margin-top:auto;
      font-size:20px;
      font-weight:500;
      color:#111827;
      text-decoration:none;
      display:inline-flex;
      align-items:center;
      gap:6px;
      padding-top:6px;
    }
    .sea-local-leagues__cta-icon {
      font-size:13px;
      transform:translateY(1px);
    }
    .sea-local-leagues__cta:hover {
      text-decoration:underline;
    }
  </style>

  <header class="sea-local-leagues__header">
    <h2 id="<?php echo esc_attr( $section_id ); ?>" class="sea-local-leagues__title">
      <?php echo esc_html( $title ); ?>
    </h2>
    <p class="sea-local-leagues__subtitle">
      <?php echo esc_html( $subtitle ); ?>
    </p>
  </header>

  <!-- Drag row: first card starts around --sea-leagues-offset from the left -->
  <div class="sea-local-leagues__track">
    <div class="sea-local-leagues__row">
      <?php
      if ( $query->have_posts() ) :
        while ( $query->have_posts() ) :
          $query->the_post();
          $thumb   = get_the_post_thumbnail_url( get_the_ID(), 'large' );
          $title_p = get_the_title();
          $excerpt = get_the_excerpt();
          ?>
          <article class="sea-local-leagues__card">
            <div class="sea-local-leagues__media" style="background-image: url('<?php echo esc_url( $thumb ? $thumb : 'https://via.placeholder.com/640x480?text=Featured+Image' ); ?>');"></div>
            <h3 class="sea-local-leagues__sport"><?php echo esc_html( $title_p ); ?></h3>
            <p class="sea-local-leagues__desc">
              <?php echo esc_html( wp_trim_words( $excerpt, 20, '…' ) ); ?>
            </p>
            <a href="<?php the_permalink(); ?>" class="sea-local-leagues__cta">
             Read More <span class="sea-local-leagues__cta-icon">➜</span>
            </a>
          </article>
          <?php
        endwhile;
        wp_reset_postdata();
      else :
        ?>
        <article class="sea-local-leagues__card">
          <div class="sea-local-leagues__media"></div>
          <h3 class="sea-local-leagues__sport">No posts yet</h3>
          <p class="sea-local-leagues__desc">Publish some posts to see them here.</p>
          <a href="#" class="sea-local-leagues__cta">Discover More <span class="sea-local-leagues__cta-icon">➜</span></a>
        </article>
      <?php endif; ?>
    </div>
  </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const track = document.querySelector('.sea-local-leagues__track');
  const row   = document.querySelector('.sea-local-leagues__row');
  if (!track || !row) return;

  let isDown = false;
  let startX;
  let scrollLeft;

  track.addEventListener('mousedown', (e) => {
    isDown = true;
    track.classList.add('is-dragging');
    startX = e.pageX - track.offsetLeft;
    scrollLeft = track.scrollLeft;
  });
  track.addEventListener('mouseleave', () => {
    isDown = false;
    track.classList.remove('is-dragging');
  });
  track.addEventListener('mouseup', () => {
    isDown = false;
    track.classList.remove('is-dragging');
  });
  track.addEventListener('mousemove', (e) => {
    if(!isDown) return;
    e.preventDefault();
    const x = e.pageX - track.offsetLeft;
    const walk = (x - startX) * 1.5;
    track.scrollLeft = scrollLeft - walk;
  });

  track.addEventListener('touchstart', (e) => {
    const t = e.touches[0];
    startDrag(t.clientX);
  }, { passive: true });

  track.addEventListener('touchmove', (e) => {
    const t = e.touches[0];
    moveDrag(t.clientX);
  }, { passive: true });

  window.addEventListener('touchend', endDrag);

  track.addEventListener('touchstart', (e) => {
    const t = e.touches[0];
    startDrag(t.clientX);
  }, { passive: true });

  track.addEventListener('touchmove', (e) => {
    const t = e.touches[0];
    moveDrag(t.clientX);
  }, { passive: true });

  window.addEventListener('touchend', endDrag);

  track.addEventListener('touchstart', (e) => {
    const t = e.touches[0];
    startDrag(t.clientX);
  }, { passive: true });

  track.addEventListener('touchmove', (e) => {
    const t = e.touches[0];
    moveDrag(t.clientX);
  }, { passive: true });

  window.addEventListener('touchend', endDrag);
});
</script>
