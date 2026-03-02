<?php
/**
 * Loader for Korean 404 Template
 * Manually sets up the HTML document structure and parses block content.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<?php wp_head(); ?>
</head>
<?php
// Load the raw block markup from the HTML template
$template_file = get_stylesheet_directory() . '/templates/ko-404.html';
if ( file_exists( $template_file ) ) {
	$raw_content = file_get_contents( $template_file );
	
	// Parse blocks (converts <!-- wp:... --> to HTML)
	$rendered_content = do_blocks( $raw_content );

	// Inject wp_footer() before the closing body tag
	ob_start();
	wp_footer();
	$footer_scripts = ob_get_clean();

	if ( false !== strpos( $rendered_content, '</body>' ) ) {
		$rendered_content = str_replace( '</body>', $footer_scripts . '</body>', $rendered_content );
	} else {
		// Fallback if no body tag found
		$rendered_content .= $footer_scripts;
	}

	echo $rendered_content;
} else {
	echo 'Template not found.';
}
?>
</html>
