<?php

	/**
	 * The public-facing functionality of the plugin.
	 *
	 * @link       http://leketshibolim.ort.org.il
	 * @since      1.0.0
	 *
	 * @package    Pelepay_Form_Inserter
	 * @subpackage Pelepay_Form_Inserter/public
	 */

	/**
	 * The public-facing functionality of the plugin.
	 *
	 * Defines the plugin name, version, and two examples hooks for how to
	 * enqueue the dashboard-specific stylesheet and JavaScript.
	 *
	 * @package    Pelepay_Form_Inserter
	 * @subpackage Pelepay_Form_Inserte/public
	 * @author     Lea Cohen <leac@ort.org.il>
	 */
	class Mop_Acf_Authors_Public {

		/**
		 * The ID of this plugin.
		 *
		 * @since    1.0.0
		 * @access   private
		 * @var      string    $plugin_name    The ID of this plugin.
		 */
		private $plugin_name;

		/**
		 * The version of this plugin.
		 *
		 * @since    1.0.0
		 * @access   private
		 * @var      string    $version    The current version of this plugin.
		 */
		private $version;

		/**
		 * The custom fields ids (authorname1, authorname2, authorname3; authorimage1, authorimage2, authorimage3; etc. ).
		 *
		 * @since    1.0.0
		 * @access   private
		 * @var      array    $acf_author_ids    The current custom fields ids.
		 */
		private $acf_field_ids = array( 1, 2, 3 );

		/**
		 * An array of author field values.
		 * Mainly used for author name, because other author fields depend on its existance.
		 *
		 * @since    1.0.0
		 * @access   private
		 * @var      array    $author_fields_values    An array of author field values.
		 */
		private $authors_collection = array();

		/**
		 * An array of author names displayd.
		 * Used to monitor unique display of each author
		 *
		 * @since    1.0.0
		 * @access   private
		 * @var      array    $random_authors    An array of author names displayd.
		 */
		private $random_authors = array();

		/**
		 * The authors page id.
		 *
		 * @since    1.0.0
		 * @access   private
		 * @var      array    $random_authors    The authors page id.
		 */
		private $authors_page_id = 9085;

		/**
		 * Initialize the class and set its properties.
		 *
		 * @since    1.0.0
		 * @var      string    $plugin_name       The name of the plugin.
		 * @var      string    $version    The version of this plugin.
		 */
		public function __construct( $plugin_name, $version ){

			$this->plugin_name = $plugin_name;
			$this->version = $version;
		}

		/**
		 * Our Authors plugin action function - show 3 arbitrary authors
		 * This is the starting point of the plugin
		 */
		function mop_acf_authors_plugin(){
			// display fixed number unique authors: http://support.advancedcustomfields.com/forums/topic/wp_query-distinct-results
			$author_posts_max = 3;

			/* Get all posts that have at least one author */
			$meta_query = $this->author_meta_query( '', '!=' );
			$args = array(
				'posts_per_page' => -1,
				'meta_query' => $meta_query
			);
			$all_author_posts = get_posts( $args );

			$total_author_posts = count( $all_author_posts ); // used for random limit, and to stop looking if all posts have been checked
			// collect all post id's we've used

			$random_author_post_ids = array(); // keep score of the post id's we've checked, so not to repeat
			// only if there are posts with authors, show them
			if ( $total_author_posts > 0 ) {
				/* loop and look for author info while we haven't gotten to the maximum authors required,
				 * or if we haven't gone through all the posts */
				while ( count( $this->random_authors ) < $author_posts_max ) {

					// if we've checked all the posts, there's no use in staying in the loop
					if ( $total_author_posts == count( $random_author_post_ids ) ) {
						break;
					}
					// get random index
					$author_ind = mt_rand( 0, $total_author_posts - 1 );
					// if we've already checked this post, move on the the next iterarion
					if ( in_array( $author_ind, $random_author_post_ids ) ) {
						continue;
					}

					/* If this post isn't in the array, then: */
					// save post id in array
					$random_author_post_ids[] = $author_ind;
					// the current post is the new random id
					$author_post = $all_author_posts[$author_ind];
					// get author
					$author_info_group = $this->find_populated_author_field( $author_post->ID );
					if ( $author_info_group > 0 ) {
						echo '<section class="author clear">';
						echo $this->get_author_info_by_field( 'image , name, description', $author_info_group, $author_post->ID );
						echo '</section>';
					}
				}
			}
		}

		/**
		 * mop_acf_post_authors action callback:
		 * Display the author info: name, image, descritption
		 * @param string $author_fields - comma delimited list of author fields  to display
		 */
		public function show_post_author_info( $author_fields, $post_id = null ){
			$author_info = '';
			// there are 3 group ids for authors:

			foreach ( $this->acf_field_ids as $acf_field_id ) {
				// get all author fields, and show whatever of them exists
				$this->set_authors_array( $acf_field_id, $post_id );
				// if there are fields, display them
				if ( ! empty( $this->authors_collection[$acf_field_id] ) ) {
					$author_info .='<section class="author clear">';
					$author_info .= $this->get_author_info_by_field( $author_fields, $acf_field_id, $post_id );
					$author_info .= '</section>';
				}
			}

			if ( ! empty( $author_info ) ) {
?>
				<?php echo $author_info ?>
				<?php

			}
		}

		/**
		 * Create meta query that gets posts that have author acf
		 * @param type $field_value
		 * @param type $comparer
		 * @return string
		 */
		function author_meta_query( $field_value = '', $comparer = '=' ){
			/* build the meta_query in a loop so more fields can easily be added */
			$meta_query = array();
			$meta_query['relation'] = 'OR';
			foreach ( $this->acf_field_ids as $acf_field_id ) {
				$meta_query[] = array( 'key' => 'authorname' . $acf_field_id,
					'value' => $field_value,
					'compare' => $comparer
				);
			}
			return $meta_query;
		}

		/**
		 * Return the group id of the authorname found
		 * @param type $post_id
		 * @return int
		 */
		function find_populated_author_field( $post_id ){
			$ret = 0;
			$found = false;
			for ( $i = 3; $i >= 1; $i -- ) {
				if ( ! $found ) {
					$this->set_authors_array( $i, $post_id );
					//echo $post_id.' '.$this->author_fields_values[$i]['authorname'].' '.$i.'<br>';
					/* all 3 fields have to exist in order for the author to be displayed */
					if ( ! empty( $this->authors_collection[$i]['authorname'] ) &&
						! empty( $this->authors_collection[$i]['authordescription'] ) &&
						! empty( $this->authors_collection[$i]['authorimage']['url'] ) ) {
						$author_name = trim( $this->authors_collection[$i]['authorname'] );
						// bail early if this country has already been added
						if ( in_array( $author_name, $this->random_authors ) ) {
							continue;
						}
						else {
							// add author name
							$this->random_authors[] = $author_name;
							$found = true;
							$ret = $i;
							break;
						}
					}
				}
			}
			$this->reset_authors_array();
			return $ret;
		}

		function set_authors_array( $group_id, $post_id ){
			if ( ! isset( $this->authors_collection[$group_id] ) ) {
				$this->authors_collection[$group_id]['authorname'] = get_field( 'authorname' . $group_id, $post_id );
				$this->authors_collection[$group_id]['authordescription'] = get_field( 'authordescription' . $group_id, $post_id );
				$this->authors_collection[$group_id]['authorimage'] = get_field( 'authorimage' . $group_id, $post_id );
			}
			// if no author name, reset the field
			if ( empty( $this->authors_collection[$group_id]['authorname'] ) ) {
				$this->authors_collection[$group_id] = array();
			}
		}

		function reset_authors_array(){
			$this->authors_collection = array();
		}

		function get_author_info_by_field( $author_fields, $author_group_id, $post_id = null ){
			$author_info = '';
			if ( $post_id == NULL ) {
				$post_id = get_the_ID();
			}
			$author_fields_arr = explode( ',', $author_fields );

			$this->authors_collection = array();
			foreach ( $author_fields_arr as $author_field ) {
				/* get the authorname first, because other fields depend on it. */
				switch ( trim( $author_field ) ) {
					case 'name':
//						echo count( $author_fields_arr );
//						echo '<br>';
//						echo $author_group_id;
//						echo '<br>';
						//var_dump( $this->authors_collection[$author_group_id]);
						/* if only the name is displayed, and there is more than on name, add a comma between each name */
						if ( count( $author_fields_arr ) == 1 && $author_group_id > 1 && ! empty( $this->authors_collection[$author_group_id]['authorname'] ) ) {
							$author_info.=', ';
						}
						$author_info .= $this->show_author_name( $author_group_id, $post_id );
						break;
					case 'image':
						$author_info .= $this->show_author_image( $author_group_id, $post_id );
						break;
					case 'description':
						$author_info .= $this->show_author_description( $author_group_id, $post_id );
						break;

					default:
						break;
				}
			}
			return $author_info;
		}

		/**
		 * Display author name
		 * @param type $author_group_id
		 */
		function show_author_name( $author_group_id, $post_id = null ){
			$ret = '';
			if ( empty( $this->authors_collection[$author_group_id]['authorname'] ) ) {
//echo '222 '.$author_group_id.'<br> ';
				$this->authors_collection[$author_group_id]['authorname'] = get_field( 'authorname' . $author_group_id, $post_id );
				/* if there is an author, print it, and save its value in the array.
				 * We'll check that array before printing an image.
				 * If an author name exists, but no image, print a default image
				 */
			}
			//echo $this->authors_collection[$author_group_id]['authorname'].'<br>';
			$ret.= '<a href="' . esc_url( add_query_arg( 'author', urlencode( $this->authors_collection[$author_group_id]['authorname'] ), get_page_link( $this->authors_page_id ) ) ) . '">' . $this->authors_collection[$author_group_id]['authorname'] . '</a>';

			return $ret;
		}

		/**
		 * Display author image
		 * @param type $author_group_id
		 */
		function show_author_image( $author_group_id, $post_id = null ){

			$ret = '';

			$value = get_field( 'authorimage' . $author_group_id, $post_id );
			if ( isset( $value['url'] ) ) {
				// get the image, with the size of fast_science
				$ret = wp_get_attachment_image( $value['id'], 'scientific-2016-fast_science_size' );
			}
			else {
				//echo '111';
				/* No image could mean no author, or author whose information hasn't been fetched yet.
				 * Check if it was fetched - if empty, then was fetched and no info - don't print image.
				 * If not set - get the name, and if exists - save it and print image
				 */
				//if ( ! empty( $this->authors_collection[$author_group_id]['authorname'] ) ) {
					$ret = '<img src="' . plugin_dir_url( __FILE__ ) . '../images/default_author.png" alt="' . __( 'default featured image', 'mop-acf-authors' ) . '">';
				//}
			}
			return $ret;
		}

		/**
		 * Display author name
		 * @param type $author_group_id
		 */
		function show_author_description( $author_group_id, $post_id = null ){
			$ret = '';
			$value = get_field( 'authordescription' . $author_group_id, $post_id );
			if ( ! empty( $value ) ) {
				$ret.= '<span>' . $value . '</span>';
			}
			return $ret;
		}

		/* http://wordpress.stackexchange.com/questions/3396/create-custom-page-templates-with-plugins */

		function page_template( $page_template ){
			if ( is_page( 'author-page' ) || is_page( $this->authors_page_id ) ) {
				$page_template = dirname( __FILE__ ) . '/author_page.php';
			}
			return $page_template;
		}

		public function posts_by_author(){
			$author_name = filter_input( INPUT_GET, 'author' ); // TODO: check against whitelist
			$meta_query = $this->author_meta_query( $author_name, 'LIKE' );

			$args = array(
				'posts_per_page' => -1,
				'post__not_in' => array( get_option( 'sticky_posts' ), 'posts' ),
				'ignore_sticky_posts' => 1,
				'meta_query' => $meta_query
			);
			return new WP_Query( $args );
		}

	}
