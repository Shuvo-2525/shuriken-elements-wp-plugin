<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Basic view for the Shuriken Elements settings page
// This can be expanded later with form elements, tabs, etc.
?>
<div class="wrap">
	<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
	<p><?php esc_html_e( 'Welcome to the Shuriken Elements dashboard. Here you can manage your Elementor widgets and view plugin settings.', 'shuriken-elements' ); ?></p>

    <div class="card" style="max-width: 600px; margin-top: 20px;">
        <h2 class="title"><?php esc_html_e( 'Plugin Information', 'shuriken-elements' ); ?></h2>
        <p>
            <strong><?php esc_html_e( 'Developed by:', 'shuriken-elements' ); ?></strong> Mohammad Rafiq Shuvo<br>
            <strong><?php esc_html_e( 'Managed by:', 'shuriken-elements' ); ?></strong> <a href="https://shurikenit.com" target="_blank">ShurikenIT</a>
        </p>
    </div>
</div>
