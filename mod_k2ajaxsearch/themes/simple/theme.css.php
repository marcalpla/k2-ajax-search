<?php 
/**
 * @version		2.1
 * @package		mod_k2ajaxsearch
 * @author		Taleia Software http://www.taleiasoftware.com
 * @copyright	Copyright (C) 2012 - 2013 Taleia Software
 				Copyright (C) 2011 Offlajn.com (Janos Biro)
 				All Rights Reserved.
 * @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */
?>

<?php $searchareawidth = $this->params->get('searchareawidth', 150);?>

#k2-ajax-search<?php echo $module->id; ?>{
  width: <?php echo $searchareawidth; ?>px;
  float: left;
  text-align: left;
}

#k2-ajax-search<?php echo $module->id; ?> .k2-ajax-search-container{
  background-color: <?php print $this->params->get('borderboxcolor');?>;
  padding: 4px;
  margin:0;
}

#k2-ajax-search<?php echo $module->id; ?> .k2-ajax-search-container.active{
  background-color: <?php print $this->params->get('highlightboxcolor');?>;
}

#k2-ajax-search<?php echo $module->id; ?> .k2-ajax-search-inner{
  width:100%;
}

#search-form<?php echo $module->id; ?>{
  margin:0;
  padding:0;
  position: relative;
  display: block !important;
}

#search-form<?php echo $module->id; ?> input{
  padding-top:1px;
  color: <?php print $this->params->get('searchformfontcolor', 727272);?>;
  font-family: "Vigra",Arial, Helvetica;
  font-weight: bold;
  font-style: normal;
  font-size: 12px;
  text-shadow: 1px 1px 0px #ffffff;
  text-decoration: none;
  outline: none;
}

.dj_ie #search-form<?php echo $module->id; ?> input{
  padding-top:0px;
}

#search-form<?php echo $module->id; ?> input:focus{
  background-color: #FFFFFF;
}

.dj_ie7 #search-form<?php echo $module->id; ?>{
  padding-bottom:0px;
}

<?php $categories = $params->get('categories', null);if(!empty($categories) && !is_array($categories))$categories = array($categories); ?>

<?php
//If all category choosers in a single one
$allinonecatchooser = $params->get('allinonecatchooser', 0);
if($allinonecatchooser == 1) {
  	$i=0;
  	foreach($categories as $category) {
  		if($i>0) {
			$categories[0] = array_merge($categories[0],$categories[$i]);
			unset($categories[$i]);
		}
		$i++;
  	}	
}
?>

<?php 
//Get extra fields
$extrafields = $this->params->get('extrafields', '');
$extrafieldlist = array();
if(!empty($extrafields))
  K2AJAXSearchHelper::getExtrafieldsMultiSelect($extrafields, $extrafieldlist); 

//Get extra fields and categories positions
$filterbuttonwidth = $this->params->get('filterbuttonwidth', 120);
$catchooserfirst = $this->params->get('catchooserfirst', 1);

$categoryright = count($categories)*$filterbuttonwidth;
$dateright = $extrafieldright = (count($extrafieldlist)+count($categories))*$filterbuttonwidth + 1;
if(($catchooserfirst || $hidecatchooser) && !empty($extrafieldlist)) {
  $extrafieldright = count($extrafieldlist)*$filterbuttonwidth;
  $dateright = $categoryright = (count($extrafieldlist)+count($categories))*$filterbuttonwidth + 1;
}
if($hidecatchooser) {
  	$dateright -= (count($categories) * $filterbuttonwidth);
}

?>

<?php
//Get filters date positions
$showfromdate = $this->params->get('showfromdate', 0);
$showtodate = $this->params->get('showtodate', 0);
$showexactdate = $params->get('showexactdate', 0);
$filterdatewidth = $this->params->get('filterdatewidth', 102);
if($showfromdate)
	$dateright += $filterdatewidth;
if($showtodate)
	$dateright += $filterdatewidth;
if($showexactdate)
	$dateright += $filterdatewidth;
?>

<?php if($showfromdate): ?>
#from_date<?php echo $module->id?>{
  display: block;
  height: 27px;
  padding: 0 5px 0 10px;
  box-sizing: border-box; /* css3 rec */
  -moz-box-sizing: border-box; /* ff2 */
  -ms-box-sizing: border-box; /* ie8 */
  -webkit-box-sizing: border-box; /* safari3 */
  -khtml-box-sizing: border-box; /* konqueror */
  
  border: 1px #bfbfbf solid;
  line-height: 27px;
  
  -webkit-box-shadow: inset 0px 1px 2px rgba(0,0,0,0.15);
  -moz-box-shadow: inset 0px 1px 2px rgba(0,0,0,0.15);
  box-shadow: inset 0px 1px 2px rgba(0,0,0,0.15);
  position: absolute;
  <?php $dateright -= $filterdatewidth;?>
  right: <?php echo $dateright;?>px;
  width: <?php echo $filterdatewidth + 1;?>px;
}
<?php endif; ?> 

<?php if($showtodate): ?>
#to_date<?php echo $module->id?>{
  display: block;
  height: 27px;
  padding: 0 5px 0 10px;
  box-sizing: border-box; /* css3 rec */
  -moz-box-sizing: border-box; /* ff2 */
  -ms-box-sizing: border-box; /* ie8 */
  -webkit-box-sizing: border-box; /* safari3 */
  -khtml-box-sizing: border-box; /* konqueror */
  
  border: 1px #bfbfbf solid;
  line-height: 27px;
  
  -webkit-box-shadow: inset 0px 1px 2px rgba(0,0,0,0.15);
  -moz-box-shadow: inset 0px 1px 2px rgba(0,0,0,0.15);
  box-shadow: inset 0px 1px 2px rgba(0,0,0,0.15);
  position: absolute;
  <?php $dateright -= $filterdatewidth;?>
  right: <?php echo $dateright;?>px;
  width: <?php echo $filterdatewidth + 1;?>px;
}
<?php endif; ?>

<?php if($showexactdate): ?>
#exact_date<?php echo $module->id?>{
  display: block;
  height: 27px;
  padding: 0 5px 0 10px;
  box-sizing: border-box; /* css3 rec */
  -moz-box-sizing: border-box; /* ff2 */
  -ms-box-sizing: border-box; /* ie8 */
  -webkit-box-sizing: border-box; /* safari3 */
  -khtml-box-sizing: border-box; /* konqueror */
  
  border: 1px #bfbfbf solid;
  line-height: 27px;
  
  -webkit-box-shadow: inset 0px 1px 2px rgba(0,0,0,0.15);
  -moz-box-shadow: inset 0px 1px 2px rgba(0,0,0,0.15);
  box-shadow: inset 0px 1px 2px rgba(0,0,0,0.15);
  position: absolute;
  <?php $dateright -= $filterdatewidth;?>
  right: <?php echo $dateright;?>px;
  width: <?php echo $filterdatewidth + 1;?>px;
}
<?php endif; ?>

<?php if(!empty($categories)): 
foreach($categories as $key=>$category):?>
#search-form<?php echo $module->id; ?> .category-chooser<?php echo $module->id.$key;?>{
  height: 25px;
  border: 1px #dadada solid;
  border-top-left-radius: 3px;
  border-bottom-left-radius: 3px;
  background-color: <?php echo $this->params->get('chooserbuttoncolor', 'f2f2f2');?>;
  position: absolute;
  <?php $categoryright -= $filterbuttonwidth;?>
  right: <?php echo $categoryright;?>px;
  width: <?php echo $filterbuttonwidth;?>px;
}
<?php endforeach; 
endif; ?>

<?php if(!empty($extrafieldlist)): 
foreach($extrafieldlist as $extrafield):?>
#search-form<?php echo $module->id;?> .extrafield-chooser<?php echo $module->id.$extrafield->id;?>{
  height: 25px;
  border: 1px #dadada solid;
  border-top-left-radius: 3px;
  border-bottom-left-radius: 3px;
  background-color: <?php echo $this->params->get('chooserbuttoncolor', 'f2f2f2');?>;
  position: absolute;
  <?php $extrafieldright -= $filterbuttonwidth;?>
  right: <?php echo $extrafieldright;?>px;
  width: <?php echo $filterbuttonwidth;?>px;
}
<?php endforeach; 
endif; ?>

<?php if(!empty($extrafieldlist)): 
foreach($extrafieldlist as $extrafield):?>
#search-form<?php echo $module->id;?> .extrafield-chooser<?php echo $module->id.$extrafield->id;?>:hover
{
  -webkit-transition: background 200ms ease-out;
  -moz-transition: background 200ms ease-out;
  -o-transition: background 200ms ease-out;
  transition: background 200ms ease-out;
  background-color: #ffffff;
}
<?php endforeach; 
endif; ?> 

<?php if(!empty($categories)): 
foreach($categories as $key=>$category):?>
#search-form<?php echo $module->id;?> .category-chooser<?php echo $module->id.$key;?>:hover
{
  -webkit-transition: background 200ms ease-out;
  -moz-transition: background 200ms ease-out;
  -o-transition: background 200ms ease-out;
  transition: background 200ms ease-out;
  background-color: #ffffff;
}
<?php endforeach; 
endif; ?> 

<?php if(!empty($extrafieldlist)): 
foreach($extrafieldlist as $extrafield):?>
#search-form<?php echo $module->id;?> .extrafield-chooser<?php echo $module->id.$extrafield->id;?>.opened 
{
  height:26px;
  border-bottom: none;
  -moz-border-radius-bottomleft: 0px;
  border-bottom-left-radius: 0px;
  background-color: #ffffff;
  box-shadow: inset 0px 1px 1px rgba(0,0,0,0.15);    
}
<?php endforeach; 
endif; ?>

<?php if(!empty($categories)): 
foreach($categories as $key=>$category):?>
#search-form<?php echo $module->id;?> .category-chooser<?php echo $module->id.$key;?>.opened
{
  height:26px;
  border-bottom: none;
  -moz-border-radius-bottomleft: 0px;
  border-bottom-left-radius: 0px;
  background-color: #ffffff;
  box-shadow: inset 0px 1px 1px rgba(0,0,0,0.15);    
}
<?php endforeach; 
endif; ?>

<?php if(!empty($extrafieldlist)): 
foreach($extrafieldlist as $extrafield):?>
#search-form<?php echo $module->id;?> .extrafield-chooser<?php echo $module->id.$extrafield->id;?> .arrow
{
  height: 26px;
  width: 23px;
  background: no-repeat center center;
  background-image: url('<?php echo $this->cacheUrl.$helper->NewColorizeImage(dirname(__FILE__).'/images/arrow.png', $this->params->get('arrowopenercolor'), '548722');?>');
  float: right;
}
<?php endforeach; 
endif; ?> 

<?php if(!empty($categories)): 
foreach($categories as $key=>$category):?>
#search-form<?php echo $module->id;?> .category-chooser<?php echo $module->id.$key;?> .arrow
{
  height: 26px;
  width: 23px;
  background: no-repeat center center;
  background-image: url('<?php echo $this->cacheUrl.$helper->NewColorizeImage(dirname(__FILE__).'/images/arrow.png', $this->params->get('arrowopenercolor'), '548722');?>');
  float: right;
}
<?php endforeach; 
endif; ?> 

<?php foreach($categories as $key=>$category):
if($key != 0 || $params->get('catchoosercaption', '') != ' '): ?>
#category-name<?php echo $module->id.$key;?>
{
  float: left; 
  margin: 5px 5px 0px 10px;
  cursor: default;
  color: <?php print $this->params->get('searchformfontcolor', 727272);?>;
  font-family: "Vigra",Arial, Helvetica;
  font-weight: bold;
  font-style: normal;
  font-size: 12px;
  text-shadow: 1px 1px 0px #ffffff;
  text-decoration: none;
  line-height: 1;
}
<?php endif;
endforeach; ?>

<?php if(!empty($extrafieldlist)): 
foreach($extrafieldlist as $extrafield):?>
#extrafield-name<?php echo $module->id.$extrafield->id?>
{
  float: left; 
  margin: 5px 5px 0px 10px;
  cursor: default;
  color: <?php print $this->params->get('searchformfontcolor', 727272);?>;
  font-family: "Vigra",Arial, Helvetica;
  font-weight: bold;
  font-style: normal;
  font-size: 12px;
  text-shadow: 1px 1px 0px #ffffff;
  text-decoration: none;
  line-height: 1;
}
<?php endforeach; 
endif; ?>

#search-area<?php echo $module->id; ?>{
  display: block;
  height: 27px;
  <?php $paddingright = 8; 
  if(!$hidecatchooser && !empty($categories))
    $paddingright += $filterbuttonwidth * count($categories);
  if(!empty($extrafieldlist))
    $paddingright += $filterbuttonwidth * count($extrafieldlist);
  if($showfromdate)
  	$paddingright += $filterdatewidth;
  if($showtodate)
  	$paddingright += $filterdatewidth;   
  if($showexactdate)
  	$paddingright += $filterdatewidth;     
  ?>
  width: <?php echo $searchareawidth - $paddingright;?>px;
  padding: 0 30px 0 30px;
  box-sizing: border-box; /* css3 rec */
  -moz-box-sizing: border-box; /* ff2 */
  -ms-box-sizing: border-box; /* ie8 */
  -webkit-box-sizing: border-box; /* safari3 */
  -khtml-box-sizing: border-box; /* konqueror */
  
  border: 1px #bfbfbf solid;
  line-height: 27px;
  
  -webkit-box-shadow: inset 0px 1px 2px rgba(0,0,0,0.15);
  -moz-box-shadow: inset 0px 1px 2px rgba(0,0,0,0.15);
  box-shadow: inset 0px 1px 2px rgba(0,0,0,0.15);    

  float: left;
  margin: 0;
}

.dj_ie #search-area<?php echo $module->id; ?>{
  line-height: 25px;
}

.dj_ie7 #search-area<?php echo $module->id; ?>{
  height: 25px;
  line-height: 25px;
}

.search-caption-on{
  color: #aaa;
}

#search-form<?php echo $module->id; ?> #search-area-close<?php echo $module->id; ?>.search-area-loading{
  background: url(<?php print $themeurl.'images/fadinglines.gif';?>) no-repeat center center;
}

#search-form<?php echo $module->id; ?> #search-area-close<?php echo $module->id; ?>{
  background: url(<?php print $themeurl.'images/x4.png';?>) no-repeat center center;
  background-image: url('<?php echo $this->cacheUrl.$helper->NewColorizeImage(dirname(__FILE__).'/images/x4.png', $this->params->get('closeimagecolor') , '548722'); ?>');
  height: 16px;
  width: 22px;
  top:50%;
  margin-top:-8px;
  <?php $closeright = 5; 
  if(!$this->params->get('hidecatchooser', 0) && !empty($categories))
    $closeright += $filterbuttonwidth * count($categories);
  if(!empty($extrafieldlist))
    $closeright += $filterbuttonwidth * count($extrafieldlist);
  if($showfromdate)
  	$closeright += $filterdatewidth;
  if($showtodate)
  	$closeright += $filterdatewidth;  
  if($showexactdate)
  	$closeright += $filterdatewidth;    
  ?>
  right: <?php echo $closeright;?>px;
  position: absolute;
  cursor: pointer;
  visibility: hidden;
}

#ajax-search-button<?php echo $module->id; ?>{
<?php
  $gradient = explode('-', $this->params->get('searchbuttongradient'));
  ob_start();
  include(dirname(__FILE__).DS.'images'.DS.'bgbutton.svg.php');
  $operagradient = ob_get_contents();
  ob_end_clean();  
?>
  height: 27px;
  width: 30px;
  border-left: 1px #cecece solid;
 
  background: transparent;
  float: left;
  cursor: pointer;
  position: absolute;
  top: 0px;
  left: 0px;
}

.dj_ie7 #ajax-search-button<?php echo $module->id; ?>{
  top: 0+1; ?>px;
  right: 0-1; ?>px;
}

.dj_opera #ajax-search-button<?php echo $module->id; ?>{
  background: transparent url(data:image/svg+xml;base64,<?php echo base64_encode($operagradient); ?>);
  border-radius: 0;
}

#ajax-search-button<?php echo $module->id; ?> .magnifier{
  background: url(<?php print $themeurl.'images/magnifier_strong_mid.png';?>) no-repeat center center;
  height: 27px;
  width: 30px;
  padding:0;
  margin:0;
}

#ajax-search-button<?php echo $module->id; ?>:hover{
  
}

#search-results<?php echo $module->id; ?>{
  position: absolute;
  top:0px;
  left:0px;
  margin-top: 2px;
  visibility: hidden;
  text-decoration: none;
  z-index:1000;
  font-size:12px;
  width: <?php print $searchresultwidth;?>px;
}

#search-results-moovable<?php echo $module->id; ?>{
  position: relative;
  overflow: hidden;
  height: 0px;
  background-color: <?php print $this->params->get('resultcolor');?>;
  border: 1px <?php print $this->params->get('resultbordercolor');?> solid;
  -webkit-box-shadow: 3px 3px 3px rgba(0, 0, 0, 0.3);
  -moz-box-shadow: 3px 3px 3px rgba(0, 0, 0, 0.3);
  box-shadow: 3px 3px 3px rgba(0, 0, 0, 0.3); 
}


#search-results-inner<?php echo $module->id; ?>{
  position: relative;
  width: <?php print $searchresultwidth;?>px; /**/
  overflow: hidden;
/*  padding-bottom: 10px;*/
}

.dj_ie #search-results-inner<?php echo $module->id; ?>{
  padding-bottom: 0px;
}

#search-results<?php echo $module->id; ?> .plugin-title{
  line-height: 27px;
  font-size: 11px;
  /* Firefox */
  background-color: <?php print $this->params->get('plugintitlecolor');?>;
  color: <?php print $this->params->get('resultsfontcolor', 4E6170);?>;
  text-shadow: 1px 1px 0px rgba(255, 255, 255, 0.8);
  text-align: left;
  border-top: 1px solid <?php print $this->params->get('resultbordercolor');?>;
  font-weight: bold;
  height: 100%;
  margin:0;
  padding:0;
}

.dj_opera #search-results<?php echo $module->id; ?> .plugin-title{
  background: #<?php print $gradient[0]; ?> url(data:image/svg+xml;base64,<?php echo base64_encode($operagradient); ?>);
/*  border-radius: 0;*/
}

#search-results<?php echo $module->id; ?> .plugin-title.first{
  margin-top: -1px;
}

.dj_opera #search-results<?php echo $module->id; ?> .plugin-title.first{
  background: #<?php print $gradient[0]; ?> url(data:image/svg+xml;base64,<?php echo base64_encode($operagradient); ?>);
/*  border-radius: 0;*/
}

.dj_ie #search-results<?php echo $module->id; ?> .plugin-title.first{
  margin-top: 0;
} 

#search-results<?php echo $module->id; ?> .ie-fix-plugin-title{
  border-top: 1px solid #B2BCC1;
  border-bottom: 1px solid #000000;
}


#search-results<?php echo $module->id; ?> .plugin-title-inner{
/* -moz-box-shadow:0 1px 2px #B2BCC1 inset;*/
  -moz-user-select:none;
  padding-left:10px;
  padding-right:5px;
  float: left;
  cursor: default;  
  color: <?php print $this->params->get('resultsfontcolor', 4E6170);?>;
  font-family: "Arimo", Arial, Helvetica;
  font-weight: bold;
  font-style: normal;
  font-size: 11px;
  text-shadow: 1px 1px 0px #ffffff;
  text-decoration: none;
}

#search-results<?php echo $module->id; ?> .pagination{
  margin: 8px;
  margin-left: 0px;
  float: right;
  width: auto;
}

#search-results<?php echo $module->id; ?> .pager{
  width: 10px;
  height: 11px;
  margin-left: 5px;
  background: url(<?php print $themeurl.'images/dot_default.png';?>) no-repeat center center;  
  float: left;
  padding:0;
}

#search-results<?php echo $module->id; ?> .pager.active{
  background: url(<?php print $themeurl.'images/dot_selected.png';?>) no-repeat center center;
  cursor: default;
}


#search-results<?php echo $module->id; ?> .page-container{
  position: relative;
  overflow: hidden;
  height: <?php print 65*$productsperplugin;?>px; /* 65px num of elements */
  width: <?php print $searchresultwidth;?>px;
}

#search-results<?php echo $module->id; ?> .page-band{
  position: absolute;
  left: 0;
  width: 10000px;
}

#search-results<?php echo $module->id; ?> .page-element{
  float: left;
  left: 0;
  cursor: hand;
}

#search-results<?php echo $module->id; ?> #search-results-inner<?php echo $module->id; ?> .result-element:hover,
#search-results<?php echo $module->id; ?> #search-results-inner<?php echo $module->id; ?> .selected-element{
  text-decoration: none;
/*  color: <?php print $this->params->get('searchformfontcolor', 727272);?>;;*/
  /* Opera */
/*  background: url(data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciPjxkZWZzPjxsaW5lYXJHcmFkaWVudCBpZD0iZ3JhZGllbnQiIHgxPSIwJSIgeTE9IjAlIiB4Mj0iMCUiIHkyPSIxMDAlIj48c3RvcCBvZmZzZXQ9IjAlIiBzdHlsZT0ic3RvcC1jb2xvcjpyZ2JhKDI0LDE0MSwyMTcsMSk7IiAvPjxzdG9wIG9mZnNldD0iMTAwJSIgc3R5bGU9InN0b3AtY29sb3I6cmdiYSgyNCw4MSwxMjUsMSk7IiAvPjwvbGluZWFyR3JhZGllbnQ+PC9kZWZzPjxyZWN0IGZpbGw9InVybCgjZ3JhZGllbnQpIiBoZWlnaHQ9IjEwMCUiIHdpZHRoPSIxMDAlIiAvPjwvc3ZnPg==);*/
  
  background-color: <?php print $this->params->get('activeresultcolor');?>;

/*  border-top: 1px solid #188dd9;*/
}

.dj_opera #search-results<?php echo $module->id; ?> #search-results-inner<?php echo $module->id; ?> .result-element:hover,
.dj_opera #search-results<?php echo $module->id; ?> #search-results-inner<?php echo $module->id; ?> .selected-element{
  background: transparent url(data:image/svg+xml;base64,<?php echo base64_encode($operagradient); ?>);
  border-radius: 0;
}


#search-results<?php echo $module->id; ?> .result-element{
  display: block;
  width: <?php print $searchresultwidth;?>px;
  height: 64px;
  font-weight: bold;
  border-top: 1px solid <?php print $this->params->get('resultbordercolor');?>;
  overflow: hidden;
}

#search-results<?php echo $module->id; ?> .result-element img{
  display: block;
  float: left;
  padding: 2px;
  padding-right:10px;
  border: 0;
}

.ajax-clear{
  clear: both;
}

#search-results<?php echo $module->id; ?> .result-element span{
  display: block;
  float: left;
  width: <?php print $searchresultwidth-17;?>px;   /*  margin:5+12 */
  margin-left:5px;
  margin-right:12px;
  line-height: 14px;
  text-align: left;
  cursor: pointer;
  margin-top: 5px;  
  color: <?php print $this->params->get('resultsfontcolor', 4E6170);?>;
  font-family: "Arimo", Arial, Helvetica;
  font-weight: bold;
  font-style: normal;
  font-size: 12px;
  text-shadow: 0px 0px 0px #000000;
  text-decoration: none;
  text-transform: none;  
}

#search-results<?php echo $module->id; ?> .result-element span.small-desc{
  margin-top : 2px;
  font-weight: normal;
  line-height: 13px;  
  color: <?php print $this->params->get('searchformfontcolor', 727272);?>;
  font-family: "Arimo", Arial, Helvetica;
  font-weight: normal;
  font-style: normal;
  font-size: 11px;
  text-shadow: 0px 0px 0px #000000;
  text-decoration: none;
  text-transform: none;
}

#search-results<?php echo $module->id; ?> .result-element:hover span.small-desc,
#search-results<?php echo $module->id; ?> .selected-element span.small-desc{
}

#search-results<?php echo $module->id; ?> .result-products span{
  width: <?php print $searchresultwidth-12-60-17;?>px;   /* padding and pictures: 10+2+60, margin:5+12  */
  margin-top: 5px;
}

#search-results<?php echo $module->id; ?> .no-result{
  display: block;
  width: <?php print $searchresultwidth;?>px;
  height: 30px;
  font-weight: bold;
  border-top: 1px solid <?php print $this->params->get('resultbordercolor');?>;
  overflow: hidden;
  text-align: center;
  padding-top:10px;
}

#search-results<?php echo $module->id; ?> .no-result-suggest {
  display: block;
  font-weight: bold;
  border-top: 1px solid <?php print $this->params->get('resultbordercolor');?>;
  overflow: hidden;
  text-align: center;
  padding-top:10px;
  padding-bottom: 6px;
  padding-left: 5px;
  padding-right: 5px;
}

#search-results<?php echo $module->id; ?> .no-result-suggest a {
  cursor: pointer;
  font-weight: bold;
  text-decoration: none;
  padding-left: 4px;
}

#search-results<?php echo $module->id; ?> .no-result-suggest,
#search-results<?php echo $module->id; ?> .no-result-suggest a{  
  color: <?php print $this->params->get('resultsfontcolor', 4E6170);?>;
  font-family: "Arimo", Arial, Helvetica;
  font-weight: bold;
  font-style: normal;
  font-size: 12px;
  text-shadow: 0px 0px 0px #000000;
  text-decoration: none;
  text-transform: none;  
}

#search-results<?php echo $module->id; ?> .no-result-suggest a:hover {
  text-decoration: underline;  
}

#search-results<?php echo $module->id; ?> .no-result span{
  width: <?php print $searchresultwidth-17;?>px;   /*  margin:5+12 */
  line-height: 20px;
  text-align: left;
  cursor: default;
  -moz-user-select:none;
}

<?php if(!empty($extrafieldlist)): 
foreach($extrafieldlist as $extrafield): ?>
#search-extrafields<?php echo $module->id.$extrafield->id; ?>
{
  border-top: 1px #d9d9d9 solid;
  border-left: 1px #d9d9d9 solid;
  border-right: 1px #d9d9d9 solid;
  background-color: <?php echo $this->params->get('choosercontentcolor', 'f2f2f2');?>;
  position: absolute;
  top:0px;
  left:0px;
  visibility: hidden;
  text-decoration: none;
  text-align: left;
  z-index:1001;
  font-size:12px;
}
<?php endforeach; 
endif; ?>

<?php if(!empty($categories)): 
foreach($categories as $key=>$category): ?>
#search-categories<?php echo $module->id.$key; ?>
{
  border-top: 1px #d9d9d9 solid;
  border-left: 1px #d9d9d9 solid;
  border-right: 1px #d9d9d9 solid;
  background-color: <?php echo $this->params->get('choosercontentcolor', 'f2f2f2');?>;
  position: absolute;
  top:0px;
  left:0px;
  visibility: hidden;
  text-decoration: none;
  text-align: left;
  z-index:1001;
  font-size:12px;
}
<?php endforeach; 
endif; ?>

<?php if(!empty($extrafieldlist)): 
foreach($extrafieldlist as $extrafield): ?>
#search-extrafields<?php echo $module->id.$extrafield->id; ?> .search-extrafields-inner div
{
  padding:6px 30px 6px 15px;
  border-bottom: 1px #d9d9d9 solid;
  cursor: default;  
  color: <?php print $this->params->get('resultsfontcolor', 4E6170);?>;
  font-family: "Vigra", Arial, Helvetica;
  font-weight: bold;
  font-style: normal;
  font-size: 11px;
  text-shadow: 1px 1px 0px #ffffff;
  text-decoration: none;
  background: url(<?php print ($themeurl.'images/unselected.png');?>) no-repeat right center;
  -webkit-touch-callout: none;
  -webkit-user-select: none;
  -khtml-user-select: none;
  -moz-user-select: none;
  -ms-user-select: none;
  -o-user-select: none;
  user-select: none;
}
<?php endforeach; 
endif; ?>

<?php if(!empty($categories)): 
foreach($categories as $key=>$category): ?>
#search-categories<?php echo $module->id.$key; ?> .search-categories-inner div
{
  padding:6px 30px 6px 15px;
  border-bottom: 1px #d9d9d9 solid;
  cursor: default;  
  color: <?php print $this->params->get('resultsfontcolor', 4E6170);?>;
  font-family: "Vigra", Arial, Helvetica;
  font-weight: bold;
  font-style: normal;
  font-size: 11px;
  text-shadow: 1px 1px 0px #ffffff;
  text-decoration: none;
  background: url(<?php print ($themeurl.'images/unselected.png');?>) no-repeat right center;
  -webkit-touch-callout: none;
  -webkit-user-select: none;
  -khtml-user-select: none;
  -moz-user-select: none;
  -ms-user-select: none;
  -o-user-select: none;
  user-select: none;
}
<?php endforeach; 
endif; ?>
  
<?php if(!empty($extrafieldlist)): 
foreach($extrafieldlist as $extrafield): ?>
#search-extrafields<?php echo $module->id.$extrafield->id; ?> .search-extrafields-inner div.last
{
  border:none;
}
<?php endforeach; 
endif; ?>

<?php if(!empty($categories)): 
foreach($categories as $key=>$category): ?>
#search-categories<?php echo $module->id.$key; ?> .search-categories-inner div.last
{
  border:none;
}
<?php endforeach; 
endif; ?>

<?php if(!empty($extrafieldlist)): 
foreach($extrafieldlist as $extrafield): ?>
#search-extrafields<?php echo $module->id.$extrafield->id; ?> .search-extrafields-inner div.selected
{
  background: url(<?php print ($themeurl.'images/selected.png');?>) no-repeat right center;
  background-color: #ffffff;
}
<?php endforeach; 
endif; ?>

<?php if(!empty($categories)): 
foreach($categories as $key=>$category): ?>
#search-categories<?php echo $module->id.$key; ?> .search-categories-inner div.selected
{
  background: url(<?php print ($themeurl.'images/selected.png');?>) no-repeat right center;
  background-color: #ffffff;
}
<?php endforeach; 
endif; ?>
