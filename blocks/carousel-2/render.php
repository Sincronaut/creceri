<?php
/**
 * Server render template for child/carousel-2.
 *
 * @var array  $attributes Block attributes.
 * @var string $content    Saved block content (unused).
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

$title          = ! empty( $attributes['title'] ) ? $attributes['title'] : '';
$intro          = ! empty( $attributes['intro'] ) ? $attributes['intro'] : '';
$items          = isset( $attributes['items'] ) && is_array( $attributes['items'] ) ? array_values( $attributes['items'] ) : array();

$starting_index = isset( $attributes['dataIndex'] ) ? max( 0, (int) $attributes['dataIndex'] ) : 0;
$show_dots      = isset( $attributes['showIndicators'] ) ? (bool) $attributes['showIndicators'] : true;
$read_label     = ! empty( $attributes['readLinkText'] ) ? $attributes['readLinkText'] : __( 'Learn more', 'vite-ttf-child-creceri' );

/** New options **/
$content_align  = isset( $attributes['contentAlign'] ) && in_array( $attributes['contentAlign'], array( 'left', 'center', 'right' ), true )
  ? $attributes['contentAlign'] : 'center';

$show_arrows    = array_key_exists( 'showArrows', $attributes ) ? (bool) $attributes['showArrows'] : true;
$show_read_link = array_key_exists( 'showReadLink', $attributes ) ? (bool) $attributes['showReadLink'] : true;

/** Keep items that at least provide a title or descriptive text. */
$items = array_filter(
  $items,
  function ( $item ) {
    return ! empty( $item['title'] ) || ! empty( $item['text'] );
  }
);

$items      = array_values( $items );
$item_count = count( $items );

if ( 0 === $item_count ) {
  return;
}

$base_key = ! empty( $attributes['dataKey'] ) ? sanitize_key( $attributes['dataKey'] ) : 'carousel';
$instance = wp_unique_id( "{$base_key}-" );

$wrap_id  = "{$instance}-wrap";
$track_id = "{$instance}-track";
$title_id = "{$instance}-title";
$intro_id = "{$instance}-intro";

/** Alignment class for the section */
$align_class = 'trends--align-' . $content_align;
?>
<section class="trends__section <?php echo esc_attr( $align_class ); ?>" id="<?php echo esc_attr( $align_class ); ?>">
  
<div class="trends__container">
    <?php if ( $title ) : ?>
      <h2 id="<?php echo esc_attr( $title_id ); ?>" class="trends__title"><?php echo esc_html( $title ); ?></h2>
    <?php endif; ?>

    <?php if ( $intro ) : ?>
      <p id="<?php echo esc_attr( $intro_id ); ?>" class="trends__subtitle"><?php echo wp_kses_post( $intro ); ?></p>
    <?php endif; ?>

    <div
      id="<?php echo esc_attr( $wrap_id ); ?>"
      class="trends__carouselwrap"
      role="region"
      <?php
      if ( $title ) {
        echo ' aria-labelledby="' . esc_attr( $title_id ) . '"';
      } elseif ( $intro ) {
        echo ' aria-describedby="' . esc_attr( $intro_id ) . '"';
      } else {
        echo ' aria-label="' . esc_attr__( 'Carousel', 'vite-ttf-child-creceri' ) . '"';
      }
      ?>
    >
      <?php if ( $show_arrows ) : ?>
        <button class="carousel__arrow carousel__arrow--prev" type="button" aria-label="<?php esc_attr_e( 'Previous slide', 'vite-ttf-child-creceri' ); ?>">
          <span aria-hidden="true">&larr;</span>
        </button>
      <?php endif; ?>

      <div id="<?php echo esc_attr( $track_id ); ?>" class="trends__carousel" data-track="true">
        <?php foreach ( $items as $index => $item ) : ?>
          <?php
          $item_title = ! empty( $item['title'] ) ? $item['title'] : '';
          $body_text  = ! empty( $item['text'] ) ? $item['text'] : '';
          $link       = ! empty( $item['url'] ) ? $item['url'] : '';
          $is_active  = ! empty( $item['active'] );
          $bg_color   = ! empty( $item['bgColor'] ) ? sanitize_text_field( $item['bgColor'] ) : '';
          $image      = isset( $item['image'] ) && is_array( $item['image'] ) ? $item['image'] : array();
          ?>
          <article class="featurecard<?php echo $is_active ? ' is-active' : ''; ?>"
            <?php
            if ( $bg_color ) {
              echo ' style="background-color:' . esc_attr( $bg_color ) . ';"';
            }
            ?>
          >
            <?php if ( $item_title ) : ?>
              <h3 class="featurecard__title"><?php echo esc_html( $item_title ); ?></h3>
            <?php endif; ?>

            <?php if ( ! empty( $image['src'] ) ) : ?>
              <figure class="featurecard__figure">
                <img
                  src="<?php echo esc_url( $image['src'] ); ?>"
                  <?php
                  if ( ! empty( $image['alt'] ) ) {
                    echo ' alt="' . esc_attr( $image['alt'] ) . '"';
                  } else {
                    echo ' alt=""';
                  }
                  if ( ! empty( $image['loading'] ) ) {
                    echo ' loading="' . esc_attr( $image['loading'] ) . '"';
                  }
                  if ( ! empty( $image['decoding'] ) ) {
                    echo ' decoding="' . esc_attr( $image['decoding'] ) . '"';
                  }
                  ?>
                />
              </figure>
            <?php endif; ?>

            <?php if ( $body_text ) : ?>
              <p class="featurecard__text"><?php echo wp_kses_post( $body_text ); ?></p>
            <?php endif; ?>

            <?php if ( $show_read_link && $link ) : ?>
              <a class="featurecard__link" href="<?php echo esc_url( $link ); ?>">
                <?php echo esc_html( $read_label ); ?>
              </a>
            <?php endif; ?>
          </article>
        <?php endforeach; ?>
      </div>

      <?php if ( $show_arrows ) : ?>
        <button class="carousel__arrow carousel__arrow--next" type="button" aria-label="<?php esc_attr_e( 'Next slide', 'vite-ttf-child-creceri' ); ?>">
          <span aria-hidden="true">&rarr;</span>
        </button>
      <?php endif; ?>

      <?php if ( $show_dots && $item_count > 1 ) : ?>
        <div class="carousel__dots" role="tablist" aria-label="<?php esc_attr_e( 'Slide indicators', 'vite-ttf-child-creceri' ); ?>">
          <?php foreach ( $items as $index => $item ) : ?>
            <button
              class="dot<?php echo 0 === $index ? ' is-active' : ''; ?>"
              type="button"
              data-dot="<?php echo esc_attr( $index ); ?>"
              aria-label="<?php printf( esc_attr__( 'Slide %d', 'vite-ttf-child-creceri' ), $index + 1 ); ?>"
            ></button>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>
</section>
<script>
(() => {
  const wrap = document.getElementById('<?php echo esc_js( $wrap_id ); ?>');
  if (!wrap) return;

  const track = wrap.querySelector('[data-track]');
  if (!track) return;

  const prev = wrap.querySelector('.carousel__arrow--prev');
  const next = wrap.querySelector('.carousel__arrow--next');
  const dots = Array.from(wrap.querySelectorAll('[data-dot]'));

  const getStep = () => {
    const first = track.firstElementChild;
    if (!first) return track.clientWidth;
    const styles = window.getComputedStyle(track);
    const gap = parseFloat(styles.columnGap || styles.gap || 16);
    return first.getBoundingClientRect().width + gap;
  };

  const setActiveDot = (index) => {
    dots.forEach((dot, idx) => {
      dot.classList.toggle('is-active', idx === index);
    });
  };

  const updateDots = () => {
    if (!dots.length) return;
    const step = getStep();
    const page = Math.round(track.scrollLeft / step);
    setActiveDot(Math.min(dots.length - 1, Math.max(0, page)));
  };

  const scrollByStep = (direction) => {
    track.scrollBy({ left: direction * getStep(), behavior: 'smooth' });
  };

  if (prev) prev.addEventListener('click', () => scrollByStep(-1));
  if (next) next.addEventListener('click', () => scrollByStep(1));

  track.addEventListener('scroll', () => {
    window.requestAnimationFrame(updateDots);
  });

  dots.forEach((dot, index) => {
    dot.addEventListener('click', () => {
      track.scrollTo({ left: index * getStep(), behavior: 'smooth' });
    });
  });

  const initialIndex = <?php echo (int) min( max( 0, $starting_index ), max( 0, $item_count - 1 ) ); ?>;
  if (initialIndex > 0) {
    window.requestAnimationFrame(() => {
      track.scrollTo({ left: initialIndex * getStep(), behavior: 'auto' });
      setActiveDot(initialIndex);
    });
  } else {
    updateDots();
  }
})();
</script>
