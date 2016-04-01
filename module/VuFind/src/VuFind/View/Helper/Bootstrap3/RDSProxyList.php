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
use VuFind\View\Helper\Bootstrap3\RDSProxyHelper;

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
    
    /**
     * Dummy 
     *
     * @return string
     */  
    public function getGuestviewBriefLink() 
    {
        $html = '';
        if ($this->driver->getGuestView() == 'brief' 
            && $this->authManager->isLoggedIn() === false
        ) {
                $html .= '<a href="' . $this->getLoginLink() .  '">';
                $html .= $this->translate("RDS_MORE_INFO_FOR_AUTHORIZED_USERS"); //transEsc
                $html .= '</a>';
        } 
        return $html;
    }
 
    /**
     * Dummy 
     *
     * @return string
     */ 
    public function getGuestviewLoginLink() 
    {
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
   
    /**
     * Dummy 
     *
     * @return string
     */ 
    public function getAuthorsEtAl() 
    {
        $html = '';      
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
        $html = '';
        $sourceDisplay = $this->driver->getSourceDisplay();
        if (!empty($sourceDisplay)) {
            $html = $sourceDisplay;
            $html .= '<br />';    
        }
        return $html;
    }
   
    /**
     * Dummy 
     *
     * @return string
     */ 
    public function getDataSource() 
    {
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
   
    /**
     * Dummy 
     *
     * @return string
     */ 
    public function getFulltextLink() 
    {
        $html = null;
        $fulltextLinks = $this->driver->getFulltextLinks();
        
        $dataview = $this->view->params->getOptions()->getListViewOption();
        
        if ($this->driver->showFulltextLinks()) {
            if (!empty($fulltextLinks)) {
                if ($fulltextLinks[0]['indicator'] == 1) {
                    if ($this->driver->getGuestView() != 'brief' && $this->authManager->isLoggedIn() == true) {
                        
                        /*     <a target="_blank" href='<?=$path?>AJAX/JSON?method=getFulltextLink&source=RDSProxy&id=<?=$ppn?>' onclick="userAction('click', 'RdsFulltextLink', '<?=$ppn?>');">&rarr;<?=$this->transEsc("RDS_FULLTEXT_LINK") ?></a> */
                        $html .= '<a target="_blank" href="' . $this->path . 'AJAX/JSON?method=getFulltextLink&source=RDSProxy&id=' . $this->uniqueId . '" onclick="userAction(\'click\', \'RdsFulltextLink\', \'' . $this->uniqueId . '\');">&rarr;' . $this->translate("RDS_FULLTEXT_LINK") . '</a>'; 
                    } else {
                        /* <a href="<?=$path?>RDSProxyrecord/<?=$ppn?>"{if $record_view != "flat"} data-view="<?=$dataView ?>" class="getFull" onclick="userAction('click', 'RdsFulltextAvailable', '<?=$ppn?>'); return false;"{/if}>&rarr;<?=$this->transEsc("RDS_FULLTEXT_AVAILABLE") ?></a> */
                        $html .= '<a href="' . $this->path . 'RDSProxyrecord/' . $this->uniqueId . '"{if $record_view != "flat"} data-view="'. $dataview .'" class="getFull" onclick="userAction(\'click\', \'RdsFulltextAvailable\', \'' . $this->uniqueId . '\'); return false;"{/if}>&rarr;'.$this->translate("RDS_FULLTEXT_AVAILABLE").'</a>';
                    }
                
                } else if ($fulltextLinks[0]['indicator'] == 2) {
                    /* <a href="<?=$path?>RDSProxyrecord/<?=$ppn?>"{if $record_view != "flat"} data-view="<?=$dataView ?>" class="getFull" onclick="userAction('click', 'RdsFulltextAvailable', '<?=$ppn?>'); return false;"{/if}>&rarr;<?=$this->transEsc("RDS_FULLTEXT_AVAILABLE") ?></a>*/
                    $html .= '<a href="' . $this->path . 'RDSProxyrecord/' . $this->uniqueId . '"{if $record_view != "flat"} data-view="'. $dataview .'" class="getFull" onclick="userAction(\'click\', \'RdsFulltextAvailable\', \'' . $this->uniqueId . '\'); return false;"{/if}>&rarr;'.$this->translate("RDS_FULLTEXT_AVAILABLE") .'</a>';
                } else {
                    /* <a target="_blank" href='<?=$fulltextLinks[0]['url']?>' onclick="userAction('click', 'RdsFulltextLink', '<?=$ppn?>');">&rarr;<?=$this->transEsc("RDS_FULLTEXT_LINK") ?></a>*/
                    $html .= '<a target="_blank" href=\''. $fulltextLinks[0]['url'] .'\' onclick="userAction(\'click\', \'RdsFulltextLink\', \'' . $this->uniqueId . '\');">&rarr;'. $this->translate("RDS_FULLTEXT_LINK") .'</a>';
                }
            } else {
                /*<a href="<?=$path?>RDSProxyRecord/<?=$ppn?>"{if $record_view != "flat"} data-view="<?=$dataView ?>" class="getFull" onclick="userAction('click', 'RdsCheckAvailability', '<?=$ppn?>'); return false;"{/if}>&rarr;<?=$this->transEsc("RDS_CHECK_AVIALABILITY") ?></a>*/
                $html .= '<a href="' . $this->path . 'RDSProxyRecord/' . $this->uniqueId . '"{if $record_view != "flat"} data-view="'. $dataview .'" class="getFull" onclick="userAction(\'click\', \'RdsCheckAvailability\', \'' . $this->uniqueId . '\'); return false;"{/if}>&rarr;'.$this->translate("RDS_CHECK_AVIALABILITY").'</a>';
            
            
            }
        }
        return $html;
    }
    

}
