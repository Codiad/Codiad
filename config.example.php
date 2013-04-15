<?php

/*
*  Copyright (c) Codiad & Kent Safranski (codiad.com), distributed
*  as-is and without warranty under the MIT License. See
*  [root]/license.txt for more. This information must remain intact.
*/

//////////////////////////////////////////////////////////////////
// PATH
//////////////////////////////////////////////////////////////////

$rel = "/";
define("BASE_PATH",$_SERVER["DOCUMENT_ROOT"] . $rel);
define("COMPONENTS",BASE_PATH . "/components");
define("THEMES",BASE_PATH . "/themes");
define("DATA",BASE_PATH . "/data");
define("WORKSPACE",BASE_PATH . "/workspace");
define("WSURL",$_SERVER["HTTP_HOST"] . $rel . "/workspace");

//////////////////////////////////////////////////////////////////
// THEME
//////////////////////////////////////////////////////////////////

define("THEME", "default");

//////////////////////////////////////////////////////////////////
// ABSOLUTE PATH
//////////////////////////////////////////////////////////////////

define("WHITEPATHS", $_SERVER["DOCUMENT_ROOT"].",/home");

//////////////////////////////////////////////////////////////////
// SESSIONS
//
// $cookie_lifetime          Default 0        e.g. 7200
// $cookie_path              Default /        e.g. /codiad 
//////////////////////////////////////////////////////////////////

$cookie_lifetime = "0";

//////////////////////////////////////////////////////////////////
// TIMEZONE
//////////////////////////////////////////////////////////////////

date_default_timezone_set("America/Chicago");

//////////////////////////////////////////////////////////////////
// TAB SIZE
//////////////////////////////////////////////////////////////////

define("tabsize", 4);
define("softtabs", false);

?>
