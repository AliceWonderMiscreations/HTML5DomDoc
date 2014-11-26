HTML5DomDoc
===========

Class for serving HTML via php DOMDocument

What This Class Does
--------------------

This class makes it a little easier to sever HTML content that exists as a
libxml2 DOMDocument class instance.

It only serves content as XML (intentionally) so Internet Explorer <= 8 users
are screwed, but I really do not give a damn about them. Sorry, I just do not.

The CSP stuff in it is very experimental.

Better README will come.

MIT license, just like the older version of this I have on phpclasses is.




scriptManager.class.php
=======================

That file is just there as an example of how the html5domdoc class public
functions `addJavaScript` and `addStyleSheet` can be used as part of a script
/ CSS manager.

That class file as it is will probably not work on your system as it uses a
constant `__PLUGIN__` that is not likely to be defined on your system or in the
way this class expects it to be.

Nutshell what this class does, I can initialize it even before I have created
the DOM object. It keeps track of what script and style sheets a page wants to
use and then adds them to the DOM using the facilities of the `html5domdoc`
class. It is just here as an example, it is not intended to be a generic class
that easily works in any project.