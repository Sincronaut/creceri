<?php
/**
 * FAQ Accordion block.
 */
if (!defined('ABSPATH')) {
    exit;
}

$attrs = is_array($attributes ?? null) ? $attributes : array();
$title = isset($attrs['title']) ? (string)$attrs['title'] : 'Frequently Asked Questions';
$intro = isset($attrs['intro']) ? (string)$attrs['intro'] : '';
$items = isset($attrs['items']) && is_array($attrs['items']) ? array_values($attrs['items']) : array();
$anchor = isset($attrs['anchor']) ? (string)$attrs['anchor'] : '';
$align = isset($attrs['align']) ? (string)$attrs['align'] : '';
$class = 'faq';
$class .= $align ? ' align' . $align : '';
$class .= !empty($attrs['className']) ? ' ' . $attrs['className'] : '';

$allowed_html = array(
    'a' => array('href' => true, 'title' => true, 'target' => true, 'rel' => true),
    'br' => true,
    'em' => true,
    'strong' => true,
    'b' => true,
    'i' => true,
    'u' => true,
    'span' => array('class' => true),
    'p' => array(),
    'ul' => array(),
    'ol' => array(),
    'li' => array(),
);

/**
 * Build a slug from text.
 */
if (!function_exists('child_faq_slug')) {
    function child_faq_slug($text, $fallback = 'faq')
    {
        $slug = sanitize_title((string)$text);
        if ($slug === '') {
            $slug = $fallback . '-' . wp_rand(100, 999);
        }
        return $slug;
    }
}

$section_id = $anchor !== '' ? $anchor : child_faq_slug($title, 'faq-section');
?>
<section id="<?php echo esc_attr($section_id); ?>" class="<?php echo esc_attr($class); ?>">
	<?php if ($title !== ''): ?>
		<h2><?php echo esc_html($title); ?></h2>
	<?php
endif; ?>

	<?php if (!empty($items)): ?>
		<ul class="faq-list">
			<?php foreach ($items as $i => $item):
        $q = isset($item['question']) ? (string)$item['question'] : '';
        $a = isset($item['answer']) ? (string)$item['answer'] : '';
        $open = !empty($item['open']);
        $item_id = isset($item['id']) && $item['id'] !== '' ? (string)$item['id'] : child_faq_slug($q ?: 'question', 'faq-' . ($i + 1));
?>
				<li class="faq-item" id="<?php echo esc_attr($item_id); ?>">
					<details <?php echo $open ? 'open' : ''; ?>>
						<?php if ($q !== ''): ?>
							<summary>
								<?php echo esc_html($q); ?>
								<span class="faq-icon" aria-hidden="true">
									<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
								</span>
							</summary>
						<?php
        endif; ?>

						<?php if ($a !== ''): ?>
							<div class="answer-wrapper"><div class="answer"><?php echo wpautop(wp_kses($a, $allowed_html)); ?></div></div>
						<?php
        endif; ?>
					</details>
				</li>
			<?php
    endforeach; ?>
		</ul>
	<?php
endif; ?>
</section>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const allItems = [];

    document.querySelectorAll('.faq details').forEach((el) => {
        const summary = el.querySelector('summary');
        const content = el.querySelector('.answer-wrapper');

        let animation = null;
        let isClosing = false;
        let isExpanding = false;

        // expose a close function so sibling items can trigger it
        const item = { shrink };
        allItems.push(item);

        summary.addEventListener('click', (e) => {
            e.preventDefault();
            el.style.overflow = 'hidden';
            if (isClosing || !el.open) {
                // close all other open panels first
                allItems.forEach((other) => { if (other !== item) other.shrink(); });
                openPanel();
            } else if (isExpanding || el.open) {
                shrink();
            }
        });

        function shrink() {
            if (!el.open) return;
            isClosing = true;
            const startHeight = `${el.offsetHeight}px`;
            const endHeight = `${summary.offsetHeight}px`;

            if (content) {
                content.style.transition = 'opacity 160ms ease, transform 160ms ease';
                content.style.opacity = '0';
                content.style.transform = 'translateY(-6px)';
            }

            if (animation) animation.cancel();

            el.style.overflow = 'hidden';
            animation = el.animate(
                { height: [startHeight, endHeight] },
                { duration: 200, easing: 'ease-out' }
            );

            animation.onfinish = () => onAnimationFinish(false);
            animation.oncancel = () => (isClosing = false);
        }

        function openPanel() {
            el.style.height = `${el.offsetHeight}px`;
            el.open = true;

            if (content) {
                content.style.transition = 'opacity 320ms ease, transform 320ms ease';
                content.style.opacity = '0';
                content.style.transform = 'translateY(-6px)';
            }

            window.requestAnimationFrame(() => {
                isExpanding = true;
                const startHeight = `${el.offsetHeight}px`;
                const endHeight = `${el.offsetHeight + content.offsetHeight}px`;

                if (animation) animation.cancel();

                animation = el.animate(
                    { height: [startHeight, endHeight] },
                    { duration: 320, easing: 'ease-out' }
                );

                // Start fade in immediately
                if (content) {
                   setTimeout(() => {
                        content.style.opacity = '1';
                        content.style.transform = 'translateY(0)';
                   }, 10);
                }

                animation.onfinish = () => onAnimationFinish(true);
                animation.oncancel = () => (isExpanding = false);
            });
        }

        function onAnimationFinish(isOpen) {
            el.open = isOpen;
            animation = null;
            isClosing = false;
            isExpanding = false;
            el.style.height = el.style.overflow = '';
            if (!isOpen && content) {
                content.style.transition = '';
                content.style.opacity = '';
                content.style.transform = '';
            }
        }
    });
});
</script>
