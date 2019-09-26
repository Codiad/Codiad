<!--
    Copyright (c) Codiad & Andr3as, distributed
    as-is and without warranty under the MIT License. 
    See http://opensource.org/licenses/MIT for more information.
    This information must remain intact.
-->
<form>
    <?php
        if ($_GET['action'] == "ignore") {
    ?>
        <div class="ignore_choose">
            <label>Ignore</label>
            <select class="ignore_range">
                <option value="file" class="filePath">Ignore just this file/directory</option>
                <option value="name" class="fileGeneral">Ignore this file/directory in every project</option>
                <option value="type" class="fileType">Ignore every file with same extension</option>
            </select>
            <button onclick="codiad.Ignore.setIgnore(); return false;">Ignore</button>
            <button onclick="codiad.modal.unload(); return false;">Close</button>
            <script>
                codiad.Ignore.isDir();
            </script>
        </div>
    <?php
        } else if ($_GET['action'] == "log") {
    ?>
        <div class="ignore_display">
            <label><span class="icon-target big-icon"></span>Ignore Settings</label>
            <hr>
            <table id="ignore_list">
                <tr>
                    <td>Path/Name/Extension</td>
                    <td>Range</td>
                    <td></td>
                </tr>
            </table>
            <br>
            <button onclick="codiad.Ignore.addRule(); return false;">Add new rule</button>
            <button onclick="codiad.Ignore.help(); return false;">Help</button>
            <script>
                codiad.Ignore.loadDialog();
            </script>
        </div>
    <?php
        } else if ($_GET['action'] == "help") {
    ?>
        <div class="ignore_help">
            <label>Ignore - Help</label>
            <table>
                <tr>
                    <td>Syntax</td>
                    <td></td>
                </tr>
                <tr>
                    <td>Just this file:</td>
                    <td>Path of the file in the workspace, f.e. Ignore/README.md</td>
                </tr>
                <tr>
                    <td>Files with the same name:</td>
                    <td>Name of the file, f.e. README.md</td>
                </tr>
                <tr>
                    <td>Files with the same extension:</td>
                    <td>Extension of files, f.e. *.php</td>
                </tr>
                <tr>
                    <th colspan="2">Same rules for directories.</th>
                </tr>
            </table>
            <button onclick="codiad.Ignore.showDialog(); return false;">Close</button>
        </div>
    <?php
        }
    ?>
</form>