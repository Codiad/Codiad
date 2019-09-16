<?php
    require_once('../../common.php');
?>
<div class="dragdrop_settings">
    <label><span class="icon-magnet big-icon"></span>Drag'n'Drop Settings</label>
    <hr>
    <table class="settings">
        <tr>
            <td style="width: 80%;">
                Enable drag'n'drop to insert or append file content in active file
            </td>
            <td>
                <select class="setting" data-setting="codiad.plugin.drag.insert">
                    <option value="true"><?php i18n("Yes"); ?></option>
                    <option value="false" selected><?php i18n("No"); ?></option>
                </select>
            </td>
        </tr>
    </table>
</div>