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
use VuFind\View\Helper\Bootstrap3\RDSHelper;

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
class RDSProxyList extends RDSProxyHelper
{
  protected $items = [
       'AuthorsEtAl',
       'SourceDisplay',
       'DataSource',
    ];  
    
  
    public function getGuestviewBriefLink() {
        $html = '';
        if ($this->driver->getGuestView() == 'brief' 
            && $this->authManager->isLoggedIn() === false)
        {
                $html .= '<a href="' . $this->getLoginLink() .  '">';
                $html .= $this->translate("RDS_MORE_INFO_FOR_AUTHORIZED_USERS"); //transEsc
                $thml .= '</a>';
        } 
        return $html;
    }
  
    public function getGuestviewLoginLink() {
        $html = null;
        if ($this->driver->getGuestView() == 'login') {
          if ($this->authManager->isLoggedIn() === false) {
                $html .= '<a href="' . $this->getLoginLink() . '">';
                $html .= $this->translate("RDS_AUTHORIZED_USERS_ONLY"); //transEsc
                $html .= '</a>';
          } else {
            $html .= $this->translate("RDS_USER_NOT_AUTHORIZED");
          }
        }
        return $html;
    
    }
    
    public function getAuthorsEtAl() {
      
      $authorsEtAl = $this->driver->getAuthorsEtAl();
      if (!empty($authorsEtAl)) {
          $html .= $authorsEtAl;
          $html .= '<br />';
      }
    	return $html;
    }
    

    /**
     * Get an datasource.
     *
     * @return String
     */
    public function getSourceDisplay() 
    {   
        $sourceDisplay = $this->driver->getSourceDisplay();
        if (!empty($sourceDisplay)) {
            $html = $sourceDisplay;
            $html .= '<br />';    
        }
        return $html;
    }
    
    public function getDataSource() {
        $html = '';
        $dataSource = $this->driver->getDataSource();
        if (!empty($dataSource)) {
            $html .= $this->translate('RDS_DATA_SOURCE') . ": ";
            $html .= $dataSource;
            $html .= ' ' . $this->getCitationLinks();
            $html .= '<br />';
        }
        
        return $html;
    }
    
    public function getFavAction() {
        $html = '';
        
        $actionUrl = $this->view->plugin('recordLink')->getActionUrl($this->driver, 'Save');
        $uniqueId = $this->driver->getUniqueId();
        $vufindId = $this->driver->getResourceSource() .'|'. $this->driver->getUniqueID();
        
        if ($this->authManager->isLoggedIn()) {
            $html .=  '<a href="' . $actionUrl . '" class="save-record modal-link favActionAdd" id="'.$uniqueId.'" title="' . $this->translate("RDS_ADD_TO_MY_LIST") . '">&rarr; ' . $this->translate("RDS_ADD_TO_MY_LIST") . '</a>';
        } else {
            $html .= '<a href="' . $actionUrl . '"'; 
            $html .=     ' class="cartAction" data-add="&rarr; ' . $this->translate("RDS_ADD_TO_MY_LIST") . '" data-remove="&rarr; ' . $this->translate("RDS_REMOVE_FROM_MY_LIST") . '" id="'.$uniqueId.'"'; 
            $html .=     ' data-id="' . $vufindId . '"';
            $html .= '></a>';
        }
        
        return $html;
    }
  
    public function getPrintAction() {
        $vufindId = $this->driver->getResourceSource() .'|'. $this->driver->getUniqueID();
        $html = '<a href="/Cart/doExport" class="doExportRecord" data-id="'. $vufindId .'">&rarr; ' . $this->translate("RDS_PRINT") . '</a>';
        
        return $html;
    }
    
    public function getFulltextLink() {
        $html = null;
        $fulltextLinks = $this->driver->getFulltextLinks();
        if ($fulltextLinks[0]['url']) {
            $html .= '<a target="_blank" href="' . $fulltextLinks[0]['url'] . '">&rarr; ' . $this->translate("RDS_FULLTEXT_LINK") . '</a>';
            $html .= '<br />';
        }
        
        return $html;
    }
    

}
