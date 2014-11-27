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

Public Methods
--------------

Public methods are functions you can call within the class. For example, if we
called our instance of the class `$html5` as in the example above:

    $html5->methodName($args);
    
That would call the public method `methodName()` with the arguments `$args`.

### rtalabel()

If the web page contains content that is not suitable for children, you should
use the `rtalabel` method method. This method takes no arguments. When served,
the RTALabel header will be sent with the content and the RTALabel meta tag
will be added to the document `head` node.

RTALabel allows parental content filters to block the content from being
displayed to children.

### usecsp()

If you wish to use Content Security Policy, this method will result in a CSP
header being sent with the content. CSP features are not yet fully implemented.

By default, using the `usecsp()` method will result in a policy keyword of
`'self.` being used for most CSP policies. An exception is images, where by
default a policy allowing images to be served from any host is sent.

A more detailed explanation of CSP will follow.

### addPolicy($directive, $allowed)

This method allows you to fine-tune the CSP policy by specifying the directive
to modify and what host you want to white-list for that directive. For example
if you want to add a YouTube video using the YouTube object method and you are
using CSP, you would have to whitelist some servers or CSP would block the
video from being played:

    $html5->addPolicy('object-src', '*.youtube.com');
    $html5->addPolicy('object-src', '*.ytimg.com');
    $html5->addPolicy('object-src', '*.googlevideo.com');
    
If you wanted to use the iframe method for embedding YouTube:

    $html5->addPolicy('frame-src', 'www.youtube.com');
    
A more default explanation of CSP will follow.

### whiteListObject($type)

The `object` element in HTML is often used maliciously to inject malware into
web pages. CSP can be used the thwart those attacks, but not all browsers
support CSP.

This class will remove object nodes that do not have a `type` attribute within
a white-listed set of allowed `type` attributes. By default, only the following
types are allowed:

* text/plain
* text/html'
* image/webp
* application/pdf
* application/xhtml+xml

With the exception of `image/webp` and `application/pdf` those types are almost
always handled by the browser. `image/webp` is handled by some browsers, and
`application/pdf` is handled by some browsers.

To add additional object types to the whitelist, use the `whiteListObject()`
method with the `type` you wish to add as the argument. For example, if you use
flash in your content, you need to use

    $html5->whiteListObject('application/x-shockwave-flash');
    
or the class will remove the object node from the DOM before serving.

### addKeywords($arg)

This method is used for adding keywords to your document that will be included
in the `keywords` meta tag within the `head` node.

The argument `$arg` can either be an array of strings or a string.

You do not have to use this function, you can also create a `keywords` meta tag
using the standard libxml2 DOMDocument functions and append it as a child to to
the `head` node.

### addDescription($desc)

This method is used for adding a description to your document that will be
included in the `description` meta tag with the `head` node. The `$desc`
argument is a string.

You do not have to use this function, you can also create a `description` meta
tag using the standard libxml2 DOMDocument functions and append it as a child
to to the `head` node.

### addStyleSheet($stylename, $serverpath, $fspath)

This method creates a `link` tag that references a CSS style sheet and appends
it to the document `head` node.

The first argument `$stylename` refers to the file name of the style sheet, for
example `layout.css`. The second argument `$serverpath` references the
directory on the web where the stylesheet is served from. For example `/css/`
or `http://someserver.tld/css/`.

The third argument `$fspath` is optional and should only be used if the
stylesheet is located on the local web server *and* you have a wrapper that
wants the modification timestap inserted into the stylesheet name as served
to the requesting clients.

This is useful for using my `scriptCache` class that you can get from
https://github.com/AliceWonderMiscreations/aliceScriptCache

When the third argument is present, the filename will be modified to include
the modification timestamp of the stylesheet in seconds since UNIX epoch.

For example:

    $html5->addStyleSheet('layout.css', '/css/', '/var/www/html/css/');
    
would result in

    <link href="/css/layout-xxxxxx.css" otheratts />

where `xxxxxx` is the modification timestamp.

Only use the third argument if you want the modification timestamp inserted
into the filename.

You do not have to use this method to add a stylesheet. You can also create a
link node using the standard libxml2 DOMDocument functions and append it as a
child of the `head` node.

### addJavaScript($scriptname, $serverpath, $fspath)

See the notes for `addStyleSheet()`

Serving the Page
----------------

When you have finished appending the content you want to the document `body`
(and any additions to `head`) the page is served using the `sendPage()`
method. For example:

    $html5->sendPage();
    
When that method is called, the class will clean up the `head` and do some
sanitizing of the `body`, send the HTTP headers, and serve the page.

Important Notes
---------------

### Mime Type

This class serves the content as `application/xhtml+xml`

There are two side effects you should be aware of:

1. Some older browsers, such as Internet Explorer <= 8, do not handle that mime
   type. I use to care and detect whether or not the client could accept it,
   but at this point I no longer give a damn about those browsers. Ignoring
   those insecure legacy browsers allows me to use jQuery 2 and not worry about
   their CSS/JS quirks. This class is for HTML5 and is intended for HTML5
   clients, all of which handle content sent as XML.
2. In XML, all of the various named entities that HTML supports are not
   supported. The only named entities that are supported are:
   
   * `&amp;`
   * `&lt;`
   * `&gt;`
   * `&quot;`
   * `&apos;`
   
   For other glyphs, you should use the action glyph or the decimal / hex
   entity. For example, use `&#160;` instead of `&nbsp;`.
   

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