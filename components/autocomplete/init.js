(function (global, $) {

    var TokenIterator = require('ace/token_iterator').TokenIterator;

    var codiad = global.codiad;

    $(function () {
        codiad.autocomplete.init();
    });

    //////////////////////////////////////////////////////////////////
    //
    // Autocomplete Component for Codiad
    // ---------------------------------
    // Show a popup with word completion suggestions.
    //
    //////////////////////////////////////////////////////////////////

    codiad.autocomplete = {

        wordRegex: /[^a-zA-Z_0-9\$]+/,

        init: function () {
            var _this = this;

            // $('#autocomplete').append('<ul id="suggestions"> <li class="suggestion">pipi</li> <li class="suggestion">popo</li> <li class="suggestion">pupu</li> <li class="suggestion">pypy</li> </ul>');

        },

        suggest: function () {
            var _this = this;

            // TODO cache suggestions and augment them incrementally.
            var suggestions = this.getSuggestions();
            // this.rankSuggestions();


            var popupContent = $('#autocomplete #suggestions');
            $.each(suggestions, function (index, value) {
                popupContent.append('<li class="suggestion">' + value + '</li>');
            });

            // Show the completion popup.
            var popup = $('#autocomplete');
            popup.css({'top': _this._computeTopOffset(), 'left': _this._computeLeftOffset()});
            popup.slideToggle('fast');

            // handle click-out autoclosing.
            var fn = function () {
                popup.hide();
                $(window).off('click', fn);
                $('.suggestion').remove();
            };
            $(window).on('click', fn);

        },

        complete: function () {
            alert('Not implemented.');
        },

        _computeTopOffset: function () {
            /* FIXME How to handle multiple cursors? This seems to compute the
             * offset using the position of the last created cursor. */
            var cursor = $('.ace_cursor');
            if (cursor.length > 0) {
                cursor = $(cursor[0]);
                var top = cursor.offset().top;
                return top;
            }
        },

        _computeLeftOffset: function () {
            /* FIXME How to handle multiple cursors? This seems to compute the
             * offset using the position of the last created cursor. */
            var cursor = $('.ace_cursor');
            if (cursor.length > 0) {
                cursor = $(cursor[0]);
                var left = cursor.offset().left;
                return left;
            }
        },

        getSuggestions: function () {
            var session = codiad.editor.getActive().getSession();
            var doc = session.getDocument();

            var iterator = new TokenIterator(session, 0, 0);
            console.log(iterator.getCurrentToken());

            iterator.stepForward();
            console.log(iterator.getCurrentToken());

            /* FIXME For now, make suggestions on the whole file content. Might
             * be a little bit smarter, e.g., remove the current partial word
             * and all the keywords associated with the current language.
             * Note: have a look at EditSession.getTokenAt(row, column). */
            var text = doc.getValue().trim();

            /* Split the text into words. */
            var identifiers = text.split(this.wordRegex);

            /* Remove duplicates and empty strings. */
            var uniqueIdentifiers = [];
            $.each(identifiers, function (index, identifier) {
                if (identifier && $.inArray(identifier, uniqueIdentifiers) === -1) {
                    uniqueIdentifiers.push(identifier);
                }
            });

            return uniqueIdentifiers;
        }

    };

})(this, jQuery);

