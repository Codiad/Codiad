<?php

/*
*  Copyright (c) Codiad & Kent Safranski (codiad.com), distributed
*  as-is and without warranty under the MIT License. See 
*  [root]/license.txt for more. This information must remain intact.
*/

require_once('../../common.php');
require_once('class.filemanager.php');

//////////////////////////////////////////////////////////////////
// Verify Session or Key
//////////////////////////////////////////////////////////////////

checkSession();

?>
<label><?php i18n("Upload Files"); ?></label>

<div id="upload-drop-zone">
    
    <span id="upload-wrapper">
    
        <input id="fileupload" type="file" name="upload[]" data-url="components/filemanager/controller.php?action=upload&path=<?php echo($_GET['path']); ?>" multiple>
        <span id="upload-clicker"><?php i18n("Drag Files or Click Here to Upload"); ?></span>
    
    </span>

    <div id="upload-progress"><div class="bar"></div></div>
    
    <div id="upload-complete"><?php i18n("Complete!"); ?></div>

</div>

<button onclick="codiad.modal.unload();"><?php i18n("Close Uploader"); ?></button>

<script>

$(function () {
    $('#fileupload').fileupload({
        dataType: 'json',
        dropZone: '#upload-drop-zone',
        progressall: function(e, data){
            var progress = parseInt(data.loaded / data.total * 100, 10);
            $('#upload-progress .bar').css(
                'width',
                progress + '%'
            );
            if(progress>98){ $('#upload-complete').fadeIn(200); }
        },
        done: function(e, data){
            $.each(data.result, function (index, file){
                var path = '<?php echo($_GET['path']); ?>';
                codiad.filemanager.createObject(path, path + "/" + file.name,'file');
                /* Notify listeners. */
                amplify.publish('filemanager.onUpload', {file: file, path: path});
            });
            setTimeout(function(){
                $('#upload-progress .bar').animate({'width':0},700);
                $('#upload-complete').fadeOut(200);
            },1000);
        }
    });
});

</script>
