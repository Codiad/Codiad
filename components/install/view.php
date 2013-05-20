<div id="installer">
<?php

/*
*  Copyright (c) Codiad & Kent Safranski (codiad.com), distributed
*  as-is and without warranty under the MIT License. See 
*  [root]/license.txt for more. This information must remain intact.
*/

$path = rtrim(str_replace("index.php", "", $_SERVER['SCRIPT_FILENAME']),"/");

$workspace = is_writable( $path . "/workspace");
$data = is_writable($path . "/data");

$conf = $path . '/config.php';

if(!file_exists($conf) && !is_writable($path)) {
    $config = false;
} elseif(!file_exists($conf)  && is_writable($path)) {
    $config = file_put_contents($conf, file_get_contents($path . "/config.example.php"));
    if($config !== false) {
        $config = true;
        unlink($conf);
    }
} elseif(file_exists($conf)) {
    $config = is_writable($conf);
}

if(ini_get('register_globals') == 1) {
    $register = true;
} else {
    $register = false;
}


if(!$workspace || !$data || !$config || $register){
    ?>
    <h1>Installation Error</h1>
    <p>Please make sure the following exist and are writeable:</p>
    <div class="install_issues">
        <p>[SYSTEM]/config.php - <?php if($config) { echo '<font style="color:green">PASSED</font>'; } else { echo '<font style="color:red">ERROR</font>'; } ?></p>
        <p>[SYSTEM]/workspace - <?php if($workspace) { echo '<font style="color:green">PASSED</font>'; } else { echo '<font style="color:red">ERROR</font>'; } ?></p>
        <p>[SYSTEM]/data - <?php if($data) { echo '<font style="color:green">PASSED</font>'; } else { echo '<font style="color:red">ERROR</font>'; } ?></p> 
    </div>
    <?php if($register) { ?>
    <p>Please make sure these environmental variables are set:</p>
    <div class="install_issues">
        <?php if($register) { echo '<p>register_globals: Off</p>'; } ?>
    </div>
    <?php } ?>
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
    <input type="text" name="project_name">
    <?php if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') { ?>
    <label>Folder Name or Absolute Path</label>
    <input type="text" name="project_path">
    <?php }  ?>
    <hr>
    
    <label>Timezone</label>
    <select name="timezone">
        <option value="Pacific/Midway">(GMT-11:00) Midway Island, Samoa</option>
        <option value="America/Adak">(GMT-10:00) Hawaii-Aleutian</option>
    	<option value="Etc/GMT+10">(GMT-10:00) Hawaii</option>
    	<option value="Pacific/Marquesas">(GMT-09:30) Marquesas Islands</option>
    	<option value="Pacific/Gambier">(GMT-09:00) Gambier Islands</option>
    	<option value="America/Anchorage">(GMT-09:00) Alaska</option>
    	<option value="America/Ensenada">(GMT-08:00) Tijuana, Baja California</option>
    	<option value="Etc/GMT+8">(GMT-08:00) Pitcairn Islands</option>
    	<option value="America/Los_Angeles">(GMT-08:00) Pacific Time (US & Canada)</option>
    	<option value="America/Denver">(GMT-07:00) Mountain Time (US & Canada)</option>
    	<option value="America/Chihuahua">(GMT-07:00) Chihuahua, La Paz, Mazatlan</option>
    	<option value="America/Dawson_Creek">(GMT-07:00) Arizona</option>
    	<option value="America/Belize">(GMT-06:00) Saskatchewan, Central America</option>
    	<option value="America/Cancun">(GMT-06:00) Guadalajara, Mexico City, Monterrey</option>
    	<option value="Chile/EasterIsland">(GMT-06:00) Easter Island</option>
    	<option value="America/Chicago">(GMT-06:00) Central Time (US & Canada)</option>
    	<option value="America/New_York">(GMT-05:00) Eastern Time (US & Canada)</option>
    	<option value="America/Havana">(GMT-05:00) Cuba</option>
    	<option value="America/Bogota">(GMT-05:00) Bogota, Lima, Quito, Rio Branco</option>
    	<option value="America/Caracas">(GMT-04:30) Caracas</option>
    	<option value="America/Santiago">(GMT-04:00) Santiago</option>
    	<option value="America/La_Paz">(GMT-04:00) La Paz</option>
    	<option value="Atlantic/Stanley">(GMT-04:00) Faukland Islands</option>
    	<option value="America/Campo_Grande">(GMT-04:00) Brazil</option>
    	<option value="America/Goose_Bay">(GMT-04:00) Atlantic Time (Goose Bay)</option>
    	<option value="America/Glace_Bay">(GMT-04:00) Atlantic Time (Canada)</option>
    	<option value="America/St_Johns">(GMT-03:30) Newfoundland</option>
    	<option value="America/Araguaina">(GMT-03:00) UTC-3</option>
    	<option value="America/Montevideo">(GMT-03:00) Montevideo</option>
    	<option value="America/Miquelon">(GMT-03:00) Miquelon, St. Pierre</option>
    	<option value="America/Godthab">(GMT-03:00) Greenland</option>
    	<option value="America/Argentina/Buenos_Aires">(GMT-03:00) Buenos Aires</option>
    	<option value="America/Sao_Paulo">(GMT-03:00) Brasilia</option>
    	<option value="America/Noronha">(GMT-02:00) Mid-Atlantic</option>
    	<option value="Atlantic/Cape_Verde">(GMT-01:00) Cape Verde Is.</option>
    	<option value="Atlantic/Azores">(GMT-01:00) Azores</option>
    	<option value="Europe/Belfast">(GMT) Greenwich Mean Time : Belfast</option>
    	<option value="Europe/Dublin">(GMT) Greenwich Mean Time : Dublin</option>
    	<option value="Europe/Lisbon">(GMT) Greenwich Mean Time : Lisbon</option>
    	<option value="Europe/London">(GMT) Greenwich Mean Time : London</option>
    	<option value="Africa/Abidjan">(GMT) Monrovia, Reykjavik</option>
    	<option value="Europe/Amsterdam">(GMT+01:00) Amsterdam, Berlin, Bern, Rome, Stockholm, Vienna</option>
    	<option value="Europe/Belgrade">(GMT+01:00) Belgrade, Bratislava, Budapest, Ljubljana, Prague</option>
    	<option value="Europe/Brussels">(GMT+01:00) Brussels, Copenhagen, Madrid, Paris</option>
    	<option value="Africa/Algiers">(GMT+01:00) West Central Africa</option>
    	<option value="Africa/Windhoek">(GMT+01:00) Windhoek</option>
    	<option value="Asia/Beirut">(GMT+02:00) Beirut</option>
    	<option value="Africa/Cairo">(GMT+02:00) Cairo</option>
    	<option value="Asia/Gaza">(GMT+02:00) Gaza</option>
    	<option value="Africa/Blantyre">(GMT+02:00) Harare, Pretoria</option>
    	<option value="Asia/Jerusalem">(GMT+02:00) Jerusalem</option>
    	<option value="Europe/Minsk">(GMT+02:00) Minsk</option>
    	<option value="Asia/Damascus">(GMT+02:00) Syria</option>
    	<option value="Europe/Moscow">(GMT+03:00) Moscow, St. Petersburg, Volgograd</option>
    	<option value="Africa/Addis_Ababa">(GMT+03:00) Nairobi</option>
    	<option value="Asia/Tehran">(GMT+03:30) Tehran</option>
    	<option value="Asia/Dubai">(GMT+04:00) Abu Dhabi, Muscat</option>
    	<option value="Asia/Yerevan">(GMT+04:00) Yerevan</option>
    	<option value="Asia/Kabul">(GMT+04:30) Kabul</option>
    	<option value="Asia/Yekaterinburg">(GMT+05:00) Ekaterinburg</option>
    	<option value="Asia/Tashkent">(GMT+05:00) Tashkent</option>
    	<option value="Asia/Kolkata">(GMT+05:30) Chennai, Kolkata, Mumbai, New Delhi</option>
    	<option value="Asia/Katmandu">(GMT+05:45) Kathmandu</option>
    	<option value="Asia/Dhaka">(GMT+06:00) Astana, Dhaka</option>
    	<option value="Asia/Novosibirsk">(GMT+06:00) Novosibirsk</option>
    	<option value="Asia/Rangoon">(GMT+06:30) Yangon (Rangoon)</option>
    	<option value="Asia/Bangkok">(GMT+07:00) Bangkok, Hanoi, Jakarta</option>
    	<option value="Asia/Krasnoyarsk">(GMT+07:00) Krasnoyarsk</option>
    	<option value="Asia/Hong_Kong">(GMT+08:00) Beijing, Chongqing, Hong Kong, Urumqi</option>
    	<option value="Asia/Irkutsk">(GMT+08:00) Irkutsk, Ulaan Bataar</option>
    	<option value="Australia/Perth">(GMT+08:00) Perth</option>
    	<option value="Australia/Eucla">(GMT+08:45) Eucla</option>
    	<option value="Asia/Tokyo">(GMT+09:00) Osaka, Sapporo, Tokyo</option>
    	<option value="Asia/Seoul">(GMT+09:00) Seoul</option>
    	<option value="Asia/Yakutsk">(GMT+09:00) Yakutsk</option>
    	<option value="Australia/Adelaide">(GMT+09:30) Adelaide</option>
    	<option value="Australia/Darwin">(GMT+09:30) Darwin</option>
    	<option value="Australia/Brisbane">(GMT+10:00) Brisbane</option>
    	<option value="Australia/Hobart">(GMT+10:00) Hobart</option>
    	<option value="Asia/Vladivostok">(GMT+10:00) Vladivostok</option>
    	<option value="Australia/Lord_Howe">(GMT+10:30) Lord Howe Island</option>
    	<option value="Etc/GMT-11">(GMT+11:00) Solomon Is., New Caledonia</option>
    	<option value="Asia/Magadan">(GMT+11:00) Magadan</option>
    	<option value="Pacific/Norfolk">(GMT+11:30) Norfolk Island</option>
    	<option value="Asia/Anadyr">(GMT+12:00) Anadyr, Kamchatka</option>
    	<option value="Pacific/Auckland">(GMT+12:00) Auckland, Wellington</option>
    	<option value="Etc/GMT-12">(GMT+12:00) Fiji, Kamchatka, Marshall Is.</option>
    	<option value="Pacific/Chatham">(GMT+12:45) Chatham Islands</option>
    	<option value="Pacific/Tongatapu">(GMT+13:00) Nuku'alofa</option>
    	<option value="Pacific/Kiritimati">(GMT+14:00) Kiritimati</option>
    </select>
    
    <button>Install</button>
    </form>
    <?php
}
?>

</div>
<script>

    $(function(){
    
        $('html, body').css('overflow', 'auto');
    
        $('#install').on('submit',function(e){
            e.preventDefault();
            
            // Check empty fields
            
            empty_fields = false;
            $('input').each(function(){
                if($(this).val()=='' && $(this).attr('name')!='path'){ empty_fields = true; }
            });
            
            if(empty_fields){ alert('All fields must be filled out'); }
            
            // Check password
            password_match = true;
            if($('input[name="password"]').val()!=$('input[name="password_confirm"]').val()){
                password_match = false;
            }
            
            // Check Path
            check_path = true;
            projectPath = '';
            if($('input[name="project_path"]').length) {
                projectPath = $('input[name="project_path"]').val();
            }
            
            if ( projectPath.indexOf("/") == 0 ) {
                check_path = confirm('Do you really want to create project with absolute path "' + projectPath + '"?');
            } 
            
            if(!password_match){ alert('The passwords entered do not match'); }
            
            if(!empty_fields && password_match && check_path){
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
