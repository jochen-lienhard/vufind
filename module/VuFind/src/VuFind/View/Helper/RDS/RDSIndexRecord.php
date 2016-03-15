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
namespace VuFind\View\Helper\RDS;
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
class RDSIndexRecord extends \VuFind\View\Helper\Root\Record
{
   
    /**
     * Get HTML to render a title.
     *
     * @param int $maxLength Maximum length of non-highlighted title.
     *
     * @return string
     */
    public function getTitleHtml($maxLength = 180)
    {
        $transEsc = $this->getView()->plugin('transEsc');
        $highlightedTitle = $this->driver->tryMethod('getHighlightedTitle');
        $ast = $this->driver->tryMethod('getAst');
        $title = trim($this->driver->tryMethod('getTitleShort'));
        // TODO Bei der RN Suche wird das angezeigt TODO
        $titleSerie = $this->driver->tryMethod('getTitleSerie');
        $volume = $this->driver->tryMethod('getVolumeDisplay');
        $editions = $this->driver->tryMethod('getEditions');
        if (!empty($titleSerie)) {
            if (!empty($volume)) {
                $title = $transEsc($titleSerie) . " " . $volume . " " . $title;
            }
        }
        // TODO Ende
        if (!empty($ast)) {
            $title .= " [ " . $ast . " ] " ;
        }
        if (!empty($editions)) {
            $title .= " - " . $editions;
        }

        if (!empty($highlightedTitle)) {
            $highlight = $this->getView()->plugin('highlight');
            $addEllipsis = $this->getView()->plugin('addEllipsis');
            return $highlight($addEllipsis($highlightedTitle, $title));
        }
        if (!empty($title)) {
            $escapeHtml = $this->getView()->plugin('escapeHtml');
            $truncate = $this->getView()->plugin('truncate');
            return $escapeHtml($truncate($title, $maxLength));
        }
        return $transEsc('Title not available');
    }

    /**
     * Render a list of record formats. RDS-Helper
     *
     * @return string
     */
    public function getMedienicon()
    {
        if ($this->driver->getResourceSource()=="RDSIndex") {
            return $this->renderTemplate('medienicon.phtml');
        } else {
            return false;
        }
    }

}
