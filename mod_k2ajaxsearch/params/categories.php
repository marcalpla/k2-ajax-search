<?php 
/**
 * @version		2.0
 * @package		mod_k2ajaxsearch
 * @author		Taleia Software http://www.taleiasoftware.com
 * @copyright	Copyright (C) 2012 - 2013 Taleia Software
 				Copyright (C) 2011 Offlajn.com (Janos Biro)
 				All Rights Reserved.
 * @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */
?>
<?php

defined('JPATH_BASE') or die;

error_reporting(E_ALL);
require_once('library'.DS.'fakeElementBase.php');
require_once('library'.DS.'flatArray.php');
require_once(dirname(__FILE__).DS.'..'.DS.'helper'.DS.'Helper.class.php');

jimport('joomla.html.html');
jimport('joomla.form.formfield');
jimport('joomla.application.component.view');
jimport('joomla.application.component.model');

class JElementCategories extends JOfflajnFakeElementBase
{
	var $_name = 'Categories';

	function universalfetchElement($name, $value, &$node){
	
	    $document =& JFactory::getDocument();
	    $document->addStyleSheet(JURI::base().'../modules/'.$this->_moduleName.'/params/css/offlajn.css');
    
		$class = ( $node->attributes('class') ? 'class="'.$node->attributes('class').'"' : 'class="inputbox"' );
		
		$categorylist = array();

		K2AJAXSearchHelper::getAllCategories($categorylist, ' - ', 1);

		if(!empty($categorylist)){
			$array = array();
			$array['id'] = 0;
			$array['name'] = '[All]';
			$categoryall[] = (object)$array;

                        $categorylist = array_merge($categoryall, $categorylist);
		
			foreach ($categorylist as $category)
				$options[] = JHTML::_('select.option',  $category->id, $category->name);		
		} else {
                      	$options[] = JHTML::_('select.option',  '', '');			
		}

		//return JHTML::_('select.genericlist', $options, $name, $class, 'value','text', $value);
		return JHTML::_('select.genericlist', $options, $name.'[]', $class.' multiple="multiple"', 'value','text', $value);
	}
}

if(version_compare(JVERSION,'1.6.0','ge')) {
  class JFormFieldCategories extends JElementCategories {}
}

?>
