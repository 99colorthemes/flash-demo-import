<?php

defined( 'ABSPATH' ) || exit;

/**
 * Flash demo import page
 *
 * Class Flash_Demo_Import_Page
 */
class Flash_Demo_Import_Page {

    function init() {
        if ( !current_user_can('edit_theme_options') ) {
            return;
        }
        add_theme_page(
            esc_html__('Flash Demo Import', 'flash-demo-import'),
            esc_html__('Flash Demo Import', 'flash-demo-import'),
            'manage_options',
            'flash-demo-import.php',
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
            FLASH_DEMO_IMPORT_PLUGIN_DOMAIN,
            FLASH_DEMO_IMPORT_URL . 'assets/flash-demo-import.js', array( 'jquery'),
            FLASH_DEMO_IMPORT_VERSION, true );

        wp_localize_script( FLASH_DEMO_IMPORT_PLUGIN_DOMAIN, 'flash_demo_import_var', array(
            'btn_importing' => esc_html__( 'Importing...', 'flash-demo-import' ),
            'btn_imported' => esc_html__( 'Imported', 'flash-demo-import' ),
            'btn_retry' => esc_html__( 'Retry', 'flash-demo-import' ),
            'btn_live_preview' => esc_html__( 'Live Preview', 'flash-demo-import' ),
            'import_failed_notice' => esc_html__( 'Failed to import.', 'flash-demo-import' ),
            'nonce' => wp_create_nonce( 'flash_demo_import_nonce' )
        ) );

    }

    /**
     * Styles
     */
    function enqueue_styles() {
        wp_enqueue_style(FLASH_DEMO_IMPORT_PLUGIN_DOMAIN,
            FLASH_DEMO_IMPORT_URL . 'assets/flash-demo-import.css', array(),
            FLASH_DEMO_IMPORT_VERSION, 'all');
    }

    /**
     * page view
     */
    function page_view() {

        $flash_demo_import_api_calls = new Flash_Demo_Import_Api_Calls(fdi_get_theme_demos_url());
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Flash Demo Import', 'flash-demo-import' ); ?> (<?php echo esc_html(fdi_get_current_theme_name()); ?>)</h1>
        <?php
        if($flash_demo_import_api_calls->is_success()) {
            $theme = $flash_demo_import_api_calls->fetch_data()['data'];

            if($theme): ?>
                <div class="wp-filter hide-if-no-js">
                    <div class="filter-section">
                        <div class="filter-count">
                            <span class="count theme-count demo-count"><?php echo count($theme['theme_demos']); ?></span>
                        </div>
                        <ul class="filter-links categories">
                            <li>
                                <a href="#" data-sort="all" class="category-tab current" aria-current="page">
                                    <?php esc_html_e( 'All', 'flash-demo-import' ); ?>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="theme-browser content-filterable rendered">
                    <div class="themes wp-clearfix">
                        <?php

                        if($theme['theme_demos']):
                        foreach($theme['theme_demos'] as $data):
                            $flash_demo_import_complete = get_transient( 'flash_demo_import_complete' . intval($data['id']));
                        ?>
                            <div class="theme focus"
                                 data-theme-demo-id="<?php echo intval($data['id']); ?>"
                                 data-home-url="<?php echo esc_url(home_url()); ?>"
                                 data-theme-demo-title="<?php echo esc_html($data['name']); ?>"
                                 data-theme-demo-settings="<?php echo esc_html(json_encode($data['settings'])); ?>">
                                <div class="theme-screenshot">
                                    <img src="<?php echo esc_url($data['image']); ?>"
                                         alt="<?php echo esc_attr($data['name']); ?>">
                                </div>
                                <?php if($data['is_premium'] == 1 && $theme['is_premium'] == 0): ?>
                                    <span class="pro-ribbon">
                                        <?php esc_html_e( 'Premium', 'flash-demo-import' ); ?>
                                    </span>
                                <?php endif; ?>

                                <?php if($flash_demo_import_complete):  ?>
                                    <div class="theme-id-container">
                                        <h2 class="theme-name">
                                            <span> <?php esc_html_e( 'Imported', 'flash-demo-import' ); ?>:</span> <?php esc_html($data['name']); ?>
                                        </h2>
                                        <div class="theme-actions">
                                            <a class="button button-primary live-preview"
                                               target="_blank"
                                               href="<?php echo esc_url(home_url()); ?>">
                                                <?php esc_html_e( 'Live Preview', 'flash-demo-import' ); ?>
                                            </a>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <div class="theme-id-container">
                                        <h2 class="theme-name">
                                            <?php echo esc_html($data['name']); ?>
                                        </h2>
                                        <div class="theme-actions">
                                            <?php if(!$flash_demo_import_complete):  ?>
                                                <?php if($data['is_premium'] == 1 && $theme['is_premium'] == 0): ?>
                                                    <a class="button button-primary purchase-now"
                                                       href="<?php echo esc_url($theme['detail_link']);?>"
                                                       target="_blank">
                                                        <?php esc_html_e( 'Buy Now', 'flash-demo-import' ); ?>
                                                    </a>
                                                <?php else: ?>
                                                    <a class="button button-primary hide-if-no-js flash-demo-import-button"
                                                       href="javascript:void(0);">
                                                        <?php esc_html_e( 'Import', 'flash-demo-import' ); ?>
                                                    </a>
                                                <?php endif; ?>
                                             <?php else: ?>
                                                <a class="button button-primary hide-if-no-js flash-demo-import-button"
                                                   href="javascript:void(0);"
                                                   data-theme-demo-id="<?php echo absint($data['id']); ?>">
                                                    <?php esc_html_e( 'Retry', 'flash-demo-import' ); ?>
                                                </a>
                                            <?php endif; ?>
                                            <a target="_blank" href="<?php echo esc_url($data['live_preview_link']); ?>" class="button preview install-theme-preview">
                                                <?php esc_html_e( 'Preview', 'flash-demo-import' ); ?>
                                            </a>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php
                        endforeach;
                        else:
                        ?>
                            <p>
                                <?php esc_html_e( 'Demo not available for current activated theme.', 'flash-demo-import' ); ?>
                            </p>
                        <?php endif; ?>

                    </div>
                </div>
            <?php else: ?>
                <div class="welcome-panel">
                    <div class="welcome-panel-content">
                        <h2>
                            <?php esc_html_e( 'Demo is not available for the activated theme.', 'flash-demo-import' ); ?>
                        </h2>
                        <p class="about-description">
                            <?php esc_html_e( 'Sorry, No demos available to import for currently activated theme. Please download and activate the theme by 99colorthemes to see the list of demos to import.', 'flash-demo-import' ); ?>
                        </p>
                    </div>
                </div>
            <?php endif; ?>
            <?php } else { ?>
                <div class="welcome-panel">
                    <div class="welcome-panel-content">
                        <h2><?php esc_html_e( 'Whoops! Something went wrong.', 'flash-demo-import' ); ?></h2>
                        <p class="about-description">
                            <?php esc_html_e( 'Please refresh the page using', 'flash-demo-import' ); ?>
                            <strong>
                                <?php esc_html_e( 'Ctr + R', 'flash-demo-import' ); ?>
                            </strong>
                            <?php esc_html_e( 'or click', 'flash-demo-import' ); ?>
                            <a href=""><?php esc_html_e( 'here', 'flash-demo-import' ); ?> </a>.
                        </p>
                    </div>
                </div>
            <?php } ?>
        </div>
       <?php
    }
}