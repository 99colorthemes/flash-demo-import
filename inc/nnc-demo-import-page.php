<?php

defined( 'ABSPATH' ) || exit;

/**
 * Nnc demo import page
 *
 * Class Nnc_Demo_Import_Page
 */
class Nnc_Demo_Import_Page {

    function init() {
        if ( !current_user_can('edit_theme_options') ) {
            return;
        }
        add_theme_page(
            esc_html__('NNC Demo Import', 'nnc-demo-import'),
            esc_html__('NNC Demo Import', 'nnc-demo-import'),
            'manage_options',
            'nnc-demo-import.php',
            array($this, 'page_view')
        );

        add_filter( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ), 10, 1 );
        add_filter( 'admin_enqueue_scripts', array( $this, 'enqueue_styles' ), 10, 1 );
    }

    /**
     * Scripts
     */
    function enqueue_scripts() {
        wp_enqueue_script(
            NNC_DEMO_PLUGIN_NAME,
            NNC_DEMO_IMPORT_URL . 'assets/nnc-demo-import.js', array( 'jquery'),
            NNC_DEMO_VERSION, true );

        wp_localize_script( NNC_DEMO_PLUGIN_NAME, 'nnc_demo_import_var', array(
            'btn_importing' => esc_html__( 'Importing...', 'nnc-demo-import' ),
            'btn_imported' => esc_html__( 'Imported', 'nnc-demo-import' ),
            'btn_retry' => esc_html__( 'Retry', 'nnc-demo-import' ),
            'btn_live_preview' => esc_html__( 'Live Preview', 'nnc-demo-import' ),
            'import_failed_notice' => esc_html__( 'Failed to import.', 'nnc-demo-import' ),
            'nonce' => wp_create_nonce( 'nnc_demo_import_nonce' )
        ) );

    }

    /**
     * Styles
     */
    function enqueue_styles() {
        wp_enqueue_style(NNC_DEMO_PLUGIN_NAME,
            NNC_DEMO_IMPORT_URL . 'assets/nnc-demo-import.css', array(),
            NNC_DEMO_VERSION, 'all');
    }

    /**
     * Nnc demo impage page view
     */
    function page_view() {

        $nnc_demo_import_api_calls = new Nnc_Demo_Import_Api_Calls(nnc_get_theme_demos_url());

        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'NNC Demo Import', 'nnc-demo-import' ); ?> (<?php echo nnc_demo_import_get_current_theme_name(); ?>)</h1>
        <?php
        if($nnc_demo_import_api_calls->is_success()) {
            $theme = $nnc_demo_import_api_calls->fetch_data()['data'];

            if($theme): ?>
                <div class="wp-filter hide-if-no-js">
                    <div class="filter-section">
                        <div class="filter-count">
                            <span class="count theme-count demo-count"><?php echo count($theme['theme_demos']); ?></span>
                        </div>
                        <ul class="filter-links categories">
                            <li>
                                <a href="#" data-sort="all" class="category-tab current" aria-current="page">
                                    <?php esc_html_e( 'All', 'nnc-demo-import' ); ?>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="theme-browser content-filterable rendered">
                    <div id="nnc-demo-import-theme-demos" class="themes wp-clearfix">
                        <?php

                        if($theme['theme_demos']):
                        foreach($theme['theme_demos'] as $data):
                            $nnc_demo_importer_phases = get_transient( 'nnc_demo_importer_'.$data['id']);
                        ?>
                            <div class="theme focus"
                                 data-theme-demo-id="<?php echo absint($data['id']); ?>"
                                 data-home-url="<?php echo home_url(); ?>"
                                 data-theme-demo-title="<?php echo esc_html($data['name']); ?>">
                                <div class="theme-screenshot">
                                    <img src="<?php echo esc_url($data['image']); ?>" alt="<?php echo esc_attr($data['name']); ?>">
                                </div>
                                <?php if($data['is_premium']): ?>
                                    <span class="pro-ribbon">
                                        <?php esc_html_e( 'Premium', 'nnc-demo-import' ); ?>
                                    </span>
                                <?php endif; ?>

                                <?php if($nnc_demo_importer_phases == 4):  ?>
                                    <div class="theme-id-container">
                                        <h2 class="theme-name">
                                            <span> <?php esc_html_e( 'Imported', 'nnc-demo-import' ); ?>:</span> <?php esc_html($data['name']); ?>
                                        </h2>
                                        <div class="theme-actions">
                                            <a class="button button-primary live-preview"
                                               target="_blank"
                                               href="<?php echo home_url(); ?>">
                                                <?php esc_html_e( 'Live Preview', 'nnc-demo-import' ); ?>
                                            </a>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <div class="theme-id-container">
                                        <h2 class="theme-name">
                                            <?php echo esc_html($data['name']); ?>
                                        </h2>
                                        <div class="theme-actions">
                                            <?php if(!$nnc_demo_importer_phases):  ?>
                                                <?php if($data['is_premium']): ?>
                                                    <a class="button button-primary purchase-now" href="#" target="_blank">
                                                        <?php esc_html_e( 'Buy Now', 'nnc-demo-import' ); ?>
                                                    </a>
                                                <?php else: ?>
                                                    <a class="button button-primary hide-if-no-js nnc-demo-import-button"
                                                       href="javascript:void(0);">
                                                        <?php esc_html_e( 'Import', 'nnc-demo-import' ); ?>
                                                    </a>
                                                <?php endif; ?>
                                             <?php else: ?>
                                                <a class="button button-primary hide-if-no-js nnc-demo-import-button"
                                                   href="javascript:void(0);"
                                                   data-theme-demo-id="<?php echo absint($data['id']); ?>">
                                                    <?php esc_html_e( 'Retry', 'nnc-demo-import' ); ?>
                                                </a>
                                            <?php endif; ?>
                                            <button class="button preview install-theme-preview">
                                                <?php esc_html_e( 'Preview', 'nnc-demo-import' ); ?>
                                            </button>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php
                        endforeach;
                        else:
                        ?>
                            <p>
                                <?php esc_html_e( 'Demo not available for current activated theme.', 'nnc-demo-import' ); ?>
                            </p>
                        <?php endif; ?>

                    </div>
                </div>
            <?php else: ?>
                <div class="welcome-panel">
                    <div class="welcome-panel-content">
                        <h2>
                            <?php esc_html_e( 'Demo is not available for the activated theme.', 'nnc-demo-import' ); ?>
                        </h2>
                        <p class="about-description">
                            <?php esc_html_e( 'Sorry, No demos available to import for currently activated theme. Please download and activate the theme by 99colorthemes to see the list of demos to import.', 'nnc-demo-import' ); ?>
                        </p>
                    </div>
                </div>
            <?php endif; ?>
            <?php } else { ?>
                <div class="welcome-panel">
                    <div class="welcome-panel-content">
                        <h2><?php esc_html_e( 'Whoops! Something went wrong.', 'nnc-demo-import' ); ?></h2>
                        <p class="about-description">
                            <?php esc_html_e( 'Please refresh the page using', 'nnc-demo-import' ); ?>
                            <strong>
                                <?php esc_html_e( 'Ctr + R', 'nnc-demo-import' ); ?>
                            </strong>
                            <?php esc_html_e( 'or click', 'nnc-demo-import' ); ?>
                            <a href=""><?php esc_html_e( 'here', 'nnc-demo-import' ); ?> </a>.
                        </p>
                    </div>
                </div>
            <?php } ?>
        </div>
       <?php
    }
}