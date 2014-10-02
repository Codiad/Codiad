<?php

/*
 *  (c) Codiad & ccvca (https://github.com/ccvca)
* @author ccvca (https://github.com/ccvca)
* This Code is released under the same licence as Codiad (https://github.com/Codiad/Codiad)
* See [root]/license.txt for more. This information must remain intact.
*/

require_once 'class.fileextension_textmode.php';

//check Session is done in constructor
$fileExTM = new fileextension_textmode();

if(!isset($_GET['action'])){
	die('Missing $_GET["action"]');
}
switch($_GET['action']){
	//////////////////////////////////////////////////////////////////
	//The form for edit the assotiations
	//////////////////////////////////////////////////////////////////
	case 'fileextension_textmode_form':
		if(!Common::checkAccess()){
			die('You are not allowed to edit the file extensions.');
		}
		//////////////////////////////////////////////////////////////////
		//Reading the current extensions
		//////////////////////////////////////////////////////////////////
		$ext = false;
		//ignore warnings
		$ext = @Common::getJSON(fileextension_textmode::storeFilename);
		
		if(!is_array($ext)){
			//default extensions
			$ext = $fileExTM->getDefaultExtensions();
		}
		
		$textModes = $fileExTM->getAvailiableTextModes();
		
		if(!@ksort($ext)){
			die(json_encode(array('status' => 'error', 'msg' => 'Internal PHP error.') ));
		}
		?>
        <label><span class="icon-pencil big-icon"></span><?php i18n("Extensions"); ?></label>
        <table id="FileExtModeHeader">
            <thead>
				<tr>
					<th><?php i18n("Extension"); ?></th>
					<th><?php i18n("Mode"); ?></th>
                </tr>
			</thead>
        </table>
		<div id="FileExtTextModeDiv">
			<table id="FileExtTextModeTable">
				<tbody id="FileExtTextModeTableTbody">
				<?php
				foreach($ext as $ex => $mode){
					//////////////////////////////////////////////////////////////////
					//print only valid assotiations
					//////////////////////////////////////////////////////////////////
					if(!$fileExTM->validTextMode($mode)){
						continue;
					}?>
					<tr>
						<td><input class="FileExtension" type="text" name="extension[]" value="<?php echo $ex ?>" /></td>
						<td><?php echo $fileExTM->getTextModeSelect($mode)?></td>
					</tr>
				<?php
				}
				?>
				</tbody>
			</table>
		</div>
		<br>
		<button class="btn-left" onClick="codiad.fileext_textmode.addFieldToForm()"><?php i18n("New Extension"); ?></button>
<?php
		break;
}
?>
