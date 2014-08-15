/*
 *  Copyright (c) Codiad, distributed
 *  as-is and without warranty under the MIT License. See
 *  [root]/license.txt for more. This information must remain intact.
 */

(function(global, $){
    
    $(function() {    
        codiad.settings.init();
    });

    codiad.settings = {

        controller: 'components/settings/controller.php',

        init: function() {
            var _this = this;

            /*
             *  Storage Event:
             *  Note: Event fires only if change was made in different window and not in this one
             *  Details: http://dev.w3.org/html5/webstorage/#dom-localstorage
             *  
             *  Workaround for Storage-Event:
             */
			$('body').append('<iframe src="components/settings/dialog.php?action=iframe"></iframe>');

            //Load Settings
            this.load();

        },

        //////////////////////////////////////////////////////////////////
        // Save Settings
        //////////////////////////////////////////////////////////////////

        save: function() {
            var key, settings = {};
            var systemRegex = /^codiad/;
            var pluginRegex = /^codiad.plugin/;

            /* Notify listeners */
            amplify.publish('settings.save',{});

            var sync_system = (localStorage.getItem('codiad.settings.system.sync') == "true");
            var sync_plugin = (localStorage.getItem('codiad.settings.plugin.sync') == "true");

            if (sync_system || sync_plugin) {
                for (var i = 0; i < localStorage.length; i++) {
                    key = localStorage.key(i);
                    if (systemRegex.test(key) && !pluginRegex.test(key) && sync_system) {
                        settings[key] = localStorage.getItem(key);
                    }
                    if (pluginRegex.test(key) && sync_plugin) {
                        settings[key] = localStorage.getItem(key);
                    }
                }
            }

            settings['codiad.settings.system.sync'] = sync_system;
            settings['codiad.settings.plugin.sync'] = sync_plugin;

            $.post(this.controller + '?action=save', {settings: JSON.stringify(settings)}, function(data){
                parsed = codiad.jsend.parse(data);
            });
        },

        //////////////////////////////////////////////////////////////////
        // Load Settings
        //////////////////////////////////////////////////////////////////

        load: function() {
            $.get(this.controller + '?action=load', function(data){
                parsed = codiad.jsend.parse(data);
                if (parsed != 'error') {
                    $.each(parsed, function(i, item){
                        localStorage.setItem(i, item);
                    });
                    amplify.publish('settings.loaded', parsed);
                }
            });
        },

        //////////////////////////////////////////////////////////////////
        //
        // Show Settings Dialog
        //
        //  Parameter
        //
        //  data_file - {String} - Location of settings file based on BASE_URL
        //
        //////////////////////////////////////////////////////////////////

        show: function(data_file) {
            codiad.modal.load(400, 'components/settings/dialog.php?action=settings');
            codiad.modal.hideOverlay();
            codiad.modal.load_process.done(function(){
                if (typeof(data_file) == 'string') {
                    codiad.settings._showTab(data_file);
                }
                
                /* Notify listeners */
                amplify.publish('settings.dialog.show',{});
            });
        },

        //////////////////////////////////////////////////////////////////
        //
        // {Private} Show Specific Tab
        //
        //  Parameter
        //
        //  data_file - {String} - Location of settings file based on BASE_URL
        //
        //////////////////////////////////////////////////////////////////

        _showTab: function(data_file) {
            if (typeof(data_file) != 'string') {
                return false;
            }
            $('.settings-view .config-menu .active').removeClass('active');
            $('.settings-view .config-menu li[data-file="' + data_file + '"]').addClass('active');
            $('.settings-view .panels .active').hide().removeClass('active');
            //Load panel
            if ($('.settings-view .panel[data-file="' + data_file + '"]').length === 0) {
                $('.settings-view .panels').append('<div class="panel active" data-file="' + data_file + '"></div>');
                $('.settings-view .panel[data-file="' + data_file + '"]').load(data_file, function(){
                    //TODO Show and hide loading information
                    /* Notify listeners */
                    amplify.publish('settings.dialog.tab_loaded',data_file);
                });
            } else {
                $('.settings-view .panel[data-file="' + data_file + '"]').show().addClass('active');
            }
        }
    };

})(this, jQuery);