<?php

/* Copyright 2014 Alice Wonder
 *  Based on earlier class I wrote for my un-finished
 *  DOMBlogger project
 *
 * Nutshell, this class only supports html5 and only
 *  supports sending content as application/xhtml+xml
 *  -- I ripped other support out
 *
 * Then I added some cool new stuff, like CSP and
 *  adding scripts/js and sanitizing the body.
 */

class html5domdoc {
	private $dom;
	public $xmlHtml;
	public $xmlHead;
	public $xmlBody;
	private $rtalabel = FALSE;
	private $sendcsp = FALSE;
	private $keywords = array();
	private $description = '';
	private $policy = array();
	private $cspstring;
	private $objectwhitelist = array('text/plain', 'text/html', 'image/webp', 'application/pdf', 'application/xhtml+xml');
	private $xmlns = 'http://www.w3.org/1999/xhtml';
	private $xmlLang = 'en';
	
	/* any function that uses head / body should call this */
	private function domNodes() {
		$this->xmlHtml = $this->dom->getElementsByTagName('html')->item(0);
		$this->xmlHead = $this->dom->getElementsByTagName('head')->item(0);
		$this->xmlBody = $this->dom->getElementsByTagName('body')->item(0);
	}
	
	//creates Content Security Policy
	private function generateCSP() {
	  //foreach($this->policy as $directive) {
	  //  $key = key($directive);
	  //  array_unique($directive);
	  //}
	  $bar = array();
    while($directive = current($this->policy)) {
      $foo = array();
      $key = key($this->policy);
      $new = $this->policy[$key];
      foreach($new as $value) {
        if(! in_array(trim($value), $foo)) {
          $foo[] = trim($value);
        }
      }
      $bar[$key] = $foo;
      next($this->policy);
    }
	  
	  $this->cspstring = 'default-src ' . implode(' ', $bar['default-src']);
	  if(isset($bar['script-src'])) {
	    $this->cspstring .= '; script-src ' . implode(' ', $bar['script-src']);
	  }
	  if(isset($bar['object-src'])) {
	    $this->cspstring .= '; object-src ' . implode(' ', $bar['object-src']);
	  }
	  if(isset($bar['img-src'])) {
	    $this->cspstring .= '; img-src ' . implode(' ', $bar['img-src']);
	  } else {
	    $this->cspstring .= '; img-src *';
	  }
	  if(isset($bar['media-src'])) {
	    $this->cspstring .= '; media-src ' . implode(' ', $bar['media-src']);
	  }
	  if(isset($bar['child-src'])) {
	    $this->cspstring .= '; child-src ' . implode(' ', $bar['child-src']);
	  }
	  //$this->cspstring .= '; child-src *';
	  if(isset($bar['frame-ancestors'])) {
	    $this->cspstring .= '; frame-ancestors ' . implode(' ', $bar['frame-ancestors']);
	  } else {
	    $this->cspstring .= "; frame-ancestors 'self'";
	  }
	  if(isset($bar['font-src'])) {
	    $this->cspstring .= '; font-src ' . implode(' ', $bar['font-src']);
	  }
	  if(isset($bar['connect-src'])) {
	    $this->cspstring .= '; connect-src ' . implode(' ', $bar['connect-src']);
	  }
	  if(isset($bar['form-action'])) {
	    $this->cspstring .= '; form-action ' . implode(' ', $bar['form-action']);
	  }
	  if(isset($bar['style-src'])) {
	    $this->cspstring .= '; style-src ' . implode(' ', $bar['style-src']);
	  }
	  //hack for firefox
	  if(isset($bar['frame-src'])) {
	    $this->cspstring .= '; frame-src ' . implode(' ', $bar['frame-src']);
	  }
	  /* I need to study these next three */
	  if(isset($bar['plugin-types'])) {
	    $this->cspstring .= '; plugin-types ' . implode(' ', $bar['plugin-types']);
	  }
	  //$this->cspstring .= '; plugin-types *'; // . implode(' ', $bar['plugin-types']);
	  if(isset($bar['reflected-xss'])) {
	    $this->cspstring .= '; reflected-xss ' . implode(' ', $bar['reflected-xss']);
	  }
	  if(isset($bar['sandbox'])) {
	    $this->cspstring .= '; sandbox ' . implode(' ', $bar['sandbox']);
	  }
	  /* I do not suggest using below */
	  if(isset($bar['report-uri'])) {
	    $this->cspstring .= '; report-uri ' . end($bar['report-uri']);
	  }
	  
	}
	
	/* Puts head elements in logical order, called by sendPage */
	private function adjustHead() {
	  if ($this->sendcsp) {
	    $this->generateCSP();
	  }
		$metaEquiv = array();
		$metaName  = array();
		$links     = array();
		$scripts   = array();
		$misc      = array();
		$newHead = $this->dom->createElement('head');
		
		$children = $this->xmlHead->childNodes;
		foreach ($children as $child) {
			$newChild = $child->cloneNode(true);
			$tag = $newChild->tagName;
			switch ($tag) {
				case 'meta' :
					if ($newChild->hasAttribute('http-equiv')) {
						$equiv = $newChild->getAttribute('http-equiv');
						if (strcmp($equiv, 'X-Content-Security-Policy') === 0) {
							$newHead->appendChild($newChild);
						} else {
							$metaEquiv[] = $newChild;
						}
					} else {
						$metaName[] = $newChild;
					}
							break;
				case 'link' :
					$links[] = $newChild;
							break;
				case 'script' :
					$scripts[] = $newChild;
							break;
				case 'title' :
					$newTitle = $newChild;
							break;
				default :
					$misc[] = $newChild;
							break;
			}
		}
		
		$j = count($metaEquiv);
		for ($i=0; $i<$j; $i++) {
			$newHead->appendChild($metaEquiv[$i]);
		}
		
		$meta = $this->dom->createElement('meta');
			$meta->setAttribute('charset', 'UTF-8');
			$newHead->appendChild($meta);
		
		if ($this->rtalabel) {
			$meta = $this->dom->createElement('meta');
				$meta->setAttribute('name', 'RATING');
				$meta->setAttribute('content', 'RTA-5042-1996-1400-1577-RTA');
				$newHead->appendChild($meta);
		}
		
		$j = count($this->keywords);
		if ($j > 0) {
			$content = implode(',', array_unique($this->keywords));
			$meta = $this->dom->createElement('meta');
				$meta->setAttribute('name', 'keywords');
				$meta->setAttribute('content', $content);
				$newHead->appendChild($meta);
		}
		
		if (strlen($this->description) > 0) {
			$meta = $this->dom->createElement('meta');
				$meta->setAttribute('name', 'description');
				$meta->setAttribute('content', $this->description);
				$newHead->appendChild($meta);
		}
		
		$genstring = 'PHP ' . phpversion() . ' DOMDocument/libxml2 ' . LIBXML_DOTTED_VERSION;
		$meta = $this->dom->createElement('meta');
			$meta->setAttribute('name', 'generator');
			$meta->setAttribute('content', $genstring);
			$newHead->appendChild($meta);
			
		$j = count($metaName);
		for ($i=0; $i<$j; $i++) {
			$newHead->appendChild($metaName[$i]);
		}
		
		$j = count($links);
		for ($i=0; $i<$j; $i++) {
			$newHead->appendChild($links[$i]);
		}
		
		$j = count($scripts);
		for ($i=0; $i<$j; $i++) {
			$newHead->appendChild($scripts[$i]);
		}
		
		$j = count($misc);
		for ($i=0; $i<$j; $i++) {
			$newHead->appendChild($misc[$i]);
		}
		
		if (! isset($newTitle)) {
			$newTitle = $this->dom->createElement('title', 'Page Title');
		}
		$newHead->appendChild($newTitle);
		
		$this->xmlHead->parentNode->replaceChild($newHead, $this->xmlHead);
	}
	
	private function sanitizeBody() {
	  $nodelist = $this->xmlBody->getElementsByTagName('script');
	  $n = $nodelist->length;
	  for($j = $n; --$j >= 0;) {
	    $nodelist->item($j)->parentNode->removeChild($nodelist->item($j));
	  }
	  $nodelist = $this->xmlBody->getElementsByTagName('embed');
	  $n = $nodelist->length;
	  for($j = $n; --$j >= 0;) {
	    $nodelist->item($j)->parentNode->removeChild($nodelist->item($j));
	  }
	  $nodelist = $this->xmlBody->getElementsByTagName('applet');
	  $n = $nodelist->length;
	  for($j = $n; --$j >= 0;) {
	    $nodelist->item($j)->parentNode->removeChild($nodelist->item($j));
	  }
	  $nodelist = $this->xmlBody->getElementsByTagName('object');
	  $n = $nodelist->length;
	  for($j = $n; --$j >= 0;) {
	    $node = $nodelist->item($j);
	    $type = 'null';
	    if($node->hasAttribute('type')) {
	      $type = strtolower(trim($node->getAttribute('type')));
	    }
	    if(in_array($type, $this->objectwhitelist)) {
	      $node->setAttribute('typemustmatch', 'typemustmatch');
	    } else {
	      $node->parentNode->removeChild($node);
	    }
	  }
	}
	
	//sends the headers, called by sendPage
	private function sendHeader() {
	  if ($this->sendcsp) {
	    header('Content-Security-Policy: ' . $this->cspstring);
	  }
		if ($this->rtalabel) {
			header('Rating: RTA-5042-1996-1400-1577-RTA');
		}
		header('Content-Type: application/xhtml+xml; charset=utf-8');
	}
	
	public function rtalabel() {
		$this->rtalabel = true;
	}
	
	public function usecsp() {
		$this->sendcsp = true;
	}
	
	public function addPolicy($directive, $allowed) {
	  if(isset($this->policy[$directive])) {
	    $this->policy[$directive][] = $allowed;
	  } else {
	    $new = array("'self'", $allowed);
	    $this->policy[$directive] = $new;
	  }
	}
	
	public function whiteListObject($type) {
	  $type = strtolower(trim($type));
	  if(! in_array($type, $this->objectwhitelist)) {
	    $this->objectwhitelist[] = $type;
	  }
	}
	
	public function addKeywords($arg=array()) {
		if (is_array($arg)) {
			$this->keywords = array_merge($this->keywords, $arg);
		} else {
			$this->keywords[] = $arg;
		}
	}
	
	public function addDescription($desc) {
		$this->description = $desc;
	}
	
	public function addStyleSheet($stylename, $serverpath, $fspath="") {
	  $stylename = trim($stylename);
	  $serverpath = trim($serverpath);
	  $fspath = trim($fspath);
	  $this->domNodes();
	  if(strlen($fspath) > 0) {
	    $fullpath = $fspath . $stylename;
	    if(file_exists($fullpath)) {
	      $modtime = filemtime($fullpath);
	      $stylename = preg_replace('/\.css$/', '-' . $modtime . '.css', $stylename);
	    }
	  }
	  $style = $this->dom->createElement('link');
	  $style->setAttribute('type', 'text/css');
	  $style->setAttribute('href', $serverpath . $stylename);
	  $style->setAttribute('rel', 'stylesheet');
	  $this->xmlHead->appendChild($style);
	}
	
	public function addJavaScript($scriptname, $serverpath, $fspath="") {
	  $scriptname = trim($scriptname);
	  $serverpath = trim($serverpath);
	  $fspath = trim($fspath);
	  $this->domNodes();
	  if(strlen($fspath) > 0) {
	    $fullpath = $fspath . $scriptname;
	    if(file_exists($fullpath)) {
	      $modtime = filemtime($fullpath);
	      $scriptname = preg_replace('/\.js$/', '-' . $modtime . '.js', $scriptname);
	    }
	  }
	  $script = $this->dom->createElement('script');
	  $script->setAttribute('type', 'application/javascript');
	  $script->setAttribute('src', $serverpath . $scriptname);
	  $this->xmlHead->appendChild($script);
	}
	
	public function sendPage() {
		$this->domNodes();
		$this->sanitizeBody();
		$this->adjustHead();
		$this->xmlHtml->setAttribute('xmlns', $this->xmlns);
		$this->xmlHtml->setAttributeNS('http://www.w3.org/XML/1998/namespace', 'xml:lang', $this->xmlLang);
		$this->sendHeader();
		print $this->dom->saveXML();
	}
	
	public function html5domdoc($dom, $xmlLang="en") {
		$this->xmlLang = $xmlLang;
		$this->dom = $dom;
		$docstring = '<?xml version="1.0" encoding="UTF-8"?><!DOCTYPE html><html><head /><body /></html>';
		$this->dom->loadXML($docstring);
		$this->domNodes();
		$self = array("'self'");
		$this->policy['default-src'] = $self;
		//$none = array("'none'"); //just for testing
		//$this->policy['object-src'] = $none;
	}
} //end of class

/* http://opensource.org/licenses/MIT

The MIT License (MIT)

Copyright (c) <year> <copyright holders>

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.

*/

/* <copyright holders>: Alice Wonder
                <year>: 2014
*/

?>