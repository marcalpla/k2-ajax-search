<?php 
/**
 * @version		1.1
 * @package		mod_k2ajaxsearch
 * @author		Taleia Software http://www.taleiasoftware.com
 * @copyright	Copyright (C) 2012 Taleia Software. All Rights Reserved.
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

class JElementExtrafields extends JOfflajnFakeElementBase
{
	var $_name = 'Extrafields';

	function universalfetchElement($name, $value, &$node){
		$class = ( $node->attributes('class') ? 'class="'.$node->attributes('class').'"' : 'class="inputbox"' );
		
		$extrafieldlist = array();

		K2AJAXSearchHelper::getAllExtrafieldsMultiSelect($extrafieldlist);

                if(!empty($extrafieldlist)){
	    	  	foreach ($extrafieldlist as $extrafield)
				$options[] = JHTML::_('select.option',  $extrafield->id, $extrafield->name);
		} else {
                        $options[] = JHTML::_('select.option',  '', '');
                }

		return JHTML::_('select.genericlist', $options, $name.'[]', $class.' multiple="multiple"', 'value','text', $value);
	}
}

if(version_compare(JVERSION,'1.6.0','ge')) {
  class JFormFieldExtrafields extends JElementExtrafields {}
}

?>
