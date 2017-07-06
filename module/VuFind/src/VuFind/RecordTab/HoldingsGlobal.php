<?php
/**
 * Holdings Global (based on german gvi index) tab
 *
 * PHP version 5
 *
 * Copyright (C) Villanova University 2016.
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
 * @category VuFind
 * @package  RecordTabs
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @author   Jochen Lienhard <lienhard@ub.uni-freiburg.de>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://vufind.org/wiki/development:plugins:record_tabs Wiki
 */
namespace VuFind\RecordTab;
use VuFind\Connection\IsilCache;

/**
 * Holdings Global (based on german gvi index) tab
 *
 * @category VuFind
 * @package  RecordTabs
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @author   Jochen Lienhard <lienhard@ub.uni-freiburg.de>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://vufind.org/wiki/development:plugins:record_tabs Wiki
 */
class HoldingsGlobal extends AbstractBase
{
    /**
     * Similar records
     *
     * @var array
     */
    protected $results;

    /**
     * Search service
     *
     * @var \VuFindSearch\Service
     */
    protected $searchService;

    /**
     * Isil cache
     *
     * @var \VuFind\Connection\IsilCache
     */
    protected $isilCache;

    /**
     * Constructor
     *
     * @param \VuFindSearch\Service $search Search service
     */
    public function __construct(\VuFindSearch\Service $search, \Zend\Http\Client $client, \VuFind\Cache\Manager $cache) 
    {
        $this->searchService = $search;
        $this->isilCache = new IsilCache($client,$cache);
    }

    /**
     * Get the on-screen description for this tab.
     *
     * @return string
     */
    public function getDescription()
    {
        return 'Holdings Global';
    }

    public function getIsilNames($isils)
    {
        return $this->isilCache->getIsilNames($isils);
    }

    /**
     * Get an array of Record Driver objects representing items similar to the one
     * passed to the constructor.
     *
     * @return array
     */
    public function getResults($query)
    {
        $record = $this->getRecordDriver();
        $result = $this->searchService->search($record->getSourceIdentifier(), $query, 0);

        return $result;

/*
        $record = $this->getRecordDriver();
        $params = new \VuFindSearch\ParamBag();
        $result = $this->searchService->retrieve(
            $record->getSourceIdentifier(), $record->getUniqueId(), $params
        );
//var_dump($result);

        $return = [];
        if ($result->getTotal() > 0) {

            foreach ($result->getRecords() as $record) {
                $libraries = [];
                $record instanceof RecordDriver\Interlending;
                $ppn = $record->getUniqueId();
                $f924 = $record->getField924(true, true);

                // iterate through all found 924 entries
                // ISILs are unified here - information is being dropped! 
                foreach ($f924 as $isil => $field) {
                    $libraries[] = [
                        'isil' => $isil,
                        'callnumber' => isset($field['g']) ? $field['g'] : '',
                        'issue' => isset($field['z']) ? $field['z'] : ''
                    ];
                }

                //Catch Errors in parsing
                if ($libraries === null) {
                    continue;
                }
                $return['holdings'][$ppn] = $libraries;
            }
            $return['numppn'] = count($return['holdings']);
            $return['numfound'] = count($libraries);

        }
        else {
            $return['numfound'] = 0;
        }
        return $return;
*/
//        return $result;
    }

    /**
     * Get an array of Record Driver objects representing items similar to the one
     * passed to the constructor.
     *
     * @return array
     */
    public function getResultsByISBN($isbn)
    {
        $record = $this->getRecordDriver();
        $params = new \VuFindSearch\ParamBag();
        $query = new \VuFindSearch\Query\Query($isbn,'isn');
        $result = $this->searchService->search($record->getSourceIdentifier(), $query, 0);

        return $result;

        $return = [];
        if ($result->getTotal() > 0) {

            foreach ($result->getRecords() as $record) {
var_dump(get_class($record));
                $libraries = [];
                $record instanceof RecordDriver\Interlending;
                $ppn = $record->getUniqueId();
                $f924 = $record->getField924(true, true);

                // iterate through all found 924 entries
                // ISILs are unified here - information is being dropped! 
                foreach ($f924 as $isil => $field) {
                    $isils[] = [$ppn => $isil];
                }

                //Catch Errors in parsing
                if ($isils === null) {
                    continue;
                }
                $return['isil'] = $isils;
            }

        }
        else {
            $return['numfound'] = 0;
        }
var_dump($return);
        return $return;

//        return $result;
    }


}

