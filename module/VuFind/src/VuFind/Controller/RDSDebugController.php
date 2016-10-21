<?php
/**
 * RDSDebug Controller
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
 * @package  Controller
 * @author   Markus Beh <markus.beh@ub.uni-freiburg.de>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org   Main Site
 */
namespace VuFind\Controller;

/**
 * RDSDebug Controller
 *
 * @category VuFind2
 * @package  Controller
 * @author   Markus Beh <markus.beh@ub.uni-freiburg.de>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org   Main Site
 */

class RDSDebugController extends AbstractBase
{
    /**
     * RDSDAIA Driver configurations
     *
     * @var array
     */
    protected $daiaConfigFiles = [
        'RDSDAIA100' => '100 - UB Hohenheim',
        'RDSDAIA21' => '21 - UB Tuebingen',
        'RDSDAIA25-122' => '25-122 - IGPP Freiburg',
        'RDSDAIA25' => '25 - UB Freiburg',
        'RDSDAIA289' => '289 - kiz Ulm',
        //'RDSDAIA31' => '31 - Badische Landesbibliothek',
        'RDSDAIA93' => '93 - UB Stuttgart',
        'RDSDAIAFrei129' => 'Frei129 - PH Freiburg',
        'RDSDAIAFrei26' => 'Frei26 - Caritas Freiburg',
        'RDSDAIATue123' => 'Tue123 - LB Tuebingen',
        'RDSDAIATue24' => 'Tue24 - LB Tuebingen',
    ];

    /**
     * Home action for RDSDebug
     *
     * @return view
     */
    public function homeAction()
    {
        $view = $this->createViewModel();;
        
        // restrict access 
        $accessGranted = $this->getAuthorizationService()
            ->isGranted('access.RDSDebug');
        $view->accessGranted = $accessGranted;
        if (! $accessGranted) {
            return $view;
        }
        
        // options for select box
        $view->daiaConfigFiles = $this->daiaConfigFiles;

        // get driver configuration name form request
        $daiaConfig = $_REQUEST['daiaConfig'];
        if (! isset($daiaConfig)) {
            $daiaConfig = 'RDSDAIA25';
        }
        $view->selectedDaiaConfig = $daiaConfig;
        
        // return page if there is no ppn given
        $ppn = $_REQUEST['ppn'];
        if (! $ppn) {
            return $view; 
        }
        $view->ppn = $ppn;
        
        // setup driver RDSDAIA driver instance
        $configPluginManager = $this->getServiceLocator()->get('VuFind\Config');
        $ilsDriverPluginManger = $this->getServiceLocator()
            ->get('VuFind\ILSDriverPluginManager');
        $driverInst = clone($ilsDriverPluginManger->get('RDSDAIA'));
        $config = $configPluginManager->get($daiaConfig);
        $driverInst->setConfig($config);
        $driverInst->init();
        
        $view->config = $config->DAIA->toArray();

        // make request to the daia server process response / errors
        $daiaServerStatus = "SUCCESS";
        try {
            $daiaServerResponse = $driverInst->doDebugHTTPRequest('ppn:' . $ppn);
            
            $response = json_decode($daiaServerResponse);
            if ($response->message[0]->errno) {
                $daiaServerStatus = 'WARN: ' . $response->message[0]->content;
            }
        } catch (\Exception $e) {
            $daiaServerStatus = 'ERROR: ' . $e->getMessage();
        }
        

        $view->daiaServerStatus = $daiaServerStatus;
        $view->daiaServerResponse = $daiaServerResponse;      
        
        return $view; 
    }
}
