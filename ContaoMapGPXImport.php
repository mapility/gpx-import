<?php if (!defined('TL_ROOT')) die('You cannot access this file directly!');

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2011 Leo Feyer
 *
 * Formerly known as TYPOlight Open Source CMS.
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at <http://www.gnu.org/licenses/>.
 *
 * PHP version 5
 * @copyright  CyberSpectrum 2011
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @package    ContaoMaps
 * @license    LGPL
 * @filesource
 */


/**
 * Class ContaoMapGPXImport 
 *
 * @copyright  Cyberspectrum 2011
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @package    Controller
 */
class ContaoMapGPXImport extends Backend
{

	public function getChildCount($xml, $nodeName)
	{
		if(phpversion() >= '5.3.0')
		{
			return $xml->$nodeName->count();
		} else {
			$namespaces = $xml->getDocNamespaces(true);
			$xml->registerXPathNamespace('__empty_ns', $namespaces['']);
			$xpath=$xml->xpath('.//__empty_ns:'.$nodeName);
			return count($xpath);
		}
	}

	public function importGpx(DataContainer $dc)
	{
		if (\Input::getInstance()->get('key') != 'importgpx')
		{
			return '';
		}

		$objTemplate = new BackendTemplate('be_importgpx');
		$objTemplate->objTree = new FileTree($this->prepareForWidget($GLOBALS['TL_DCA']['tl_catalog_items']['fields']['source'], 'source', null, 'source', 'tl_contaomap_layer'));

		$referer = $this->getReferer();

		if(\Input::getInstance()->post('key')=='importgpx')
		{
			$_SESSION['TL_CONFIRM'] = null;
			$_SESSION['TL_ERROR'] = null;
			// check that we have a GPX file.
			$strFile = \FilesModel::findById(\Input::getInstance()->post('source'))->path;
			$objFile = new File($strFile);

			if ($objFile->extension != 'gpx')
			{
				$this->Session->set('tl_gpx_import', null);
				$_SESSION['TL_ERROR'][] = sprintf($GLOBALS['TL_LANG']['ERR']['filetype'], $objFile->extension);
				$this->redirect($referer);
			}
			// open file
			$useError = libxml_use_internal_errors(false);
			if (!($gpxFile = simplexml_load_file(TL_ROOT .'/'. $strFile)))
			{
				$this->Session->set('tl_gpx_import', null);
				$_SESSION['TL_ERROR'][] = $GLOBALS['TL_LANG']['ERR']['noGPXData'];
				foreach (libxml_get_errors() as $error)
				{
					$_SESSION['TL_ERROR'][] = $error;
				}
				$this->redirect($referer);
			}
			libxml_use_internal_errors($useError);
			if (\Input::getInstance()->post('removeData'))
			{
				\Database::getInstance()->prepare('DELETE FROM tl_contaomap_marker WHERE pid=?')
								->execute(\Input::getInstance()->get('id'));
				\Database::getInstance()->prepare('DELETE FROM tl_contaomap_polygon WHERE pid=?')
								->execute(\Input::getInstance()->get('id'));
				\Database::getInstance()->prepare('DELETE FROM tl_contaomap_polyline WHERE pid=?')
								->execute(\Input::getInstance()->get('id'));
			}
			// now cycle through all xml entities and add them properly.

			// specs: http://www.topografix.com/GPX/1/1/

			// Routes
			$i=0;
			$max=strlen((string)$this->getChildCount($gpxFile, 'rte'));
			foreach($gpxFile->rte as $objRte)
			{
				$name = ($objRte->name[0])?((string)$objRte->name[0]):sprintf('GPX Route %s %0'.$max.'d', $strFile, ++$i);
				$arrPoints=array();
				foreach($objRte->rtept as $objPoint)
				{
					$arrPoints[] = $objPoint->attributes()->lat.','.$objPoint->attributes()->lon;
				}
				$arrExtends = ContaoMap::calcExtends($arrPoints);
				$arrData = array(
					'pid' => \Input::getInstance()->get('id'),
					'tstamp' => time(),
					'name' => $name,
					'min_latitude' => $arrExtends[0][0],
					'min_longitude'=> $arrExtends[0][1],
					'max_latitude'=> $arrExtends[1][0],
					'max_longitude'=> $arrExtends[1][1],
					'coords' => implode($arrPoints, "\n"),
					'strokecolor' => 'ff6600',
					'strokeweight' => '3',
					'strokeopacity' => '100',
				);
				\Database::getInstance()->prepare('INSERT INTO tl_contaomap_polyline %s')->set($arrData)->execute();
				$_SESSION['TL_CONFIRM'][] = sprintf($GLOBALS['TL_LANG']['MSC']['importGPXRoute'], $name);
			}

			// Tracks
			$i=0;
			$max=strlen((string)$this->getChildCount($gpxFile, 'trk'));
			foreach($gpxFile->trk as $objTrk)
			{
				$j=0;
				$i++;
				$max2=strlen((string)$this->getChildCount($objTrk, 'trkseg'));
				$name = ($objTrk->name[0])?((string)$objTrk->name[0]):sprintf('GPX Track %s %0'.$max.'d', $strFile, $i);
				foreach($objTrk->trkseg as $objTrkSeg)
				{
					$arrPoints=array();
					foreach($objTrkSeg->trkpt as $objPoint)
					{
						$arrPoints[] = $objPoint->attributes()->lat.','.$objPoint->attributes()->lon;
					}
					$arrExtends = ContaoMap::calcExtends($arrPoints);
					$arrData = array(
						'pid' => \Input::getInstance()->get('id'),
						'tstamp' => time(),
						'name' => sprintf('%s %0'.$max2.'d', $name, ++$j),
						'min_latitude' => $arrExtends[0][0],
						'min_longitude'=> $arrExtends[0][1],
						'max_latitude'=> $arrExtends[1][0],
						'max_longitude'=> $arrExtends[1][1],
						'coords' => implode($arrPoints, "\n"),
						'strokecolor' => '000000',
						'strokeweight' => '3',
						'strokeopacity' => '100',
					);
					\Database::getInstance()->prepare('INSERT INTO tl_contaomap_polyline %s')->set($arrData)->execute();
				}
				$_SESSION['TL_CONFIRM'][] = sprintf($GLOBALS['TL_LANG']['MSC']['importGPXTracks'], $name, $j);
			}

			// Waypoints
			$i=0;
			$max=strlen((string)$this->getChildCount($gpxFile, 'wpt'));
			foreach($gpxFile->wpt as $objPoint)
			{
				$name = ($objPoint->name[0])?((string)$objPoint->name[0]):sprintf('GPX Trackpoint %s %0'.$max.'d', \Input::getInstance()->post('source'), ++$i);
				$arrCoord = array($objPoint->attributes()->lat, $objPoint->attributes()->lon);
				$arrData = array(
					'pid' => \Input::getInstance()->get('id'),
					'tstamp' => time(),
					'name' => $name,
					'coords' => implode($arrCoord, ','),
					'latitude' => floatval($arrCoord[0]),
					'longitude' => floatval($arrCoord[1]),
					'icon' => '',
					'shadow' => '',
					'anchor' => '',
					'text' => (string)$objPoint->desc,
					'info_anchor' => '',
					'info_auto' => '',
				);
				\Database::getInstance()->prepare('INSERT INTO tl_contaomap_marker %s')->set($arrData)->execute();
				$_SESSION['TL_CONFIRM'][] = sprintf($GLOBALS['TL_LANG']['MSC']['importGPXPoints'], $name);

/*
<wpt
lat="latitudeType [1] ?"
lon="longitudeType [1] ?">
<ele> xsd:decimal </ele> [0..1] ?
<time> xsd:dateTime </time> [0..1] ?
<magvar> degreesType </magvar> [0..1] ?
<geoidheight> xsd:decimal </geoidheight> [0..1] ?
<name> xsd:string </name> [0..1] ?
<cmt> xsd:string </cmt> [0..1] ?
<desc> xsd:string </desc> [0..1] ?
<src> xsd:string </src> [0..1] ?
<link> linkType </link> [0..*] ?
<sym> xsd:string </sym> [0..1] ?
<type> xsd:string </type> [0..1] ?
<fix> fixType </fix> [0..1] ?
<sat> xsd:nonNegativeInteger </sat> [0..1] ?
<hdop> xsd:decimal </hdop> [0..1] ?
<vdop> xsd:decimal </vdop> [0..1] ?
<pdop> xsd:decimal </pdop> [0..1] ?
<ageofdgpsdata> xsd:decimal </ageofdgpsdata> [0..1] ?
<dgpsid> dgpsStationType </dgpsid> [0..1] ?
<extensions> extensionsType </extensions> [0..1] ?
</wpt>
*/
			}
			$this->reload();
		}
		return $objTemplate->parse();
	}
}
?>