<?php

/**
 * Main class loader for initializing and  setting up the plugin.
 *
 * @since 0.1.0
 */
class ThemeSetup {

    /**
     * Initialise the program after everything is ready.
     *
     * @since 0.1.0
     */
    public static function init() {
        add_action( 'init', [ __CLASS__, 'init_assets' ] );
        // add_action( 'init', [ __CLASS__, 'init_gutenberg_custom_assets' ] );
        add_action( 'switch_theme', [ __CLASS__, 'flush_rewrite_rules' ] );
        add_action( 'after_switch_theme', [ __CLASS__, 'activate' ] );
        add_filter( 'Esq_assets_include_jquery', '__return_true' );

        self::init_theme_config();
        self::init_gutenberg_config();
    }

    /**
     * Checks program environment to see if all dependencies are available. If at least one
     * dependency is absent, deactivate the theme.
     *
     * @since 0.1.0
     */
    public static function activate() {
        global $wp_version;

        if ( version_compare( $wp_version, 'ESQUELETO_MINIMUM_WP_VERSION', '<' ) ) {
            if ( is_admin() ) :
                add_action(
                    'admin_notices',
                    function() {
                        ?>
                        <div class="notice notice-error">
                            <p><?php echo esc_html( 'Please update WordPress!' ); ?></p>
                        </div>
                        <?php
                    }
                );
            endif;
        }

        self::check_dependencies();
        self::flush_rewrite_rules();
    }

    /**
     * Dependency checks
     */
    public static function check_dependencies() {
        if ( is_admin() ) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';

            if ( is_plugin_active( 'metabox/metabox.php' ) ) {
                return;
            }

            add_action(
                'admin_notices',
                function () {
                    ?>
                    <div class="notice notice-error">
                        <p><?php echo esc_html( 'The metabox is not active!' ); ?></p>
                    </div>
                    <?php
                }
            );
        }
    }

    /**
     * Register the CPTs and flush the rewrite rules in order to have correct permalinks.
     *
     * @since 0.1.0
     */
    public static function flush_rewrite_rules() {
        self::init();
        flush_rewrite_rules();
    }

    /**
     * Init the CSS and JS assets.
     */
    public static function init_assets() {
        $version = self::get_version_number();
        // css
        $cssFilePath = glob( get_template_directory() . '/frontend/dist//main.min.*' );
        $cssFileURI = get_template_directory_uri() . '/frontend/dist/' . basename($cssFilePath[0]);
        // js

        $jsFilePath = glob( get_template_directory() . '/frontend/dist/app.min.*.js' );
        $jsFileURI = get_template_directory_uri() . '/frontend/dist/' . basename($jsFilePath[0]);


        $assets  = new \Esq\Assets(
            [
                'css_uri'          => $cssFileURI,
                'css_version'      => $version,
                'js_uri'           => 	$jsFileURI,
                'automatic_suffix' => false,
            ]

        // [
        // 	'css_uri'          => get_template_directory_uri() . '/frontend/dist/main.css',
        // 	'css_version'      => $version,
        // 	'js_uri'           => get_template_directory_uri() . '/frontend/dist/main.js',
        // 	'automatic_suffix' => false,
        // ]
        );

        $assets->load();
    }

    /**
     * Function that adds the custom CSS to the WP editor in the Dashboard.
     */
    public static function init_gutenberg_custom_assets() {
        add_action(
            'enqueue_block_assets',
            function() {
                if ( is_admin() ) {
                    wp_enqueue_style(
                        'custom-styles-editor',
                        get_stylesheet_directory_uri() . '/frontend/dist/main.css',
                        [
                            'wp-edit-blocks',
                        ],
                        '1.0'
                    );
                }
            }
        );
    }

    /**
     * Adds support to Post Featured images.
     * Adds Title Tag to the <HEAD>.
     * Adds HTML5 to wp frontend components.
     */
    public static function init_theme_config() {
        add_theme_support( 'post-thumbnails' );
        add_theme_support( 'title-tag' );
        add_theme_support( 'html5' );
    }

    /**
     * Gutenberg configurations.
     */
    public static function init_gutenberg_config() {
        /**
         * Add support for Gutenberg.
         */

        // Wide/Fullwidth images.
        add_theme_support( 'align-wide' );

        // Responsive embeds.
        add_theme_support( 'responsive-embeds' );

        // Default Gutenberg styles on Frontend.
        add_theme_support( 'wp-block-styles' );
    }

    /**
     * Function used to generate the version number based from the .deploy file.
     */
    private static function get_version_number() {
        $version_number = "1.0";
        return $version_number;
    }
}
