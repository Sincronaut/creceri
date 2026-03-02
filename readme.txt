== Twenty-Twenty-Five-Child ==

Contributors: 
Requires at least: 6.8
Tested up to: 6.8
Requires PHP: 5.7
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html


== Description ==

Twenty-Twenty-Five-Child


== Changelog ==

= 1.0.0 =
* Initial release


== Copyright ==

Twenty-Twenty-Five-Child WordPress Theme, (C) 2025 
Twenty-Twenty-Five-Child is distributed under the terms of the GNU GPL.

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.


Twenty-Twenty-Five-Child is a child theme of Twenty Twenty-Five (https://wordpress.org/themes/twentytwentyfive/), (C) the WordPress team, [GPLv2 or later](http://www.gnu.org/licenses/gpl-2.0.html)




This is for calling default font size

From your file (font sizes):

body → var(--wp--preset--font-size--body) → class .has-body-font-size

h-3 → var(--wp--preset--font-size--h-3) → class .has-h-3-font-size

h-2 → var(--wp--preset--font-size--h-2) → class .has-h-2-font-size

h-1 → var(--wp--preset--font-size--h-1) → class .has-h-1-font-size

<!-- Font size via utility class -->
<h2 class="has-h-2-font-size">Heading</h2>

<!-- Font size via CSS variable -->
<p style="font-size: var(--wp--preset--font-size--body);">
  Body text using the body size
</p>

<!-- Font family (after you add fontFamilies) -->
<div style="font-family: var(--wp--preset--font-family--inter);">
  Uses Inter font family
</div>

<!-- Combine with your existing snippet -->
<h2 class="wp-block-heading has-h-2-font-size has-text-align-center" style="margin-bottom:.25rem">
  <?php echo esc_html( $title ); ?>
</h2>


/* Map your own selectors to theme.json sizes */
.site-title { font-size: var(--wp--preset--font-size--h-1); }
.section-title { font-size: var(--wp--preset--font-size--h-2); }
.card h3 { font-size: var(--wp--preset--font-size--h-3); }
body, .entry-content { font-size: var(--wp--preset--font-size--body); }

/* If you define font families */
body { font-family: var(--wp--preset--font-family--inter); }
blockquote { font-family: var(--wp--preset--font-family--serif); }

/* Optional: fallbacks */
.entry-content { font-size: var(--wp--preset--font-size--body, 1rem); }
