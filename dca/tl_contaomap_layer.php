<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

/**
 * PHP version 5
 * @copyright  Cyberspectrum 2011
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @package    ContaoMaps 
 * @license    LGPL 
 * @filesource
 */

/**
 * Table tl_contaomap_layer
 */

$GLOBALS['TL_DCA']['tl_contaomap_layer']['list']['operations']['importgpx'] = array
(
	'label'					=> &$GLOBALS['TL_LANG']['tl_contaomap_layer']['importgpx'],
	'href'					=> 'key=importgpx',
	'icon'					=> 'system/modules/contaomaps_gpx/html/gpx.gif',
	'button_callback'		=> array('tl_contaomap_layer', 'internalButton')
);

?>