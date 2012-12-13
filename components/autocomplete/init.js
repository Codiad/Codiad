(function (global, $) {

    var TokenIterator = require('ace/token_iterator').TokenIterator;
    var eventManager = require('ace/lib/event').eventManager;


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

        isVisible: false,

        init: function () {
            var _this = this;

            // eventManager.addListener(text, "input", _this.onTextInput);
        },

        suggest: function () {
            var _this = this;

            var editor = codiad.editor.getActive();
            var session = editor.getSession();
            var doc = session.getDocument();

            if (this.isVisible) {
                this.hide();
            }

            var position = editor.getCursorPosition();

            /* Extract the word being typed. It is somehow the prefix of the
             * wanted full word. Make sure we only keep one word. */
            var prefix = session.getTokenAt(position.row, position.column).value;
            prefix = prefix.split(this.wordRegex).slice(-1)[0];
            console.log(prefix);

            /* Build and order the suggestions themselves. */
            // TODO cache suggestions and augment them incrementally.
            var suggestionsAndDistance = this.getSuggestions(position);
            var suggestions = this.rankSuggestions(prefix, suggestionsAndDistance);
            console.log(suggestions);


            var popupContent = $('#autocomplete #suggestions');
            $.each(suggestions, function (index, suggestion) {
                popupContent.append('<li class="suggestion">' + suggestion + '</li>');
            });

            // Show the completion popup.
            this.show();

            // handle click-out autoclosing.
            var fn = function () {
                _this.hide();
                $(window).off('click', fn);
            };
            $(window).on('click', fn);

        },

        show: function () {
            this.isVisible = true;
            var popup = $('#autocomplete');
            popup.css({'top': this._computeTopOffset(), 'left': this._computeLeftOffset()});
            popup.slideToggle('fast');
        },

        hide: function () {
            this.isVisible = false;
            $('#autocomplete').hide();
            $('.suggestion').remove();
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

            /* Get all the text, put a marker at the cursor position. The
             * marker uses word character so that it won't be discarded by a
             * word split. */
            var markerString = '__autocomplete_marker__';
            var text = doc.getLines(0, position.row - 1).join("\n") + "\n";
            var currentLine = doc.getLine(position.row);
            text += currentLine.substr(0, position.column);
            text += markerString;
            if (position.column === currentLine.length) {
                // position is at end of line, add a break line.
                text += "\n";
            }
            text += currentLine.substr(position.column + 1);
            text += doc.getLines(position.row + 1, doc.getLength()).join("\n") + "\n";

            /* Split the text into words. */
            var suggestions = text.split(this.wordRegex);

            /* Get the index of the word at the cursor position. */
            var markerIndex = 0;
            var markedWord = '';
            $.each(suggestions, function (index, value) {
                if (value.search(markerString) !== -1) {
                    markerIndex = index;
                    markedWord = value;
                    return false;
                }
            });

            /* Build an objet associating the suggestions with their distance
             * to the word at cursor position. */
            var suggestionsAndDistance = {};
            $.each(suggestions, function (index, suggestion) {
                var distance = Math.abs(index - markerIndex);
                console.log(distance);
                suggestionsAndDistance[suggestion] = distance;
            });

            /* Remove from the suggestions the word under the cursor. */
            delete suggestionsAndDistance[markedWord];

            return suggestionsAndDistance;
        },

        /* Given an object associating suggestions and their distances to the
         * word under the cursor (the prefix), return a ranked array of
         * suggestions with the best match first. The suggestions are ranked
         * based on how much they match the given prefix and their distances to
         * the prefix. The suggestions with a score lower than the maximum
         * score will be discarded. */
        rankSuggestions: function (prefix, suggestionsAndDistance) {
            /* Initialize maxScore to one to ensure removing the non matching
             * suggestions (those with a zero score). */
            var maxScore = 1;
            var ranks = {};
            var suggestionsAndMatchScore = {};
            for (var suggestion in suggestionsAndDistance) {
                if (suggestionsAndDistance.hasOwnProperty(suggestion)) {
                    var score = this.simpleMatchScorer(prefix, suggestion);
                    if (score > maxScore) {
                        maxScore = score;
                    }

                    suggestionsAndMatchScore[suggestion] = score;
                }
            }

            /* Remove the suggestions with a score lower than the maximum
             * score. */
            for (suggestion in suggestionsAndMatchScore) {
                if (suggestionsAndMatchScore.hasOwnProperty(suggestion)) {
                    if (suggestionsAndMatchScore[suggestion] < maxScore) {
                        delete suggestionsAndMatchScore[suggestion];
                    }
                }
            }
            
            /* Now for each suggestion we have its matching score and its
             * distance to the word under the cursor. So compute its final
             * score as a combination of both. */
            for (suggestion in suggestionsAndMatchScore) {
                if (suggestionsAndMatchScore.hasOwnProperty(suggestion)) {
                    ranks[suggestion] = suggestionsAndMatchScore[suggestion] -
                                            suggestionsAndDistance[suggestion];
                }
            }

            /* Make an array of suggestions and make sure to rank them in the
             * ascending scores order. */
            var suggestions = [];
            for (suggestion in ranks) {
                if (ranks.hasOwnProperty(suggestion)) {
                    suggestions.push(suggestion);
                }
            }
            
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

