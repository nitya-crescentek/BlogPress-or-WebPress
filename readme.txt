=== WebPress ===
Contributors: nityasaha
Donate link: https://buymeacoffee.com/nityasaha
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Tags: blog, one-column, two-columns, three-columns, left-sidebar, right-sidebar, custom-colors, custom-logo, custom-menu, editor-style, featured-images, footer-widgets, post-formats, rtl-language-support, sticky-post, theme-options, threaded-comments, translation-ready, wide-blocks
Requires at least: 6.6
Requires PHP: 7.4
Tested up to: 7.1
Stable tag: 1.0.1

Fast, lightweight WordPress blog theme with 71 colour controls, six layouts, schema.org markup and 35 developer hooks.

== Description ==

WebPress is a lightweight, classic (non-block) WordPress theme for blogs, magazines, news sites and personal
writing. Everything is configured from the Customizer with a live preview — there is no separate options panel,
no bundled page builder, and no upsell screens.

= Built to load fast =

On a default install the front end loads three files: main.min.css, menu.min.js and back-to-top.min.js. Together
they come to roughly 7 KB gzipped. Colour, spacing and typography settings are compiled into a single cached inline
stylesheet rather than shipped as a large stylesheet of unused rules, so a page only carries the CSS its own
settings actually produce.

Out of the box the theme uses a system font stack and makes no third-party requests — no external fonts, no CDN,
no tracking. Icons are inline SVG by default rather than an icon font, so there is no extra font file to download.

= Search-engine friendly markup =

WebPress outputs structured data and semantic HTML without needing a plugin:

* Schema.org microdata throughout — Blog, CreativeWork, WPHeader, WPFooter, SiteNavigationElement, Comment and
  Person, with headline, datePublished, author, image and url properties on entries
* Semantic HTML5 landmarks — header, nav, main, article, aside and footer, each with an aria-label where more than
  one of the same landmark can appear on a page
* A single h1 per page, with entry titles demoted to h2 in archive listings
* title-tag support, so WordPress and your SEO plugin control the document title
* Automatic feed links and a responsive viewport meta tag
* A clean, shallow DOM — the theme does not wrap content in unnecessary container divs

The microdata output can be switched off with a single filter if you would rather let an SEO plugin emit JSON-LD
instead. See webpress_schema_type below.

= Accessibility =

A skip-to-content link, keyboard-operable dropdown menus with an arrow toggle, focus styles that appear for
keyboard users but not mouse users, aria-expanded state on the mobile menu toggle, and screen-reader text on
icon-only controls.

= Design control without a page builder =

* 71 colour settings covering the body, header, top bar, navigation, buttons, forms, sidebars, footer and the
  back-to-top control, built on a seven-colour global palette you can redefine
* Dynamic typography — font family, size, weight, transform, line height and letter spacing per element, with the
  option to add Google Fonts if you want them
* Separate layouts for pages, single posts and the blog and archives, each overridable per post or page:
  * Three container layouts — normal, narrow (a comfortable reading width) and full width
  * Six sidebar layouts — right sidebar, left sidebar, no sidebar, both sidebars, both left, both right
* Three blog post layouts — classic, list (featured image beside the text) and a grid of two, three or four columns
* Six positions for the primary menu — above the header, below the header, floated left or right of the site
  title, or inside the left or right sidebar
* Dropdown menus that open on hover or on click
* Ten widget areas — Right Sidebar, Left Sidebar, Header, Footer 1 to 5, Footer Bar and Top Bar
* A search modal and an optional navigation search bar
* Container width, padding and spacing controls throughout

= Block editor =

The theme ships a theme.json (schema v3), so the block editor offers the theme's own colour palette, layout widths
and font family, and the editor canvas reflects your front-end typography and colours. Wide alignment and
responsive embeds are supported. Your Customizer settings stay authoritative — the theme keeps theme.json in step
with them automatically.

= Translation and RTL =

Fully internationalised with 366 translatable strings and a bundled .pot file, covering the PHP templates, the
Customizer's React interface and the theme.json palette names. Ships with a right-to-left stylesheet for both the
front end and the block editor.

== Customizing WebPress ==

WebPress is built to be extended from a child theme or a small plugin. It provides 35 action hooks and 27 filters,
so in most cases you can add or change output without editing or copying a template file.

A full reference with arguments and examples is coming. The most useful hooks are listed here.

= Action hooks: header and branding =

* webpress_before_header — before the site header
* webpress_inside_site_header — inside the header container, before the branding
* webpress_before_logo / webpress_after_logo — around the logo
* webpress_before_site_title / webpress_after_site_title — around the title and tagline
* webpress_after_header — after the header and any below-header navigation
* webpress_before_top_bar / webpress_after_top_bar — around the top bar

= Action hooks: navigation =

* webpress_before_navigation — before the nav element
* webpress_inside_navigation — inside the navigation, before the menu
* webpress_after_mobile_menu_button — after the mobile toggle button
* webpress_menu_bar_items — inside the menu bar items container
* webpress_after_navigation — after the nav element

= Action hooks: layout =

* webpress_inside_container — inside the page container
* webpress_before_main_content / webpress_after_main_content — inside the content wrapper

= Action hooks: posts and pages =

Each of these receives a $context string — content, single, page, link, 404, none or woocommerce — so you can
target one template without writing conditionals.

* webpress_before_content — top of the article
* webpress_before_entry_title / webpress_after_entry_title — around the entry title
* webpress_after_entry_header — after the entry header
* webpress_before_content_output — immediately before the content or excerpt
* webpress_after_entry_content — after the content wrapper
* webpress_after_content — bottom of the article

= Action hooks: sidebars and footer =

* webpress_before_right_sidebar_content / webpress_after_right_sidebar_content
* webpress_before_left_sidebar_content / webpress_after_left_sidebar_content
* webpress_before_footer / webpress_after_footer — around the site footer
* webpress_before_footer_content / webpress_after_footer_content — inside the footer

= Filters =

* webpress_copyright — the footer copyright line
* webpress_show_title — whether the content title is displayed
* webpress_show_excerpt — excerpts instead of full content in listings
* webpress_default_loop — return false to suppress the theme's loop entirely
* webpress_sidebar_layout — the sidebar layout for the current view
* webpress_container_layout — the container layout for the current view: normal, narrow or full-width
* webpress_blog_post_layout — the post layout in blog and archive listings: classic, list or grid
* webpress_post_image_location — whether listing images print above-title or below-title
* webpress_post_image_size — the image size used for featured images in listings
* webpress_footer_widgets — how many footer widget columns to show
* webpress_show_post_navigation — the older/newer navigation below listings
* webpress_svg_icon — the markup of any built-in SVG icon
* webpress_mobile_menu_label — the mobile toggle label
* webpress_schema_type — the schema.org type on the body element
* webpress_google_fonts_uri — the Google Fonts URL, or an empty string to block the request
* webpress_attr_{$context} — the HTML attributes of any theme element, where $context is header, inside-header,
  navigation, entry-header, main, right-sidebar and so on

= Examples =

Add a notice bar directly under the header:

    add_action( 'webpress_after_header', function () {
        echo '<div class="promo-bar">Free shipping this week</div>';
    } );

Add an author box after the content on single posts only:

    add_action( 'webpress_after_entry_content', function ( $context ) {
        if ( 'single' !== $context ) {
            return;
        }
        get_template_part( 'template-parts/author-box' );
    }, 10, 1 );

Replace the footer copyright:

    add_filter( 'webpress_copyright', function () {
        return '<span class="copyright">&copy; ' . gmdate( 'Y' ) . ' Acme Ltd</span>';
    } );

Add a data attribute to the header element:

    add_filter( 'webpress_attr_header', function ( $attributes ) {
        $attributes['data-sticky'] = 'true';
        return $attributes;
    } );

Turn off the theme's microdata so an SEO plugin can supply JSON-LD instead:

    add_filter( 'webpress_schema_type', '__return_empty_string' );

The theme's template functions are also wrapped in function_exists(), so a child theme can redefine them outright
when a hook is not enough. The default option, colour, typography and spacing arrays each have a filter
( webpress_option_defaults, webpress_color_option_defaults, webpress_font_option_defaults and
webpress_spacing_option_defaults ), so you can change a single default without copying the whole array.

== Installation ==

= From within WordPress =
1. Visit "Appearance > Themes > Add New"
1. Search for "WebPress"
1. Install and activate

= Manually =
1. Upload the `webpress` folder to `/wp-content/themes/`
1. Activate the theme through "Appearance > Themes"

After activating, go to "Appearance > Customize" to set your colours, typography, layout and widget areas.

== Frequently Asked Questions ==

= Is WebPress free? =
Yes. WebPress is licensed under the GPL, version 2 or later.

= Where are the theme options? =
All options live in the Customizer, under "Appearance > Customize". There is no separate settings page.

= Does WebPress require a page builder? =
No. The theme works on its own and does not bundle or require any builder plugin.

= Does WebPress load anything from third-party servers? =
Not by default. The theme uses a system font stack and inline SVG icons, so a default install makes no external
requests. If you add a Google Font in the Customizer's typography settings, that font is then requested from
Google. Remove it, or use the webpress_google_fonts_uri filter, to go back to no external requests.

= Do I need an SEO plugin? =
The theme already outputs schema.org microdata, semantic landmarks and a sensible heading structure, and supports
title-tag so a plugin can control titles and meta descriptions. An SEO plugin is complementary, not required. If
your plugin outputs its own JSON-LD and you want to avoid duplication, disable the theme's microdata with the
webpress_schema_type filter.

= How many widget areas does WebPress have? =
Ten. You can add widgets to them under "Appearance > Widgets".

= Can I change the layout for a single post or page? =
Yes. Set the defaults for pages, single posts and the blog under "Appearance > Customize > Layout". Each post and
page also has a Layout box in the editor sidebar where you can override the container layout, the sidebar layout
and the number of footer widget columns.

= How do I customize the theme without losing changes on update? =
Use a child theme. WebPress provides 35 action hooks and 27 filters, and wraps its template functions in
function_exists(), so most changes can be made without copying template files. See "Customizing WebPress" above.

= Is WebPress translation ready? =
Yes. It ships with a .pot file containing 366 strings in /languages/, and includes a right-to-left stylesheet.

== Copyright ==

WebPress WordPress Theme, (C) 2026 Nitya.
WebPress is distributed under the terms of the GNU General Public License v2 or later.

This program is free software: you can redistribute it and/or modify it under the terms of the GNU General
Public License as published by the Free Software Foundation, either version 2 of the License, or (at your
option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the
implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License
for more details.

= Derivative work notice =

WebPress derives its template structure and much of its Customizer architecture from GeneratePress,
Copyright (C) Tom Usborne, licensed GPLv2 or later — https://generatepress.com
GeneratePress is itself based on Underscores, (C) 2012-2026 Automattic, Inc., licensed GPLv2 or later —
http://underscores.me/

= Bundled resources =

selectWoo — inc/customizer/controls/js/selectWoo.min.js, inc/customizer/controls/css/selectWoo.min.css
MIT License — https://github.com/woocommerce/selectWoo/blob/master/LICENSE.md
Copyright (C) Automattic and select2 contributors

React Select — compiled into assets/dist/customizer.js
MIT License — https://github.com/JedWatson/react-select/blob/master/LICENSE
Copyright (C) Jed Watson

TinyColor — compiled into assets/dist/customizer.js and assets/dist/block-editor.js
MIT License — https://github.com/bgrins/TinyColor/blob/master/LICENSE
Copyright (C) Brian Grinstead — http://briangrinstead.com

React DnD (with its Redux dependency) — compiled into assets/dist/customizer.js
MIT License — https://github.com/react-dnd/react-dnd/blob/main/LICENSE
Copyright (C) Dan Abramov

= Source code =

The files in assets/dist/ are compiled. Their uncompiled source is available at:
TODO-ADD-PUBLIC-REPOSITORY-URL-BEFORE-SUBMITTING

== Changelog ==

= 1.0.1=
* New feature: layout coustomization for templates. 

= 1.0.0 =
* Initial release.
