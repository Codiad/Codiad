<?php
    require_once('../../common.php');
?>
<label><span class="icon-home big-icon"></span><?php i18n("Editor Settings"); ?></label>
<hr>
<table class="settings">

    <tr>

        <td width="50%"><?php i18n("Theme"); ?></td>
        <td>

        <select class="setting" data-setting="codiad.editor.theme">
        <?php
        	$files = glob( COMPONENTS . "/editor/ace-editor/theme-*.js" );
        	foreach( $files as $file ) {
        		$name = pathinfo( $file, PATHINFO_FILENAME );
        		// if( strpos( strtolower( $name ), strtolower( "theme-" ) ) !== false ) {
        			$value = str_replace( "theme-", "", str_replace( ".js", "", $name ) );
        			$name = ucwords( str_replace( "_", " ", $value ) );
        			echo('<option value="' . $value . '">' . $name .'</option>');
        		// }
        	}
	     ?>
	        </select>

        </td>

    </tr>
    <tr>

        <td><?php i18n("Font Size"); ?></td>
        <td>

        <select class="setting" data-setting="codiad.editor.fontSize">
            <option value="10px">10px</option>
            <option value="11px">11px</option>
            <option value="12px">12px</option>
            <option value="13px" selected>13px</option>
            <option value="14px">14px</option>
            <option value="15px">15px</option>
            <option value="16px">16px</option>
            <option value="17px">17px</option>
            <option value="18px">18px</option>
        </select>

        </td>

    </tr>
    <tr>

        <td><?php i18n("Highlight Active Line"); ?></td>
        <td>

            <select class="setting" data-setting="codiad.editor.highlightLine">
                <option value="true" selected><?php i18n("Yes"); ?></option>
                <option value="false"><?php i18n("No"); ?></option>
            </select>

        </td>

    </tr>
    <tr>

        <td><?php i18n("Indent Guides"); ?></td>
        <td>

        <select class="setting" data-setting="codiad.editor.indentGuides">
            <option value="true" selected><?php i18n("On"); ?></option>
            <option value="false"><?php i18n("Off"); ?></option>
        </select>

        </td>

    </tr>
    <tr>

        <td><?php i18n("Print Margin"); ?></td>
        <td>

        <select class="setting" data-setting="codiad.editor.printMargin">
            <option value="true"><?php i18n("Show"); ?></option>
            <option value="false" selected><?php i18n("Hide"); ?></option>
        </select>

        </td>

    </tr>
    <tr>

        <td><?php i18n("Print Margin Column"); ?></td>
        <td>

        <select class="setting" data-setting="codiad.editor.printMarginColumn">
            <option value="80" selected>80</option>
            <option value="85">85</option>
            <option value="90">90</option>
            <option value="95">95</option>
            <option value="100">100</option>
            <option value="105">105</option>
            <option value="110">110</option>
            <option value="115">115</option>
            <option value="120">120</option>
        </select>

        </td>

    </tr>
    <tr>

        <td><?php i18n("Wrap Lines"); ?></td>
        <td>

        <select class="setting" data-setting="codiad.editor.wrapMode">
            <option value="false" selected><?php i18n("No wrap"); ?></option>
            <option value="true"><?php i18n("Wrap Lines"); ?></option>
        </select>

        </td>

    </tr>
    <tr>

        <td><?php i18n("Tab Size"); ?></td>
        <td>

        <select class="setting" data-setting="codiad.editor.tabSize">
            <option value="2">2</option>
            <option value="3">3</option>
            <option value="4" selected>4</option>
            <option value="5">5</option>
            <option value="6">6</option>
            <option value="7">7</option>
            <option value="8">8</option>
        </select>

        </td>

    </tr>
    <tr>

        <td><?php i18n("Soft Tabs"); ?></td>
        <td>

        <select class="setting" data-setting="codiad.editor.softTabs">
            <option value="false" selected><?php i18n("No"); ?></option>
            <option value="true"><?php i18n("Yes"); ?></option>
        </select>

        </td>

    </tr>
</table>
