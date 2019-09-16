<?php
    require_once('../../common.php');
?>
<!--
    Copyright (c) Codiad & Andr3as, distributed
    as-is and without warranty under the MIT License. 
    See http://opensource.org/licenses/MIT for more information.
    This information must remain intact.
-->
<form id="beautify_form">
    <label><span class="icon-brush big-icon"></span>Beautify Settings</label>
    <hr>
    Hint: Ctrl-alt-b to beautify current selection<br>
    <br>
    <label>Enable autobeautify at save:</label>
    <table class="settings">
        <tr>
            <td>
                <input type="checkbox" id="beautify_js">Beautify JS
            </td>
        </tr>
        <tr>
            <td>
                <input type="checkbox" id="beautify_json">Beautify JSON
            </td>
        </tr>
        <tr>
            <td>
                <input type="checkbox" id="beautify_html">Beautify HTML
            </td>
        </tr>
        <tr>
            <td>
                <input type="checkbox" id="beautify_css">Beautify CSS
            </td>
        </tr>
        <tr>
            <td>
                <input type="checkbox" id="beautify_php">Beautify PHP
            </td>
        </tr>
    </table>
    
    <hr>
    
    <h3>Experimental settings</h3>
    <table class="settings">
        <tr>
            <td style="width: 80%;">
                Guess cursor position
            </td>
            <td>
                <select class="setting" data-setting="codiad.plugin.beautify.guessCursorPosition">
                    <option value="true"><?php i18n("Yes"); ?></option>
                    <option value="false" selected><?php i18n("No"); ?></option>
                </select>
            </td>
        </tr>
    </table>
    
    <script>
        codiad.Beautify.get();
    </script>
</form>
