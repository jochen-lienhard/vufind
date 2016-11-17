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
    protected $path = '';
    
    protected $escapeJs;
    protected $escapeHtmlAttr;
    
    protected $items = [];

    /**
     * Dummy 
     *
     * @param string $driver driver
     *
     * @return string
     */
    public function __invoke($driver)
    {
        $this->translator = $this->view->plugin('translate')->getTranslator();
        $this->authManager = $this->view->plugin('auth');
        $this->escapeJs = $this->view->plugin('escapeJs');
        $this->escapeHtmlAttr = $this->view->plugin('escapeHtmlAttr');
        $this->driver = $driver;
        $this->path = $this->view->plugin('url')->__invoke('home');
        return $this;
    }
    
    /**
     * Dummy 
     *
     * @return string
     */
    public function getItems() 
    {
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
   
    /**
     * Dummy 
     *
     * @return string
     */
    public function getFavAction() 
    {
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
        
        $list_id = $this->view->list->id;
        /* Use a different delete URL if we're removing from a specific list or the overall favorites: */
        $deleteUrl = null === $list_id
            ? $this->view->plugin('url')->__invoke('myresearch-favorites')
            : $this->view->plugin('url')->__invoke('userList', array('id' => $list_id));
        $deleteFavoriteConfirmation = null === $list_id
            ? 'RDS_FAV_LIST_REMOVE_FORM_ALL_LISTS'
            : 'RDS_FAV_LIST_REMOVE_FORM_CURRENT_LIST';
        $deleteUrlGet = $deleteUrl . '?delete=' . urlencode($this->driver->getUniqueID()) . '&amp;source=' . urlencode($this->driver->getResourceSource());
            $dLabel = 'delete-label-' . preg_replace('[\W]', '-', $id);

        $html .= '<span class="dropdown favActionDel hidden">';      
        $html .= '  <a class="dropdown-toggle" id="'. $dLabel . '" role="button" data-toggle="dropdown" data-target="#" href="' . $deleteUrlGet .'">&rarr; ' . $this->translate('RDS_REMOVE_FROM_MY_LIST') . '</a>';
        $html .= '  <ul class="dropdown-menu" role="menu" aria-labelledby="' . $dLabel . '">';
        $html .= '        <li><a onClick="$.post(\'' .$deleteUrl . '\', {\'delete\':\'' .  $this->escapeJs($this->driver->getUniqueID()) . '\',\'source\':\'' . $this->escapeHtmlAttr($this->driver->getResourceSource()) . '\',\'confirm\':true},function(){location.reload(true)})" title="' . $this->translate('confirm_delete_brief') . '">' . $this->translate($deleteFavoriteConfirmation) . '</a></li>';
        $html .= '        <li><a>' . $this->translate('RDS_FAV_LIST_CANCEL_DELETE') . '</a></li>';
        $html .= '  </ul>';
        $html .= '</span>';
        
        $html .= '';
        
        return $html;
    }
    
    /**
     * Dummy 
     *
     * @return string
     */
    public function getPrintAction() 
    {
        $vufindId = $this->driver->getResourceSource() .'|'. $this->driver->getUniqueID();
        $html = '<a href="/Cart/doExport" class="doExportRecord" data-id="'. $vufindId .'">&rarr; ' . $this->translate("RDS_PRINT") . '</a>';
        
        return $html;
    }

    /**
     * Dummy 
     *
     * @param string $template name of the template that should be rendered
     *
     * @return string
     */
    protected function render($template) 
    {
        return $this->view->render($template);
    }
   
    /**
     * Dummy 
     *
     * @param string $str string that must be translated
     *
     * @return string
     */
    protected function translate($str) 
    {
        $translation = ($this->translator) ? $this->translator->translate($str) : $str;
        return $translation;
    }
   
    /**
     * Dummy 
     *
     * @return string
     */
    protected function getLocale() 
    {
        $locale = ($this->translator) ? $this->translator->getLocale() : 'de';
        return $locale;
    }
   
    /**
     * Dummy 
     *
     * @param string $str string that must be escaped 
     *
     * @return string
     */
    protected function escapeJS($str) 
    {
        return $this->escapeJs->__invoke($str);
    }
   
    /**
     * Dummy  
     *
     * @param string $str string that must be escaped 
     *
     * @return string
     */ 
    protected function escapeHtmlAttr($str) 
    {
        return $this->escapeHtmlAttr->__invoke($str);
    }
    
    /**
     * Dummy.
     *
     * @return Mixed
     */
    public function getLoginLink()
    {
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
}
