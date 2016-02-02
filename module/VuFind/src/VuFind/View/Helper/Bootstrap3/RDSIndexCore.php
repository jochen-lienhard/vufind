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
namespace VuFind\View\Helper\Bootstrap3;
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
class RDSIndexCore extends \Zend\View\Helper\AbstractHelper implements TranslatorAwareInterface
{
    use \VuFind\I18n\Translator\TranslatorAwareTrait;
    
    /**
    * Result structure  
    *
    * @array
    */
    protected $items = [
    "TITLE",
    "COLLTITLE", 
    "TITLE_PART",
    "EST",
    "CJKTITLE",
    "TITLECUT", // only Hoh ??
    "UREIHE",
    "CJKUREIHE",
    "PERSON",
    "CJKAUT",
    "BEIGWERK",
    "CORP",
    "CJKCO",
    "EDITION",
    "CJKEDITION",
    "PUBLISH",
    "CJKPUBLISH",
    "LANGUAGES",
    "PUBRUN",
    "REGISTER", // only Ulm
    "Language",
    "SCOPE",
    "CJKSCOPE",
    "ISBN",
    "ISSN",
    "LINKS",
    "DESCR",
    "STOCK",
    "REFVALUE",
    "MEDIUM",
    "FN",
    "CJKFN",
    "ENTHWERK",
    "CJKFNENTH",
    "FNEBOOK",
    "HSS",
    "BIBLZUS",
    "WERK",
    "INWERK",
    "SERIES",
    "CJKSERIE",
    "NATINFO",
    ];

    protected $driver = null;

    //	protected $ppn = $this->driver->gettPPN();


    /**
    * Initial method
    * @param $driver the driver
    * @return driver
    */
    public function __invoke($driver) 
    {
        // Set up driver context:
        $this->driver = $driver;
        return $this;

    }

    /**
    * Get Items for display
    * @return results 
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
     * Get ppn of title 
     *
     * @return ppn
    */
    protected function getPpn()
    {
        $ppn = $this->driver->getPPN();
        return $ppn;
    }
    /**
     * Get title 
     *
     * @return html_result 
    */
    protected function getTITLE()
    {
        $html_result = "";
        $transEsc = $this->getView()->plugin('escapeHtml');
        $ast = $this->driver->getAst();
        if (!empty($ast)) {
            $html_result .= "[".$transEsc($ast)."]"; 
        }
        $html_result .= $transEsc($this->driver->getTitle());

        return $html_result;

    }

    /**
     * Get spez title parts
     *
     * @return html_result 
    */
    protected function getCOLLTITLE()
    {
        $html_result = "";
        $transEsc = $this->getView()->plugin('escapeHtml');
        $titleLong_f = $this->driver->getTitleLongf();
        $titleLong_f_sec = $this->driver->getTitleLongfsec();
        if (!empty($titleLong_f)) {
            $html_result .= $transEsc($titleLong_f); 
        }
        if (!empty($titleLong_f_sec)) {
            foreach ($titleLong_f_sec as $field) {
                $html_result .= $transEsc($field);
            }
        }
        return $html_result;
    }

    /**
     * Get title part
     *
     * @return html_result 
    */
    protected function getTITLEPART()
    {
        $html_result = "";
        $transEsc = $this->getView()->plugin('escapeHtml');
        $titlePart = $this->driver->getTitlePart();
        if (!empty($titlePart)) {
            foreach ($titlePart as $field) {
                $last_item = end($titlePart);
                $html_result .= $transEsc($field);
                if ($titlePart!= $last_item ) {
                    $html_result .="<br /> " ; 
                }
            }        
        }
        return $html_result;
    }
    

    /**
     * Get cjk title 
     *
     * @return html_result 
    */
    protected function getCJKTITLE()
    {
        $html_result = "";
        $transEsc = $this->getView()->plugin('escapeHtml');
        $cjkTitle = $this->driver->getCjkTitle();
        $titleCjkAst = $this->driver->getCjkAst();
        if (!empty($cjkTitle)) {
            $html_result .= $transEsc($cjkTitle);
            if (!empty($titleCjkAst)) {
                $html_result .= $transEsc($cjkAst); 
            }
        }
        return $html_result;
    }

    /**
     * Get title cut (hoh only)
     *
     * @return html_result 
    */
    protected function getTITLECUT()
    {
        $html_result = "";
        $transEsc = $this->getView()->plugin('escapeHtml');
        $titlePart = $this->driver->getTitleCut();
        if (!empty($titlePart)) {
            foreach ($titlePart as $field) {
                $last_item = end($titlePart);
                $html_result .= $transEsc($field);
                if ($titlePart!= $last_item) {
                    $html_result .="<br /> " ; 
                }
            }        
        }
        return $html_result;
    }

    /**
     * Get unterreihe
     *
     * @return html_result 
    */
    protected function getUREIHE()
    {
        $html_result = "";
        $transEsc = $this->getView()->plugin('escapeHtml');
        $titlePart = $this->driver->getUnterreihe();
        if (!empty($titlePart)) {
            foreach ($titlePart as $field) {
                $last_item = end($titlePart);
                $html_result .= $transEsc($field);
                if ($titlePart!= $last_item) {
                    $html_result .="<br /> " ; 
                }
            }        
        }
        return $html_result;
    }

    /**
     * Get CjkUreihe 
     *
     * @return html_result 
    */
    protected function getCJKUREIHE()
    {
        $html_result = "";
        $transEsc = $this->getView()->plugin('escapeHtml');
        $titlePart = $this->driver->getCjkUreihe();
        if (!empty($titlePart)) {
            foreach ($titlePart as $field) {
                $last_item = end($titlePart);
                $html_result .= $transEsc($field);
                if ($titlePart!= $last_item) {
                    $html_result .="<br /> " ; 
                }
            }        
        }
        return $html_result;
    }

    /**
     * Get AuthorsLong
     *
     * @return html_result 
    */
    protected function getPERSON()
    {
        $html_result = "";
        $transEsc = $this->getView()->plugin('escapeHtml');
        $authors_long = $this->driver->getAuthorsLong();
        if (isset($authors_long) && !empty($authors_long)) {
            foreach ($authors_long as $field) {

                $last_item = end($authors_long);
                $html_result .= "<a href=".$this->view->render('/RecordDriver/RDSIndex/link-author.phtml', ['lookfor' => $field['link']]).">"
                .$transEsc($field['link'])."</a>";
                if (isset($field['link_text']) && !empty($field['link_text'])) {
                    $html_result .= $transEsc($field['link_text']); 
                }
                if (isset($field['gnd']) && !empty($field['gnd'])) {
                    $html_result .= " <a class='dnb' href="
                    .$this->view->render('/RecordDriver/RDSIndex/link-gnd.phtml', ['lookfor' => $field['gnd']])
                    ." target ='_blank' title='".$this->translate('RDS_PERS_DNB')."'></a>";
                    $myau = explode(",", $field['link']);
                    $mywiki = str_replace(' ', '_', trim($myau[1])) . "_" . $myau[0];
                    $html_result .= " <a class='wikipedia' href="
                    .$this->view->render('/RecordDriver/RDSIndex/link-wiki.phtml', ['lookfor' => $mywiki])
                    ." target='_blank' title='".$this->translate('RDS_PERS_WIKI')."'</a>";
                }
                if ($authors_long != $last_item) {
                    $html_result .="<br /> " ; 
                }
            }
        }
        return $html_result;
    }

    /**
     * Get CjkAut 
     *
     * @return html_result 
    */
    protected function getCJKAUT()
    {
        $html_result = "";
        $transEsc = $this->getView()->plugin('escapeHtml');
        $cjkAut = $this->driver->getCjkAut();
        if (!empty($cjkAut)) {
            $html_result .= $transEsc($cjkAut);
        }
        return $html_result;
    }

    /**
     * Get IncludedWork
     *
     * @return html_result 
    */
    protected function getBEIGWERK()
    {
        $html_result = "";
        $transEsc = $this->getView()->plugin('escapeHtml');
        $titlePart = $this->driver->getIncludedWork();
        if (!empty($titlePart)) {
            foreach ($titlePart as $field) {
                $last_item = end($titlePart);
                $html_result .= $transEsc($field);
                if ($titlePart!= $last_item) {
                    $html_result .="<br /> " ; 
                }
            }
        }
        return $html_result;
    }

    /**
     * Get Corporation
     *
     * @return html_result 
    */
    protected function getCORP()
    {
        $html_result = "";
        $transEsc = $this->getView()->plugin('escapeHtml');
        $co_long = $this->driver->getCorporation();
        if (isset($co_long) && !empty($co_long)) {
            foreach ($co_long as $field) {

                $last_item = end($co_long);
                $html_result .= "<a href=".$this->view->render('/RecordDriver/RDSIndex/link-corporate.phtml', ['lookfor' => $field['link']]).">"
                .$transEsc($field['link'])."</a>";
                if (isset($field['link_text']) && !empty($field['link_text'])) {
                    $html_result .= " [".$transEsc($field['link_text'])."]"; 
                }
                if (isset($field['gnd']) && !empty($field['gnd'])) {
                    $html_result .= " <a class='dnb' href="
                    .$this->view->render('/RecordDriver/RDSIndex/link-gnd.phtml', ['lookfor' => $field['gnd']])
                    ." target ='_blank' title='".$this->translate('RDS_CO_DNB')."'></a>";
                }
                if ($authors_long != $last_item) {
                    $html_result .="<br /> " ; 
                }
            }
        }
        return $html_result;
    }

    /**
     * Get CjkCorp
     *
     * @return html_result 
    */
    protected function getCJKCO()
    {
        $html_result = "";
        $transEsc = $this->getView()->plugin('escapeHtml');
        $cjkCo = $this->driver->getCjkCorp();
        if (!empty($cjkCo)) {
            $html_result .= $transEsc($cjkCo);
        }
        return $html_result;
    }

    /**
     * Get Editions
     *
     * @return html_result 
    */
    protected function getEDITION()
    {
        $html_result = "";
        $transEsc = $this->getView()->plugin('escapeHtml');
        $result = $this->driver->getEditions();
        if (!empty($result)) {
            $html_result .= $transEsc($result);
        }
        return $html_result;
    }

    /**
     * Get CjkEdition
     *
     * @return html_result 
    */
    protected function getCJKEDITION()
    {
        $html_result = "";
        $transEsc = $this->getView()->plugin('escapeHtml');
        $result = $this->driver->getCjkEdition();
        if (!empty($result)) {
            $html_result .= $transEsc($result);
        }
        return $html_result;
    }

    /**
     * Get PublishDisplay
     *
     * @return html_result 
    */
    protected function getPUBLISH()
    {
        $html_result = "";
        $transEsc = $this->getView()->plugin('escapeHtml');
        $pu_pp_display = $this->driver->getPublishDisplay(); 
        $pp_norm_display = $this->driver->getppNormDisplay();
        if (isset($pu_pp_display) && !empty($pu_pp_display)) {
            foreach ($pu_pp_display as $field) {
                $last_item = end($pu_pp_display);
                $html_result .= $transEsc($field);
                if ($pu_pp_display != $last_item ) {
                    $html_result .="<br /> " ; 
                }
            }
            if (isset($pp_norm_display) && !empty($pp_norm_display)) {
                $html_result .= "<span class='handwriting'>".$this->translate('RDS_HAND_NORM')."</span>";
                foreach ($pp_norm_display as $field) {
                    $last_item = end($pp_norm_display);
                    $html_result .= $transEsc($field);
                    if ($pp_norm_display != $last_item ) {
                        $html_result .="<br /> " ; 
                    }
                }
            }
        }
        return $html_result;
    }

    /**
     * Get CjkPp
     *
     * @return html_result 
    */
    protected function getCJKPUBLISH()
    {
        $html_result = "";
        $transEsc = $this->getView()->plugin('escapeHtml');
        $result = $this->driver->getCjkPp();
        if (!empty($result)) {
            $html_result .= $transEsc($result);
        }
        return $html_result;
    }

    /**
     * Get ZsVerlauf 
     *
     * @return html_result 
    */
    protected function getPUBRUN() 
    {
        $html_result = "";
        $transEsc = $this->getView()->plugin('escapeHtml');
        $result = $this->driver->getZsVerlauf();
        if (!empty($result)) {
            $html_result .= $transEsc($result);
        }
        return $html_result;
    }

    /**
     * Get Register (ulm only)
     *
     * @return html_result 
    */
    protected function getREGISTER() 
    {
        $html_result = "";
        $transEsc = $this->getView()->plugin('escapeHtml');
        $result = $this->driver->getRegister();
        if (!empty($result)) {
            $html_result .= $transEsc($result);
        }
        return $html_result;
    }
    
    /**
     * Get Languages
     *
     * @return html_result 
    */
    protected function getLANGUAGES()
    {
        $html_result = "";
        $transEsc = $this->getView()->plugin('escapeHtml');
        $result = $this->driver->getLanguages();
        if (!empty($result)) {
            foreach ($result as $field) {
                $last_item = end($result);
                $html_result .= $this->translate($transEsc($field));
                if ($result!= $last_item ) {
                    $html_result .="<br /> " ; 
                }
            }
        }
        return $html_result;
    }

    /**
     * Get Scope
     *
     * @return html_result 
    */
    protected function getSCOPE() 
    {
        $html_result = "";
        $transEsc = $this->getView()->plugin('escapeHtml');
        $result = $this->driver->getScope();
        if (!empty($result)) {
            $html_result .= $transEsc($result);
        }
        return $html_result;
    }
    
    /**
     * Get CjkScope 
     *
     * @return html_result 
    */
    protected function getCJKSCOPE() 
    {
        $html_result = "";
        $transEsc = $this->getView()->plugin('escapeHtml');
        $result = $this->driver->getCjkScope();
        if (!empty($result)) {
            $html_result .= $transEsc($result);
        }
        return $html_result;
    }
    
    /**
     * Get ISBN
     *
     * @return html_result 
    */
    protected function getISBN()
    {
        $html_result = "";
        $transEsc = $this->getView()->plugin('escapeHtml');
        $result = $this->driver->getISBN();
        if (!empty($result)) {
            foreach ($result as $field) {
                $last_item = end($result);
                $html_result .= $transEsc($field);
                if ($result!= $last_item ) {
                    $html_result .="<br /> " ; 
                }
            }
        }
        return $html_result;
    }
    
    /**
     * Get ISSN
     *
     * @return html_result 
    */
    protected function getISSN() 
    {
        $html_result = "";
        $transEsc = $this->getView()->plugin('escapeHtml');
        $result = $this->driver->getISSN();
        if (!empty($result)) {
            foreach ($result as $field) {
                $last_item = end($result);
                $html_result .= $transEsc($field);
                if ($result!= $last_item ) {
                    $html_result .="<br /> " ; 
                }
            }
        }
        return $html_result;
    }

    /**
     * Get Est
     *
     * @return html_result 
    */
    protected function getEST() 
    {
        $html_result = "";
        $result = $this->driver->getEst();
        $transEsc = $this->getView()->plugin('escapeHtml');
        if (!empty($result)) {
            foreach ($result as $field) {
                $last_item = end($result);
                $html_result .= $transEsc($field['txt']);
                if (isset($field['gnd']) && !empty($field['gnd'])) {
                    $html_result .= " <a class='dnb' href="
                    .$this->view->render('/RecordDriver/RDSIndex/link-gnd.phtml', ['lookfor' => $field['gnd']])." target ='_blank'"
                    ."title='".$this->translate('RDS_CO_DNB')."'></a>";
                }
            }
        }
        return $html_result;
    }

    /**
     * Get EbookLink 
     *
     * @return html_result 
    */
    protected function getLINKS() 
    {
        $html_result = "";
        if (strstr($this->getPpn(), "NL")) {
            $transEsc = $this->getView()->plugin('escapeHtml');
            $result = $this->driver->getEbookLink();
            if (isset($result) && !empty($result)) {
                foreach ($result as $field) {
                    $last_item = end($result);
                    $html_result .= "<a href='".$field['url']."'>".$transEsc($field['lnk_txt'])."</a>";
                    if ($result!= $last_item ) {
                        $html_result .="<br /> " ; 
                    }
                }
            }
        }
        return $html_result;
    }

    /**
     * Get HandwritingDesc
     *
     * @return html_result 
    */
    protected function getDESCR()
    {
        $html_result = "";
        $transEsc = $this->getView()->plugin('escapeHtml');
        $result = $this->driver->getHandwritingDesc();
        if (isset($result) && !empty($result)) {
            foreach ($result as $field) {
                $last_item = end($result);
                $html_result .= "<span class='handwriting'>".$transEsc($field['txt1'])."</span>";
                if (isset($field['txt2'])) {
                    $html_result .= $transEsc($field['txt2']); 
                }
                if ($result!= $last_item ) {
                    $html_result .="<br /> " ; 
                }
            }
        }
        return $html_result;
    }

    /**
     * Get HandwritingBase
     *
     * @return html_result 
    */
    protected function getSTOCK()
    {
        $html_result = "";
        $transEsc = $this->getView()->plugin('escapeHtml');
        $result = $this->driver->getHandwritingBase();
        if (isset($result) && !empty($result)) {
            foreach ($result as $field) {
                $last_item = end($result);
                $html_result .= "<span class='handwriting'>".$transEsc($field['title'])."</span>";
                $html_result .= $transEsc($field['txt']);
                if ($result!= $last_item ) {
                    $html_result .="<br /> " ; 
                }
            }
        }
        return $html_result;
    }

    /**
     * Get HandwritingRefValue
     *
     * @return html_result 
    */
    protected function getREFVALUE()
    {
        $html_result = "";
        $transEsc = $this->getView()->plugin('escapeHtml');
        $result = $this->driver->getHandwritingRefValue();
        if (isset($result) && !empty($result)) {
            foreach ($result as $field) {
                $last_item = end($result);
                $html_result .= "<span class='handwriting'>".$transEsc($field['title'])."</span>";
                $html_result .= $transEsc($field['txt']);
                if ($result!= $last_item ) {
                    $html_result .="<br /> " ; 
                }
            }
        }
        return $html_result;
    }

    /**
     * Get Medium
     *
     * @return html_result 
    */
    protected function getMEDIUM()
    {
        $html_result = "";
        $transEsc = $this->getView()->plugin('escapeHtml');
        $result = $this->driver->getMedium();
        if (isset($result) && !empty($result)) {
            $html_result .= $transEsc($result);
        }
        return $html_result;
    }

    /**
     * Get Fn
     *
     * @return html_result 
    */
    protected function getFN() 
    {
        $html_result = "";
        $transEsc = $this->getView()->plugin('escapeHtml');
        $result = $this->driver->getFn();
        if (!empty($result)) {
            foreach ($result as $field) {
                $last_item = end($result);
                if (!empty($field['url'])) {
                    $html_result .=  $transEsc($field['text'])."<a href=".$field['url'].">".$field['url']."</a>";
                } else {
                    $html_result .= $transEsc($field['text']); 
                }
                if ($result!= $last_item) {
                    $html_result .="<br /> " ; 
                }
            }
        }
        $result2 = $this->driver->getSekundaer();
        if (!empty($result2)) {
            foreach ($result2 as $field) {
                $last_item = end($result2);
                $html_result .= $transEsc($field);
                if ($result2!= $last_item) {
                    $html_result .="<br /> " ; 
                }
            }
            
        }
        return $html_result;
    }

    /**
     * Get CjkFN
     *
     * @return html_result 
    */
    protected function getCJKFN()
    {
        $html_result = "";
        $transEsc = $this->getView()->plugin('escapeHtml');
        $result = $this->driver->getCjkFN();
        if (!empty($result)) {
            $html_result .= $transEsc($result);
        }
        return $html_result;
    }


    /**
     * Get EnthWerk 
     *
     * @return html_result 
    */
    protected function getENTHWERK() 
    {
        $html_result = "";
        $transEsc = $this->getView()->plugin('escapeHtml');
        $result = $this->driver->getEnthWerk();
        if (!empty($result)) {
            foreach ($result as $field) {
                $last_item = end($result);
                $html_result .= $transEsc($field);
                if ($result!= $last_item ) {
                    $html_result .="<br /> " ; 
                }
            }
        }
        return $html_result;
    }
    
    /**
     * Get CjkFNEnth
     *
     * @return html_result 
    */
    protected function getCJKFNENTH() 
    {
        $html_result = "";
        $transEsc = $this->getView()->plugin('escapeHtml');
        $result = $this->driver->getCjkFNEnth();
        if (!empty($result)) {
            foreach ($result as $field) {
                $last_item = end($result);
                $html_result .= $transEsc($field);
                if ($result!= $last_item ) {
                    $html_result .="<br /> " ; 
                }
            }
        }
        return $html_result;
    }

    /**
     * Get FnEbook
     *
     * @return html_result 
    */
    protected function getFNEBOOK() 
    {
        $html_result = "";
        $transEsc = $this->getView()->plugin('escapeHtml');
        $result = $this->driver->getFnEbook();
        if (!empty($result)) {
            foreach ($result as $field) {
                $last_item = end($result);
                $html_result .= $transEsc($field);
                if ($result!= $last_item) {
                    $html_result .="<br /> " ; 
                }
            }
        }
        return $html_result;
    }

    /**
     * Get Hss
     *
     * @return html_result 
    */
    protected function getHSS() 
    {
        $html_result = "";
        $transEsc = $this->getView()->plugin('escapeHtml');
        $result = $this->driver->getHss();
        if (!empty($result)) {
            foreach ($result as $field) {
                $html_result .= $transEsc($field);
            }
        }
        return $html_result;
    }


    /**
     * Get JournalInfo
     *
     * @return html_result 
    */
    protected function getBIBLZUS() 
    {
        $html_result = "";
        $transEsc = $this->getView()->plugin('escapeHtml');
        $zs_info = $this->driver->getJournalInfo();
        if (isset($zs_info) && !empty($zs_info)) {
            foreach ($zs_info as $field) {
                $last_item = end($zs_info);
                if (!empty($field['pre-text'])) {
                    $html_result .=  $transEsc($field['pre-text']).": "; 
                }
                if (!empty($field['id'])) {
                    $html_result .= "<a href=".$this->view->render('/RecordDriver/RDSIndex/link-id.phtml', ['lookfor' => $field['id']]).">"
                    .$transEsc($field['text'])."</a>";
                } else {
                    if (!empty($field['text'])) {
                        $html_result .=  $transEsc($field['text']); 
                    }
                }
                if ($zs_info != $last_item) {
                    $html_result .="<br /> " ; 
                }
            }
        }
        return $html_result;
    }

    /**
     * Get Werk
     *
     * @return html_result 
    */
    protected function getWERK() 
    {
        $html_result = "";
        if ($this->driver->getArticleInfo() == '0') {
            $transEsc = $this->getView()->plugin('escapeHtml');
            $info = $this->driver->getWerk();
            if (isset($info) && !empty($info)) {
                foreach ($info as $field) {
                    if (!empty($field['id'])) {
                        $html_result .= "<a href="
			.$this->view->render('/RecordDriver/RDSIndex/link-id.phtml', ['lookfor' => $field['id']]).">"
                        .$transEsc($field['lnk_txt']);
                    }
                    if (!empty($field['bnd'])) {
                        $html_result .= " ; ".$transEsc($field['bnd'])."</a><br/>"; 
                    } else {
                        $html_result .= "</a><br/>"; 
                    }
                }
            }
        }
        return $html_result;
    }

    /**
     * Get INWERK 
     *
     * @return html_result 
    */
    protected function getINWERK() 
    {
        $html_result = "";
        if ($this->driver->getArticleInfo() == '1') {
            $transEsc = $this->getView()->plugin('escapeHtml');
            $info = $this->driver->getWerk();
            if (isset($info) && !empty($info)) {
                foreach ($info as $field) {
                    if (!empty($field['id'])) {
                        $html_result .= "<a href="
                        .$this->view->render('/RecordDriver/RDSIndex/link-id.phtml', ['lookfor' => $field['id']]).">"
                        .$transEsc($field['lnk_txt']);
                    }
                    if (!empty($field['bnd'])) {
                        $html_result .= " ; ".$transEsc($field['bnd'])."</a><br/>"; 
                    } else {
                        $html_result .= "</a><br/>"; 
                    }
                }
            }
        }
        return $html_result;
    }

    /**
     * Get Series
     *
     * @return html_result 
    */
    protected function getSERIES() 
    {
        $html_result = "";
        $transEsc = $this->getView()->plugin('escapeHtml');
        $info = $this->driver->getSeriesTit();
        $info2 = $this->driver->getUngezReihe();
        if (isset($info) && !empty($info)) {
            foreach ($info as $field) {
                if (!empty($field['id'])) {
                    $html_result .= "<a href="
                     .$this->view->render('/RecordDriver/RDSIndex/link-id.phtml', ['lookfor' => $field['id']]).">"
                    .$transEsc($field['lnk_txt']);
                }
                if (!empty($field['bnd'])) {
                    $html_result .= " ; ".$transEsc($field['bnd'])."</a><br/>"; 
                } else {
                    $html_result .= "</a><br/>"; 
                }
            }
        }
        if (isset($info2) && !empty($info2)) {
            foreach ($info2 as $field) {
                $html_result .= $transEsc($field);
            }
        }
        return $html_result;
    }

    /**
     * Get CjkSeriesTit
     *
     * @return html_result 
    */
    protected function getCJKSERIE() 
    {
        $html_result = "";
        $transEsc = $this->getView()->plugin('escapeHtml');
        $result = $this->driver->getCjkSeriesTit();
        if (!empty($result)) {
            foreach ($result as $field) {
                $last_item = end($result);
                if (!empty($field['id'])) {
                    $html_result .= "<a href=".$this->view->render('/RecordDriver/RDSIndex/link-id.phtml', ['lookfor' => $field['id']]).">"
                    .$transEsc($field['lnk_txt'])."</a><br/>";
                }
                if ($result!= $last_item) {
                    $html_result .="<br /> " ; 
                }
            }
        }
        return $html_result;
    }

    /**
     * Get NatInfo
     *
     * @return html_result 
    */
    protected function getNATINFO()
    {
        $html_result = "";
        $transEsc = $this->getView()->plugin('escapeHtml');
        $result = $this->driver->getNatInfo();
        if (isset($result) && !empty($result)) {
            $html_result .= $transEsc($result);
        }
        return $html_result;
    }
}
