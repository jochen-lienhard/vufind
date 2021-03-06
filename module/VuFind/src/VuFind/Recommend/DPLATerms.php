<?php
/**
 * DPLATerms Recommendations Module
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
 * @package  Recommendations
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/vufind2:recommendation_modules Wiki
 */
namespace VuFind\Recommend;
use Zend\Http\Client as HttpClient,
    Zend\Http\Client\Adapter\Exception\TimeoutException;

/**
 * DPLATerms Recommendations Module
 *
 * This class uses current search terms to query the DPLA API.
 *
 * @category VuFind2
 * @package  Recommendations
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/vufind2:recommendation_modules Wiki
 */
class DPLATerms implements RecommendInterface
{
    /**
     * Config
     *
     * @var string
     */
    protected $apiKey;

    /**
     * Vufind HTTP Client
     *
     * @var HttpClient
     */
    protected $client;

    /**
     * Setting of initial collapsedness
     *
     * @var bool
     */
    protected $collapsed;

    /**
     * Search results object
     *
     * @var \VuFind\Search\Base\Results
     */
    protected $searchObject;

    /**
     * Map of Solr field names to equivalent API parameters
     *
     * @var array
     */
    protected $formatMap = array(
        'authorStr'           => 'sourceResource.creator',
        'building'            => 'provider.name',
        'format'              => 'sourceResource.format',
        'geographic_facet'    => 'sourceResource.spatial.region',
        'institution'         => 'provider.name',
        'language'            => 'sourceResource.language.name',
        'publishDate'         => 'sourceResource.date.begin',
    );

    /**
     * List of fields to retrieve from the API
     *
     * @var array
     */
    protected $returnFields = array(
        'id',
        'dataProvider',
        'sourceResource.title',
        'sourceResource.description',
    );

    /**
     * Constructor
     *
     * @param string     $apiKey API key
     * @param HttpClient $client VuFind HTTP client
     */
    public function __construct($apiKey, HttpClient $client)
    {
        $this->apiKey = $apiKey;
        $this->client = $client;
    }

    /**
     * setConfig
     *
     * Store the configuration of the recommendation module.
     *
     * @param string $settings Settings from searches.ini.
     *
     * @return void
     */
    public function setConfig($settings)
    {
        $this->collapsed = filter_var($settings, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Abstract-required method
     *
     * @param \VuFind\Search\Base\Params $params  Search parameter object
     * @param \Zend\StdLib\Parameters    $request Parameter object representing user
     * request.
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function init($params, $request)
    {
        // No action needed.
    }

    /**
     * process
     *
     * Called after the Search Results object has performed its main search.  This
     * may be used to extract necessary information from the Search Results object
     * or to perform completely unrelated processing.
     *
     * @param \VuFind\Search\Base\Results $results Search results object
     *
     * @return void
     */
    public function process($results)
    {
        $this->searchObject = $results;
    }

    /**
     * Get terms related to the query.
     *
     * @return array
     */
    public function getResults()
    {
        $this->client->setUri('http://api.dp.la/v2/items');
        $this->client->setMethod('GET');
        $this->client->setParameterGet($this->getApiInput());
        try {
            $response = $this->client->send();
        } catch (TimeoutException $e) {
            error_log('DPLA API timeout -- skipping recommendations.');
            return array();
        }
        if (!$response->isSuccess()) {
            return array();
        }
        return $this->processResults($response->getBody());
    }

    /**
     * Get input parameters for API call.
     *
     * @return array
     */
    protected function getApiInput()
    {
        // Extract the first search term from the search object:
        $search = $this->searchObject->getParams()->getQuery();
        $filters = $this->searchObject->getParams()->getFilters();
        $lookfor = ($search instanceof \VuFindSearch\Query\Query)
            ? $search->getString()
            : '';

        $params = array(
            'q' => $lookfor,
            'fields' => implode(',', $this->returnFields),
            'api_key' => $this->apiKey
        );
        foreach ($filters as $field=>$filter) {
            if (isset($this->formatMap[$field])) {
                $params[$this->formatMap[$field]] = implode(',', $filter);
            }
        }
        return $params;
    }

    /**
     * Process the API response.
     *
     * @param string $response API response
     *
     * @return array
     */
    protected function processResults($response)
    {
        $body = json_decode($response);
        $results = array();
        if ($body->count > 0) {
            $title = 'sourceResource.title';
            $desc = 'sourceResource.description';
            foreach ($body->docs as $i => $doc) {
                $results[$i] = array(
                    'title' => is_array($doc->$title)
                        ? current($doc->$title)
                        : $doc->$title,
                    'provider' => is_array($doc->dataProvider)
                        ? current($doc->dataProvider)
                        : $doc->dataProvider,
                    'link' => 'http://dp.la/item/'.$doc->id
                );
                if (isset($doc->$desc)) {
                    $results[$i]['desc'] = is_array($doc->$desc)
                        ? current($doc->$desc)
                        : $doc->$desc;
                }
            }
        }
        return $results;
    }

    /**
     * Return the list of facets configured to be collapsed
     *
     * @return array
     */
    public function isCollapsed()
    {
        return $this->collapsed;
    }
}