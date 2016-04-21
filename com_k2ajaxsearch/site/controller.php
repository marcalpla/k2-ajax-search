<?php
/**
 * @version		2.2
 * @package		com_k2ajaxsearch
 * @author		Taleia Software http://www.taleiasoftware.com
 * @copyright	Copyright (C) 2012 - 2013 Taleia Software		
				Copyright (C) 2011 Offlajn.com
				Copyright (C) 2005 - 2011 Open Source Matters, Inc
				All rights reserved.
 * @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.controller');

/**
 * Search Component Controller
 *
 * @package		Joomla.Site
 * @subpackage	com_search
 * @since 1.5
 */
 
if (version_compare(JVERSION,'3.0.0','ge')) {
	class _JController extends JControllerLegacy {}
} else {
	class _JController extends JController {}	
}

class SearchController extends _JController
{
	/**
	 * Method to display a view.
	 *
	 * @param	boolean			If true, the view output will be cached
	 * @param	array			An array of safe url parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
	 *
	 * @return	JController		This object to support chaining.
	 * @since	1.5
	 */
	public function display($cachable = false, $urlparams = false)
	{
		JRequest::setVar('view','search'); // force it to be the search view

		return parent::display($cachable, $urlparams);
	}

	function search()
	{
		// slashes cause errors, <> get stripped anyway later on. # causes problems.
		$badchars = array('#','>','<','\\');
		$searchword = trim(str_replace($badchars, '', JRequest::getString('searchword', null, 'post')));
		// if searchword enclosed in double quotes, strip quotes and do exact match
		if (substr($searchword,0,1) == '"' && substr($searchword, -1) == '"') {
			$post['searchword'] = substr($searchword,1,-1);
			JRequest::setVar('searchphrase', 'exact');
		}
		else {
			$post['searchword'] = $searchword;
		}
		$post['ordering']	= JRequest::getWord('ordering', null, 'post');
		$post['searchphrase']	= JRequest::getWord('searchphrase', 'all', 'post');
		$post['limit']  = JRequest::getInt('limit', null, 'post');
		if ($post['limit'] === null) unset($post['limit']);

		$areas = JRequest::getVar('areas', null, 'post', 'array');
		if ($areas) {
			foreach($areas as $area)
			{
				$post['areas'][] = JFilterInput::getInstance()->clean($area, 'cmd');
			}
		}
		
		//Get module ID
		$post['module_id'] = JRequest::getInt('module_id', null, 'post');
		
		//Get categories, extra fields and dates range
		$post['categ'] = JRequest::getVar('categ', null, 'post');
		$post['efields'] = JRequest::getVar('efields', null, 'post');	
		$post['fromdate'] = JRequest::getVar('fromdate', null, 'post');
		$post['todate'] = JRequest::getVar('todate', null, 'post');	
		$post['exactdate'] = JRequest::getVar('exactdate', null, 'post');		
		
				// set Itemid id for links from menu
		$app	= JFactory::getApplication();
		$menu	= $app->getMenu();
		$items	= $menu->getItems('link', 'index.php?option=com_k2ajaxsearch&view=search');

		if(isset($items[0])) {
			$post['Itemid'] = $items[0]->id;
		} elseif (JRequest::getInt('Itemid') > 0) { //use Itemid from requesting page only if there is no existing menu
			$post['Itemid'] = JRequest::getInt('Itemid');
		}

		unset($post['task']);
		unset($post['submit']);

		$uri = JURI::getInstance();
		$uri->setQuery($post);
		$uri->setVar('option', 'com_k2ajaxsearch');


		$this->setRedirect(JRoute::_('index.php'.$uri->toString(array('query', 'fragment')), false));
	}

	function searchAjax()
	{
		ob_end_clean();

		if(isset($_SERVER['HTTP_X_REQUESTED_WITH']))
		  unset($_SERVER['HTTP_X_REQUESTED_WITH']); // MAGEBRIDGE fix

		if ($_GET['search_exp']!=''){

	  	    require_once( dirname(__FILE__).'/helpers/functions.php' );
	  		require_once( dirname(__FILE__).'/helpers/caching.php' );
			require_once JPATH_SITE . DS . 'administrator' . DS . 'components' . DS . 'com_search/helpers/search.php';
	  		jimport( 'joomla.html.parameter' );
	  
	  		$db =& JFactory::getDBO();
	  		$searchresult = array();
		  	settype($_GET['module_id'], 'integer');
		  	$q =  sprintf("SELECT params, id FROM #__modules WHERE id = %d " ,$_GET['module_id']);
		  	SearchHelper::logSearch($_GET['search_exp']);
		  	$db->setQuery($q);
		  	$res = $db->loadResult();
		  	$params = (version_compare(JVERSION,'3.0.0','ge') ? new JRegistry() : new JParameter(""));
		  	parseParams($params, $res);
			$text = array();
			$minchars = $params->get('minchars', 3);
		  	$categories = $params->get('categories', '');
		  	$includecatchildrens = $params->get('includecatchildrens', 1);
			$includecatchildrenssearch = $params->get('includecatchildrenssearch', 0);			
		 	$logicBetweenValuesExtraField = $params->get('logicvalextrafield', 1);
		  	$logicBetweenExtraFields = $params->get('logicextrafields', 1); 
		  	$logicBetweenCatAndExtraFields = $params->get('logiccatextrafields', 1);
		  	$categorylist = array();
		  	$categoriesToSearch = array();
		  	$extraFieldsToSearch = array();
		  	$introlength = 50; //Intro-text length
		  	jimport( 'joomla.application.component.model' );
		  	JPluginHelper::importPlugin('k2ajaxsearch', 'k2ajaxsearch');
		  	$imagecache = new OfflajnImageCaching;
		  	$resultsbycategory = $params->get('resultsbycategory', 1);
		  	$resultstitle = $params->get('resultstitle', 'Results');

			/*
			 * Check minimum length of search strings
			 */
			$text_aux = explode(' ', $_GET['search_exp']);
			for ($i = 0; $i < count($text_aux); $i++) {
				if(strlen($text_aux[$i]) >= $minchars)
					$text[] = $text_aux[$i];
			}
			$text = implode(' ', $text);
			
	  		/*
	   		 * Build an array with ID of categories to search
	   		 */
	  		if(isset($_GET['categ'])){
		    	if($includecatchildrenssearch) {
					$categories = $_GET['categ'];
		  			if(!is_array($categories))
		  				$categories = array($categories);					
					foreach($categories as $category){
	  	  				$categorylist = array();				
			    		getCategoryAndChildrens($category, 0, $categorylist);		
			    		if(!empty($categorylist)) {
			      			foreach($categorylist as $row){
								$categoriesToSearch[]=$row->id;
			      			}
						}						
					}
				}					
				else {
					$categoriesToSearch = $_GET['categ'];
				}		    		
		  	}else {
		  		if(!is_array($categories))
		  			$categories = array($categories);
				
  				foreach($categories as $category){
  	  				$categorylist = array();				
		    		if($includecatchildrenssearch || $category == 0)
		      			getCategoryAndChildrens($category, 0, $categorylist);
		    		else
		      			getCategory($category, $categorylist);
	
		    		if(!empty($categorylist)) {
		      			foreach($categorylist as $row){
							$categoriesToSearch[]=$row->id;
		      			}
					}
		   	 	}
		  	}

		 	/*
		   	 * Build an array with ID and values of extrafields to search
		   	 */
		  	if(isset($_GET['efields'])){
		    		$extrafieldslist = $_GET['efields'];
		    		foreach($extrafieldslist as $extrafield){
		      			$extrafield = explode(',',(string)$extrafield[0]);
		      			$i=0;
		      			$extraFieldsToSearchRow['value'] = array();
		     			foreach($extrafield as $value){
						    if($i == 0)
			  				    $extraFieldsToSearchRow['id'] = $value;
						    else
			  				    $extraFieldsToSearchRow['value'][] = $value;
						    $i++;
		      			}
		      			$extraFieldsToSearch[] = (object)$extraFieldsToSearchRow;
		    		}
		  	} 
			
			/*
			 * Get from date, to date and exact day
			 */
		 	$fromdate = isset($_GET['fromdate']) ? $_GET['fromdate'] : NULL;			 	
            $todate = isset($_GET['todate']) ? $_GET['todate'] : NULL;			 	
            $exactdate = isset($_GET['exactdate']) ? $_GET['exactdate'] : NULL;			 	
			 
		  	$searchparams = array($text,'all','newest', null, $categoriesToSearch, $extraFieldsToSearch, 
							$logicBetweenValuesExtraField, $logicBetweenExtraFields, $logicBetweenCatAndExtraFields,
							$fromdate, $todate, $exactdate);
		  	$dispatcher =& JDispatcher::getInstance();
		  	$results = null;
		  	if(version_compare(JVERSION,'1.6.0','ge')) {
		    		$results = $dispatcher->trigger( 'onContentSearch', $searchparams );
		  	}else{
		    		$results = $dispatcher->trigger( 'onSearch', $searchparams );
		  	} 

		      	foreach ($results[0] as $key=>$value) { //results[0] --> We only use one search plugin
				if($resultsbycategory) $resultstitle = $value->section;
				$i=0;
				while(!empty($searchresult[$resultstitle][$i])) $i++;
				if($params->get('image', 1)){
			  		$image_url = (isset($value->image) ? $value->image : null);
			  		if($image_url){ //If is set the item image
			    		$searchresult[$resultstitle][$i]->product_img = $imagecache->generateImage($image_url, 60, 60, $value->title);  
			  		} elseif ($value->text){ //Else get the first image of content
			    			preg_match_all('/<img.*?src=["\'](.*?((jpg)|(png)|(jpeg)))["\'].*?>/i',$value->text, $result);
			    			if (isset($result[1]) && isset($result[1][0])){
			      				$searchresult[$resultstitle][$i]->product_img = $imagecache->generateImage($result[1][0], 60, 60, $value->title);
			    			}else{
			      				preg_match_all('/<img.*?src=["\'](.*?gif)["\'].*?>/i',$value->text, $result);
			      				if (isset($result[1]) && isset($result[1][0])){
								$searchresult[$resultstitle][$i]->product_img = $imagecache->generateImage($result[1][0], 60, 60, $value->title);
			      				}
			    			}
			  		}
				}
				$searchresult[$resultstitle][$i]->title = $value->title;
				if(function_exists('mb_substr') && mb_detect_encoding($value->text) && iconv(mb_detect_encoding($value->text), "UTF-8", $value->text)) {
					$value->text = iconv(mb_detect_encoding($value->text), "UTF-8", $value->text);
					$value->text = mb_substr(strip_tags(preg_replace('/\{.*?\}(.*?\{\/.*?\})?/','',$value->text)),0,$introlength);
				}
				else {
					$value->text = substr(strip_tags(preg_replace('/\{.*?\}(.*?\{\/.*?\})?/','',$value->text)),0,$introlength);
				}
				$searchresult[$resultstitle][$i]->text = trim($value->text)." ...";		
				$searchresult[$resultstitle][$i]->href = html_entity_decode(JRoute::_($value->href));
		      	}

		  	echo json_encode($searchresult);
		  	exit;
		}

	}
}
