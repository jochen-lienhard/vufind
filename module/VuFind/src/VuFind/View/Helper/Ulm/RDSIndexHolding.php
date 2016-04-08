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
namespace VuFind\View\Helper\Ulm;

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
    protected $adis_clients = ["289"];

    /**
     * Generates a string for bib_link based on local data set
     *
     * @param string $bib_sigel id of library
     *
     * @return string 
     */
    public function getBibLink($bib_sigel)
    {
        if ($bib_sigel === "LFER") {
            return null;
        } else {
            return "ULM_LINK_" . $bib_sigel;
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
        if (isset($lok_set["adis_sigel"])) {
            return $this->translate("BIB_" . $lok_set["adis_sigel"]);
        }
    }

    /**
     * Make the aDIS Location readable 
     *
     * @param string $adis_loc storage of an item based on adis
     * @param string $rds_loc  location based on loc_set
     *
     * @return string 
     */
    protected function createReadableLocation($adis_loc, $rds_loc=null)
    {
        return $rds_loc . " " . $adis_loc;
    }



}
