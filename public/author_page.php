<?php
/**
 * The template for displaying author (meta-box) pages.
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package scientific_2016
 */
get_header();
?>

<div id="primary" class="content-area">
    <main id="main" class="site-main" role="main">

	<?php
	if ( class_exists( 'Mop_Acf_Authors' ) ) {

	    $author_posts = apply_filters( 'mop_acf_posts_by_author', '' );
	    ?>

	    <?php
	    if ( $author_posts->have_posts() ) :
		$author_name = filter_input( INPUT_GET, "author" );
		?>

		<header class="page-header">
		    <h1 class="page-title"><?php _e( 'Articles by', 'mop-acf-authors' ) ; echo ' ' . $author_name ?></h1>
		</header><!-- .page-header -->

		<section class="page-authors-wrapper clear">
		    <?php
		    $acf_group_id = filter_input( INPUT_GET, "group_id" );
		    $post_id = filter_input( INPUT_GET, "post_id" );
		    echo apply_filters( 'mop_acf_specific_author', 'image, name, description, ', $acf_group_id, $post_id );
		    ?>
		</section>
		<div class="article-wrapper">
		    <?php /* Start the Loop */ ?>
		    <?php while ( $author_posts->have_posts() ) : $author_posts->the_post(); ?>

			<?php
			/*
			 * Include the Post-Format-specific template for the content.
			 * If you want to override this in a child theme, then include a file
			 * called content-___.php (where ___ is the Post Format name) and that will be used instead.
			 */
			// set content_type for proper image display
			set_query_var( 'content_type', Scientific2016ContentType::Articles );
			get_template_part( 'template-parts/content', get_post_format() );
			?>

		    <?php endwhile; ?>
		</div>
		<?php the_posts_navigation(); ?>

	    <?php else : ?>

		<?php get_template_part( 'template-parts/content', 'none' ); ?>

	    <?php endif; ?>
	<?php } ?>

    </main><!-- #main -->
</div><!-- #primary -->

<?php get_sidebar(); ?>
<?php get_footer();
