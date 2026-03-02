<?php
/**
 * Server render template for child/cta-card.
 *
 * @var array $attributes Block attributes.
 * @var string $content  Saved block content (unused).
 */

defined('ABSPATH') || exit;

$title       = ! empty($attributes['title']) ? $attributes['title'] : '';
$intro       = ! empty($attributes['intro']) ? $attributes['intro'] : '';
$button_text = ! empty($attributes['buttonText']) ? $attributes['buttonText'] : '';
$button_url  = ! empty($attributes['buttonUrl']) ? $attributes['buttonUrl'] : '';
$gradient    = ! empty($attributes['gradient']) ? esc_attr($attributes['gradient']) : 'linear-gradient(90deg, #e5b19c 0%, #cde3ef 100%)';

if (!$title && !$intro && !$button_text) {
	return;
}
?>

<section class="cta-card" style="background: <?php echo $gradient; ?>;">
	<div class="cta-card__container">
		<?php if ($title) : ?>
			<h2 class="cta-card__title"><?php echo esc_html($title); ?></h2>
		<?php endif; ?>

		<?php if ($intro) : ?>
			<p class="cta-card__intro"><?php echo wp_kses_post($intro); ?></p>
		<?php endif; ?>

		<?php if ($button_text && $button_url) : ?>
			<div class="cta-card__actions">
				<a href="<?php echo esc_url($button_url); ?>" class="cta-card__button btn-pill">
					<?php echo esc_html($button_text); ?>
				</a>
			</div>
		<?php endif; ?>
	</div>
</section>
