<?php
    require_once('../../common.php');
?>
<div class="compress_settings">
    <label><span class="icon-brush big-icon"></span>Compress Settings</label>
    <hr>
    <table class="settings">
        <tr>
            <td style="width: 80%;">
                Compress on save
            </td>
            <td>
                <select class="setting" data-setting="codiad.plugin.compress.compressOnSave">
                    <option value="true"><?php i18n("Yes"); ?></option>
                    <option value="false" selected><?php i18n("No"); ?></option>
                </select>
            </td>
        </tr>
    </table>
</div>