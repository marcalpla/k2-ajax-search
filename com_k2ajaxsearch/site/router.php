<?php
/**
 * @version		$Id: router.php 21389 2011-05-26 17:28:26Z dextercowley $
 * @package		Joomla.Site
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * @param	array
 * @return	array
 */
function K2ajaxsearchBuildRoute(&$query)
{
	$segments = array();

	if (isset($query['view'])) {
		unset($query['view']);
	}
	return $segments;
}

/**
 * @param	array
 * @return	array
 */
function K2ajaxsearchParseRoute($segments)
{
	$vars = array();

	$searchword	= array_shift($segments);
	$vars['searchword'] = $searchword;
	$vars['view'] = 'search';

	return $vars;
}
