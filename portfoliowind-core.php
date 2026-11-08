<?php
/**
 * Plugin Name: PortfolioWind Core
 * Plugin URI: https://github.com/Muquarrabin/portfoliowind-core
 * Description: Core companion functionality for the PortfolioWind theme, providing the Portfolio Projects custom post type, taxonomies, case study meta boxes, and Elementor section shortcodes.
 * Version: 1.0.0
 * Author: Nasif Muquarrabin
 * Author URI: https://nasifmuquarrabin.me
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: portfoliowind-core
 *
 * @package PortfolioWind_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'PORTFOLIOWIND_CORE_VERSION', '1.0.0' );

/**
 * Register Custom Post Type: Portfolio Projects
 */
function portfoliowind_core_register_cpt() {
	$labels = array(
		'name'                  => _x( 'Projects', 'Post Type General Name', 'portfoliowind-core' ),
		'singular_name'         => _x( 'Project', 'Post Type Singular Name', 'portfoliowind-core' ),
		'menu_name'             => __( 'Projects', 'portfoliowind-core' ),
		'name_admin_bar'        => __( 'Project', 'portfoliowind-core' ),
		'archives'              => __( 'Project Archives', 'portfoliowind-core' ),
		'attributes'            => __( 'Project Attributes', 'portfoliowind-core' ),
		'parent_item_colon'     => __( 'Parent Project:', 'portfoliowind-core' ),
		'all_items'             => __( 'All Projects', 'portfoliowind-core' ),
		'add_new_item'          => __( 'Add New Project', 'portfoliowind-core' ),
		'add_new'               => __( 'Add New', 'portfoliowind-core' ),
		'new_item'              => __( 'New Project', 'portfoliowind-core' ),
		'edit_item'             => __( 'Edit Project', 'portfoliowind-core' ),
		'update_item'           => __( 'Update Project', 'portfoliowind-core' ),
		'view_item'             => __( 'View Project', 'portfoliowind-core' ),
		'view_items'            => __( 'View Projects', 'portfoliowind-core' ),
		'search_items'          => __( 'Search Project', 'portfoliowind-core' ),
		'not_found'             => __( 'Not found', 'portfoliowind-core' ),
		'not_found_in_trash'    => __( 'Not found in Trash', 'portfoliowind-core' ),
		'featured_image'        => __( 'Project Cover Image', 'portfoliowind-core' ),
		'set_featured_image'    => __( 'Set cover image', 'portfoliowind-core' ),
		'remove_featured_image' => __( 'Remove cover image', 'portfoliowind-core' ),
		'use_featured_image'    => __( 'Use as cover image', 'portfoliowind-core' ),
	);

	$args = array(
		'label'                 => __( 'Project', 'portfoliowind-core' ),
		'description'           => __( 'Portfolio case studies and projects', 'portfoliowind-core' ),
		'labels'                => $labels,
		'supports'              => array( 'title', 'editor', 'thumbnail', 'excerpt', 'custom-fields', 'elementor' ),
		'taxonomies'            => array( 'portfoliowind_project_category' ),
		'hierarchical'          => false,
		'public'                => true,
		'show_ui'               => true,
		'show_in_menu'          => true,
		'menu_position'         => 20,
		'menu_icon'             => 'dashicons-portfolio',
		'show_in_admin_bar'     => true,
		'show_in_nav_menus'     => true,
		'can_export'            => true,
		'has_archive'           => false,
		'exclude_from_search'   => false,
		'publicly_queryable'    => true,
		'capability_type'       => 'post',
		'show_in_rest'          => true,
		'rewrite'               => array(
			'slug'       => 'project',
			'with_front' => false,
		),
	);
	register_post_type( 'portfoliowind_proj', $args );

	// Register Custom Taxonomy: Project Category
	$tax_labels = array(
		'name'              => _x( 'Project Categories', 'taxonomy general name', 'portfoliowind-core' ),
		'singular_name'     => _x( 'Project Category', 'taxonomy singular name', 'portfoliowind-core' ),
		'search_items'      => __( 'Search Categories', 'portfoliowind-core' ),
		'all_items'         => __( 'All Categories', 'portfoliowind-core' ),
		'parent_item'       => __( 'Parent Category', 'portfoliowind-core' ),
		'parent_item_colon' => __( 'Parent Category:', 'portfoliowind-core' ),
		'edit_item'         => __( 'Edit Category', 'portfoliowind-core' ),
		'update_item'       => __( 'Update Category', 'portfoliowind-core' ),
		'add_new_item'      => __( 'Add New Category', 'portfoliowind-core' ),
		'new_item_name'     => __( 'New Category Name', 'portfoliowind-core' ),
		'menu_name'         => __( 'Categories', 'portfoliowind-core' ),
	);

	$tax_args = array(
		'hierarchical'      => true,
		'labels'            => $tax_labels,
		'show_ui'           => true,
		'show_admin_column' => true,
		'query_var'         => true,
		'show_in_rest'      => true,
		'rewrite'           => array( 'slug' => 'project-category' ),
	);
	register_taxonomy( 'portfoliowind_project_category', array( 'portfoliowind_proj' ), $tax_args );
}
add_action( 'init', 'portfoliowind_core_register_cpt', 0 );

/**
 * Register Meta Boxes for Project Details
 */
function portfoliowind_core_add_project_meta_boxes() {
	$screens = array( 'portfoliowind_proj', 'portfolio_project' );
	foreach ( $screens as $screen ) {
		add_meta_box(
			'portfoliowind_project_details',
			__( 'Project Case Study Information', 'portfoliowind-core' ),
			'portfoliowind_core_render_project_meta_box',
			$screen,
			'normal',
			'high'
		);
	}
}
add_action( 'add_meta_boxes', 'portfoliowind_core_add_project_meta_boxes' );

/**
 * Helper to get project meta with legacy fallback
 */
function portfoliowind_core_get_meta( $post_id, $key, $legacy_key = '' ) {
	$value = get_post_meta( $post_id, $key, true );
	if ( '' === $value && ! empty( $legacy_key ) ) {
		$value = get_post_meta( $post_id, $legacy_key, true );
	}
	return $value;
}

/**
 * Render Meta Box Form
 */
function portfoliowind_core_render_project_meta_box( $post ) {
	wp_nonce_field( 'portfoliowind_save_project_meta', 'portfoliowind_project_meta_nonce' );

	$client     = portfoliowind_core_get_meta( $post->ID, '_portfoliowind_client', '_project_client' );
	$timeline   = portfoliowind_core_get_meta( $post->ID, '_portfoliowind_timeline', '_project_timeline' );
	$role       = portfoliowind_core_get_meta( $post->ID, '_portfoliowind_role', '_project_role' );
	$live_url   = portfoliowind_core_get_meta( $post->ID, '_portfoliowind_live_url', '_project_live_url' );
	$github_url = portfoliowind_core_get_meta( $post->ID, '_portfoliowind_github_url', '_project_github_url' );
	$tech_stack = portfoliowind_core_get_meta( $post->ID, '_portfoliowind_tech_stack', '_project_tech_stack' );
	$image_url  = portfoliowind_core_get_meta( $post->ID, '_portfoliowind_image_url', '_project_image_url' );
	$m1_val     = portfoliowind_core_get_meta( $post->ID, '_portfoliowind_metric1_val', '_project_metric1_val' );
	$m1_lbl     = portfoliowind_core_get_meta( $post->ID, '_portfoliowind_metric1_lbl', '_project_metric1_lbl' );
	$m2_val     = portfoliowind_core_get_meta( $post->ID, '_portfoliowind_metric2_val', '_project_metric2_val' );
	$m2_lbl     = portfoliowind_core_get_meta( $post->ID, '_portfoliowind_metric2_lbl', '_project_metric2_lbl' );
	$m3_val     = portfoliowind_core_get_meta( $post->ID, '_portfoliowind_metric3_val', '_project_metric3_val' );
	$m3_lbl     = portfoliowind_core_get_meta( $post->ID, '_portfoliowind_metric3_lbl', '_project_metric3_lbl' );
	?>
	<table class="form-table" style="width:100%;">
		<tr>
			<th style="width:25%;"><label for="portfoliowind_client"><?php esc_html_e( 'Client / Company', 'portfoliowind-core' ); ?></label></th>
			<td><input type="text" id="portfoliowind_client" name="_portfoliowind_client" value="<?php echo esc_attr( $client ); ?>" class="regular-text" placeholder="e.g. Acme Corp / Open Source"></td>
		</tr>
		<tr>
			<th><label for="portfoliowind_timeline"><?php esc_html_e( 'Timeline / Year', 'portfoliowind-core' ); ?></label></th>
			<td><input type="text" id="portfoliowind_timeline" name="_portfoliowind_timeline" value="<?php echo esc_attr( $timeline ); ?>" class="regular-text" placeholder="e.g. 2026 / 3 Months"></td>
		</tr>
		<tr>
			<th><label for="portfoliowind_role"><?php esc_html_e( 'Your Role', 'portfoliowind-core' ); ?></label></th>
			<td><input type="text" id="portfoliowind_role" name="_portfoliowind_role" value="<?php echo esc_attr( $role ); ?>" class="regular-text" placeholder="e.g. Lead Architect / Full-Stack Engineer"></td>
		</tr>
		<tr>
			<th><label for="portfoliowind_tech_stack"><?php esc_html_e( 'Tech Stack (Comma Separated)', 'portfoliowind-core' ); ?></label></th>
			<td><input type="text" id="portfoliowind_tech_stack" name="_portfoliowind_tech_stack" value="<?php echo esc_attr( $tech_stack ); ?>" class="large-text" placeholder="e.g. React, Tailwind CSS, WordPress REST API, Node.js"></td>
		</tr>
		<tr>
			<th><label for="portfoliowind_image_url"><?php esc_html_e( 'External Cover Image URL', 'portfoliowind-core' ); ?></label></th>
			<td>
				<input type="url" id="portfoliowind_image_url" name="_portfoliowind_image_url" value="<?php echo esc_url( $image_url ); ?>" class="large-text" placeholder="https://example.com/project-cover.jpg">
				<p class="description"><?php esc_html_e( 'Optional. Used if no WordPress Featured Image is set (useful for bulk imported projects).', 'portfoliowind-core' ); ?></p>
			</td>
		</tr>
		<tr>
			<th><label for="portfoliowind_live_url"><?php esc_html_e( 'Live Preview URL', 'portfoliowind-core' ); ?></label></th>
			<td><input type="url" id="portfoliowind_live_url" name="_portfoliowind_live_url" value="<?php echo esc_url( $live_url ); ?>" class="large-text" placeholder="https://example.com"></td>
		</tr>
		<tr>
			<th><label for="portfoliowind_github_url"><?php esc_html_e( 'Source Code / Repository URL', 'portfoliowind-core' ); ?></label></th>
			<td><input type="url" id="portfoliowind_github_url" name="_portfoliowind_github_url" value="<?php echo esc_url( $github_url ); ?>" class="large-text" placeholder="https://github.com/username/project"></td>
		</tr>
		<tr>
			<th><label><?php esc_html_e( 'Key Deliverables & Metrics', 'portfoliowind-core' ); ?></label></th>
			<td>
				<div style="display:flex; gap:12px; margin-bottom:8px; align-items:center;">
					<span style="min-width:60px; font-weight:600;"><?php esc_html_e( 'KPI 1:', 'portfoliowind-core' ); ?></span>
					<input type="text" name="_portfoliowind_metric1_val" value="<?php echo esc_attr( $m1_val ); ?>" placeholder="<?php esc_attr_e( 'e.g. 100/100 or 99.99%', 'portfoliowind-core' ); ?>" style="width:140px;">
					<input type="text" name="_portfoliowind_metric1_lbl" value="<?php echo esc_attr( $m1_lbl ); ?>" placeholder="<?php esc_attr_e( 'e.g. Lighthouse Speed or Uptime', 'portfoliowind-core' ); ?>" style="width:220px;">
				</div>
				<div style="display:flex; gap:12px; margin-bottom:8px; align-items:center;">
					<span style="min-width:60px; font-weight:600;"><?php esc_html_e( 'KPI 2:', 'portfoliowind-core' ); ?></span>
					<input type="text" name="_portfoliowind_metric2_val" value="<?php echo esc_attr( $m2_val ); ?>" placeholder="<?php esc_attr_e( 'e.g. < 0.5s or 4.2x', 'portfoliowind-core' ); ?>" style="width:140px;">
					<input type="text" name="_portfoliowind_metric2_lbl" value="<?php echo esc_attr( $m2_lbl ); ?>" placeholder="<?php esc_attr_e( 'e.g. First Contentful Paint or Performance', 'portfoliowind-core' ); ?>" style="width:220px;">
				</div>
				<div style="display:flex; gap:12px; align-items:center;">
					<span style="min-width:60px; font-weight:600;"><?php esc_html_e( 'KPI 3:', 'portfoliowind-core' ); ?></span>
					<input type="text" name="_portfoliowind_metric3_val" value="<?php echo esc_attr( $m3_val ); ?>" placeholder="<?php esc_attr_e( 'e.g. 100% or 1.2M+', 'portfoliowind-core' ); ?>" style="width:140px;">
					<input type="text" name="_portfoliowind_metric3_lbl" value="<?php echo esc_attr( $m3_lbl ); ?>" placeholder="<?php esc_attr_e( 'e.g. Responsive Fidelity or Active Users', 'portfoliowind-core' ); ?>" style="width:220px;">
				</div>
				<p class="description" style="margin-top:6px;"><?php esc_html_e( 'Shown in the Key Deliverables & Outcomes summary card on the project case study page.', 'portfoliowind-core' ); ?></p>
			</td>
		</tr>
	</table>
	<?php
}

/**
 * Save Project Meta Box Data
 */
function portfoliowind_core_save_project_meta( $post_id ) {
	if ( ! isset( $_POST['portfoliowind_project_meta_nonce'] ) ) {
		return;
	}

	$nonce = sanitize_text_field( wp_unslash( $_POST['portfoliowind_project_meta_nonce'] ) );
	if ( ! wp_verify_nonce( $nonce, 'portfoliowind_save_project_meta' ) ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$fields = array(
		'_portfoliowind_client'      => 'sanitize_text_field',
		'_portfoliowind_timeline'    => 'sanitize_text_field',
		'_portfoliowind_role'        => 'sanitize_text_field',
		'_portfoliowind_tech_stack'  => 'sanitize_text_field',
		'_portfoliowind_image_url'   => 'esc_url_raw',
		'_portfoliowind_live_url'    => 'esc_url_raw',
		'_portfoliowind_github_url'  => 'esc_url_raw',
		'_portfoliowind_metric1_val' => 'sanitize_text_field',
		'_portfoliowind_metric1_lbl' => 'sanitize_text_field',
		'_portfoliowind_metric2_val' => 'sanitize_text_field',
		'_portfoliowind_metric2_lbl' => 'sanitize_text_field',
		'_portfoliowind_metric3_val' => 'sanitize_text_field',
		'_portfoliowind_metric3_lbl' => 'sanitize_text_field',
	);

	foreach ( $fields as $field => $sanitizer ) {
		if ( isset( $_POST[ $field ] ) ) {
			if ( 'esc_url_raw' === $sanitizer ) {
				$value = esc_url_raw( wp_unslash( $_POST[ $field ] ) );
			} else {
				$value = sanitize_text_field( wp_unslash( $_POST[ $field ] ) );
			}
			update_post_meta( $post_id, $field, $value );

			// Also persist to legacy _project_* key for backward compatibility with existing templates/data
			$legacy_field = str_replace( '_portfoliowind_', '_project_', $field );
			update_post_meta( $post_id, $legacy_field, $value );
		}
	}
}
add_action( 'save_post_portfoliowind_proj', 'portfoliowind_core_save_project_meta' );
add_action( 'save_post_portfolio_project', 'portfoliowind_core_save_project_meta' );

/**
 * Enable Elementor support for portfoliowind_proj
 */
function portfoliowind_core_enable_elementor_cpt_support() {
	add_post_type_support( 'portfoliowind_proj', 'elementor' );
	add_post_type_support( 'portfolio_project', 'elementor' );
}
add_action( 'init', 'portfoliowind_core_enable_elementor_cpt_support' );

/**
 * Section Shortcode Helper
 */
function portfoliowind_core_render_section_buffer( $section_name ) {
	if ( function_exists( 'get_template_part' ) ) {
		ob_start();
		get_template_part( 'template-parts/section', $section_name );
		return ob_get_clean();
	}
	return '';
}

/**
 * Register Section Shortcodes for Elementor & Page Builders
 */
add_shortcode( 'portfoliowind_hero', function() {
	return portfoliowind_core_render_section_buffer( 'hero' );
} );

add_shortcode( 'portfoliowind_about', function() {
	return portfoliowind_core_render_section_buffer( 'about' );
} );

add_shortcode( 'portfoliowind_services', function() {
	return portfoliowind_core_render_section_buffer( 'services' );
} );

add_shortcode( 'portfoliowind_projects', function() {
	return portfoliowind_core_render_section_buffer( 'projects' );
} );

add_shortcode( 'portfoliowind_resume', function() {
	return portfoliowind_core_render_section_buffer( 'resume' );
} );

add_shortcode( 'portfoliowind_blog', function() {
	return portfoliowind_core_render_section_buffer( 'blog' );
} );

add_shortcode( 'portfoliowind_contact', function() {
	return portfoliowind_core_render_section_buffer( 'contact' );
} );

add_shortcode( 'portfoliowind_all', function() {
	if ( function_exists( 'get_template_part' ) ) {
		ob_start();
		get_template_part( 'template-parts/section', 'hero' );
		get_template_part( 'template-parts/section', 'about' );
		get_template_part( 'template-parts/section', 'services' );
		get_template_part( 'template-parts/section', 'projects' );
		get_template_part( 'template-parts/section', 'resume' );
		get_template_part( 'template-parts/section', 'blog' );
		get_template_part( 'template-parts/section', 'contact' );
		return ob_get_clean();
	}
	return '';
} );

/**
 * Flush rewrite rules upon plugin activation.
 */
function portfoliowind_core_activate() {
	portfoliowind_core_register_cpt();
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'portfoliowind_core_activate' );

/**
 * Flush rewrite rules upon plugin deactivation.
 */
function portfoliowind_core_deactivate() {
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'portfoliowind_core_deactivate' );

