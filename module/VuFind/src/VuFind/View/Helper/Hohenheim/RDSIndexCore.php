<?php
/**
 * Record driver view helper
 *
 * PHP version 5
 *
 * Copyright (C) Villanova University 2010.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License version 2,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * @category VuFind2
 * @package  View_Helpers
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @author   Hannah Born <born@ub.uni-freiburg.de>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/vufind2:developer_manual Wiki
 */
namespace VuFind\View\Helper\Hohenheim;
use Zend\View\Exception\RuntimeException, Zend\View\Helper\AbstractHelper;
use     VuFind\I18n\Translator\TranslatorAwareInterface;
use Zend\View\Helper\EscapeHtml;


/**
 * Record driver view helper
 *
 * @category VuFind2
 * @package  View_Helpers
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @author   Hannah Born <born@ub.uni-freiburg.de>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/vufind2:developer_manual Wiki
 */
class RDSIndexCore extends  \VuFind\View\Helper\Bootstrap3\RDSIndexCore
{
	use \VuFind\I18n\Translator\TranslatorAwareTrait;

/*	protected $items = [
		"TITLE",
		"COLL_TITLE",
		"TITLE_PART",
		"CJK_TITLE",
		"TITLECUT", // only Hoh ??
		"UREIHE",
		"CJK_UREIHE",
		"PERSON",
		"CJK_AUT",
		"BEIGWERK",
		"CORP",
		"CJK_CO",
		"EDITION",
		"CJK_EDITION",
		"PUBLISH",
		"CJK_PUBLISH",
		"PUB_RUN",
		"Language",
		"SCOPE",
		"CJK_SCOPE",
		"ISBN",
		"ISSN",
		"Links",
		"DESCR",
		"STOCK",
		"REFVALUE",
		"MEDIUM",
		"FN",
		"ENTHWERK",
		"BIBL_ZUS",
		];
*/
	protected $driver = null;

	protected function getTITLECUT(){
		$html_result = "";
		$transEsc = $this->getView()->plugin('escapeHtml');
		$titlePart = $this->driver->getTitleCut();
		if (!empty($titlePart)){
			foreach($titlePart as $field){
				$last_item = end($titlePart);
				$html_result .= $transEsc($field);
				if($titlePart!= $last_item )
					$html_result .="<br /> " ;
			}
		}
		return $html_result;
	}

	protected function getISSN() {
		$html_result = "";
		$transEsc = $this->getView()->plugin('escapeHtml');
		$result = $this->driver->getISSN();
		$issnprint =  $this->driver->getISSNPrint(); 
		$issnonline =  $this->driver->getISSNOnline();
		if (!empty($result)){
			foreach($result as $field){
				$last_item = end($result);
				$html_result .= $transEsc($field);
				if($result!= $last_item )
					$html_result .="<br /> " ;
			}
		}
		if (!empty($issnprint))
			$html_result .= $this->translate('RDS_ISSNPRINT').": ".$transEsc($issnprint);
		if (!empty($issnonline))
			$html_result .= $this->translate('RDS_ISSNONLINE').": ".$transEsc($issnonline);

		return $html_result;
	}


}
