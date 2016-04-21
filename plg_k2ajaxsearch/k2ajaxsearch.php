<?php
/**
 * @version		2.0
 * @package		plg_k2ajaxsearch
 * @author		Taleia Software http://www.taleiasoftware.com
 * @copyright	Copyright (C) 2012 - 2013 Taleia Software		
				Copyright (C) 2006 - 2012 JoomlaWorks Ltd
				All rights reserved.
 * @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.plugin.plugin');
jimport('joomla.html.parameter');
jimport('joomla.filesystem.file');

class plgK2ajaxsearchK2ajaxsearch extends JPlugin {

	function onContentSearchAreas() {
		return $this -> onSearchAreas();
	}

	function onContentSearch($text, $phrase = '', $ordering = '', $areas = null, $categories = null, $extrafields = null, 
                                 $logicBetweenValuesExtraField = 1, $logicBetweenExtraFields = 1, $logicBetweenCatAndExtraFields = 1,
								 $fromdate = '', $todate = '', $exactdate = '') {
		return $this -> onSearch($text, $phrase, $ordering, $areas, $categories, $extrafields, $logicBetweenValuesExtraField, 
									$logicBetweenExtraFields, $logicBetweenCatAndExtraFields, $fromdate, $todate, $exactdate);
	}

	function onSearchAreas() {
		JPlugin::loadLanguage('plg_search_k2', JPATH_ADMINISTRATOR);
		static $areas = array('k2' => 'K2_ITEMS');
		return $areas;
	}

	function onSearch($text, $phrase = '', $ordering = '', $areas = null, $categories = null, $extrafields = null, 
                          $logicBetweenValuesExtraField = 1, $logicBetweenExtraFields = 1, $logicBetweenCatAndExtraFields = 1,
						  $fromdate = '', $todate = '', $exactdate = '') {
		JPlugin::loadLanguage('plg_search_k2', JPATH_ADMINISTRATOR);
		jimport('joomla.html.parameter');
		$mainframe = &JFactory::getApplication();
		$db = &JFactory::getDBO();
		$jnow = &JFactory::getDate();
		$now = (version_compare(JVERSION,'3.0.0','ge') ? $jnow -> toSql() : $jnow -> toMySQL());
		$nullDate = $db -> getNullDate();
		$user = &JFactory::getUser();
		$accessCheck = " IN(" . implode(',', (version_compare(JVERSION,'3.0.0','ge') ? $user->getAuthorisedViewLevels() : $user->authorisedLevels())) . ") ";
		$tagIDs = array();
		$itemIDs = array();

		require_once (JPATH_SITE . DS . 'administrator' . DS . 'components' . DS . 'com_search' . DS . 'helpers' . DS . 'search.php');
		require_once (JPATH_SITE . DS . 'components' . DS . 'com_k2' . DS . 'helpers' . DS . 'route.php');

		$searchText = $text;
		if (is_array($areas)) {
			if (!array_intersect($areas, array_keys($this -> onSearchAreas()))) {
				return array();
			}
		}

		$plugin = &JPluginHelper::getPlugin('k2ajaxsearch', 'k2ajaxsearch');
		$pluginParams = (version_compare(JVERSION,'3.0.0','ge') ?  new JRegistry($plugin -> params) : new JParameter($plugin -> params));

		$limit = $pluginParams -> def('search_limit', 50);

		$text = JString::trim($text);
		if ($text == '') {
			return array();
		}

		$rows = array();

		if ($limit > 0) {

			if ($pluginParams -> get('search_tags')) {
				$tagQuery = JString::str_ireplace('*', '', $text);
				$words = explode(' ', $tagQuery);
				for ($i = 0; $i < count($words); $i++) {
					$words[$i] .= '*';
				}
				$tagQuery = implode(' ', $words);
				$tagQuery = $db -> Quote((version_compare(JVERSION,'3.0.0','ge') ? $db -> escape($tagQuery, true) : $db -> getEscaped($tagQuery, true)), false);

				$query = "SELECT id FROM #__k2_tags WHERE MATCH(name) AGAINST ({$tagQuery} IN BOOLEAN MODE) AND published=1";
				$db -> setQuery($query);
				$tagIDs = $db -> loadResultArray();

				if (count($tagIDs)) {
					JArrayHelper::toInteger($tagIDs);
					$query = "SELECT itemID FROM #__k2_tags_xref WHERE tagID IN (" . implode(',', $tagIDs) . ")";
					$db -> setQuery($query);
					$itemIDs = $db -> loadResultArray();
				}
			}

			$wheres = array();
			switch ($phrase) {
				case 'exact':
					$text		= $db->Quote('%'.(version_compare(JVERSION,'3.0.0','ge') ? $db -> escape($text, true) : $db -> getEscaped($text, true)).'%', false);
					$wheres2	= array();
					$wheres2[]	= 'i.title LIKE '.$text;
					$wheres2[]	= 'i.introtext LIKE '.$text;
					$wheres2[]	= 'i.fulltext LIKE '.$text;
					$wheres2[]	= 'i.extra_fields_search LIKE '.$text;
					$wheres2[]	= 'i.image_caption LIKE '.$text;
					$wheres2[]	= 'i.image_credits LIKE '.$text;
					$wheres2[]	= 'i.video_caption LIKE '.$text;
					$wheres2[]	= 'i.video_credits LIKE '.$text;
					$wheres2[]	= 'i.metadesc LIKE '.$text;
					$wheres2[]	= 'i.metakey LIKE '.$text;
					$wheres2[]	= 'i.created_by_alias LIKE '.$text;
					$where		= '(' . implode(') OR (', $wheres2) . ')';
					break;

				case 'all':
				case 'any':
				default:
					$words = explode(' ', $text);
					$wheres = array();
					foreach ($words as $word) {
						$word		= $db->Quote('%'.(version_compare(JVERSION,'3.0.0','ge') ? $db -> escape($word, true) : $db -> getEscaped($word, true)).'%', false);
						$wheres2	= array();
						$wheres2[]	= 'i.title LIKE '.$word;
						$wheres2[]	= 'i.introtext LIKE '.$word;
						$wheres2[]	= 'i.fulltext LIKE '.$word;
						$wheres2[]	= 'i.extra_fields_search LIKE '.$word;
						$wheres2[]	= 'i.image_caption LIKE '.$word;
						$wheres2[]	= 'i.image_credits LIKE '.$word;
						$wheres2[]	= 'i.video_caption LIKE '.$word;
						$wheres2[]	= 'i.video_credits LIKE '.$word;
						$wheres2[]	= 'i.metadesc LIKE '.$word;
						$wheres2[]	= 'i.metakey LIKE '.$word;
						$wheres2[]	= 'i.created_by_alias LIKE '.$word;
						$wheres[]	= implode(' OR ', $wheres2);
					}
					$where = '(' . implode(($phrase == 'all' ? ') AND (' : ') OR ('), $wheres) . ')';
					break;
			}

			$query = "
			SELECT i.title AS title,
			i.id,
	    	i.metadesc,
	    	i.metakey,
	    	c.name as section,
	    	i.image_caption,
	    	i.image_credits,
	    	i.video_caption,
	    	i.video_credits,
	    	i.extra_fields_search,
	    	i.created,
    		CONCAT(i.introtext, i.fulltext) AS text,
    		CASE WHEN CHAR_LENGTH(i.alias) THEN CONCAT_WS(':', i.id, i.alias) ELSE i.id END as slug,
    		CASE WHEN CHAR_LENGTH(c.alias) THEN CONCAT_WS(':', c.id, c.alias) ELSE c.id END as catslug
    		FROM #__k2_items AS i
    		INNER JOIN #__k2_categories AS c ON c.id=i.catid AND c.access {$accessCheck}
			WHERE (";
			if ($pluginParams -> get('search_tags') && count($itemIDs)) {
				JArrayHelper::toInteger($itemIDs);
				$query .= " i.id IN (" . implode(',', $itemIDs) . ") OR ";
			}
			$query .= "(" . $where . ")
			)			
			AND i.trash = 0
	    	AND i.published = 1
	    	AND i.access {$accessCheck}";
            if(!empty($categories)){ 
				$query .= (!empty($extrafields) ? " AND (" : " AND ");
            	$query .= "i.catid IN (".implode(',', $categories).")";
	    	}
            if(!empty($extrafields)){
				$query .= (!empty($categories) && $logicBetweenCatAndExtraFields ? " OR ((extra_fields REGEXP " : " AND ((extra_fields REGEXP ");
				$i=0;
            	foreach($extrafields as $extrafield){                        
					if($i>0)
						$query .= ($logicBetweenExtraFields ? " OR (extra_fields REGEXP " : " AND (extra_fields REGEXP ");
					$j=0;
					foreach($extrafield->value as $extrafield_value){
						if($j>0)
							$query .= ($logicBetweenValuesExtraField ? " OR extra_fields REGEXP " : " AND extra_fields REGEXP ");
						$query .= '\'({"id":"'.$extrafield->id.'","value":\\\\[("[0-9]+",)*"'.$extrafield_value.'"(,"[0-9]+")*\\\\]})\'';				
						$j++;
					}
					$query .= ")";
					$i++;
				}
				$query .= ")"; 
				if(!empty($categories))	
					$query .= ")"; 		
        	}            
        	$query .= " AND c.published = 1
	    	AND c.access {$accessCheck}
	    	AND c.trash = 0
	    	AND ( i.publish_up = " . $db -> Quote($nullDate) . " OR i.publish_up <= " . $db -> Quote($now) . " )
        	AND ( i.publish_down = " . $db -> Quote($nullDate) . " OR i.publish_down >= " . $db -> Quote($now) . " )";
			if ($mainframe -> getLanguageFilter()) {
				$languageTag = JFactory::getLanguage() -> getTag();
				$query .= " AND c.language IN (" . $db -> Quote($languageTag) . ", " . $db -> Quote('*') . ") AND i.language IN (" . $db -> Quote($languageTag) . ", " . $db -> Quote('*') . ") ";
			}
			if (!empty($fromdate)) {
				$query .= " AND i.created >= str_to_date(" . $db -> Quote((version_compare(JVERSION,'3.0.0','ge') ? $db -> escape($fromdate, true) : $db -> getEscaped($fromdate, true)) . ' 00:00:00') . ", '%m/%d/%Y %H:%i:%s')";
			}
			if (!empty($todate)) {
				$query .= " AND i.created <= str_to_date(" . $db -> Quote((version_compare(JVERSION,'3.0.0','ge') ? $db -> escape($todate, true) : $db -> getEscaped($todate, true)) . ' 23:59:59') . ", '%m/%d/%Y %H:%i:%s')";
			}			
			if (!empty($exactdate)) {
				$query .= " AND i.created >= str_to_date(" . $db -> Quote((version_compare(JVERSION,'3.0.0','ge') ? $db -> escape($exactdate, true) : $db -> getEscaped($exactdate, true)) . ' 00:00:00') . ", '%m/%d/%Y %H:%i:%s')";
				$query .= " AND i.created <= str_to_date(" . $db -> Quote((version_compare(JVERSION,'3.0.0','ge') ? $db -> escape($exactdate, true) : $db -> getEscaped($exactdate, true)) . ' 23:59:59') . ", '%m/%d/%Y %H:%i:%s')";
			}				
			$query .= " GROUP BY i.id ";

			switch ($ordering) {
				case 'oldest' :
					$query .= 'ORDER BY i.created ASC';
					break;

				case 'popular' :
					$query .= 'ORDER BY i.hits DESC';
					break;

				case 'alpha' :
					$query .= 'ORDER BY i.title ASC';
					break;

				case 'category' :
					$query .= 'ORDER BY c.name ASC, i.title ASC';
					break;

				case 'newest' :
				default :
					$query .= 'ORDER BY i.created DESC';
					break;
			}

			$db -> setQuery($query, 0, $limit);
			$list = $db -> loadObjectList();
			$limit -= count($list);
			if (isset($list)) {
				foreach ($list as $key => $item) {
					$list[$key] -> href = JRoute::_(K2HelperRoute::getItemRoute($item -> slug, $item -> catslug));

				}
			}
			$rows[] = $list;
		}

		$results = array();
		if (count($rows)) {
			foreach ($rows as $row) {
				$new_row = array();
				foreach ($row as $key => $item) {
					$item -> browsernav = '';
					$item -> tag = $searchText;

					if (searchHelper::checkNoHTML($item, $searchText, array('text', 'title', 'metakey', 'metadesc', 'section', 'image_caption', 'image_credits', 'video_caption', 'video_credits', 'extra_fields_search', 'tag'))) {
						$new_row[] = $item;
					}
					
					$image_path = 'media' .DS. 'k2' .DS. 'items' .DS. 'cache';
					if (JFile::exists(JPATH_SITE .DS. $image_path .DS. md5("Image".$item->id) . '_Generic.jpg')) {
						$item -> image = JURI::root(). $image_path .DS. md5("Image".$item->id).'_Generic.jpg';	
					}
				}

  			        $results = array_merge($results, (array)$new_row);
			}
		}

		return $results;
	}

}
