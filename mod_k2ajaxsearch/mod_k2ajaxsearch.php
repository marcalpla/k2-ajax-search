<?php 
/**
 * @version		2.1
 * @package		mod_k2ajaxsearch
 * @author		Taleia Software http://www.taleiasoftware.com
 * @copyright	Copyright (C) 2012 - 2013 Taleia Software		
				Copyright (C) 2011 Offlajn.com
				All rights reserved.
 * @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */
?>
<?php
  if( !defined( '_VALID_MOS' ) && !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );

  if (!extension_loaded('gd') || !function_exists('gd_info')) {
      echo "K2 Ajax Search needs the <a href='http://php.net/manual/en/book.image.php'>GD module</a> enabled in your PHP runtime 
      environment. Please consult with your System Administrator and he will 
      enable it!";
      return;
  }
  $_SESSION['fakeItemid'] = $_REQUEST['Itemid'];
  /* For demo parameter editor */
  if(defined('DEMO')){
     $_SESSION['module_id'] = $module->id;
    if(!isset($_SESSION[$module->module.'a'][$module->id])){
      $_SESSION[$module->module.'a'] = array();
      $a = $params->toArray();
      $a['params'] = $a;
      $params->loadArray($a);
      $_SESSION[$module->module."_orig"] = $params->toString();
      $_SESSION[$module->module.'a'][$module->id] = true;
      $_SESSION[$module->module."_params"] = $params->toString();
      header('LOCATION: '.$_SERVER['REQUEST_URI']);
    }
    if(isset($_SESSION[$module->module."_params"])){
      $params = new JRegistry();
      $params->loadJSON($_SESSION[$module->module."_params"]);
    }
    $a = $params->toArray();
    require_once(dirname(__FILE__).DS.'params'.DS.'library'.DS.'flatArray.php');
    $params->loadArray(offflat_array($a['params']));
  }
  $searchresultwidth = $params->get('resultareawidth', 250);
  $filterbuttonwidth = $params->get('filterbuttonwidth', 120);
  $productsperplugin = $params->get('itemsperplugin', 3);
  $minchars = $params->get('minchars', 3);
  $maxchars = $params->get('maxchars', 50);
  $resultalign = 0; // 0:left 1:right
  $scrolling = 1; //0:disable mouse scrolling 1:enable mouse scrolling
  $intro = 1; //0:disable show intro-text to the results 1:enable show intro-text to the results
  $stext = 'No results found. Did you mean?';
  $hidecatchooser = $params->get('hidecatchooser', 0);
  $allinonecatchooser = $params->get('allinonecatchooser', 0);
  $hidecatroot = $params->get('hidecatroot', 1);
  $catchooserfirst = $params->get('catchooserfirst', 1);
  $categories = $params->get('categories', null);
  $includecatchildrens  = $params->get('includecatchildrens', 1);
  $catchoosercaption  = $params->get('catchoosercaption', '');
  $categorylist = array();
  $categorieslist = array();
  $categorieskey = array();
  $extrafields = $params->get('extrafields', '');
  $extrafieldlist = array();
  $extrafieldids = array();    
  $searchboxcaption = $params->get('searchbox', 'Search..');
  $noresultstitle = $params->get('noresultstitle', 'Results (0)');
  $noresults = $params->get('noresults', 'No results found for the keyword!');
  $params->def('theme', 'simple');
  $theme = $params->get('theme', 'simple');
  if(is_object($theme)){ //For 1.6, 1.7, 2.5
    $params->merge(new JRegistry($params->get('theme')));
    $params->set('theme', $theme->theme);
    $theme = $params->get('theme');
  }  
  $keypresswait = $params->get('stimeout', 500);
  $searchformurl = JRoute::_('index.php');
  $document =& JFactory::getDocument();
  $showfromdate = $params->get('showfromdate', 0);
  $showtodate = $params->get('showtodate', 0);
  $showexactdate = $params->get('showexactdate', 0);
  $fromdatechoosercaption  = $params->get('fromdatechoosercaption', 'From date...');
  $todatechoosercaption  = $params->get('todatechoosercaption', 'To date...');
  $exactdatechoosercaption  = $params->get('exactdatechoosercaption', 'Date...');

  /*
  Build the Javascript cache and scopes
  */ 
  require_once(dirname(__FILE__).DS.'classes'.DS.'cache.class.php');
  $cache = new OfflajnSearchThemeCache('default', $module, $params);
  /*
  Build the CSS
  */ 
  $cache->addCss(dirname(__FILE__) .DS. 'themes' .DS. 'clear.css.php');
  $cache->addCss(dirname(__FILE__) .DS. 'themes' .DS. $theme .DS. 'theme.css.php');
  
  /*
  Load OfflajnAJAXSearchHelper and K2AJAXSearchHelper
  */
  require_once(dirname(__FILE__).DS.'helper'.DS.'Helper.class.php');
  
  /*
  Set up enviroment variables for the cache generation
  */
  $module->url = JURI::root(true).'/modules/'.$module->module.'/';
  $themeUrl = $module->url.'themes/'.$theme.'/';
  $cache->addCssEnvVars('themeurl', $themeUrl);
  $cache->addCssEnvVars('module', $module);
  $cache->addCssEnvVars('helper', new OfflajnAJAXSearchHelper($cache->cachePath));
  $cache->addCssEnvVars('productsperplugin', $productsperplugin);
  $cache->addCssEnvVars('searchresultwidth', $searchresultwidth);
  $cache->addCssEnvVars('hidecatchooser', $hidecatchooser);

  $cache->addJs(dirname(__FILE__).DS.'themes'.DS.'K2AJAXSearchBase.js');
  $cache->addJs(dirname(__FILE__).DS.'themes'.DS.$theme.DS.'js'.DS.'engine.js');
  $document->addScript('modules/mod_k2ajaxsearch/engine/localdojo.js');
  
  if($showfromdate == 1 || $showtodate == 1 || $showexactdate == 1) {
  	$document->addScript('modules/mod_k2ajaxsearch/engine/jquery.min.js');
	$document->addScript('modules/mod_k2ajaxsearch/engine/jquery-ui-datapicker.min.js');
	$document->addScript('modules/mod_k2ajaxsearch/engine/jquery-k2ajaxsearch-noconflict.js');  	
  	$document->addStyleSheet('modules/mod_k2ajaxsearch/engine/css/jquery-ui-datapicker.css');
  }
  
  /*
  Add cached contents to the document
  */
  $cacheFiles = $cache->generateCache();
  $document->addStyleSheet($cacheFiles[0]);
  $document->addScript($cacheFiles[1]);
  
  //Get categories 
  if(!empty($categories) && !is_array($categories))$categories = array($categories);
  foreach($categories as $key=>$category){
  	  $categorylist = array();
	  
	  if($includecatchildrens || $category == 0)
	    K2AJAXSearchHelper::getCategoryAndChildrens($category, 0, $categorylist, '&nbsp;&nbsp;&nbsp;', ($category == 0 || !$hidecatroot || $allinonecatchooser ? 1 : 2));
	  else
	    K2AJAXSearchHelper::getCategory($category, $categorylist);  
	  
	  $categorieslist[] = $categorylist;
	  $categorieskey[] = $key;	
  }
  
  //If all category choosers in a single one
  if($allinonecatchooser == 1) { 
  	$i=0;
  	foreach($categorieslist as $category) {
  		if($i>0) {
			$categorieslist[0] = array_merge($categorieslist[0],$categorieslist[$i]);
			unset($categorieslist[$i]);
			unset($categorieskey[$i]);
		}
		$i++;
  	}
  }

  //Get extra fields
  if(!empty($extrafields)){
    K2AJAXSearchHelper::getExtrafieldsMultiSelect($extrafields, $extrafieldlist); 
  }
  
  $document->addScriptDeclaration("
  dojo.addOnLoad(function(){
      new K2AJAXSearch".$theme."({
        id : '".$module->id."',
        node : dojo.byId('k2-ajax-search".$module->id."'),
        searchForm : dojo.byId('search-form".$module->id."'),
        textBox : dojo.byId('search-area".$module->id."'),
        fromDateInput : dojo.byId('from_date".$module->id."'),
        toDateInput : dojo.byId('to_date".$module->id."'),
        exactDateInput : dojo.byId('exact_date".$module->id."'),
        searchButton : dojo.byId('ajax-search-button".$module->id."'),
        closeButton : dojo.byId('search-area-close".$module->id."'),
        searchCategories : 'search-categories".$module->id."',
        searchExtraFields : 'search-extrafields".$module->id."',
        productsPerPlugin : $productsperplugin,
        searchRsWidth : $searchresultwidth,
        filterbuttonwidth : $filterbuttonwidth,
        minChars : $minchars,
        maxChars : $maxchars,
        searchBoxCaption : '$searchboxcaption',
        noResultsTitle : '$noresultstitle',
        noResults : '$noresults',
        searchFormUrl : '$searchformurl',
        enableScroll : '$scrolling',
        showIntroText: '$intro',
        lang: '".JRequest::getCmd('lang')."',
        stext: '$stext',
        moduleId : '$module->id',
        resultAlign : '$resultalign',
        keypressWait: '$keypresswait',
        hideCatChooser : $hidecatchooser,
        catchooserfirst : $catchooserfirst,
        extraFields : '".($extrafields == '' || empty($extrafieldlist) ? '' : implode(',', $extrafields))."',
        categoriesKey : '".($categorieskey == '' || empty($categorieskey) ? '' : implode(',', $categorieskey))."',
        fromDate : $showfromdate,
		toDate : $showtodate,
		exactDate : $showexactdate,
		fromdatechoosercaption : '$fromdatechoosercaption',
		todatechoosercaption : '$todatechoosercaption',
		exactdatechoosercaption : '$exactdatechoosercaption'
      })
    });"
  );

  if($showfromdate == 1) {
  	$document->addScriptDeclaration("  
	  	jq_k2as(function() {
			jq_k2as('#from_date" . $module->id . "').datepicker();
			jq_k2as('#ui-datepicker-div').wrap('<div class=\"k2-ajax-search-container\"></div>');
		});"
	); 
  } 
  
  if($showtodate == 1) {
	$document->addScriptDeclaration("  
		jq_k2as(function() {
			jq_k2as('#to_date" . $module->id . "').datepicker();
			jq_k2as('#ui-datepicker-div').wrap('<div class=\"k2-ajax-search-container\"></div>');
		});"
	);   	
  }
  
  if($showexactdate == 1) {
	$document->addScriptDeclaration("  
		jq_k2as(function() {
			jq_k2as('#exact_date" . $module->id . "').datepicker();
			jq_k2as('#ui-datepicker-div').wrap('<div class=\"k2-ajax-search-container\"></div>');
		});"
	);   	
  }  

?>

<div id="k2-ajax-search<?php echo $module->id; ?>">
  <div class="k2-ajax-search-container">
   <form id="search-form<?php echo $module->id; ?>" action="<?php echo $searchformurl ?>" method="get" onSubmit="return false;">
    <div class="k2-ajax-search-inner">
    <?php 
    	if ($hidecatchooser==0 && !empty($categorieslist)) {
    		foreach($categorieslist as $key=>$categorylist){  			
   				echo '<div class="category-chooser'.$module->id . $key.'">'; 
				if($key == 0 && !empty($catchoosercaption)){					
					echo ($catchoosercaption != ' ' ? '<div id="category-name' . $module->id . $key . '">' . $catchoosercaption . '</div>' : '');
				} else {
					echo '<div id="category-name' . $module->id . $key . '">' . ($categories[$key] == 0 ? 'All categories' : $categorylist[0]->name) . '</div>';
				}  				 
   				echo '<div class="arrow"></div></div>'; 			
    		}    		
    	}
    ?>
    <?php
      if(!empty($extrafieldlist)) {
        foreach($extrafieldlist as $extrafield) {
          echo '<div class="extrafield-chooser'.$module->id.$extrafield->id.'"><div id="extrafield-name'.$module->id.$extrafield->id.'">'.$extrafield->name.'</div><div class="arrow"></div></div>';
        }
      }
    ?>
      <input type="text" name="searchword" id="search-area<?php echo $module->id; ?>" value="" autocomplete="off" />
      <?php if ($showfromdate == 1) : ?><input type="text" name="fromdate" id="from_date<?php echo $module->id; ?>" placeholder="<?php echo $fromdatechoosercaption; ?>" /><?php endif; ?>
      <?php if ($showtodate == 1) : ?><input type="text" name="todate" id="to_date<?php echo $module->id; ?>" placeholder="<?php echo $todatechoosercaption; ?>" /><?php endif; ?>
      <?php if ($showexactdate == 1) : ?><input type="text" name="exactdate" id="exact_date<?php echo $module->id; ?>" placeholder="<?php echo $exactdatechoosercaption; ?>" /><?php endif; ?>
	  <input type="hidden" name="option" value="com_k2ajaxsearch" />
	  <input type="hidden" name="module_id" value="<?php echo $module->id; ?>" />

      <div id="search-area-close<?php echo $module->id; ?>"></div>
      <div id="ajax-search-button<?php echo $module->id; ?>"><div class="magnifier"></div></div>
      <div class="ajax-clear"></div>
    </div>
  </form>
  <div class="ajax-clear"></div>
  </div>
    <?php
      if($hidecatchooser==0 && !empty($categorieslist)) {
        foreach($categorieslist as $key=>$categorylist) {
          echo '<div id="search-categories'.$module->id.$key.'">';
          echo '<div class="search-categories-inner">';
          if(!empty($categorylist)) {
          	foreach ($categorylist as $category) {
				if($category->id != $categories[$key] || ($category->id == $categories[$key]) && !$hidecatroot || $allinonecatchooser)
                		echo '<div id="search-category-'.$category->id.'">'.$category->name.'</div>';
            }
	      }		        
          echo '</div>';
          echo '</div>';
        }
      }
    ?>   
    <?php
      if(!empty($extrafieldlist)) {
        foreach($extrafieldlist as $extrafield) {
          echo '<div id="search-extrafields'.$module->id.$extrafield->id.'">';
          echo '<div class="search-extrafields-inner">';
          if(!empty($extrafield->value)) {
            foreach($extrafield->value as $value)
              echo '<div id="search-extrafield-'.$extrafield->id.'-'.$value->value.'">'.$value->name.'</div>';
          }
          echo '</div>';
          echo '</div>';
        }
      }
    ?>
</div>
<div class="ajax-clear"></div>
