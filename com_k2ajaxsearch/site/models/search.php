<?php
/**
 * @version		2.0
 * @package		com_k2ajaxsearch
 * @author		Taleia Software http://www.taleiasoftware.com
 * @copyright	Copyright (C) 2012 - 2013 Taleia Software		
				Copyright (C) 2005 - 2011 Open Source Matters, Inc
				All rights reserved.
 * @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.model');
jimport('joomla.html.parameter');

/**
 * Search Component Search Model
 *
 * @package		Joomla.Site
 * @subpackage	com_search
 * @since 1.5
 */
 
if (version_compare(JVERSION,'3.0.0','ge')) {
	class _JModel extends JModelLegacy {}
} else {
	class _JModel extends JModel {}	
} 
 
class SearchModelSearch extends _JModel
{
	/**
	 * Sezrch data array
	 *
	 * @var array
	 */
	var $_data = null;

	/**
	 * Search total
	 *
	 * @var integer
	 */
	var $_total = null;

	/**
	 * Search areas
	 *
	 * @var integer
	 */
	var $_areas = null;

	/**
	 * Pagination object
	 *
	 * @var object
	 */
	var $_pagination = null;
	
	/**
	 * Lower limit characters to search
	 *
	 * @var integer
	 */		
	var $_lowerLimitSearchWord = null;

	/**
	 * Upper limit characters to search
	 *
	 * @var integer
	 */		
	var $_upperLimitSearchWord = null;
	
	/**
	 * Constructor
	 *
	 * @since 1.5
	 */
	function __construct()
	{
		parent::__construct();

		//Get configuration
		$app	= JFactory::getApplication();
		$config = JFactory::getConfig();

		// Get the pagination request variables
		$this->setState('limit', $app->getUserStateFromRequest('com_k2ajaxsearch.limit', 'limit', $config->get('list_limit'), 'int'));
		$this->setState('limitstart', JRequest::getVar('limitstart', 0, '', 'int'));
		
		//Get module ID
		$module_id		= JRequest::getInt('module_id', null);

		// Set the search parameters
		$keyword		= urldecode(JRequest::getString('searchword'));
		$match			= JRequest::getWord('searchphrase', 'all');
		$ordering		= JRequest::getWord('ordering', 'newest');
		$categories		= JRequest::getVar('categ', null);
		$extrafields	= JRequest::getVar('efields', null); 
		$fromdate		= JRequest::getVar('fromdate', null); 
		$todate			= JRequest::getVar('todate', null); 
		$exactdate		= JRequest::getVar('exactdate', null); 
		$this->setSearch($keyword, $match, $ordering, $categories, $extrafields, $module_id, $fromdate, $todate, $exactdate);

		//Set the search areas
		$areas = JRequest::getVar('areas');
		$this->setAreas($areas);
	}

	/**
	 * Method to set the search parameters
	 *
	 * @access	public
	 * @param string search string
	 * @param string mathcing option, exact|any|all
	 * @param string ordering option, newest|oldest|popular|alpha|category
	 */
	function setSearch($keyword, $match = 'all', $ordering = 'newest', $categories ,$extrafields, $module_id, $fromdate, $todate, $exactdate)
	{
		if (isset($keyword)) {
			$this->setState('origkeyword', $keyword);
			if($match !== 'exact') {
				$keyword 		= preg_replace('#\xE3\x80\x80#s', ' ', $keyword);
			}
			$this->setState('keyword', $keyword);
		}

		if (isset($match)) {
			$this->setState('match', $match);
		}

		if (isset($ordering)) {
			$this->setState('ordering', $ordering);
		}
		
		require_once JPATH_COMPONENT.DS.'helpers'.DS.'functions.php';

		//Get module parameters
	   	$db =& JFactory::getDBO();
	   	$db->setQuery("SELECT params, id FROM #__modules WHERE id = ".$module_id);
	   	$res = $db->loadResult();
	   	$params = (version_compare(JVERSION,'3.0.0','ge') ? new JRegistry() : new JParameter(""));
	   	parseParams($params, $res);

		//Set lower and upper limits search word
		$this->_lowerLimitSearchWord = $params->get('minchars', 3);
		$this->_upperLimitSearchWord = $params->get('maxchars', 50);
		$language = JFactory::getLanguage();
		$language->setLowerLimitSearchWordCallback(array($this,'setLowerLimitSearchWord')) ;
		$language->setUpperLimitSearchWordCallback(array($this,'setUpperLimitSearchWord')) ;	   	
	   		   	
	   	//Get logical operations to apply between categories and extra fields	
  		$this->setState('logicBetweenValuesExtraField', $params->get('logicvalextrafield', 1));
  		$this->setState('logicBetweenExtraFields', $params->get('logicextrafields', 1));
  		$this->setState('logicBetweenCatAndExtraFields', $params->get('logiccatextrafields', 1));
		
		$categorylist = array();
		$categoriesToSearch = array();
		$extraFieldsToSearch = array();
		
		//Get from date, to date and exact date
		$this->setState('fromdate', $fromdate);
		$this->setState('todate', $todate);
		$this->setState('exactdate', $exactdate);
				
		// Build an array with ID of categories to search
		$categories_param = $params->get('categories', '');
  		$includecatchildrens = $params->get('includecatchildrens', 1);	
		$includecatchildrenssearch = $params->get('includecatchildrenssearch', 0);
			
  		if(isset($categories)){
	    	if($includecatchildrenssearch) {
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
				$categoriesToSearch = $categories;
			}			
	  	}else{
	  		if(!is_array($categories_param))
	  			$categories_param = array($categories_param);
			
			foreach($categories_param as $category){
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
		
	  	$this->setState('categoriesToSearch', $categoriesToSearch);
		
		//Build an array with ID and values of extrafields to search
	  if(isset($extrafields)){
		foreach($extrafields as $extrafield){
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
	  
		$this->setState('extraFieldsToSearch', $extraFieldsToSearch);
	}

	/**
	 * Method to set the search areas
	 *
	 * @access	public
	 * @param	array	Active areas
	 * @param	array	Search areas
	 */
	function setAreas($active = array(), $search = array())
	{
		$this->_areas['active'] = $active;
		$this->_areas['search'] = $search;
	}

	/**
	 * Method to get weblink item data for the category
	 *
	 * @access public
	 * @return array
	 */
	function getData()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->_data))
		{
			$areas = $this->getAreas(); 
			
			JPluginHelper::importPlugin('k2ajaxsearch', 'k2ajaxsearch');
			$dispatcher = JDispatcher::getInstance();
			$results = $dispatcher->trigger('onContentSearch', array(
				$this->getState('keyword'),
				$this->getState('match'),
				$this->getState('ordering'),
				$areas['active'],
				$this->getState('categoriesToSearch'),
				$this->getState('extraFieldsToSearch'),
  				$this->getState('logicBetweenValuesExtraField'),
  				$this->getState('logicBetweenExtraFields'),
  				$this->getState('logicBetweenCatAndExtraFields'),
				$this->getState('fromdate'),
				$this->getState('todate'),
				$this->getState('exactdate'))
			);

			$rows = array();
			foreach ($results as $result) {
				$rows = array_merge((array) $rows, (array) $result);
			}

			$this->_total	= count($rows);
			if ($this->getState('limit') > 0) {
				$this->_data	= array_splice($rows, $this->getState('limitstart'), $this->getState('limit'));
			} else {
				$this->_data = $rows;
			}
		}

		return $this->_data;
	}

	/**
	 * Method to get the total number of weblink items for the category
	 *
	 * @access public
	 * @return integer
	 */
	function getTotal()
	{
		return $this->_total;
	}

	/**
	 * Method to get a pagination object of the weblink items for the category
	 *
	 * @access public
	 * @return integer
	 */
	function getPagination()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->_pagination))
		{
			jimport('joomla.html.pagination');
			$this->_pagination = new JPagination($this->getTotal(), $this->getState('limitstart'), $this->getState('limit'));
		}

		return $this->_pagination;
	}

	/**
	 * Method to get the search areas
	 *
	 * @since 1.5
	 */
	function getAreas()
	{
		// Load the Category data
		if (empty($this->_areas['search']))
		{
			$areas = array();

			JPluginHelper::importPlugin('k2ajaxsearch', 'k2ajaxsearch');
			$dispatcher = JDispatcher::getInstance();
			$searchareas = $dispatcher->trigger('onContentSearchAreas');

			foreach ($searchareas as $area) {
				if (is_array($area)) {
					$areas = array_merge($areas, $area);
				}
			}

			$this->_areas['search'] = $areas;
		}

		return $this->_areas;
	}

	/**
	 * To set lowerLimitSearchWord callback
	 *
	 * @access public
	 * @return integer
	 */
	function setLowerLimitSearchWord()
	{ 
		return $this->_lowerLimitSearchWord;
	}

	/**
	 * To set upperLimitSearchWord callback
	 *
	 * @access public
	 * @return integer
	 */	
	function setUpperLimitSearchWord()
	{
		return $this->_upperLimitSearchWord;
	}	
}
