<?php

/* custom project type */
function create_post_type() {
  $labels = array(
    'name'               => 'Projects',
    'singular_name'      => 'Project',
    'menu_name'          => 'Projects',
    'name_admin_bar'     => 'Project',
    'add_new'            => 'Add New',
    'add_new_item'       => 'Add New Project',
    'new_item'           => 'New Project',
    'edit_item'          => 'Edit Project',
    'view_item'          => 'View Project',
    'all_items'          => 'All Projects',
    'search_items'       => 'Search Projects',
    'parent_item_colon'  => 'Parent Project',
    'not_found'          => 'No Projects Found',
    'not_found_in_trash' => 'No Projects Found in Trash'
  );

  $args = array(
    'labels'              => $labels,
    'public'              => true,
    'exclude_from_search' => false,
    'publicly_queryable'  => true,
    'show_ui'             => true,
    'show_in_nav_menus'   => true,
    'show_in_menu'        => true,
    'show_in_admin_bar'   => true,
    'menu_position'       => 5,
    'menu_icon'           => 'dashicons-admin-appearance',
    'capability_type'     => 'post',
    'hierarchical'        => false,
    'supports'            => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments' ),
    'has_archive'         => true,
    'rewrite'             => array( 'slug' => 'projects' ),
    'query_var'           => true,
    'taxonomies'		=> array('category')
    );

  register_post_type( 'cetls_project', $args );
}
add_action( 'init', 'create_post_type' );

//Modify the main query    
function custom_archive_query( $query ){
	if ( is_admin() || !$query->is_main_query()) {
		return;
	}

	echo 'celts';

	if ( is_archive() && (is_author() || is_category()) && empty( $query->query_vars['suppress_filters']) ) {
		
		$query->set( 'post_type', array(
			'post',
			'cetls_project'
		) );
	}
}
add_action('pre_get_posts', 'custom_archive_query');

function canvas_parent_theme_enqueue_styles() {
    wp_enqueue_style( 'canvas-style', get_template_directory_uri() . '/style.css' );
    wp_enqueue_style( 'bmcc-acert-style',
        get_stylesheet_directory_uri() . '/style.css',
        array( 'canvas-style' )
    );
}
add_action( 'wp_enqueue_scripts', 'canvas_parent_theme_enqueue_styles' );

/**
 * Filter Canvas's post_author_posts_link shortcode output to account for co-authors.
 */
add_filter( 'woo_shortcode_post_author_posts_link', function( $output, $atts ) {
	ob_start();
	coauthors_posts_links();
	$links = ob_get_clean();
	$output = sprintf( '<span class="author vcard">%2$s<span class="fn">%1$s</span>%3$s</span>', $links, $atts['before'], $atts['after'] );

	return $output;
}, 10, 2 );

/**
 * Override the Canvas author box function to show one box for each co-author.
 */
function woo_author_box () {
	global $post;

	$coauthors = get_coauthors( get_queried_object_id() );

	foreach ( $coauthors as $coauthor ) {
		// Adjust the arrow, if is_rtl().
		$arrow = '&rarr;';
		if ( is_rtl() ) $arrow = '&larr;';
?>
<aside id="post-author">
	<div class="profile-image"><?php echo coauthors_get_avatar( $coauthor, '80' ); ?></div>
	<div class="profile-content">
		<h4><?php printf( esc_attr__( 'About %s', 'woothemes' ), $coauthor->display_name ); ?></h4>
		<?php echo $coauthor->description; ?>
		<?php if ( is_singular() ) { ?>
		<div class="profile-link">
			<a href="<?php echo esc_url( get_author_posts_url( $coauthor->ID, $coauthor->user_nicename ) ); ?>">
				<?php printf( __( 'View all posts by %s %s', 'woothemes' ), $coauthor->display_name, '<span class="meta-nav">' . $arrow . '</span>' ); ?>
			</a>
		</div><!-- #profile-link -->
		<?php } ?>
	</div>
	<div class="fix"></div>
</aside>
<?php
	}
} // End woo_author_box()
