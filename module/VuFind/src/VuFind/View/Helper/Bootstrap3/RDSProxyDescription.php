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
class RDSProxyDescription extends RDSHelper
{
    protected $items = [
        'SubjectsGeneral',
        'Abstracts',
        'Review',
        'Reviewers'
    ];
    
    public function getSubjectsGeneral() 
    {
        foreach ($this->driver->getSubjectsGeneral() as $subjectGeneral) {
            $html .= $subjectGeneral . '<br />';
        }
        
        return $html;
    }
    
    public function getAbstracts() 
    {
        foreach ($this->driver->getAbstracts() as $abstract) {
            $html .= $abstract . '<br />';
        }
        
        return $html;
    }
    
    public function getReview() 
    {
        return $this->driver->getReview();
    }
    
    public function getReviewers() 
    {
        return $this->driver->getReviewers();
    }
    
}
