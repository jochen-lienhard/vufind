<?php
/**
 * RDSToFormat exporter for rds data
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
 * @author   Markus Beh <markus.beh@ub.uni-freiburg.de>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/vufind2:developer_manual Wiki
 */

namespace VuFind\Export;
use VuFind\Export\DataProvider\RDSDataProviderProxy;
use VuFind\Export\DataProvider\RDSDataProviderIndex;

/**
 * RDSToFormat exporter for rds data
 *
 * @category VuFind2
 * @package  View_Helpers
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @author   Jochen Lienhard <lienhard@ub.uni-freiburg.de>
 * @author   Markus Beh <markus.beh@ub.uni-freiburg.de>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/vufind2:developer_manual Wiki
 */
abstract class RDSToFormat
{

    protected $dataProvider;
    protected $driver;

    /**
     * Constructor 
     *
     * @param string $driver current driver 
     */
    public function __construct($driver) 
    {
        $sourceIdentifier = $driver->getSourceIdentifier();
        
        switch ($sourceIdentifier) {
        case 'RDSIndex':
            $this->dataProvider = new RDSDataProviderIndex($driver->getRawData(), $driver);
            break;
        case 'RDSProxy':
            $this->dataProvider = new RDSDataProviderProxy($driver->getRawData(), $driver);
            break;
        }
         $this->driver = $driver;
    }
    
    /**
     * Get the data provider
     *
     * @return string 
     */
    public function getDataProvider() 
    {
        return $this->dataProvider;
    }

    /**
     * Set the data provider
     *
     * @param string $dataProvider current provider
     *
     * @return void
     */
    public function setDataProvider($dataProvider) 
    {
        $this->dataProvider = $dataProvider;
    }
   
    /**
     * Abstract method for getRecord
     *
     * @return void
     */ 
    abstract public function getRecord();
}

?>
