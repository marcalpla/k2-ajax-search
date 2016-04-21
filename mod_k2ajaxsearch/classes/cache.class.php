<?php 
/*------------------------------------------------------------------------
# mod_jo_accordion - Vertical Accordion Menu for Joomla 1.5 
# ------------------------------------------------------------------------
# author    Roland Soos 
# copyright Copyright (C) 2011 Offlajn.com. All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://www.offlajn.com
-------------------------------------------------------------------------*/
?>
<?php
// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

if(!defined('OfflajnSearchThemeCache')) {
  define("OfflajnSearchThemeCache", null);

  jimport('joomla.registry.registry');
  jimport('joomla.filesystem.path');
  jimport('joomla.filesystem.file');
  jimport('joomla.filesystem.folder');

  class OfflajnSearchThemeCache extends JRegistry{
    
    var $module;
    
    var $params;
    
    var $env;
  
    var $cachePath;
    
    var $cacheUrl;
    
    var $cssCompress;
    
    var $jsCompress;
    
    var $js;
    
    var $css;
    
    function OfflajnSearchThemeCache($namespace, &$_module, &$_params){
      $this->cssCompress = 1;
      $this->jsCompress = 1;
      $this->js = array();
      $this->css = array();
      $this->module = &$_module;
      $this->params = &$_params;
      $this->env = array('params' => &$_params);
      
      $writeError = false;
      $folder = $this->module->id;
      $registry = JFactory::getConfig();
      $curLanguage = (version_compare(JVERSION,'3.0.0','ge') ? $registry->get("joomfish.language") : $registry->getValue("joomfish.language"));
      if(is_object($curLanguage)){
        $folder.= '-lang'.$curLanguage->get('lang_id');
      }else if(is_string($curLanguage) &&  $curLanguage != ''){
        $folder.= '-lang'.$curLanguage;
      }
      $this->cachePath = JPath::clean(JPATH_SITE.DS.'modules'.DS.$this->module->module.DS.'cache'.DS.$folder.DS);
      if(!JFolder::exists($this->cachePath)) JFolder::create($this->cachePath , 0777);
      if(!JFolder::exists($this->cachePath)){
        $writeError = true;
      }
      if($writeError){
        JText::printf("%s is unwriteable or non-existent, because the system does not allow the operation from PHP. Please create the directory and set the writing access!", $this->cachePath);
        exit;
      }
      $this->cacheUrl = JURI::base(true).'/modules/'.$this->module->module.'/cache/'.$folder.'/';
      $this->moduleUrl = JURI::base(true).'/modules/'.$this->module->module.'/';
    }
    
    /*
      return the two url in an array
    */
    function generateCache(){
      return array($this->generateCSSCache(), $this->generateJSCache());
    }
    
    function addCss($css){
      if(!in_array($css, $this->css)){
        $this->css[] = $css; 
      }
    }
    
    /*
      This vars will be available in the CSS as $$k
    */
    function addCssEnvVars($k, &$v){
      $this->env[$k] = &$v;
    }
    
    function generateCssName(){
      $cachetext = '';
      foreach($this->css as $css){
        $cachetext.=$css.filemtime($css);
      }
      $hash = md5($cachetext.serialize($this->params->toArray()));
      return $hash.'.css';
    }
    
    function generateCSSCache(){
      $cssName = $this->generateCssName();
      $file = $this->cachePath.$cssName;
      if(!is_file($file)){
        $needToDelete = JFolder::files($this->cachePath, '(css)|(png)|(jpg)|(svg)$', false, true);
        if(is_array($needToDelete) && count($needToDelete) > 0){
          JFile::delete($needToDelete); // CSS cache cleaned
        }
        $ks = array_keys($this->env);
        for($i = 0;$i < count($ks); $i++ ){
          $$ks[$i] = &$this->env[$ks[$i]];
        }
        ob_start();
        foreach($this->css AS $css){
          include($css);
        }
        $rawcss = ob_get_contents();
        ob_end_clean();
        
        /*if(class_exists('CssMin')){
          //$rawcss = CssMin::minify($rawcss);
        }*/
        file_put_contents($file, $rawcss);
      }
      return $this->cacheUrl.$cssName; // url to the CSS
    }
    
    function addJs($js){
      if(!in_array($js, $this->js)){
        $this->js[] = $js; 
      }
    }
    
    function generateJsName(){
      $cachetext = '';
      foreach($this->js as $js){
        $cachetext.=$js.filemtime($js);
      }
      $hash = md5($cachetext);
      return $hash.'.js';
    }
    
    function generateJSCache(){
      $jsName = $this->generateJsName();
      $file = $this->cachePath.$jsName;
      if(!is_file($file)){
        $needToDelete = JFolder::files($this->cachePath, 'js$', false, true);
        if(is_array($needToDelete) && count($needToDelete) > 0){
          JFile::delete($needToDelete); // JS cache cleaned
        }
        $jst= "(function(){";
        foreach($this->js as $js){
          $jst.= file_get_contents($js)."\n";
        }
        $jst.= "})();";
        file_put_contents($file, $jst);
      }
      return $this->cacheUrl.$jsName; // url to the JS
    }
    
    
    function getFilesFromCache(){
      
    }

  	function set($key, $value = '', $group = '_default'){
  		return $this->setValue($group.'.'.$key, (string) $value);
  	}
  
  	function get($key, $default = '', $group = '_default'){
  		$value = (version_compare(JVERSION,'3.0.0','ge') ? $this->get($group.'.'.$key) : $this->getValue($group.'.'.$key));
  		$result = (empty($value) && ($value !== 0) && ($value !== '0')) ? $default : $value;
  		return $result;
  	}
  	
  	function def($key, $default = '', $group = '_default') {
  		$value = $this->get($key, (string) $default, $group);
  		return $this->set($key, $value);
  	}
  }
}
?>