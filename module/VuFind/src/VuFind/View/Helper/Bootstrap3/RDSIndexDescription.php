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
namespace VuFind\View\Helper\Bootstrap3;
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
class RDSIndexDescription extends \Zend\View\Helper\AbstractHelper implements TranslatorAwareInterface
{
    use \VuFind\I18n\Translator\TranslatorAwareTrait;


    /**
     * Result structure for mergeData 
     *
     * @array
     */
    protected $items = [
    "ABSTRACT",
    "LINKS",
    "NOTATION", // not Hohenheim
    "CT",
    "LOKCT",
    "CTGENRE",
    ];

    protected $driver = null;

    /**
    * Initial method
    *
    * @param string $driver the driver
    *
    * @return driver
    */
    public function __invoke($driver)
    {
        // Set up driver context:
        $this->driver = $driver;
        return $this;
    }

    /**
    * Get the names of the functions 
    *
    * @return array 
    */
    public function getItems() 
    {
        $results = [];
        foreach ($this->items as $item) {
            $function = [$this, 'get' . $item];
            $itemValue = call_user_func($function);
            if ($itemValue) {
                $results['RDS_' . $item] = $itemValue;
            }
        }
        return $results;
    }

    /**
     * Get abstracts 
     *
     * @return string 
     */
    protected function getABSTRACT()
    {
        $html_result = "";
        $abstract = $this->driver->getAbstract();
        if ($abstract != null) {
            foreach ($abstract as $value) {
                $last_item = end($abstract);
                $html_result .= htmlspecialchars($value);
                if ($string != $last_item ) {
                    $html_result .="<br /> " ; 
                }
            }

        }
        return $html_result;
    }

    /**
     * Get link in html format 
     *
     * @return string
     */
    protected function getLINKS() 
    {
        $html_result = "";
        $links = $this->driver->getLinks();
        if ($links != null) {
            foreach ($links as $field) {
                $last_item = end($links);
                $html_result .= "<a class='link-external' href=".$field['url'].">".$field['txt']."</a>";
                if (isset($field['jahr']) && !empty($field['jahr'])) {
                    $html_result .= $field['jahr']; 
                }
                if ($links != $last_item ) {
                    $html_result .="<br /> " ; 
                }
            }
        }
        return $html_result;
    }

    /**
     * Get link with long description in html format 
     *
     * @return string
     */
    protected function getLONGLINKS() 
    {
        // TODO **** not for all bibs => tue, blb, ulm NOT tested *** TODO
        $html_result = "";
        $links = $this->driver->getLongLinksTab2();
        if ($links != null) {
            foreach ($links as $field) {
                $last_item = end($links);
                $html_result .= "<a class='link-external' href=".$field['url'].">".$field['txt']."</a>";
                if ($links != $last_item ) {
                    $html_result .="<br /> " ; 
                }
            }
        }
        return $html_result;
    }

    /**
     * Get rvk notation 
     *
     * @return string
     */
    protected function getNOTATION() 
    {
        // TODO **** not Hohenheim  *** TODO
        $html_result = "";
        $links = $this->driver->getNotation();
        if ($links != null) {
            foreach ($links as $field) {
                $last_item = end($links);
                if (!empty($field['url'])) {
                    $html_result .= "<a class='link-internal' href=".$this->view->render('/RecordDriver/RDSIndex/link-rvk.phtml', ['lookfor' => $field['url']]).">".$field['url']."</a>";
                }
                if (isset($field['txt']) && !empty($field['txt'])) {
                    $html_result .=    " (".$field['txt'].")"; 
                }
                if ($links != $last_item ) {
                    $html_result .="<br /> " ; 
                }
            }
        }
        return $html_result;
    }

    /**
     * Get link for dnb description in html format 
     *
     * @return string
     */
    protected function getCT()
    {
        $html_result = "";
        $ct_display = $this->driver->getCT();
        if (isset($ct_display) && !empty($ct_display)) {
            foreach ($ct_display as $ct_field) {
                $last_item = end($ct_display);
                foreach ($ct_field as $field) {
                    $last_item = end($ct_field);
                    $html_result .= "<a class='link-internal' href=".$this->view->render('/RecordDriver/RDSIndex/link-ct.phtml', ['lookfor' => $field['link']]).">".$field['link']."</a>";
                    if (isset($field['gnd']) && !empty($field['gnd'])) {
                        $html_result .= " <a class='dnb link-external' href=".$this->view->render('/RecordDriver/RDSIndex/link-gnd.phtml', ['lookfor' => $field['gnd']])." target ='_blank'"
                        ."title='".$this->translate('RDS_CT_DNB')."'></a>";
                    }
                    if ($field != $last_item ) {
                        $html_result .=" / " ; 
                    }
                }
                if ($ct_display != $last_item ) {
                    $html_result .="<br /> " ; 
                }
            }
        }
        return $html_result;
    }
       
    /**
     * Get local subjects with link in html format 
     *
     * @return string
     */ 
    protected function getLOKCT() 
    {
        $html_result = "";
        $links = $this->driver->getLokCt();
        if ($links != null) {
            foreach ($links as $field) {
                $last_item = end($links);
                $html_result .= "<a class='link-internal' href=".$this->view->render('/RecordDriver/RDSIndex/link-zs.phtml', ['lookfor' => $field]).">".$field."</a>";
                if ($links != $last_item ) {
                    $html_result .="<br /> " ; 
                }
            }
        }
        return $html_result;
    }
    /**
     * Get genre 
     *
     * @return string
     */
    protected function getCTGENRE ()
    {
        $html_result = "";
        $result = $this->driver->getCtGenre();
        if ($result != null) {
            foreach ($result as $value) {
                $last_item = end($abstract);
                $html_result .= htmlspecialchars($value);
                if ($result != $last_item ) {
                    $html_result .="<br /> " ; 
                }
            }
        }
        return $html_result;
    }

}
