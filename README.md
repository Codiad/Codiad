# Atheos Web IDE, updated from [Codiad](http://codiad.com/)

![Screenshot: Atheos](/docs/atheos.png?raw=true "Atheos")

Atheos is an updated and currently maintained fork of Codiad.

Codiad is a web-based IDE framework with a small footprint and minimal requirements. 

Codiad was built with simplicity in mind, allowing for fast, interactive development without the massive overhead of some of the larger desktop editors. That being said even users of IDE's such as Eclipse, NetBeans and Aptana are finding Codiad's simplicity to be a huge benefit. While simplicity was key, we didn't skimp on features and have a team of dedicated developer actively adding more.

For more information on the project please check out the **[check out the Wiki](https://github.com/HLSiira/Atheos/wiki)** or **[the Atheos Website](http://www.codiad.com)**

## ~~Un~~ Maintained Status:

Atheos is Codiad to its core, however Atheos will be updated to use all the latest versions of libraries used if possible. Where not possible, newer libraries will replace older libraries. The front end has/is being updated with a new flat look, Font Awesome Icons in place of EnTypo, and eventually planning on replacing the SVGs for the file extensions with a smaller web font.

## RoadMap:
01. Update all dependendencies
    - Ace Editor has been updated, but it needs some work
    - jQuery is actually so out of date that it will require a lot to update it
02. Remove jQuery
    - I prefer pure javascript and it's a great learning experience
03. Standardize all modules/components
    - Guessing here, but it looks like through development, as the code base grew, practices & standards were changed and so I'm going to try to put everything on the same level
04. Modify everything to meet best practices, SEO and accessabilty standards.
    - Make everything pretty, new icons, cleaner design, better ingredients

## Planned goals/features
| 01 | Closing tabs sometimes has issues, only way is to refresh |
| 02 | Marketplace gets mad if something is missing              |
| 03 | Settings sometimes don't stick and have to be reset       |
| 04 | Git Plugin seems to struggle with larger folders          |
| 05 | Apache complains of an unknown filter: includes           |
| 06 | In-Browser Theme Support                                  |
| 07 | Reduce client requests as much as possible                |
| 08 | Address open issues in original Codiad repo(s)            |
| 09 | Update all the themes I found on the internet             |
| 10 | Autosave / caching functions for file changes             |