<?php
/**
 * Block: Card Blocks 2 (Guides Chooser)
 * Path: your-child-theme/blocks/card-blocks-2/render.php
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

/* Helpers */
if ( ! function_exists( 'cb2_allowed_html' ) ) {
  function cb2_allowed_html() {
    return array(
      'a'      => array( 'href' => true, 'title' => true, 'target' => true, 'rel' => true, 'class' => true ),
      'br'     => array(),
      'strong' => array(),
      'b'      => array(),
      'em'     => array(),
      'i'      => array(),
      'span'   => array( 'class' => true, 'id' => true ),
    );
  }
}

/* Attributes */
$attrs        = is_array( $attributes ?? null ) ? $attributes : array();
$className    = isset( $attrs['className'] ) ? (string) $attrs['className'] : '';

$sectionId    = ! empty( $attrs['sectionId'] ) ? $attrs['sectionId'] : 'guides-chooser';
$titleId      = ! empty( $attrs['titleId'] )   ? $attrs['titleId']   : 'gc-title';
$subtitleId   = ! empty( $attrs['subtitleId']) ? $attrs['subtitleId'] : 'gc-subtitle';

$title        = isset( $attrs['title'] ) ? $attrs['title'] :'Your Go-To Guides for CMS and Web Development Platforms';
$lede         = isset( $attrs['lede'] ) ? $attrs['lede'] :
  'No two businesses are the same. Pick the path that matches your budget, timeline, and growth plans.';
$lineStatus   = isset( $attrs['lineStatus'] ) ? $attrs['lineStatus'] : 'Off';

/**
 * items: [
 *  {
 *    "type": "phase" | "connector",
 *    "title": "CMS Development",
 *    "label": "",
 *    "img":   {"src":"", "alt":"", "width":96, "height":96},
 *    "desc":  ["Paragraph or bullet strings..."],
 *    "cta":   {"text":"Explore...", "url":"#"}
 *  },
 *  ...
 * ]
 */
$items = is_array( $attrs['items'] ?? null ) ? $attrs['items'] : array(
  array(
    'type'  => 'phase',
    'title' => 'CMS Development',
    'img'   => array(
      'src'    => 'https://creceri.com/wp-content/uploads/2025/10/b5fa370a6aa3d1c463e4ef7a57476ae7d7a78de1.png',
      'alt'    => 'CMS Development icon',
      'width'  => 96,
      'height' => 96,
    ),
    'desc' => array(
      'A CMS (like WordPress, Shopify, or Magento) helps you build and manage a website without heavy coding.',
      '<span id="gc-card_strong">Best for:</span> Small businesses, startups, bloggers, or online shops.',
      '<span id="gc-card_strong">Why use it:</span> Quick setup, lower cost, easy updates.'
    ),
    'cta'  => array( 'text' => 'Explore CMS Development Guides', 'url' => '#' )
  ),
  array(
    'type'  => 'phase',
    'title' => 'Custom Web Development',
    'img'   => array(
      'src'    => 'https://creceri.com/wp-content/uploads/2025/10/6bcdde0a8451b1a01309d44738426fb4641f8edb.png',
      'alt'    => 'Custom Web Development icon',
      'width'  => 96,
      'height' => 96,
    ),
    'desc' => array(
      'Custom development means building your site from scratch to fit your exact needs.',
      '<span id="gc-card_strong">Best for:</span> Enterprises, large stores, or brands needing unique features.',
      '<span id="gc-card_strong">Why use it:</span> More control, full flexibility, grows with your business.'
    ),
    'cta'  => array( 'text' => 'Explore Custom Development Guides', 'url' => '#' )
  ),
);

$khtml = cb2_allowed_html();

$wrapper_classes = trim( 'guides-chooser card-blocks-2 ' . ( $lineStatus === 'On' ? 'has-line ' : '' ) . $className );
?>
<section class="<?php echo esc_attr( $wrapper_classes ); ?>"
         id="<?php echo esc_attr( $sectionId ); ?>"
         aria-labelledby="<?php echo esc_attr( $titleId ); ?>">
  <div class="gc-inner">
    <?php if ( $title ) : ?>
      <h2 class="gc-title" id="<?php echo esc_attr( $titleId ); ?>">
        <?php echo esc_html( $title ); ?>
      </h2>
    <?php endif; ?>

    <?php if ( ! empty( $lede ) ) : ?>
      <p class="gc-subtitle" id="<?php echo esc_attr( $subtitleId ); ?>">
        <?php echo wp_kses( $lede, $khtml ); ?>
      </p>
    <?php endif; ?>

    <div class="gc-grid" role="list">
      <?php foreach ( $items as $index => $item ) :
        $type   = isset( $item['type'] ) ? $item['type'] : 'phase';
        if ( $type !== 'phase' ) { // this layout renders phases as cards; connectors are ignored for this UI
          continue;
        }

        $img    = is_array( $item['img'] ?? null ) ? $item['img'] : array();
        $src    = isset( $img['src'] )    ? $img['src']    : '';
        $alt    = isset( $img['alt'] )    ? $img['alt']    : '';
        $w      = isset( $img['width'] )  ? (int) $img['width']  : 96;
        $h      = isset( $img['height'] ) ? (int) $img['height'] : 96;

        $title3 = isset( $item['title'] ) ? $item['title'] : '';
        $desc   = is_array( $item['desc'] ?? null ) ? $item['desc'] : array();

        $cta    = is_array( $item['cta'] ?? null ) ? $item['cta'] : array();
        $ctaTxt = isset( $cta['text'] ) ? $cta['text'] : '';
        $ctaUrl = isset( $cta['url'] )  ? $cta['url']  : '';

        // Added logic to handle direct label/url keys or fall back to cta
        $button     = ! empty( $item['label'] ) ? $item['label'] : $ctaTxt;
        $buttonlink = ! empty( $item['url'] )   ? $item['url']   : $ctaUrl;

        $card_id = 'gc-card-' . ( $index + 1 );
        ?>
        <article class="gc-card" id="<?php echo esc_attr( $card_id ); ?>" role="listitem">
          <header class="gc-card__header">
            <?php if ( $src ) : ?>
              <img class="gc-card__icon"
                   src="<?php echo esc_url( $src ); ?>"
                   alt="<?php echo esc_attr( $alt ); ?>"
                   width="<?php echo esc_attr( $w ); ?>"
                   height="<?php echo esc_attr( $h ); ?>">
            <?php endif; ?>

            <?php if ( $title3 ) : ?>
              <h3 class="gc-card__title"><?php echo esc_html( $title3 ); ?></h3>
            <?php endif; ?>
          </header>

          <div class="gc-card__body">
            <?php
              // First desc -> body copy; remaining -> lines
              if ( ! empty( $desc ) ) {
                $first = array_shift( $desc );
                echo '<p class="gc-card__copy">' . wp_kses( $first, $khtml ) . '</p>';
              }
              foreach ( $desc as $line ) {
                echo '<p class="gc-card__line">' . wp_kses( $line, $khtml ) . '</p>';
              }
            ?>
          </div>

            <?php if ( $button && $buttonlink ) : ?>
            <a class="gc-card__cta" href="<?php echo esc_url( $buttonlink ); ?>">
             <?php echo esc_html( $button ); ?>
            </a>
            <?php endif; ?>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>
