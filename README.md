# Axiom Web IDE, updated from [Codiad](http://codiad.com/)

![Screenshot: Codiad VS Axiom](/docs/axiom-codiad.png?raw=true "Axiom compared to Codiad")

Codiad is a web-based IDE framework with a small footprint and minimal requirements. 

Codiad was built with simplicity in mind, allowing for fast, interactive development without the massive overhead of some of the larger desktop editors. That being said even users of IDE's such as Eclipse, NetBeans and Aptana are finding Codiad's simplicity to be a huge benefit. While simplicity was key, we didn't skimp on features and have a team of dedicated developer actively adding more.

For more information on the project please check out the **[check out the Wiki](https://github.com/HLSiira/Axiom/wiki)** or **[the Codiad Website](http://www.codiad.com)**

## ~~Un~~ Maintained Status:

Axiom is Codiad to its core, however Axiom will be updated to use all the latest versions of libraries used if possible. Where not possible, newer libraries will replace older libraries. The front end has/is being updated with a new flat look, Font Awesome Icons in place of EnTypo, and eventually planning on replacing the SVGs for the file extensions with a smaller web font.

## Current changes from Codiad to Axiom:
01. Updated Ace Editor
02. 90% replaced Entypo with FontAwesome
03. New File Icons in FileManager, aixture of (Flat-Remix)[https://drasite.com/flat-remix] / (File-Icon-Vectors)[https://fileicons.org/]
04. Compressed CSS files into a single SCSS compiled CSS
05. Minor CSS and IDE changes


## Planned features / Goals:
| 01 | Update JQuery where required, where not required, attempt to remove it all together                     | Package all JS files into 1 application                           |                                                                                |
|----|---------------------------------------------------------------------------------------------------------|-------------------------------------------------------------------|--------------------------------------------------------------------------------|
| 02 | Using a PHP script, have all plugins returned as single JS/CSS files                                    |                                                                   |                                                                                |
| 03 | Add In-Browser Theme support (Change file icon colors, Etc.) (Fonts)                                    |                                                                   |                                                                                |
| 04 | Address open issues on Original Codiad Repo                                                             |                                                                   |                                                                                |
| 05 | Update all plugins                                                                                      | Create plugins for bundling JS files, compiling Typescript & LESS | Update beautification for more language support & add minification/compression |
| 06 | Fix Market Place  The Marketplace loads slowly, uses embedded CSS, & listens for old Codiad Marketplace |                                                                   |                                                                                |
| 07 | Update default theme to match modern requirements (SCSS, File locations, Favicons)                      |                                                                   |                                                                                |
| 08 | Autosave / Caching functions for file changes                                                           |                                                                   |                                                                                |
| 09 | Fix file/folder naming issues (spaces)                                                                  |                                                                   |                                                                                |
| 10 |                                                                                                         |                                                                   |                                                                                |


| 01 | Closing tabs sometimes has issues, only way is to refresh |
|----|-----------------------------------------------------------|
| 02 | Marketplace gets mad if something is missing              |
| 03 |                                                           |
| 04 |                                                           |
| 05 |                                                           |
| 06 |                                                           |
| 07 |                                                           |
| 08 |                                                           |
| 09 |                                                           |
| 10 |                                                           |

Distributed under the MIT-Style License. See `LICENSE.txt` file for more information.
