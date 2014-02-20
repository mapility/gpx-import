<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2014 Leo Feyer
 *
 * @package Contaomaps_gpx
 * @link    https://contao.org
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */


/**
 * Register the classes
 */
ClassLoader::addClasses(array
(
	'ContaoMapGPXImport' => 'system/modules/contaomaps_gpx/ContaoMapGPXImport.php',
));


/**
 * Register the templates
 */
TemplateLoader::addFiles(array
(
	'be_importgpx' => 'system/modules/contaomaps_gpx/templates',
));
