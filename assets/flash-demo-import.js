
jQuery( document ).ready( function ( $ ) {
    //Demo importing object
    var flash_demo_import_object = {
        data:function () {
          this.clicked_button = null;
          this.flash_demo_importer_counter = 1;
          this.counterInterval = null;
          this.main_theme = null;
        },
        init: function() {

            this.data();

            var demo_import_button =  $('.flash-demo-import-button');

            if(demo_import_button.length > 0) {
                demo_import_button.click(function (e) {
                    e.preventDefault();

                    this.clicked_button = $(e.currentTarget);
                    this.clicked_button.addClass('updating-message');
                    this.main_theme = $(e.currentTarget).parents('.theme');

                    this.startingCounter(38, 1200);

                    this.import_demo_data();

                }.bind(this));
            }
        },
        /**
         * Import demo
         */
        import_demo_data: function() {
            if(this.main_theme == null) {return;}

            var demo_settings = this.main_theme.data('theme-demo-settings');

            this.import_demo_ajax_call(demo_settings)

        },
        import_demo_ajax_call: function(demo_settings) {

            if(this.main_theme == null) {return;}

            var theme_demo_id = this.main_theme.data('theme-demo-id');
            var home_url = this.main_theme.data('home-url');
            var demo_title = this.main_theme.data('theme-demo-title');

            this.ajax_call(theme_demo_id, demo_settings)
                .done(function (r) {
                    if(this.flash_demo_importer_counter != demo_settings.progress_bar_limit) {
                        clearInterval(this.counterInterval);
                    }
                    if(demo_settings.settings) {
                        this.startingCounter(
                            demo_settings.progress_bar_end,
                            demo_settings.progress_bar_end_speed,
                            demo_settings.progress_bar_start,
                            demo_settings.progress_bar_start_speed
                        );
                        this.import_demo_ajax_call(demo_settings.settings)
                    } else {
                        this.startingCounter(
                            demo_settings.progress_bar_end,
                            demo_settings.progress_bar_end_speed
                        );

                        var demo_imported_html = ' <h2 class="theme-name">\n' +
                            '<span>'+flash_demo_import_var.btn_imported+':</span> \n' +
                            demo_title +
                            '</h2>\n' +
                            '<div class="theme-actions">\n' +
                            '<a class="button button-primary live-preview"\n' +
                            'target="_blank"\n' +
                            'href="'+home_url+'">\n' +
                            flash_demo_import_var.btn_live_preview+'\n' +
                            '</a>\n' +
                            '</div>';

                        this.main_theme.find('.theme-id-container').html('').html(demo_imported_html)
                    }
                }.bind(this)).fail(function( res ) { this.handle_ajax_call_fail(res); }.bind(this));
        },

        handle_ajax_call_fail: function (res) {
            clearInterval(this.counterInterval);

            var response = $.parseJSON(res.responseText);
            var msg = flash_demo_import_var.import_failed_notice;

            if(response.status === 'forbidden_api_call') {
                msg = response.message;
            }
            var ajax_fail = '<div class="notice update-message notice-error notice-alt"><p>' + msg + '</p></div>';
            this.clicked_button.removeClass('updating-message');

            var theme_browser = $('.theme-browser');
            var notice = theme_browser.find('.notice-error');

            if(notice.length > 0){
                notice.remove();
            }

            theme_browser.prepend(ajax_fail);
            this.clicked_button.text('').text(flash_demo_import_var.btn_retry);
        },

        startingCounter: function (counter_limit, speed_limit, next_counter_limit = null, next_speed_limit = null) {
            if(this.clicked_button == null) {return;}

            this.counterInterval = setInterval(function () {
                if(this.flash_demo_importer_counter > counter_limit) {
                    clearInterval(this.counterInterval);
                    if(next_counter_limit !== null) {
                        this.startingCounter(next_counter_limit, next_speed_limit)
                    }
                }

                this.flash_demo_importer_counter = this.flash_demo_importer_counter + 1;
                this.clicked_button.text('').text(flash_demo_import_var.btn_importing + ' | '+ this.flash_demo_importer_counter + '%');
            }.bind(this), speed_limit);

        },

        ajax_call: function(theme_demo_id, demo_settings) {
           return $.ajax({
                type: "POST",
                url: ajaxurl,
                data: {
                    theme_demo_id: theme_demo_id,
                    action : demo_settings.action,
                    nounce : flash_demo_import_var.nonce,
                    page: demo_settings.page,
                    limit: demo_settings.limit,
                }
            });
        }
    };

    flash_demo_import_object.init();
});