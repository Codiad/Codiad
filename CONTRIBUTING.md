# How to Contribute

Since Codiad is still early in development we're very open about how contributions are made, however, to keep order to things please take the following 
into consideration:

* Check the issues to ensure that someone else isn't already working on the bug or feature
* Submit an issue for bugs and feature additions
* Familiarize yourself with the documentation in the [Wiki](https://github.com/Fluidbyte/Codiad/wiki)

There is an established format for `components` which utilizes one JS (`init.js`) and one CSS (`screen.css`) which is handled by the loader file. Any other 
resources used should be loaded or accessed from one of these.

**Don't Reinvent the Wheel!** There's an API and defined, easy-to-inderstand set of methods for a reason - use them.

Stick to the conventions defined in other components as closely as possible. 

* Utilize the same commenting structure
* Use underscores in namespaces instead of interCaps
* When working with the editor utilize the `active` object whenever possible instead of going direct to the `editor`

If you have questions, please ask. Submit an issue or [contact me (fluidbyte) directly](mailto:dev@codiad.com).