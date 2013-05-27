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
		<div id="FileExtTextModeDiv">
			<label>Edit file extensions</label>
			<table id="FileExtTextModeTable" width="100%">
				<thead>
					<tr>
						<th width="50%">Extension</th>
						<th width="50%">Mode</th></tr>
				</thead>
				<tbody id="FileExtTextModeTableTbody">
					<tr>
						<td class="firstLine" colspan="2">&nbsp;</td>
					</tr>
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
		<button class="btn-left" onClick="codiad.fileext_textmode.addFieldToForm()">New Extension</button><button class="btn-mid" onClick="codiad.fileext_textmode.sendForm();">Save Scheme</button><button class="btn-right" onClick="codiad.modal.load(400, 'components/editor/dialog.php?action=settings');"><?php echo Common::get_i18n('Close')?></button>
<?php
		break;
}
?>