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

            var editor = codiad.editor.getActive();
            var session = editor.getSession();
            var doc = session.getDocument();

            var position = editor.getCursorPosition();

            /* Extract the word being typed. It is somehow the prefix of the
             * wanted full word. */
            var prefix = session.getTokenAt(position.row, position.column).value;

            /* Build and order the suggestions themselves. */
            // TODO cache suggestions and augment them incrementally.
            var suggestions = this.getSuggestions(position);
            suggestions = this.rankSuggestions(prefix, suggestions);
            console.log(suggestions);


            var popupContent = $('#autocomplete #suggestions');
            $.each(suggestions, function (index, suggestion) {
                popupContent.append('<li class="suggestion">' + suggestion + '</li>');
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
                var fontSize = codiad.editor.getActive().container.style.fontSize.replace('px', '');
                var interLine = 1.7;
                cursor = $(cursor[0]);
                var top = cursor.offset().top + fontSize * interLine;
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

        /* Get suggestions of completion for the current position in the
         * document. */
        getSuggestions: function (position) {
            var editor = codiad.editor.getActive();
            var session = editor.getSession();
            var doc = session.getDocument();

            // The following is just for testing purpose.
            // var iterator = new TokenIterator(session, 0, 0);
            // console.log(iterator.getCurrentToken());
            // iterator.stepForward();
            // console.log(iterator.getCurrentToken());

            /* FIXME For now, make suggestions on the whole file content except
             * the current token. Might be a little bit smarter, e.g., remove
             * all the keywords associated with the current language. */

            /* Get the token corresponding to the given position. */
            var token = session.getTokenAt(position.row, position.column);

            /* Get all the text minus token. */
            var text = doc.getLines(0, position.row - 1).join("\n") + "\n";
            var currentLine = doc.getLine(position.row);
            text += currentLine.substr(0, token.start);
            text += currentLine.substr(token.start + token.value.length);
            text += doc.getLines(position.row + 1, doc.getLength()).join("\n") + "\n";

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
        },

        /* Rank an array of suggestions based on how much the suggestion
         * matches the given prefix. The suggestions with a score lower than
         * the maximum score will be discarded. Best match will be first in the
         * ranked array. Also return the ranked array. */
        rankSuggestions: function (prefix, suggestions) {
            /* Initialize maxScore to one to ensure removing the non matching
             * suggestions (those with a zero score). */
            var maxScore = 1;
            var ranks = {};
            for (var i = 0; i < suggestions.length; ++i) {
                var score = this.simpleMatchScorer(prefix, suggestions[i]);
                if (score > maxScore) {
                    maxScore = score;
                }

                ranks[suggestions[i]] = score;
            }

            /* Remove the suggestions with a score lower than the maximum
             * score. */
            for (i = suggestions.length - 1; i >= 0; i--) {
                if (ranks[suggestions[i]] < maxScore) {
                    suggestions.splice(i, 1);
                }
            }

            /* Make sure to rank in the ascending scores order. */
            suggestions.sort(function (firstSuggestion, secondSuggestion) {
                return ranks[secondSuggestion] - ranks[firstSuggestion];
            });

            return suggestions;
        },

        /* Return the number of letters in suggestion that match prefix. For
         * instance, this.simpleMatchScorer(cod, codiad) will return 3. If
         * suggestion is shorter than prefix, return a score of zero. */
        simpleMatchScorer: function (prefix, suggestion) {
            if (suggestion.length < prefix.length) {
                return 0;
            } else if (suggestion === prefix) {
                return prefix.length;
            } else {
                var score = 0;
                for (var i = 0; i < prefix.length; ++i) {
                    if (suggestion[i] === prefix[i]) {
                        ++score;
                    } else {
                        break;
                    }
                }

                return score;
            }
        }
    };

})(this, jQuery);

