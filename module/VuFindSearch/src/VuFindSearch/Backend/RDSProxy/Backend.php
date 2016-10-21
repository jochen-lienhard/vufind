<?php
/**
 * SOLR backend.
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
 * @package  Search
 * @author   David Maus <maus@hab.de>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org
 */
namespace VuFindSearch\Backend\RDSProxy;

use VuFindSearch\Query\AbstractQuery;
use VuFindSearch\Query\Query;

use VuFindSearch\ParamBag;

use VuFindSearch\Response\RecordCollectionInterface;
use VuFindSearch\Response\RecordCollectionFactoryInterface;

use VuFindSearch\Backend\RDSProxy\Response\Json\Terms;

use VuFindSearch\Backend\AbstractBackend;
use VuFindSearch\Feature\SimilarInterface;
use VuFindSearch\Feature\RetrieveBatchInterface;

use VuFindSearch\Backend\Exception\BackendException;
use VuFindSearch\Backend\Exception\RemoteErrorException;

use VuFindSearch\Exception\InvalidArgumentException;

/**
 * SOLR backend.
 *
 * @category VuFind2
 * @package  Search
 * @author   David Maus <maus@hab.de>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org
 */
class Backend extends \VuFindSearch\Backend\Solr\Backend
    implements SimilarInterface, RetrieveBatchInterface
{
    /**
     * Retrieve a batch of documents.
     *
     * @param array    $ids    Array of document identifiers
     * @param ParamBag $params Search backend parameters
     *
     * @return RecordCollectionInterface
     */
    public function retrieveBatch($ids, ParamBag $params = null)
    {
        
        $result = null;
        foreach ($ids as $id) {
            $recordCollection = $this->retrieve($id, $params);
            
            if (isset($result)) {
                $result->add($recordCollection->first());
            } else {
                $result = $recordCollection;
            }
        }
        
        return $result;
        
        // Load 100 records at a time; this is a good number to avoid memory
        // problems while still covering a lot of ground.
        $pageSize = 100;

        // Callback function for formatting IDs:
        $formatIds = function ($i) {
            return '"' . addcslashes($i, '"') . '"';
        };

        // Retrieve records a page at a time:
        $results = false;
        while (count($ids) > 0) {
            $currentPage = array_splice($ids, 0, $pageSize, array());
            //$currentPage = array_map($formatIds, $currentPage);
            $params = new ParamBag(
                array(
                    'q' => 'id:(' . implode(' OR ', $currentPage) . ')',
                    'start' => 0,
                    'rows' => $pageSize
                )
            );
            $this->injectResponseWriter($params);
            $next = $this->createRecordCollection(
                $this->connector->search($params)
            );
            if (!$results) {
                $results = $next;
            } else {
                foreach ($next->getRecords() as $record) {
                    $results->add($record);
                }
            }
        }
        $this->injectSourceIdentifier($results);
        return $results;
    }
}
