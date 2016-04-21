<?php
/**
 * @version		2.0
 * @package		com_k2ajaxsearch
 * @author		Taleia Software http://www.taleiasoftware.com
 * @copyright	Copyright (C) 2012 - 2013 Taleia Software. All rights reserved.
 * @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */
 
if(!function_exists('parseParams')){
  function parseParams(&$p, $vals){
  	if(version_compare(JVERSION,'3.0.0','ge')) {
      $p->loadString($vals);
    }else if(version_compare(JVERSION,'1.6.0','>=')) {
      $p->loadJSON($vals);
    }else{
      $p->loadIni($vals);
    }
  }
}
  
if(!function_exists('buildCategoryNameArray')){
  function buildCategoryNameArray($a){
    $newa = array();
    $tmp = '';
    foreach($a AS $k => $v){
      ($k % 2 == 0) ? $tmp = $v : $newa[$tmp] = $v;
    }
    return $newa;
  }
}

// If json_encode is not defined - PHP4
if (!function_exists('json_encode'))
{
  function json_encode($a=false)
  {
    if (is_null($a)) return 'null';
    if ($a === false) return 'false';
    if ($a === true) return 'true';
    if (is_scalar($a))
    {
      if (is_float($a))
      {
        // Always use "." for floats.
        return floatval(str_replace(",", ".", strval($a)));
      }

      if (is_string($a))
      {
        static $jsonReplaces = array(array("\\", "/", "\n", "\t", "\r", "\b", "\f", '"'), array('\\\\', '\\/', '\\n', '\\t', '\\r', '\\b', '\\f', '\"'));
        return '"' . str_replace($jsonReplaces[0], $jsonReplaces[1], $a) . '"';
      }
      else
        return $a;
    }
    $isList = true;
    for ($i = 0, reset($a); $i < count($a); $i++, next($a))
    {
      if (key($a) !== $i)
      {
        $isList = false;
        break;
      }
    }
    $result = array();
    if ($isList)
    {
      foreach ($a as $v) $result[] = json_encode($v);
      return '[' . join(',', $result) . ']';
    }
    else
    {
      foreach ($a as $k => $v) $result[] = json_encode($k).':'.json_encode($v);
      return '{' . join(',', $result) . '}';
    }
  }
}

if (!function_exists('getCategory'))
{
  function getCategory($id = 1, &$category){
    $id = (int) $id;
    $db = &JFactory::getDBO();
	$user = &JFactory::getUser();

    $query = "SELECT id, name FROM #__k2_categories WHERE published=1 AND trash=0 AND id={$id} AND access IN(".implode(',', (version_compare(JVERSION,'3.0.0','ge') ? $user->getAuthorisedViewLevels() : $user->authorisedLevels())).") ORDER BY ordering";
  
    $db->setQuery($query);
    $category[] = (object)$db->loadAssoc();
  }
}

if (!function_exists('getCategoryAndChildrens'))
{
  function getCategoryAndChildrens($id = 0, $level = 0, &$categorylist){
    $id = (int) $id;
    $db = &JFactory::getDBO();
	$user = &JFactory::getUser();

    $query = "SELECT id, name FROM #__k2_categories WHERE published=1 AND trash=0 AND access IN(".implode(',', (version_compare(JVERSION,'3.0.0','ge') ? $user->getAuthorisedViewLevels() : $user->authorisedLevels())).") ";
    
    if($level == 0 && $id != 0)
      $query .= " AND id={$id} ";      
    else
      $query .= " AND parent={$id} ";

	$query .= " ORDER BY ordering";

    $db->setQuery($query);
    $results = $db->loadObjectList();

    if(!empty($results)){
      $level++;
      $prefix = '';
      for($i = 1; $i < $level; $i++) {
        $prefix .= ' - ';
      }

      foreach($results as $key=>$item) {
        $item->name = $prefix . $item->name;
        $categorylist[] = $item; 
        getCategoryAndChildrens($item->id, $level, $categorylist);           
      }
    }
  }
}

if (!function_exists('getAllCategories'))
{
  function getAllCategories(&$categorylist){
    getCategoryAndChildrens(0, 0, $categorylist); 
  }
}
?>
