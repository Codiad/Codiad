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
            <option value="ambiance">Ambiance</option>
            <option value="chaos">Chaos</option>
            <option value="chrome">Chrome</option>
            <option value="clouds">Clouds</option>
            <option value="clouds_midnight">Clouds - Midnight</option>
            <option value="cobalt">Cobalt</option>
            <option value="crimson_editor">Crimson Editor</option>
            <option value="dawn">Dawn</option>
            <option value="dreamweaver">Dreamweaver</option>
            <option value="eclipse">Eclipse</option>
            <option value="github">GitHub</option>
            <option value="idle_fingers">Idle Fingers</option>
            <option value="iplastic">IPlastic</option>
            <option value="katzenmilch">Katzenmilch</option>
            <option value="kuroir">Kuroir</option>
            <option value="kr_theme">krTheme</option>
            <option value="merbivore">Merbivore</option>
            <option value="merbivore_soft">Merbivore Soft</option>
            <option value="mono_industrial">Mono Industrial</option>
            <option value="monokai">Monokai</option>
            <option value="pastel_on_dark">Pastel On Dark</option>
            <option value="solarized_dark">Solarized Dark</option>
            <option value="solarized_light">Solarized Light</option>
            <option value="sqlserver">SQL Server</option>
            <option value="terminal">Terminal</option>
            <option value="textmate">Textmate</option>
            <option value="tomorrow">Tomorrow</option>
            <option value="tomorrow_night">Tomorrow Night</option>
            <option value="tomorrow_night_blue">Tomorrow Night Blue</option>
            <option value="tomorrow_night_bright">Tomorrow Night Bright</option>
            <option value="tomorrow_night_eighties">Tomorrow Night Eighties</option>
            <option value="twilight" selected>Twilight</option>
            <option value="vibrant_ink">Vibrant Ink</option>
            <option value="xcode">XCode</option>
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
