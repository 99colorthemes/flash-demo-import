
jQuery( document ).ready( function ( $ ) {

    var nnc_demo_importer_counter = 1;

    var counterInterval = null;

    function startingCounter(counter_limit, speed_limit, next_counter_limit = null, next_speed_limit = null) {
        counterInterval = setInterval(function () {
            if(nnc_demo_importer_counter > counter_limit) {
                clearInterval(counterInterval);
                if(next_counter_limit !== null) {
                    startingCounter(next_counter_limit, next_speed_limit)
                }
            }

            nnc_demo_importer_counter = nnc_demo_importer_counter + 1;
            $('.nnc-demo-import-button').text('').text(nnc_demo_import_var.btn_importing + ' | '+ nnc_demo_importer_counter + '%');
        }, speed_limit);
    }

    var nnc_demo_import_object = {
        init: function() {
            this.import_demo();
        },
        import_demo: function() {
            var demo_import_button =  $('.nnc-demo-import-button');
            if(demo_import_button.length > 0) {
                demo_import_button.click(function (e) {
                    e.preventDefault();

                    demo_import_button.addClass('updating-message');

                    var main_theme = $(e.currentTarget).parents('.theme');
                    var clicked_button =  $(e.currentTarget);

                    this.import_demos_ajax_call(clicked_button, main_theme);

                }.bind(this));
            }
        },
        import_demos_ajax_call :function (clicked_button, main_theme){
            var theme_demo_id = main_theme.data('theme-demo-id');
            var home_url = main_theme.data('home-url');
            var demo_title = main_theme.data('theme-demo-title');

            startingCounter(48, 900);

            this.ajax_call(theme_demo_id, 'nnc_demo_import_attachments')
                .done(function(r) {
                    //if counter is not complete speed up
                    if(nnc_demo_importer_counter != 50) {
                        clearInterval(counterInterval);
                    }

                    startingCounter(48, 200, 78, 600);

                    this.ajax_call(theme_demo_id, 'nnc_demo_import_contents')
                        .done(function (r) {
                            if(nnc_demo_importer_counter != 80) {
                                clearInterval(counterInterval);
                            }

                            startingCounter(78, 200, 88, 500);

                            this.ajax_call(theme_demo_id, 'nnc_demo_import_customizer')
                                .done(function (r) {

                                    if(nnc_demo_importer_counter != 90) {
                                        clearInterval(counterInterval);
                                    }
                                    startingCounter(88, 200, 97, 400);

                                    this.ajax_call(theme_demo_id, 'nnc_demo_import_widgets')
                                        .done(function (r) {
                                            if(nnc_demo_importer_counter != 100) {
                                                clearInterval(counterInterval);
                                            }
                                            startingCounter(97, 200);

                                            var demo_imported_html = ' <h2 class="theme-name">\n' +
                                                '<span>'+nnc_demo_import_var.btn_imported+':</span> \n' +
                                                demo_title +
                                                '</h2>\n' +
                                                '<div class="theme-actions">\n' +
                                                '<a class="button button-primary live-preview"\n' +
                                                'target="_blank"\n' +
                                                'href="'+home_url+'">\n' +
                                                nnc_demo_import_var.btn_live_preview+'\n' +
                                                '</a>\n' +
                                                '</div>';

                                            main_theme.find('.theme-id-container').html('')
                                                .html(demo_imported_html)

                                        }.bind(this)).fail(function( res ) {  this.handle_ajax_call_fail(clicked_button, res); }.bind(this));
                                }.bind(this)).fail(function( res ) {  this.handle_ajax_call_fail(clicked_button, res); }.bind(this));
                        }.bind(this)).fail(function( res ) {  this.handle_ajax_call_fail(clicked_button, res); }.bind(this));
                }.bind(this)).fail(function( res ) {  this.handle_ajax_call_fail(clicked_button, res); }.bind(this));
        },

        handle_ajax_call_fail: function (clicked_button, res) {
            clearInterval(counterInterval);
            var response = $.parseJSON(res.responseText);

            var msg = nnc_demo_import_var.import_failed_notice;

            if(response.status === 'forbidden_api_call') {
                msg = response.message;
            }
            var ajax_fail = '<div class="notice update-message notice-error notice-alt"><p>' + msg + '</p></div>';
            $('.nnc-demo-import-button').removeClass('updating-message');

            var theme_browser = $('.theme-browser');
            var notice = theme_browser.find('.notice-error');

            if(notice.length > 0){
                notice.remove();
            }

            theme_browser.prepend(ajax_fail);
            clicked_button.text('').text(nnc_demo_import_var.btn_retry);
        },

        ajax_call: function(theme_demo_id, action) {
           return $.ajax({
                type: "POST",
                url: ajaxurl,
                data: {
                    theme_demo_id: theme_demo_id,
                    action : action,
                    nounce : nnc_demo_import_var.nonce
                }
            });
        }
    };

    nnc_demo_import_object.init();
});

