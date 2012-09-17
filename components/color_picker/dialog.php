<!--/*
*  Copyright (c) Codiad & Kent Safranski (codiad.com), distributed
*  as-is and without warranty under the MIT License. See 
*  [root]/license.txt for more. This information must remain intact.
*/-->

<div id="colorpicker_region">

</div>

<button class="right" onclick="modal.unload();">Close</button>

<button class="btn-left" onclick="color_picker.insert('hex');">Insert HEX</button><button class="btn-right" onclick="color_picker.insert('rgb');">Insert RGB</button>

<script>

    $(function(){
    
        selected = active.get_selected_text();
        sellength = selected.length;
        
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