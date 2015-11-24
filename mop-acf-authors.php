<?php

	/*
	 * Plugin Name: Mop Acf Authors
	 * Version: 1.0
	 * Plugin URI: http://www.sciam.org.il/
	 * Description: This is a plugin for displaying random authors from the site.
	 * Author: ORT Israel MOP Team
	 * Author URI: http://www.creative.ort.org.il/
	 * Requires at least: 4.0
	 * Tested up to: 4.0
	 *
	 * Text Domain: mop-acf-authors
	 * Domain Path: /lang/
	 *
	 * @package WordPress
	 * @author ORT Israel MOP Team
	 * @since 1.0.0
	 */

	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}

	class Mop_Acf_Authors {

		/**
		 * The loader that's responsible for maintaining and registering all hooks that power
		 * the plugin.
		 *
		 * @since    1.0.0
		 * @access   protected
		 * @var      Pelepay_Form_Inserter_Loader    $loader    Maintains and registers all hooks for the plugin.
		 */
		protected $loader;

		/**
		 * The unique identifier of this plugin.
		 *
		 * @since    1.0.0
		 * @access   protected
		 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
		 */
		protected $plugin_name;
		protected $version;

		/**
		 * The name of the class that takes care of the front end
		 *
		 * @since    1.0.0
		 * @access   protected
		 * @var      string    $plugin_public    The string used to represent the frontend class of the plugin.
		 */
		protected $plugin_public = null;

		/**
		 * Define the core functionality of the plugin.
		 *
		 * Set the plugin name and the plugin version that can be used throughout the plugin.
		 * Load the dependencies, define the locale, and set the hooks for the Dashboard and
		 * the public-facing side of the site.
		 *
		 * @since    1.0.0
		 */
		public function __construct(){
			$this->plugin_name = 'mop-acf-authors';
			$this->version = '1.0.0';

			$this->load_dependencies();
			$this->define_public_hooks();
		}

		/**
		 * Load the required dependencies for this plugin.
		 *
		 * Include the following files that make up the plugin:
		 *
		 * - Plugin_Name_Loader. Orchestrates the hooks of the plugin.
		 * - Plugin_Name_i18n. Defines internationalization functionality.
		 * - Plugin_Name_Admin. Defines all hooks for the dashboard.
		 * - Plugin_Name_Public. Defines all hooks for the public side of the site.
		 *
		 * Create an instance of the loader which will be used to register the hooks
		 * with WordPress.
		 *
		 * @since    1.0.0
		 * @access   private
		 */
		private function load_dependencies(){

			/**
			 * Define internationalization functionality of the plugin.
			 */
			load_plugin_textdomain( 'mop-acf-authors', false, plugin_basename( dirname( __FILE__ ) ) . '/languages' );

			/**
			 * The class responsible for defining all actions that occur in the public-facing
			 * side of the site.
			 */
			require_once plugin_dir_path( __FILE__ ) . 'public/class-mop-acf-authors-public.php';
		}

		/**
		 * Register all of the hooks related to the public-facing functionality
		 * of the plugin.
		 *
		 * @since    1.0.0
		 * @access   private
		 */
		private function define_public_hooks(){
			$this->plugin_public = new Mop_Acf_Authors_Public( $this->get_plugin_name(), $this->get_version() );
			/* hook for Our Writers block */
			add_action( 'mop_acf_our_authors_plugin', array( $this->plugin_public, 'mop_acf_authors_plugin' ) );
			/* hook for showing all authors of a post */
			add_action( 'mop_acf_post_authors', array( $this->plugin_public, 'show_post_author_info' ) );
			/* hook for getting all posts by given author - used by page template */
			add_filter( 'mop_acf_posts_by_author', array( $this->plugin_public, 'posts_by_author' ) );
			/* hook for getting a specific author's info */
			add_filter( 'mop_acf_specific_author', array( $this->plugin_public, 'page_template_author_info' ), 10, 3 );
			/* hook for showing the author page template */
			add_filter( 'template_include', array( $this->plugin_public, 'page_template' ) );
		}

		/**
		 * The name of the plugin used to uniquely identify it within the context of
		 * WordPress and to define internationalization functionality.
		 *
		 * @since     1.0.0
		 * @return    string    The name of the plugin.
		 */
		public function get_plugin_name(){
			return $this->plugin_name;
		}

		/**
		 * The reference to the class that orchestrates the hooks with the plugin.
		 *
		 * @since     1.0.0
		 * @return    Plugin_Name_Loader    Orchestrates the hooks of the plugin.
		 */
		public function get_loader(){
			return $this->loader;
		}

		/**
		 * Retrieve the version number of the plugin.
		 *
		 * @since     1.0.0
		 * @return    string    The version number of the plugin.
		 */
		public function get_version(){
			return $this->version;
		}

	}

	function run_mop_acf_author(){
		$plugin = new Mop_Acf_Authors();
	}

	run_mop_acf_author();
