<?php
/**
 * Block: Card Slider (What's New)
 * Path: https://creceri.com/wp-content/themes/your-child-theme/blocks/card/render.php
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

/* Helpers */
if ( ! function_exists( 'wn_allowed_text_html' ) ) {
  function wn_allowed_text_html() {
    return array(
      'a'      => array( 'href' => true, 'title' => true, 'target' => true, 'rel' => true ),
      'br'     => array(),
      'strong' => array(),
      'b'      => array(),
      'em'     => array(),
      'i'      => array(),
      'span'   => array( 'class' => true ),
    );
  }
}

/* Attributes */
$attrs      = is_array( $attributes ?? null ) ? $attributes : array();
$className  = isset( $attrs['className'] ) ? (string) $attrs['className'] : '';

$title      = isset( $attrs['title'] )   ? $attrs['title']   : "What's New?";
$intro      = isset( $attrs['intro'] )   ? $attrs['intro']   : '';
$ctaText    = isset( $attrs['ctaText'] ) ? $attrs['ctaText'] : 'Browse All Updates';
$ctaUrl     = isset( $attrs['ctaUrl'] )  ? $attrs['ctaUrl']  : '#all-updates';

$titleId    = ! empty( $attrs['titleId'] ) ? $attrs['titleId'] : 'whats-new-title';
$listId     = ! empty( $attrs['listId']  ) ? $attrs['listId']  : 'wn-track';

$cards      = is_array( $attrs['cards'] ?? null ) ? $attrs['cards'] : array();
$khtml      = wn_allowed_text_html();

$latest_posts = get_posts(
  array(
    'numberposts' => 4,
    'post_status' => 'publish',
    'orderby'     => 'date',
    'order'       => 'DESC',
  )
);

if ( ! empty( $latest_posts ) && ! is_wp_error( $latest_posts ) ) {
  $cards = array();

  foreach ( $latest_posts as $post_obj ) {
    $thumb_id  = get_post_thumbnail_id( $post_obj );
    $thumb_src = $thumb_id ? get_the_post_thumbnail_url( $post_obj, 'medium_large' ) : '';
    $thumb_alt = $thumb_id ? get_post_meta( $thumb_id, '_wp_attachment_image_alt', true ) : '';
    $p_title   = wp_specialchars_decode( get_the_title( $post_obj ) );

    $cards[] = array(
      'title' => $p_title,
      'text'  => wp_trim_words( wp_strip_all_tags( get_the_excerpt( $post_obj ) ), 24 ),
      'url'   => get_permalink( $post_obj ),
      'image' => array(
        'src' => $thumb_src ? $thumb_src : '',
        'alt' => $thumb_alt ? $thumb_alt : $p_title,
      ),
    );
  }
}

$wrapper_classes = trim( 'whats-new ' . $className );
?>
<section class="<?php echo esc_attr( $wrapper_classes ); ?>" aria-labelledby="<?php echo esc_attr( $titleId ); ?>" id="whats_new" style="margin-top:20px;">
  <div class="wn-container"><br>
    <header class="wn-head">
      <div class="wn-intro">
        <?php if ( $title !== '' ) : ?>
          <h2 id="<?php echo esc_attr( $titleId ); ?>"><?php echo esc_html( $title ); ?></h2>
        <?php endif; ?>

        <?php if ( $intro !== '' ) : ?>
          <p class="card_intro"><?php echo wp_kses( $intro, $khtml ); ?></p>
        <?php endif; ?>
      </div>

      <?php if ( ! empty( $ctaText ) && ! empty( $ctaUrl ) ) : ?>
        <a class="btn btn-pill btn-pill-desk" href="<?php echo esc_url( $ctaUrl ); ?>">
          <?php echo esc_html( $ctaText ); ?>
        </a>
      <?php endif; ?>
    </header>

    <div class="wn-slider">
      <ul class="card-grid" id="<?php echo esc_attr( $listId ); ?>" role="list">
        <?php
        $i = 0;
        foreach ( $cards as $card ) :
          $i++;
          $li_id   = 'wn-slide-' . $i;

          $img     = is_array( $card['image'] ?? null ) ? $card['image'] : array();
          $img_src = isset( $img['src'] ) ? $img['src'] : '';
          $img_alt = isset( $img['alt'] ) ? $img['alt'] : '';

          $c_title = isset( $card['title'] ) ? $card['title'] : '';
          $c_text  = isset( $card['text'] )  ? $card['text']  : '';
          $c_link  = isset( $card['url'] )   ? $card['url']   : '';
          ?>
          <li id="<?php echo esc_attr( $li_id ); ?>">
            
            <article class="card" >
              <figure class="card-media">
                <?php if ( $img_src ) : ?>
                <a href="<?php echo esc_url( $c_link ); ?>" >
                  <img src="<?php echo esc_url( $img_src ); ?>" alt="<?php echo esc_attr( $img_alt ); ?>" />
                </a>
                <?php endif; ?>
              </figure>

              <div class="card-body">
                <?php if ( $c_title ) : ?> 
                  <a href="<?php echo esc_url( $c_link ); ?>" style="text-decoration:none !important;">
                    <?php if ( $c_link ) : ?>
                    <h3 class="card-title" >
                        <?php echo esc_html( $c_title ); ?>
                      <?php else : ?>
                        <?php echo esc_html( $c_title ); ?>
                      <?php endif; ?>
                    </h3>
                  </a>
                <?php endif; ?>

                <?php if ( $c_text ) : ?>
                  <a href="<?php echo esc_url( $c_link ); ?>" style="text-decoration:none !important;">
                    <p class="card-text" ><?php echo wp_kses( $c_text, $khtml ); ?></p>
                  </a>
                <?php endif; ?>
              </div>
            </article>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>

    <?php if ( ! empty( $ctaText ) && ! empty( $ctaUrl ) ) : ?>
      <div class="wn-cta">
        <a class="btn btn-pill btn-pill-mobile" href="<?php echo esc_url( $ctaUrl ); ?>">
          <?php echo esc_html( $ctaText ); ?>
        </a>
      </div>
    <?php endif; ?>
  </div>
</section>
