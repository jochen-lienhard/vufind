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
 * @author   Jochen Lienhard <lienhard@ub.uni-freiburg.de>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/vufind2:developer_manual Wiki
 */

namespace VuFind\View\Helper\Bootstrap3;
use Zend\View\Exception\RuntimeException, Zend\View\Helper\AbstractHelper;
use VuFind\Export\DataProvider\RDSDataProviderProxy;
use VuFind\Export\DataProvider\RDSDataProviderIndex;
use VuFind\Export\RDSToBibTeX;
use VuFind\Export\RDSToRIS;
use VuFind\Export\RDSToJSON;
use VuFind\Export\RDSToCOinS;
use VuFind\Export\RDSToHTML;
use VuFind\Export\RDSToPRINT;

/**
 * Record driver view helper
 *
 * @category VuFind2
 * @package  View_Helpers
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @author   Jochen Lienhard <lienhard@ub.uni-freiburg.de>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/vufind2:developer_manual Wiki
 */
class RDSExport extends AbstractHelper
{
    protected $formatter = null;
    protected $linkresolver = null;

    /**
     * Dummy 
     *
     * @param string $linkresolver link resolver
     *
     * @return string
     */
    public function __construct($linkresolver) 
    {
        $this->linkresolver = $linkresolver;
    }
   
    /**
     * Dummy 
     *
     * @param string $driver driver
     * @param string $format format
     *
     * @return string
     */
    public function __invoke($driver, $format) 
    {
        switch ($format) {
        case 'HTML':
            $formatter = new RDSToHTML($driver, $this->view, $this->linkresolver);
            break;
        case 'PRINT':
            $formatter = new RDSToPrint($driver, $this->view, $this->linkresolver);
            break;
        case 'BibTeX':
            $formatter = new RDSToBibTeX($driver);
            break;
        case 'RIS':
            $formatter = new RDSToRIS($driver);
            break;                
        case 'COinS':
            $formatter = new RDSToCOinS($driver);
            break;
        }
        $this->formatter = $formatter;
        
        return $this;
    }
   
    /**
     * Dummy 
     *
     * @return string
     */
    public function getRecord() 
    {
        return $this->formatter->getRecord();
    }

    /**
     * Dummy 
     *
     * @return string
     */
    public function getCore() 
    {
        return $this->formatter->getCore();
    }
    
    /**
     * Dummy 
     *
     * @return string
     */
    public function getDescription() 
    {
        return $this->formatter->getDescription();
    }
    
    /**
     * Dummy 
     *
     * @return string
     */
    public function getHoldings() 
    {
        return $this->formatter->getHoldings();
    }
    
}

