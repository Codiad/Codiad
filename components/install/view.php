<div id="installer">
<?php

/*
*  Copyright (c) Codiad & Kent Safranski (codiad.com), distributed
*  as-is and without warranty under the MIT License. See 
*  [root]/license.txt for more. This information must remain intact.
*/

$path = rtrim(str_replace("index.php", "", $_SERVER['PHP_SELF']),"/");

$workspace = is_writable($_SERVER['DOCUMENT_ROOT'] . $path . "/workspace");
$data = is_writable($_SERVER['DOCUMENT_ROOT'] . $path . "/data");
$config = is_writable($_SERVER['DOCUMENT_ROOT'] . $path . "/config.php");

if(!$workspace || !$data || !$config){
    ?>
    <h1>Installation Error</h1>
    <p>Please make sure the following are writeable:</p>
    <pre>[SYSTEM]/config.php
[SYSTEM]/workspace
[SYSTEM]/data</pre>
<button onclick="window.location.reload();">Re-Test</button>
    <?php
}else{
    ?>
    <form id="install">
    <h1>Initial Setup</h1>
    
    <input type="hidden" name="path" value="<?php echo($path); ?>">
    
    <label>New Username</label>
    <input type="text" name="username" autofocus="autofocus">
    
    <div style="float:left; width: 48%; margin-right: 4%;"> 
    
        <label>Password</label>
        <input type="password" name="password">
    
    </div>
    
    <div style="float:left; width: 48%;"> 
    
        <label>Confirm Password</label>
        <input type="password" name="password_confirm">
    
    </div>
    
    <div style="clear:both;"></div>
    
    <hr>
    
    <label>New Project Name</label>
    <input type="text" name="project">
    
    <hr>
    
    <label>Timezone</label>
    <?php
    function formatOffset($offset) {
        $hours = $offset / 3600;
        $remainder = $offset % 3600;
        $sign = $hours > 0 ? '+' : '-';
        $hour = (int) abs($hours);
        $minutes = (int) abs($remainder / 60);

        if ($hour == 0 AND $minutes == 0) {
            $sign = ' ';
        }
        return $sign . str_pad($hour, 2, '0', STR_PAD_LEFT) .':'. str_pad($minutes,2, '0');

    }
    
    $utc = new DateTimeZone('UTC');
    $dt = new DateTime('now', $utc);
    
    echo '<select name="timezone">';
    foreach(DateTimeZone::listIdentifiers() as $tz) {
        $current_tz = new DateTimeZone($tz);
        $offset =  $current_tz->getOffset($dt);
        $transition =  $current_tz->getTransitions($dt->getTimestamp(), $dt->getTimestamp());
        $abbr = $transition[0]['abbr'];
    
        echo '<option value="' ,$tz, '">' ,$tz, ' [' ,$abbr, ' ', formatOffset($offset), ']</option>';
    }
    echo '</select>';
    ?>
    
    <button>Install</button>
    </form>
    <?php
}
?>

</div>
<script>

    $(function(){
        $('#install').on('submit',function(e){
            e.preventDefault();
            
            // Check empty fields
            
            empty_fields = false;
            $('input').each(function(){
                if($(this).val()==''){ empty_fields = true; }
            });
            
            if(empty_fields){ alert('All fields must be filled out'); }
            
            // Check password
            password_match = true;
            if($('input[name="password"]').val()!=$('input[name="password_confirm"]').val()){
                password_match = false;
            }
            
            if(!password_match){ alert('The passwords entered do not match'); }
            
            if(!empty_fields && password_match){
                $.post('components/install/process.php',$('#install').serialize(),function(data){
                    if(data=='success'){
                        window.location.reload();
                    }else{
                        alert("An Error Occoured<br><br>"+data);
                    }
                });
            }
            
        });
    });

</script>