<?php
    /*
    *  Copyright (c) Codiad & Kent Safranski (codiad.com), distributed
    *  as-is and without warranty under the MIT License. See
    *  [root]/license.txt for more. This information must remain intact.
    */

    CustomAuth::login();

    class CustomAuth {

       public static function login(){

            // do whatever you do for auth and provide credentials in array $logged_in


            $users = getJSON('users.php');
            foreach($users as $user)
            {
                if($user['username']==$logged_in['username'] && $user['password']==$logged_in['password'])
                {
                    $_SESSION['user'] = $logged_in['username'];
                    $_SESSION['lang'] = $logged_in['lang'];
                    $_SESSION['theme'] = "default";
                    if($user['project']!=''){ $_SESSION['project'] = $user['project']; }
                    unlink($logged_in_file);
                    break;
                }
            }
       }
   }



