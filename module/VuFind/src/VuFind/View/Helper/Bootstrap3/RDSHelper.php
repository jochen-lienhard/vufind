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
class RDSHelper extends AbstractHelper
{
    protected $driver = null; 
    protected $translator = null;
    protected $sourceIdentifier = '';
    protected $authManager = null;
    protected $linkresolver = null;
    
    protected $items = [];

    public function __invoke($driver)
    {
        $this->driver = $driver;
        $this->translator = $this->view->plugin('translate')->getTranslator();
        $this->authManager = $this->view->plugin('auth');
        return $this;
    }
    
    public function getItems() {
        $results = [];
        foreach ($this->items as $item) {
            $function = [$this, 'get' . $item];
            $itemValue = call_user_func($function);
            if ($itemValue) {
                $wordingKey = preg_replace('/([A-Z])/', '_$1', $item);
                $wordingKey = preg_replace('/^_/', '', $wordingKey);
                $results['RDS_' . strtoupper($wordingKey)] = $itemValue;
            }
        }
        return $results;
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
    
        $html .= '';
        
        return $html;
    }
    
    
    
    public function getPrintAction() {
        $vufindId = $this->driver->getResourceSource() .'|'. $this->driver->getUniqueID();
        $html = '<a href="/Cart/doExport" class="doExportRecord" data-id="'. $vufindId .'">&rarr; ' . $this->translate("RDS_PRINT") . '</a>';
        
        return $html;
    }
    
    protected function render($template) {
        return $this->view->render($template);
    }
    
    protected function translate($str) {
        $translation = ($this->translator) ? $this->translator->translate($str) : $str;
        return $translation;
    }
    
    protected function getLocale() {
        $locale = ($this->translator) ? $this->translator->getLocale() : 'de';
        return $locale;
    }
}
