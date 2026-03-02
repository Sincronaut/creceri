<?php
/**
 * Server-side render for child/card-block.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$attributes = is_array( $attributes ?? null ) ? $attributes : array();

$attrs = wp_parse_args(
	$attributes,
	array(
		'title' => '',
		'intro' => '',
		'items' => array(),
	)
);

$items = is_array( $attrs['items'] ) ? $attrs['items'] : array();

$wrapper_attrs = get_block_wrapper_attributes(
	array(
		'class' => 'what-we-stand-for',
	)
);

$background_style = '';
if ( ! empty( $attrs['background'] ) ) {
  if ( 'on' === strtolower( $attrs['background'] ) ) {
    $background_style = 'background:linear-gradient(180deg,#E9B796 0%,#FFFFFF 80%) !important;';
  } else {
    $background_style = $attrs['background'];
  }
}

?>
<section <?php echo $wrapper_attrs; ?> id="card-section" <?php echo $background_style ? 'style="' . esc_attr( $background_style ) . '"' : ''; ?>>
	<div class="container">
		<?php if ( ! empty( $attrs['title'] ) ) : ?>
			<h2><?php echo esc_html( $attrs['title'] ); ?></h2>
		<?php endif; ?>

		<?php if ( ! empty( $attrs['intro'] ) ) : ?>
			<p class="subtitle"><?php echo wp_kses_post( $attrs['intro'] ); ?></p>
		<?php endif; ?>

		<div class="values">
			<?php foreach ( $items as $item ) :
				$item       = is_array( $item ) ? $item : array();
				$title      = isset( $item['title'] ) ? $item['title'] : '';
				$text       = isset( $item['text'] ) ? $item['text'] : '';
				$url        = isset( $item['url'] ) ? $item['url'] : '';
				$active     = ! empty( $item['active'] );
				$bg_color   = isset( $item['bgColor'] ) ? sanitize_html_class( $item['bgColor'] ) : '';
				$image      = isset( $item['image'] ) && is_array( $item['image'] ) ? $item['image'] : array();
				$image_src  = isset( $image['src'] ) ? $image['src'] : '';
				$image_alt  = isset( $image['alt'] ) ? $image['alt'] : '';

				$card_classes = array( 'value-card' );
				if ( $active ) {
					$card_classes[] = 'is-active';
				}
				if ( $bg_color ) {
					$card_classes[] = 'bg-' . $bg_color;
				}
				?>
				<?php if ( ! empty( $url ) ) : ?>
					<a class="value-card-link" href="<?php echo esc_url( $url ); ?>">
				<?php endif; ?>
    				<div class="<?php echo esc_attr( implode( ' ', array_filter( $card_classes ) ) ); ?>">
    					
    
    					<?php if ( ! empty( $image_src ) ) : ?>
    						<img src="<?php echo esc_url( $image_src ); ?>" alt="<?php echo esc_attr( $image_alt ); ?>">
    					<?php endif; ?>
    
    					<?php if ( ! empty( $title ) ) : ?>
    						<h3 class="card_title"><?php echo esc_html( $title ); ?></h3>
    					<?php endif; ?>
    
    					<?php if ( ! empty( $text ) ) : ?>
    						<p class="card_desc"><?php echo esc_html( $text ); ?></p>
    					<?php endif; ?>
    
    				</div>
				<?php if ( ! empty( $url ) ) : ?>
					</a>
				<?php endif; ?>
			<?php endforeach; ?>
		</div>
	</div>
</section>
