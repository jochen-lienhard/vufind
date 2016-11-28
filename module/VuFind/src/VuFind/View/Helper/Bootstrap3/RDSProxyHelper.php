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
class RDSProxyHelper extends RDSHelper
{
    protected $authManager = null;
    protected $linkresolver = null;
    protected $authorizationService = null;
    
    protected $items = [];

    protected $isLoggedIn = false;
    protected $accessRestrictedContent = false;
    protected $guestview = '';
    
    /**
     * Dummy.
     *
     * @param string $linkresolver link resolver
     */
    public function __construct($linkresolver, $authorizationService) 
    {
        $this->linkresolver = $linkresolver;
        $this->authorizationService = $authorizationService;
        $this->accessRestrictedContent 
            = $this->authorizationService->isGranted("access.RDSRestrictedContent"); 
    }
   
    /**
     * Dummy.
     *
     * @param string $driver driver 
     *
     * @return Mixed 
     */
    public function __invoke($driver)
    {
        $this->guestview = $driver->getGuestView();
        $this->authManager = $this->view->plugin('auth');
        $this->isLoggedIn = $this->authManager->isLoggedIn();
        
        return parent::__invoke($driver);
    }
   
    /**
     * Dummy.
     *
     * @return Mixed 
     */ 
    public function getDataSource() 
    {
        $value = $this->driver->getDataSource();
        return $value;
    }
   
    /**
     * Dummy.
     *
     * @return Mixed 
     */ 
    public function getCitationLinks() 
    {
        //<a target="_blank" href="{$summCitationLinks[0].url}" onclick="userAction('click', 'RdsCitationLink', '{$ppn}');">&rarr;{translate text="Link zum Zitat"}</a>
      
        if ($this->driver->showCitationLinks() == false) {
            return '';
        };  
        
        $html = '';
        $citationLinks = (is_array($this->driver->getCitationLinks())) 
            ? $this->driver->getCitationLinks() : [];
        foreach ($citationLinks as $citationLink) {
          $html .= '<a class="link-external" target="_blank" href="' . $citationLink['url'] . '" onclick="userAction(\'click\', \'RdsCitationLink\', \'' . $this->driver->getUniqueId() . '\');"> ' . $this->translate("RDS_CITATION_LINK") .'</a>';
        }  
      
        return $html;
    }
   
}
