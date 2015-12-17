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
