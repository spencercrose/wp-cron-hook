<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://gov.bc.ca
 * @since      1.0.0
 *
 * @package    WP_Cron_Hook
 * @subpackage WP_Cron_Hook/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    WP_Cron_Hook
 * @subpackage WP_Cron_Hook/includes
 * @author     Spencer <spencer.rose@gov.bc.ca>
 */
class WP_Cron_Handler {

    protected $plugin_dir = WP_PLUGIN_DIR . '/wp-cron-hook/';

    /**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      WP_Cron_Hook_Loader    $loader    Maintains and registers all hooks for the plugin.
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

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;
    /**
     * @var array
     */
    private $settings;

    /**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'WP_CRON_HOOK_VERSION' ) ) {
			$this->version = WP_CRON_HOOK_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'wp-cron-hook';

		$this->load_dependencies();
		$this->define_admin_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 **
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once $this->plugin_dir . 'includes/class-wp-cron-hook-loader.php';

    /**
     * The class responsible for logging database errors and warnings.
     */
    require_once $this->plugin_dir . 'includes/class-wp-cron-hook-logger.php';

		$this->loader = new WP_Cron_Hook_Loader();

	}

  /**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

    if ( !is_admin() && current_user_can('administrator') ) {
      // load purge request JS
      wp_enqueue_script(
        $this->plugin_name,
        plugin_dir_url( __FILE__ ) . 'wpch-admin-purge.js',
        array( 'jquery' ),
        null,
        false
      );

      // create localized admin cache purge url
      wp_localize_script( $this->plugin_name, 'wpch_local_obj',
          array(
            'purge_url' => $this->wpch_get_purge_url( get_permalink() ),
            'purge_nonce' => wp_create_nonce( 'purge_nonce' )
          )
      );
    }
  }


	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

        // Create event logger
        $logger = new WP_Cron_Hook_Logger();

        // Add purge button to admin toolbar
        $this->loader->add_action('admin_bar_menu', $this, 'wpch_add_toolbar_items', 100);

        // Register update hook to clear NGINX cache
        $this->loader->add_action( 'save_post', $this, 'wpch_clear_cache_on_save', 10, 3 );

        // Register cron hook API routes
        $this->loader->add_action( 'rest_api_init', $this, 'wpch_register_routes' );

        // Script to send purge request
        $this->loader->add_action( 'wp_enqueue_scripts', $this, 'enqueue_scripts' );

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @return    WP_Cron_Hook_Loader    Orchestrates the hooks of the plugin.
	 *@since     1.0.0
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

  /**
   * Build purge request key into provided permalink
   **/
  private function wpch_get_purge_url( $permalink ) {
    $purge_key = getenv("NGINX_PURGE_KEY") ? getenv("NGINX_PURGE_KEY") : "purge";
    $url_array = explode('/', $permalink);
    array_splice($url_array, 3, 0, $purge_key);
    return implode('/', $url_array);
  }

  /**
   * Add purge cache button to admin toolbar
   **/

  public function wpch_add_toolbar_items( $admin_bar ){
    if ( !is_admin() && current_user_can('administrator') ) {
      $admin_bar->add_menu( array(
          'id'    => 'wpch_purge_cache',
          'title' => 'Purge Cache',
          'href'  => '#',
          'meta'  => array(
              'title' => __('Purge Cache'),
          ),
      ));
    }
  }

    /**
     * Clear NGINX cache
     **/

    public function wpch_clear_cache_on_save( $post_id, $post, $update ) {

      // get purge request url
      $permalink = get_permalink( $post_id );
      $url = $this->wpch_get_purge_url( $permalink );

      // send NGINX purge request
      if ($update) {
        $purge_request = "curl --insecure '" . $url . "' -H 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8' -H 'Accept-Language: en-CA,en-US;q=0.7,en;q=0.3' -H 'Accept-Encoding: gzip, deflate, br' -H 'Connection: keep-alive' -H 'Upgrade-Insecure-Requests: 1' -H 'Sec-Fetch-Dest: document' -H 'Sec-Fetch-Mode: navigate' -H 'Sec-Fetch-Site: none' -H 'Sec-Fetch-User: ?1' -H 'Pragma: no-cache' -H 'Cache-Control: no-cache' 2>/dev/null";
        shell_exec($purge_request);
      }
      return;

    }

    /**
     * Register WP REST API Cron route.
     **/

    public function wpch_register_routes( $args ) {
        $namespace = 'wpch/v1';

        // ping WP cron scripts
        register_rest_route( $namespace, '/cron', array(
            'methods' => 'GET',
            'callback' => array($this, 'wpch_run_cron'),
            'permission_callback' => function() {return true;},
        ) );

    }

    /**
     * Set up callback: Run cron scripts
     **/
    public function wpch_run_cron( $request_data ) {
        // run MailPoet plugin cron script
        // runs every minute
        $command_mailpoet = "php /bitnami/wordpress/wp-content/plugins/mailpoet/mailpoet-cron.php /opt/bitnami/wordpress/ 2>/dev/null";
        shell_exec($command_mailpoet);

        // run WP cron script
        // use random value to trigger cron every ten minutes
        $rand_select = rand(1, 4);
        $ran_wpcron = $rand_select == 4 ? 'yes' : 'no';
        if ( $rand_select == 4 ) {
            $command_wp = "cd /opt/bitnami/wordpress; php -q wp-cron.php 2>/dev/null";
            shell_exec($command_wp);
        }

        // send response
        return new WP_REST_Response(array(
            'status'  => 200,
            'message' => 'Ran cron scripts. WP Cron [' . $rand_select. ']::4 '. $ran_wpcron
        ));
    }


}
