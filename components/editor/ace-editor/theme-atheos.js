ace.define("ace/theme/atheos",["require","exports","module","ace/lib/dom"], function(require, exports, module) {

exports.isDark = true;
exports.cssClass = "ace-atheos";
exports.cssText = ".ace-atheos .ace_gutter {\
background: #121212;\
color: #929292\
border-right: 1px solid #282828;\
}\
.ace-atheos .ace_gutter-cell.ace_warning {\
background-image: none;\
background: #FC0;\
border-left: none;\
padding-left: 0;\
color: #000;\
}\
.ace-atheos .ace_gutter-cell.ace_error {\
background-position: -6px center;\
background-image: none;\
background: #F10;\
border-left: none;\
padding-left: 0;\
color: #000;\
}\
.ace-atheos .ace_print-margin {\
width: 1px;\
background: #232323\
}\
.ace-atheos {\
background-color: #191919;\
color: #929292\
}\
.ace-atheos .ace_cursor {\
color: #7DA5DC\
}\
.ace-atheos .ace_marker-layer .ace_selection {\
background: #3e50b4\
}\
.ace-atheos.ace_multiselect .ace_selection.ace_start {\
box-shadow: 0 0 3px 0px #191919;\
}\
.ace-atheos .ace_marker-layer .ace_step {\
background: rgb(102, 82, 0)\
}\
.ace-atheos .ace_marker-layer .ace_bracket {\
margin: -1px 0 0 -1px;\
border: 1px solid #BFBFBF\
}\
.ace-atheos .ace_marker-layer .ace_active-line {\
background: rgba(215, 215, 215, 0.031)\
}\
.ace-atheos .ace_gutter-active-line {\
background-color: rgba(215, 215, 215, 0.031)\
}\
.ace-atheos .ace_marker-layer .ace_selected-word {\
border: 1px solid #3e50b4\
}\
.ace-atheos .ace_invisible {\
color: #666\
}\
.ace-atheos .ace_keyword,\
.ace-atheos .ace_meta,\
.ace-atheos .ace_support.ace_constant.ace_property-value {\
color: #f08d24\
}\
.ace-atheos .ace_keyword.ace_operator {\
color: #f08d24\
}\
.ace-atheos .ace_keyword.ace_other.ace_unit {\
color: #366F1A\
}\
.ace-atheos .ace_constant.ace_language {\
color: #39946A\
}\
.ace-atheos .ace_constant.ace_numeric {\
color: #46A609\
}\
.ace-atheos .ace_constant.ace_character.ace_entity {\
color: #A165AC\
}\
.ace-atheos .ace_invalid {\
color: #FFFFFF;\
background-color: #E92E2E\
}\
.ace-atheos .ace_fold {\
background-color: #927C5D;\
border-color: #929292\
}\
.ace-atheos .ace_storage,\
.ace-atheos .ace_support {\
color: #E8341C\
}\
.ace-atheos .ace_string {\
color: #1cc3e8\
}\
.ace-atheos .ace_constant,\
.ace-atheos .ace_variable {\
color: #fafafa\
}\
.ace-atheos .ace_comment {\
color: #6e7174\
}\
.ace-atheos .ace_entity.ace_name.ace_tag,\
.ace-atheos .ace_entity.ace_other.ace_attribute-name {\
color: #606060\
}\
.ace-atheos .ace_indent-guide {\
background: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAACCAYAAACZgbYnAAAAEklEQVQImWNgYGBgYHB3d/8PAAOIAdULw8qMAAAAAElFTkSuQmCC) right repeat-y\
}";

var dom = require("../lib/dom");
dom.importCssString(exports.cssText, exports.cssClass);
});                (function() {
                    ace.require(["ace/theme/atheos"], function(m) {
                        if (typeof module == "object" && typeof exports == "object" && module) {
                            module.exports = m;
                        }
                    });
                })();