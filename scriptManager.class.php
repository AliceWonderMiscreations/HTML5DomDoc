<?php

class scriptManager {

  private $scriptArray=array();
  private $styleArray=array();
  private $jswebpath;
  private $jslocalpath;
  private $jsplugin;
  private $csswebpath;
  private $csslocalpath;
  private $cssplugin;


  // this function adds the scripts to the DOM
  public function addToDOM($html5) {
    //add CSS
    $n = count($this->styleArray);
    if($n > 0) {
      $processed = array();
      for ($i=0; $i<$n; $i++) {
        $t = $this->styleArray[$i][0];
        if(! in_array($t, $processed)) {
          $processed[] = $t;
          $wpath = $this->csswebpath;
          if(strlen($this->styleArray[$i][2]) > 0) {
            $wpath .= $this->styleArray[$i][2] . '/';
            $lpath = preg_replace('/__PLUGIN__/', $this->styleArray[$i][2], $this->cssplugin);
          } else {
            $lpath = $this->csslocalpath;
          }
          if($this->styleArray[$i][3]) {
            $html5->addStyleSheet($this->styleArray[$i][1], $wpath, $lpath);
          } else {
            $html5->addStyleSheet($this->styleArray[$i][1], $wpath);
          }
        }
      }
    }
    
    //add JavaScript
    $n = count($this->scriptArray);
    if($n > 1) {
      $processed = array();
      for ($i=0; $i<$n; $i++) {
        $t = $this->scriptArray[$i][0];
        if(! in_array($t, $processed)) {
          $processed[] = $t;
          $wpath = $this->jswebpath;
          if(strlen($this->scriptArray[$i][2]) > 0) {
            $wpath .= $this->scriptArray[$i][2] . '/';
            $lpath = preg_replace('/__PLUGIN__/', $this->scriptArray[$i][2], $this->jsplugin);
          } else {
            $lpath = $this->jslocalpath;
          }
          if($this->scriptArray[$i][3]) {
            $html5->addJavaScript($this->scriptArray[$i][1], $wpath, $lpath);
          } else {
            $html5->addJavaScript($this->scriptArray[$i][1], $wpath);
          }
        }
      }
    }
  }

  // These two functions are how you let the class know there is a js/css file to add
  public function addStyle($script, $plugin='', $min=TRUE) {
    $script = trim($script);
    $plugin = trim($plugin);
    $name = preg_replace('/\.css$/', '', $script);
    if(strlen($plugin) > 0) {
      $name = $plugin . '_' . $name;
    }
    $a = array($name, $script, $plugin, $min);
    $this->styleArray[] = $a;
  }

  public function addScript($script, $plugin='', $min=TRUE) {
    $script = trim($script);
    $plugin = trim($plugin);
    $name = preg_replace('/\.js$/', '', $script);
    if(strlen($plugin) > 0) {
      $name = $plugin . '_' . $name;
    }
    $a = array($name, $script, $plugin, $min);
    $this->scriptArray[] = $a;
  }

  /* constructor */
  public function scriptManager($jswebpath, $jslocalpath, $jsplugin, $csswebpath, $csslocalpath, $cssplugin, $jq='jquery-2.1.1.min.js') {
    $this->jswebpath=trim($jswebpath);
    $this->jslocalpath=trim($jslocalpath);
    $this->jsplugin=trim($jsplugin);
    $this->csswebpath=trim($csswebpath);
    $this->csslocalpath=trim($csslocalpath);
    $this->cssplugin=trim($cssplugin);
    $jq=trim($jq);
    $jquery = array('jquery', $jq, '', FALSE);
    $this->scriptArray[] = $jquery;
  }



}

?>