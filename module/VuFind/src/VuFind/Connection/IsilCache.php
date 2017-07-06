<?php
/**
 * Isil Cache Webservice 
 *
 * PHP version 5
 *
 * Copyright (C) Villanova University 2017.
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
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * @category VuFind
 * @package  IsilCache
 * @author   Jochen Lienhard <jochen.lienhard@ub.uni-freiburg.de> 
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://vufind.org/wiki/development Wiki
 */
namespace VuFind\Connection;

/**
 * Isil Cache 
 *
 * Class for accessing and caching ISIL-corresponding data.
 *
 * @category VuFind
 * @package  IsilCache
 * @author   Jochen Lienhard <jochen.lienhard@ub.uni-freiburg.de>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://vufind.org/wiki/development Wiki
 */
class IsilCache 
{
    /**
     * HTTP client
     *
     * @var \Zend\Http\Client
     */
    protected $client;

    /**
     * Cache manager
     *
     * @var \VuFind\Cache\Manager
     */
    protected $cacheManager;

    /**
     * Cache for lobid-json 
     */
    protected $cache;

    /**
     * Constructor
     *
     * @param \Zend\Http\Client $client HTTP client
     * @param \VuFind\Cache\Manager  $cacheManager  Cache manager 
     */
    public function __construct(\Zend\Http\Client $client, \VuFind\Cache\Manager $cacheManager) 
    {
        $this->cacheManager = $cacheManager;
        $this->client = $client;
        $this->cache = $this->cacheManager->getCache('object');
    }

    /**
     * Get alternate names for the isils.
     *
     * @param array $isils isils of the librarys
     *
     * @return array of altName with isil as key
     */
    public function getIsilNames($isils) {
        $isil_list = [];
        foreach ($isils as $isil) {
           $isil_list[$isil] = $this->getLobid($isil)->alternateName[0];
        } 
        return $isil_list;
    }

    /**
     * Checks if data for isil is in cache and returns the data 
     * (either cached or received).
     *
     * @param string $isil isil of the library
     *
     * @return string decoded json object
     */
    public function getLobid($isil) {
        if (!$this->cache->hasItem('isil'.$isil)) {
            $url = "http://lobid.org/organisations/" . $isil . "?format=json";
            $result = $this->retrieve($url);
            $this->cache->setItem('isil'.$isil,$result);
            $obj = json_decode($result);
        } else {
            $obj = json_decode($this->cache->getItem('isil'.$isil));
        }
        return $obj;
    }

    /**
     * Retrieve data over HTTP.
     *
     * @param string $url URL to access.
     *
     * @return string
     */
    protected function retrieve($url)
    {
        $response = $this->client->setUri($url)->setMethod('GET')->send();
        if ($response->isSuccess()) {
            return $response->getBody();
        } else {
            return null;
        }
    }

}
