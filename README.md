HTML5DomDoc
===========

Class for serving HTML via php DOMDocument

What This Class Does
--------------------

This class makes it a little easier to sever HTML content that exists as a
libxml2 DOMDocument class instance.

Basically what it does is a lot of the dirty work, such as setting up the
document type and minimal page structure, organization of the `head` node, and
sending the proper HTTP headers when the page is served.

It only serves content as XML (intentionally) so Internet Explorer <= 8 users
are screwed, but I really do not give a damn about them. Sorry, I just do not.

The CSP stuff in it is very experimental.

Better README will come.

MIT license, just like the older version of this I have on phpclasses is.

Initialize the Class
--------------------

The class does not create the DOM object itself, you need to create that prior
to creating an instance of the class:

    $dom = new DOMDocument("1.0", "UTF-8");
    $dom->formatOutput = TRUE;
    $dom->preserveWhitespace = FALSE;

The `formatOutput` and `preserveWhitespace` options are not necessary, but they
tend to make the generated output easier to read. Once you have created your
DOMDocument object, you can create an instance of the html5domdoc class:

    $html5 = new html5domdoc($dom);
    $head = $html5->xmlHead;
    $body = $html5->xmlBody;
    
The first argument when initializing the class must be a DOMDocument class
instance. An optional second argument allows you to define the XML lang. This
corresponds with the `xml:lang` attribute of the `html` root node of the
DOMDocument object. The default is `en`.

In the above code example, `$html5->xmlHead` is a public property of the class
that corresponds to the `head` node of the document object. `$html5->xmlBody`
is the public property of the class that corresponds to the `body` node of the
document object.

You can create child nodes using the DOMDocument class as needed and append
them to those nodes.

Enabling Features
-----------------

Blah


scriptManager.class.php
-----------------------

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