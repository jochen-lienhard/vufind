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
use Zend\Filter\File\UpperCase;

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
    
    protected $items = [];

    public function __construct($linkresolver) {
        $this->linkresolver = $linkresolver;
    }
    
    public function __invoke($driver)
    {
        $this->authManager = $this->view->plugin('auth');
        return parent::__invoke($driver);
    }
    
   protected function getLoginLink() {
        $followupUrl = $this->view->plugin('serverUrl')->__invoke() . $_SESSION['Search']['last'];
        $target = $this->view->plugin('url')->__invoke('myresearch-home') . '?followupUrl=' . urlencode($followupUrl);
  
        $sessionInitiator = $this->authManager->getManager()->getSessionInitiator($target);
        if ($sessionInitiator) {
            $loginLink = $this->view->plugin('escapeHtmlAttr')->__invoke($sessionInitiator);
        } else {
            $loginLink = $this->view->plugin('url')->__invoke('myresearch-userlogin');
        }
        return $loginLink;
    }
    
    public function getDataSource() {
      $value = $this->driver->getDataSource();
      return $value;
    }
    
    public function getCitationLinks() {
      //<a target="_blank" href="{$summCitationLinks[0].url}" onclick="userAction('click', 'RdsCitationLink', '{$ppn}');">&rarr;{translate text="Link zum Zitat"}</a>
      
      if ($this->driver->showCitationLinks() == false) {
        return '';
      };  
        
      $html = '';
      foreach ($this->driver->getCitationLinks() as $citationLink) {
          $html .= '<a target="_blank" href="' . $citationLink[url] . '" onclick="userAction(\'click\', \'RdsCitationLink\', \'{$ppn}\');">&rarr; ' . $this->translate("Link zum Zitat") .'</a>';
      }  
      
      return $html;
    }
    
   
}
