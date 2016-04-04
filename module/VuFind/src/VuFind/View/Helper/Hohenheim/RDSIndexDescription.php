<?php
/**
 * RDSIndexDescription view helper
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
 * @author   Jochen Lienhard <lienhard@ub.uni-freiburg.de>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/vufind2:developer_manual Wiki
 */
namespace VuFind\View\Helper\Hohenheim;
use     VuFind\I18n\Translator\TranslatorAwareInterface;
use Zend\View\Helper\EscapeHtml;

/**
 * RDSIndexDescription view helper
 *
 * @category VuFind2
 * @package  View_Helpers
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @author   Jochen Lienhard <lienhard@ub.uni-freiburg.de>
 * @author   Hannah Born <born@ub.uni-freiburg.de>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/vufind2:developer_manual Wiki
 */
class RDSIndexDescription extends  \VuFind\View\Helper\Bootstrap3\RDSIndexDescription
{


    /**
     * Result structure  
     *
     * @array
     */
    protected $items = [
    "ABSTRACT",
    "LINKS",
    "SY",
    "CT",
    "LOCCT"
    ];


    /**
     * Get SY 
     *
     * @return string 
    */
    protected function getSY()
    {
        $html_result = "";
        $lok_no = $this->driver->getLokNotation();
        $rvk_no = $this->getNotation();
        if ((!empty($rvk_no))) {
            $html_result  = $rvk_no;
        }
        if (!empty($lok_no)) {
            foreach ($lok_no as $field) {
                $last_item = end($lok_no);
                //$html_result .= "<a href=".$this->record($this->driver)->getLink('zr', $field.">".$field."</a>";
                $html_result .= "<a href=".$this->view->render('/RecordDriver/RDSIndex/link-zr.phtml', ['lookfor' => $field]).">".$field."</a>";
                $html_result .= " ".$this->translate('BB_'.$field);
                if ($lok_no != $last_item ) {
                    $html_result .="<br /> " ; 
                }
            }
        }
        return $html_result;
    }

}
