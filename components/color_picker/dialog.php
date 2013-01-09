<!--/*
*  Copyright (c) Codiad & Kent Safranski (codiad.com), distributed
*  as-is and without warranty under the MIT License. See 
*  [root]/license.txt for more. This information must remain intact.
*/-->

<div id="colorpicker_region">

</div>

<button class="right" onclick="codiad.modal.unload();"><?=lang("Close"); ?></button>

<button class="btn-left" onclick="codiad.colorPicker.insert('hex');"><?=lang("Insert HEX"); ?></button
><button class="btn-right" onclick="codiad.colorPicker.insert('rgb');"><?=lang("Insert RGB"); ?></button>

<script>

    $(function(){
    
        selected = codiad.active.getSelectedText();
        
        if(selected==null){ 
            selected = '#45818a'; 
            sellength = 7;
        }else{
            sellength = selected.length;
        }
        
        var colorRegEx = /^#?([a-f]|[A-F]|[0-9]){3}(([a-f]|[A-F]|[0-9]){3})?$/;
        seltest = colorRegEx.test(selected);

       
        // Fix format issues with rgb for parser
        returnRGBWrapper = true;
        if(selected.indexOf(',')>0 && selected.indexOf('rgb')){
            selected='rgb('+selected+')';
            returnRGBWrapper = false;
        }
    
        var color = new RGBColor(selected);
        if (color.ok) { // 'ok' is true when the parsing was a success
            $('#colorpicker_region').ColorPicker({flat: true, color: color.toHex() });
        }else{
            $('#colorpicker_region').ColorPicker({flat: true, color: '#454b8a' });
        }
    
    });

</script>