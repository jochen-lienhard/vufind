<?php
/**
 * RDSIndexHolding view helper
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
namespace VuFind\View\Helper\Freiburg;
use     VuFind\I18n\Translator\TranslatorAwareInterface;

/**
 * RDSIndexHolding view helper
 *
 * @category VuFind2
 * @package  View_Helpers
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @author   Jochen Lienhard <lienhard@ub.uni-freiburg.de>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/vufind2:developer_manual Wiki
 */
class RDSIndexHolding extends \VuFind\View\Helper\Bootstrap3\RDSIndexHolding
{
    /**
     * List of adis client 
     *
     * @array
     */
    protected $adis_clients = ["25", "Frei26", "Frei129"];

    /**
     * List of clients, where all is based on daia
     *
     * @array
     */
    protected $daia_only_clients = ["Frei26", "Frei129"];

    /**
     * Result order
     *
     * @array
     */
    protected $resultOrder = [
       "RDS_LEA",
       "RDS_SIGNATURE",
       "RDS_STATUS",
       "RDS_LOCATION",
       "RDS_URL",
       "RDS_HINT",
       "RDS_HOLDING",
       "RDS_HOLDING_LEAK",
       "RDS_COMMENT",
       "RDS_INTERN",
       "RDS_PROVENIENCE",
       "RDS_LOCAL_NOTATION",
    ];


    /**
     * Check if signatures fit together 
     *
     * @param string $daia_sig  signature from daia
     * @param string $loc_sig   signature from loc set 
     * @param string $bib_sigel sigel of the library
     *
     * @return boolean
     */
    protected function checkSignature($daia_sig,$loc_sig, $bib_sigel)
    {
        if ($bib_sigel == "25") {
            if (preg_match('/\</', $daia_sig) {
               $loc_length = strlen(str_replace(' ', '', (strtolower($loc_sig))));  
            } else {
               $loc_length = strlen(str_replace(' ', '', (strtolower($daia_sig))));
            }
            if (substr(str_replace(' ', '', (strtolower($daia_sig))),0,$loc_length)== substr(str_replace(' ', '', (strtolower($loc_sig))),0,$loc_length)) {
               return true;
            } else {
               return false;
            }
        } else {
            if (str_replace(' ', '', (strtolower($daia_sig))) == str_replace(' ', '', (strtolower($loc_sig)))) {
               return true;
            } else {
               return false;
            }
        }
    }


    /**
     * Check if item is part of something special 
     *
     * @param string $lok_set local data set 
     *
     * @return boolean
     */
    protected function checkSummary($lok_set) 
    {
        // for Freiburg
        if (preg_match('/^LB/', $lok_set["signatur"])) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Creates the status based on lok_set 
     *
     * @param array $lok_set 
     *
     * @return string
     */
    protected function setLocStatus($lok_set)
    {
        if (isset($lok_set["praesenz"]) && $lok_set["praesenz"]=='p') {
            return "RDS_REF_STOCK";
        } else {
           if ($lok_set["bib_sigel"] == "Frei160") {
               return "<a href=javascript:msgWindow=window.open('http://www.eh-freiburg.de/studieren/bibliothek','KIOSK','width=1024,height=580,location=no,menubar=yes,toolbar=not,status=yes,scrollbars=yes,directories=no,resizable=yes,alwaysRaised=yes,hotkeys=no,top=0,left=200,screenY=0,screenX=200');msgWindow.focus();>Bibliothek der evangelischen Hochschule Freiburg</a>";
           }
           return null;
        }
    }

    /**
     * Creates the location depending on the data loc set
     *
     * @param array $lok_set 
     *
     * @return string
     */
    protected function setLocation($lok_set) 
    {
        if (isset($lok_set["standort"])) {
            if (isset($lok_set["R84_zusatz_standort"])) {
                return $lok_set["R84_zusatz_standort"] . " " . $lok_set["standort"];
            }
            return $lok_set["standort"];
        }
    }

    /**
     * Creates the local notation based on lok_set 
     *
     * @param array $lok_set 
     *
     * @return array 
     */
    protected function setLocalNotation($lok_set)
    {
       $urlHelper = $this->getView()->plugin('url');
       $url = $urlHelper('rdsindex-search');
       $localvalue = "";
       foreach ($lok_set["lok_no"] as $lok_no) {
             $localvalue .= "<a href='". $url . "?lookfor=zr:\"". $lok_no . "\"&type=allfields&submit=Suchen'>" . $lok_no . "</a><br />";
       }
       return $localvalue;
    }


    /**
     * Generates a string for bib_name based on local data set
     *
     * @param string $bib_sigel id of library
     *
     * @return string 
     */
    public function getBibAddon($bib_sigel)
    {
        if (($bib_sigel === 'Frei85') || ($bib_sigel === '25-122') || ($bib_sigel === '25-66')) {
            return "DE" . str_replace("-", "", $bib_sigel) . "Addon";
        } else {
            return null;
        }
    }


    /**
     * Generates a string for bib_link based on local data set
     *
     * @param string $bib_sigel id of library
     *
     * @return string 
     */
    public function getBibLink($bib_sigel)
    {
        // for Freiburg
        return "https://www.ub.uni-freiburg.de/index.php?id=1272&sigel=" . $bib_sigel;
    }

    /**
     * Returns the array of the result order 
     *
     * @param string $bib_sigel id of library
     *
     * @return array 
     */
    public function getSpecial($bib_sigel)
    {
        $mybibarr = [
           "25",
           "25-3",
           "25-5",
           "25-6",
           "25-15",
           "25-16",
           "25-19",
           "25-33",
           "25-34",
           "25-462",
           "25-71",
           "25-74",
           "25-77",
           "25-91",
           "25-140",
        ];
        if (in_array($bib_sigel, $mybibarr)) {
           // ToDo issn and ti not available
           return "<a href='https://mybib.ub.uni-freiburg.de/intern/bestellung2/?sigel=" . $bib_sigel . "&amp;id=" . $this->lok["t_idn"] . "&amp;sign=" . $this->lok["signatur"] . "&amp;best=" . $this->lok["bestand8032"] . "+" . $this->lok["lueckenangabe8033"] . "'>" . $this->translate("RDS_PRINT_COPY") . "</a>";
        } else {
           return null;
        }
    }

    /**
     * Generates a string for adis_link based on daia result 
     *
     * @param string $bib_sigel id of library 
     *
     * @return string 
     */
    public function getAdisLink($bib_sigel)
    {
        $adisLink = null;
        foreach ($this->daia[$bib_sigel] as $loc_daia) {
            foreach ($loc_daia as $items) {
                foreach ($items as $item) {
                    if (isset ($item['ilslink'])) {
                        $adisLink = $item['ilslink'];
                    }
                }
            }
        }
        if (in_array($bib_sigel, $this->adis_clients) && $adisLink) {
            return "javascript:msgWindow=window.open('" . $adisLink . "&specialconfig" . "','KIOSK','width=1024,height=580,location=no,menubar=yes,toolbar=not,status=yes,scrollbars=yes,directories=no,resizable=yes,alwaysRaised=yes,hotkeys=no,top=0,left=200,screenY=0,screenX=200');msgWindow.focus();";
        } else {
            return null;
        }
    }

}
