/*
 *  Copyright (c) Codiad & Kent Safranski (codiad.com), distributed
 *  as-is and without warranty under the MIT License. See
 *  [root]/license.txt for more. This information must remain intact.
 */

(function (global, $) {

    var EventEmitter = ace.require('ace/lib/event_emitter').EventEmitter;
    var Range = ace.require('ace/range').Range;


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

        standardGoLineDownExec: null,

        standardGoLineUpExec: null,
        
        _suggestionCache: null,

        standardGoToRightExec: null,

        standardGoToLeftExec: null,

        standardIndentExec: null,

        init: function () {
            var _this = this;
            
            this.$onDocumentChange = this.onDocumentChange.bind(this);
            this.$selectNextSuggestion = this.selectNextSuggestion.bind(this);
            this.$selectPreviousSuggestion = this.selectPreviousSuggestion.bind(this);
            this.$complete = this.complete.bind(this);
            this.$hide = this.hide.bind(this);
            
            /* Catch click on suggestion */
            $('#autocomplete li').live('click', function () {
                $('#autocomplete li.active-suggestion').removeClass('active-suggestion');
                $(this).addClass('active-suggestion');
                _this.complete();
            });

            /* In debug mode, run some tests here. */
            // this._testSimpleMatchScorer();
            // this._testFuzzyMatcher();
        },

        suggest: function () {
            var _this = this;

            var cursorPosition = this._getEditor().getCursorPosition();
            var foundSuggestions = this.updateSuggestions(cursorPosition);
            if (foundSuggestions) {
                this.addListenerToOnDocumentChange();

                // Show the completion popup.
                this.show();

                // handle click-out autoclosing.
                var fn = function () {
                    _this.hide();
                    $(window).off('click', fn);
                };
                $(window).on('click', fn);
            } else {
                this.clearSuggestionCache();
            }
        },

        /* Update the suggestions for the word at the given position. Return true if
         * some suitable suggestions could be found, false if not. */
        updateSuggestions: function (position) {
            var _this = this;

            var session = this._getEditSession();

            /* Extract the word being typed. Keep only the part of the word
             * which is before the given position. It is somehow the prefix of
             * the wanted full word. Make sure we only keep one word. */
            var token = session.getTokenAt(position.row, position.column);
            if (!token) {
                /* No word at the given position. */
                this.removeSuggestions();
                return false;
            }

            var prefix = token.value.substr(0, position.column - token.start);
            prefix = prefix.split(this.wordRegex).slice(-1)[0];
            if (prefix === '') {
                /* No word at the given position. */
                this.removeSuggestions();
                return false;
            }

            /* Build and order the suggestions themselves. */
            // TODO cache suggestions and augment them incrementally.
            var suggestionsAndDistance = this.getSuggestions(position);
            var suggestions = this.rankSuggestions(prefix, suggestionsAndDistance);
            if (suggestions.length < 1) {
                /* No suitable suggestions found. */
                this.removeSuggestions();
                return false;
            }

            /* Remove the existing suggestions and populate the popup with the
             * updated ones. */
            this.removeSuggestions();
            var popupContent = $('#autocomplete #suggestions');
            $.each(suggestions, function (index, suggestion) {
                /* First get rid of the suggestion suffix. */
                suggestion = suggestion.slice(0, -1);

                var indexes = _this.getMatchIndexes(prefix, suggestion);
                $.each(indexes.reverse(), function (index, matchIndex) {
                    suggestion = suggestion.substr(0, matchIndex) +
                    '<span class="matched">' +
                    suggestion.substr(matchIndex, 1) +
                    '</span>' +
                    suggestion.substr(matchIndex + 1);
                });
                popupContent.append('<li class="suggestion">' + suggestion + '</li>');
            });

            this.selectFirstSuggestion();

            return true;
        },

        show: function () {
            this.isVisible = true;

            var popup = $('#autocomplete');
            popup.css({
                'top': this._computeTopOffset(),
                'left': this._computeLeftOffset(),
                'font-family': $('.ace_editor').css('font-family'),
                'font-size': $('.ace_editor').css('font-size')
            });
            popup.slideToggle('fast', function(){
                $(this).css('overflow', '');
            });

            this.addKeyboardCommands();
        },

        hide: function () {
            this.isVisible = false;

            $('#autocomplete').hide();
            this.removeSuggestions();
            this.clearSuggestionCache();

            this.removeListenerToOnDocumentChange();
            this.removeKeyboardCommands();
        },

        /* Return a jQuery object containing the currently selected suggestion. */
        getSelectedSuggestion: function () {
            var selectedSuggestion = $('#autocomplete li.suggestion.active-suggestion');

            if (selectedSuggestion.length < 1) {
                alert(i18n('No suggestion selected. Might be a bug.'));
            } else if (selectedSuggestion.length > 1) {
                alert(i18n('More than one suggestions selected. Might be a bug.'));
            }

            return selectedSuggestion;
        },

        selectFirstSuggestion: function () {
            var firstChild = $('li.suggestion:first-child');
            firstChild.addClass('active-suggestion');
            this._ensureVisible(firstChild, $('#autocomplete'));
        },

        selectLastSuggestion: function () {
            var lastChild = $('li.suggestion:last-child');
            lastChild.addClass('active-suggestion');
            this._ensureVisible(lastChild, $('#autocomplete'));
        },

        selectNextSuggestion: function () {
            var selectedSuggestion = this.getSelectedSuggestion();
            selectedSuggestion.removeClass('active-suggestion');
            var nextSuggestion = selectedSuggestion.next();
            if (nextSuggestion.length > 0) {
                nextSuggestion.addClass('active-suggestion');
                this._ensureVisible(nextSuggestion, $('#autocomplete'));
            } else {
                /* The currently selected suggestion is the last one.
                 * Go back to first one. */
                this.selectFirstSuggestion();
            }
        },

        selectPreviousSuggestion: function () {
            var selectedSuggestion = this.getSelectedSuggestion();
            selectedSuggestion.removeClass('active-suggestion');
            var previousSuggestion = selectedSuggestion.prev();
            if (previousSuggestion.length > 0) {
                previousSuggestion.addClass('active-suggestion');
                this._ensureVisible(previousSuggestion, $('#autocomplete'));
            } else {
                /* The currently selected suggestion is the first one.
                 * Go back to last one. */
                this.selectLastSuggestion();
            }
        },

        addListenerToOnDocumentChange: function () {
            var session = this._getEditSession();
            session.addEventListener('change', this.$onDocumentChange);
        },

        removeListenerToOnDocumentChange: function () {
            var session = this._getEditSession();
            session.removeEventListener('change', this.$onDocumentChange);
        },

        onDocumentChange: function (e) {
            if (e.data.text.search(/^\s+$/) !== -1) {
                this.hide();
                return;
            }

            var position = null;
            if (e.data.action === 'insertText') {
                position = e.data.range.end;
            } else if (e.data.action === 'removeText') {
                position = e.data.range.start;
            } else {
                alert('Unkown document change action.');
            }

            var foundSuggestions = this.updateSuggestions(position);
            if (!foundSuggestions) {
                this.hide();
            }
        },

        addKeyboardCommands: function () {
            var _this = this;
            var commandManager = this._getEditor().commands;

            /* Save the standard commands that will be overwritten. */
            this.standardGoLineDownExec = commandManager.commands.golinedown.exec;
            this.standardGoLineUpExec = commandManager.commands.golineup.exec;

            this.standardGoToRightExec = commandManager.commands.gotoright.exec;
            this.standardGoToLeftExec = commandManager.commands.gotoleft.exec;
            this.standardIndentExec = commandManager.commands.indent.exec;

            /* Overwrite with the completion specific implementations. */
            commandManager.commands.golinedown.exec = this.$selectNextSuggestion;
            commandManager.commands.golineup.exec = this.$selectPreviousSuggestion;

            commandManager.commands.gotoright.exec = this.$complete;
            commandManager.commands.gotoleft.exec = this.$hide;
            commandManager.commands.indent.exec = this.$complete;

            commandManager.addCommand({
                name: 'hideautocomplete',
                bindKey: 'Esc',
                exec: function () {
                    _this.hide();
                }
            });

            commandManager.addCommand({
                name: 'autocomplete',
                bindKey: 'Return',
                exec: this.$complete
            });
        },

        removeKeyboardCommands: function () {
            var commandManager = this._getEditor().commands;

            /* Make sure the standard exec have been initialized. */
            if (this.standardGoLineDownExec !== null) {
                commandManager.commands.golinedown.exec = this.standardGoLineDownExec;
            }

            if (this.standardGoLineUpExec !== null) {
                commandManager.commands.golineup.exec = this.standardGoLineUpExec;
            }

            if (this.standardGoToRightExec !== null) {
                commandManager.commands.gotoright.exec = this.standardGoToRightExec;
            }

            if (this.standardGoToLeftExec !== null) {
                commandManager.commands.gotoleft.exec = this.standardGoToLeftExec;
            }

            if (this.standardIndentExec !== null) {
                commandManager.commands.indent.exec = this.standardIndentExec;
            }

            commandManager.removeCommand('hideautocomplete');
            commandManager.removeCommand('autocomplete');
        },

        /* Complete the word at the given position with the currently selected
         * suggestion. Only the part of the word before the position is
         * replaced. */
        complete: function (position) {
            var editor = this._getEditor();
            var session = this._getEditSession();

            var position = editor.getCursorPosition();

            /* Get the length of the word being typed. */
            var token = session.getTokenAt(position.row, position.column);
            if (!token) {
                /* No token at the given position. */
                this.clearSuggestionCache();
                this.hide();
                editor.focus();
            }

            var prefix = token.value.substr(0, position.column - token.start);
            var prefixLength = prefix.split(this.wordRegex).slice(-1)[0].length;

            var range = new Range(position.row,
                                position.column - prefixLength,
                                position.row,
                                position.column);

            var suggestion = this.getSelectedSuggestion().text();
            session.replace(range, suggestion);

            this.hide();
            editor.focus();
        },

        /* Remove the suggestions from the Dom. */
        removeSuggestions: function () {
            $('.suggestion').remove();
        },

        /* Get suggestions of completion for the current position in the
         * document. */
        getSuggestions: function (position) {
            
            /* If suggestions are cached,
             * return them directely */
            if (this._suggestionCache) {
                return this._suggestionCache;
            }
            
            var doc = this._getDocument();

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

            /* Build an object associating the suggestions with their distance
             * to the word at cursor position. To make sure that no suggestion
             * string overrides a built-in method of the suggestionsAndDistance
             * object, suffix all the suggestions with '-'. Afterward, make
             * sure of removing that suffix before using the stored
             * suggestions! */
            var suggestionsAndDistance = {};
            $.each(suggestions, function (index, suggestion) {
                var distance = Math.abs(index - markerIndex);
                if (!suggestionsAndDistance[suggestion + '-'] ||
                    distance < suggestionsAndDistance[suggestion]) {
                    suggestionsAndDistance[suggestion + '-'] = distance;
                }
            });

            /* Remove from the suggestions the word under the cursor. */
            delete suggestionsAndDistance[markedWord + '-'];

            /* Fill the cache */
            this._suggestionCache = suggestionsAndDistance;
            
            return suggestionsAndDistance;
        },
        
        /* Clear the suggestion cache */
        clearSuggestionCache: function () {
            this._suggestionCache = null;
        },

        /* Given an object associating suggestions and their distances to the
         * word under the cursor (the prefix), return a ranked array of
         * suggestions with the best match first. The suggestions are ranked
         * based on if they match the prefix fuzzily, how much they match the
         * given prefix in the computeSimpleMatchScore sense and  on their
         * distances to the prefix. */
        rankSuggestions: function (prefix, suggestionsAndDistance) {
            /* Initialize maxScore to one to ensure removing the non matching
             * suggestions (those with a zero score). */
            var maxScore = 1;
            var suggestionsAndMatchScore = {};
            for (var suggestion in suggestionsAndDistance) {
                if (Object.prototype.hasOwnProperty.call(suggestionsAndDistance, suggestion)) {
                    var score = this.computeSimpleMatchScore(prefix, suggestion);
                    if (score > maxScore) {
                        maxScore = score;
                    }
                    suggestionsAndMatchScore[suggestion] = score;
                }
            }

            /* Remove the suggestions that do not match the prefix fuzzily. */
            for (suggestion in suggestionsAndMatchScore) {
                if (Object.prototype.hasOwnProperty.call(suggestionsAndMatchScore, suggestion)) {
                    if (!this.isMatchingFuzzily(prefix, suggestion)) {
                        delete suggestionsAndMatchScore[suggestion];
                    }
                }
            }
            
            /* Now for each suggestion we have its matching score and its
             * distance to the word under the cursor. So compute its final
             * score as a combination of both. */
            var suggestionsAndFinalScore = {};
            for (suggestion in suggestionsAndMatchScore) {
                if (Object.prototype.hasOwnProperty.call(suggestionsAndMatchScore, suggestion)) {
                    //suggestionsAndFinalScore[suggestion] = suggestionsAndMatchScore[suggestion] -
                    //                        suggestionsAndDistance[suggestion];
                    suggestionsAndFinalScore[suggestion] = suggestionsAndMatchScore[suggestion] / suggestion.length;
                }
            }

            /* Make an array of suggestions and make sure to rank them in the
             * ascending scores order. */
            var suggestions = [];
            for (suggestion in suggestionsAndFinalScore) {
                if (Object.prototype.hasOwnProperty.call(suggestionsAndFinalScore, suggestion)) {
                    suggestions.push(suggestion);
                }
            }
            
            suggestions.sort(function (firstSuggestion, secondSuggestion) {
                return suggestionsAndFinalScore[secondSuggestion] - suggestionsAndFinalScore[firstSuggestion];
            });

            return suggestions;
        },

        /* Return the number of consecutive letters starting from the first
         * letter in suggestion that match prefix. For instance,
         * this.computeSimpleMatchScore(cod, codiad) will return 3. If
         * suggestion is shorter than prefix, return a score of zero. The score
         * is computed using a Vim-like smartcase behavior. */
        computeSimpleMatchScore: function (prefix, suggestion) {
            /* Use a Vim-like smartcase behavior. If prefix is all lowercase,
             * compute the match score case insensitive, if it is not, compute
             * the score case sensitive. */
            var localSuggestion = this._isLowerCase(prefix) ? suggestion.toLowerCase() : suggestion;

            if (localSuggestion.length < prefix.length) {
                return 0;
            } else if (localSuggestion === prefix) {
                return prefix.length;
            } else {
                var score = 0;
                for (var i = 0; i < prefix.length; ++i) {
                    if (localSuggestion[i] === prefix[i]) {
                        ++score;
                    } else {
                        break;
                    }
                }

                return score;
            }
        },

        /* Return true if suggestion fuzzily matches prefix. Because everybody
         * loves fuzzy matches.
         * For instance, this.isMatchingFuzzily(mlf, mylongfunctionname)
         * will return true. The score is computed using a Vim-like smartcase
         * behavior. */
        isMatchingFuzzily: function (prefix, suggestion) {
            /* Use a Vim-like smartcase behavior. If prefix is all lowercase,
             * compute the match score case insensitive, if it is not, compute
             * the score case sensitive. */
            var localSuggestion = this._isLowerCase(prefix) ? suggestion.toLowerCase() : suggestion;

            /* Escape the characters that have a special meaning for regex in
             * the prefix. */
            var localPrefix = prefix.replace(/[-[\]{}()*+?.,\\^$|#\s]/g, "\\$&");

            var fuzzyRegex = '^.*?';
            for (var i = 0; i < localPrefix.length; ++i) {
                if (localPrefix[i] === '\\') {
                    fuzzyRegex += localPrefix[i];
                    ++i;
                }

                fuzzyRegex += localPrefix[i];
                fuzzyRegex += '.*?';
            }

            if (localSuggestion.search(fuzzyRegex) !== -1) {
                return true;
            } else {
                return false;
            }
        },

        getMatchIndexes: function (prefix, suggestion) {
            /* Use a Vim-like smartcase behavior. If prefix is all lowercase,
             * find the match indexes case insensitive, if it is not, find them
             * case sensitive. */
            var localSuggestion = this._isLowerCase(prefix) ? suggestion.toLowerCase() : suggestion;

            var matchIndexes = [];
            var startIndex = 0;
            for (var i = 0; i < prefix.length; ++i) {
                var index = localSuggestion.indexOf(prefix[i], startIndex);
                matchIndexes.push(index);
                startIndex = index + 1;
            }

            return matchIndexes;
        },

        _isLowerCase: function (str) {
            return (str.toLowerCase() === str);
        },

        _ensureVisible: function (el, parent) {
            var offset = 1;
            var paneMin = parent.scrollTop();
            var paneMax = paneMin + parent.innerHeight();
            var itemMin = el.position().top + paneMin - offset;
            var itemMax = itemMin + el.outerHeight() + 2*offset;
            if (itemMax > paneMax) {
                parent.stop().animate({
                    scrollTop: itemMax - parent.innerHeight()
                }, 100);
            } else if (itemMin < paneMin) {
                parent.stop().animate({
                    scrollTop: itemMin
                }, 100);
            }
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

        /* Set of helper methods to manipulate the editor. */
        _getEditor: function () {
            return codiad.editor.getActive();
        },

        _getEditSession: function () {
            return codiad.editor.getActive().getSession();
        },

        _getDocument: function () {
            return codiad.editor.getActive().getSession().getDocument();
        },

        /* Some unit tests. */
        //i don't need to translate this... this is just for testing things... 
        _testSimpleMatchScorer: function () {
            var prefix = 'myprefix';
            var suggestion = 'myprefixisshort';
            var score = this.computeSimpleMatchScore(prefix, suggestion);
            if (score !== 8) {
                alert('_testSimpleMatchScorer lowercase test failed.');
            }

            prefix = 'MYPREFIX';
            suggestion = 'MYPREFIXISSHORT';
            score = this.computeSimpleMatchScore(prefix, suggestion);
            if (score !== 8) {
                alert('_testSimpleMatchScorer uppercase test failed.');
            }

            prefix = 'myPrefix';
            suggestion = 'myprefixisshort';
            score = this.computeSimpleMatchScore(prefix, suggestion);
            if (score !== 2) {
                alert('_testSimpleMatchScorer mixed case vs. lowercase test failed.');
            }

            prefix = 'myPrefixIs';
            suggestion = 'myPrefixIsShort';
            score = this.computeSimpleMatchScore(prefix, suggestion);
            if (score !== 10) {
                alert('_testSimpleMatchScorer mixed case test failed.');
            }
        },

        _testFuzzyMatcher: function () {
            var isMatching = this.isMatchingFuzzily('mlf', 'mylongfunctionname');
            if (!isMatching) {
                alert('_testFuzzyMatcher mlf vs. mylongfunctionname failed.');
            }

            isMatching = this.isMatchingFuzzily('mLn', 'myLongFunctionName');
            if (!isMatching) {
                alert('_testFuzzyMatcher mLn vs. myLongFunctionName failed.');
            }
            isMatching = this.isMatchingFuzzily('mLFuny', 'myLongFunctionName');
            if (isMatching) {
                alert('_testFuzzyMatcher mLFuny. myLongFunctionName failed.');
            }
        }

    };

})(this, jQuery);
