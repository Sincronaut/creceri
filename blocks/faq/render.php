<?php
/**
 * FAQ Accordion block.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$attrs     = is_array( $attributes ?? null ) ? $attributes : array();
$title     = isset( $attrs['title'] ) ? (string) $attrs['title'] : 'Frequently Asked Questions';
$intro     = isset( $attrs['intro'] ) ? (string) $attrs['intro'] : '';
$items     = isset( $attrs['items'] ) && is_array( $attrs['items'] ) ? array_values( $attrs['items'] ) : array();
$anchor    = isset( $attrs['anchor'] ) ? (string) $attrs['anchor'] : '';
$align     = isset( $attrs['align'] ) ? (string) $attrs['align'] : '';
$class     = 'faq';
$class    .= $align ? ' align' . $align : '';
$class    .= ! empty( $attrs['className'] ) ? ' ' . $attrs['className'] : '';

$allowed_html = array(
	'a'     => array( 'href' => true, 'title' => true, 'target' => true, 'rel' => true ),
	'br'    => true,
	'em'    => true,
	'strong'=> true,
	'b'     => true,
	'i'     => true,
	'u'     => true,
	'span'  => array( 'class' => true ),
	'p'     => array(),
	'ul'    => array(),
	'ol'    => array(),
	'li'    => array(),
);

/**
 * Build a slug from text.
 */
if ( ! function_exists( 'child_faq_slug' ) ) {
	function child_faq_slug( $text, $fallback = 'faq' ) {
		$slug = sanitize_title( (string) $text );
		if ( $slug === '' ) {
			$slug = $fallback . '-' . wp_rand( 100, 999 );
		}
		return $slug;
	}
}

$section_id = $anchor !== '' ? $anchor : child_faq_slug( $title, 'faq-section' );
?>
<section id="<?php echo esc_attr( $section_id ); ?>" class="<?php echo esc_attr( $class ); ?>">
	<?php if ( $title !== '' ) : ?>
		<h2><?php echo esc_html( $title ); ?></h2>
	<?php endif; ?>

	<?php if ( ! empty( $items ) ) : ?>
		<ul class="faq-list">
			<?php foreach ( $items as $i => $item ) :
				$q   = isset( $item['question'] ) ? (string) $item['question'] : '';
				$a   = isset( $item['answer'] ) ? (string) $item['answer'] : '';
				$open = ! empty( $item['open'] );
				$item_id = isset( $item['id'] ) && $item['id'] !== '' ? (string) $item['id'] : child_faq_slug( $q ?: 'question', 'faq-' . ( $i + 1 ) );
				?>
				<li class="faq-item" id="<?php echo esc_attr( $item_id ); ?>">
					<details <?php echo $open ? 'open' : ''; ?>>
						<?php if ( $q !== '' ) : ?>
							<summary><?php echo esc_html( $q ); ?></summary>
						<?php endif; ?>

						<?php if ( $a !== '' ) : ?>
							<div class="answer"><?php echo wpautop( wp_kses( $a, $allowed_html ) ); ?></div>
						<?php endif; ?>
					</details>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>
</section>
