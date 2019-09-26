<!--
    Copyright (c) Codiad & Andr3as, distributed
    as-is and without warranty under the MIT License. 
    See http://opensource.org/licenses/MIT for more information.
    This information must remain intact.
-->
<form>
    <label>Duplicate</label>
    <p>Enter new name</p>
    <input type="text" id="duplicate_name" value="<?php echo $_GET['name'] ?>">
    <button onclick="codiad.Duplicate.duplicate(); return false;">Duplicate</button>
    <button onclick="codiad.modal.unload(); return false;">Close</button>
</form>