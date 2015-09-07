<?php
/**
 * ILS Driver for VuFind to query availability information via DAIA.
 *
 * Based on the proof-of-concept-driver by Till Kinstler, GBV.
 * Relaunch of the daia driver developed by Oliver Goldschmidt.
 *
 * PHP version 5
 *  
 * Copyright (C) Jochen Lienhard 2014.
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
 * @package  ILS_Drivers
 * @author   Jochen Lienhard <lienhard@ub.uni-freiburg.de>
 * @author   Oliver Goldschmidt <o.goldschmidt@tu-harburg.de>
 * @author   André Lahmann <lahmann@ub.uni-leipzig.de>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/vufind2:building_an_ils_driver Wiki
 */
namespace VuFind\ILS\Driver;
use DOMDocument, VuFind\Exception\ILS as ILSException,
    VuFindHttp\HttpServiceAwareInterface as HttpServiceAwareInterface,
    Zend\Log\LoggerAwareInterface as LoggerAwareInterface;

/**
 * ILS Driver for VuFind to query availability information via DAIA.
 *
 * @category VuFind2
 * @package  ILS_Drivers
 * @author   Jochen Lienhard <lienhard@ub.uni-freiburg.de>
 * @author   Oliver Goldschmidt <o.goldschmidt@tu-harburg.de>
 * @author   André Lahmann <lahmann@ub.uni-leipzig.de>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/vufind2:building_an_ils_driver Wiki
 */ 
class RDSDAIA extends DAIA
{
    /**
     * DAIA summaryKey 
     *
     * @var string
     */
    protected $summaryKey = null;

    /**
     * DAIA summaryValue
     *
     * @var string
     */
    protected $summaryValue = null;

    /**
     * Initialize the driver.
     *
     * Validate configuration and perform all resource-intensive tasks needed to
     * make the driver active.
     *
     * @throws ILSException
     * @return void
     */
    public function init()
    {
        parent::init();
        if (isset($this->config['DAIA']['summaryKey']) && isset($this->config['DAIA']['summaryValue'])) {
            $this->summaryKey = $this->config['DAIA']['summaryKey'];
            $this->summaryValue = $this->config['DAIA']['summaryValue'];
        } 
    }

    /**
     * Parse an array with DAIA status information.
     *
     * @param string $id        Record id for the DAIA array.
     * @param array  $daiaArray Array with raw DAIA status information.
     *
     * @return array            Array with VuFind compatible status information.
     */
    protected function parseDaiaArray($id, $daiaArray)
    {
        $doc_id = null;
        $doc_href = null;
        if (array_key_exists('id', $daiaArray)) {
            $doc_id = $daiaArray['id'];
        }
        if (array_key_exists('href', $daiaArray)) {
            // url of the document (not needed for VuFind)
            $doc_href = $daiaArray['href'];
        }
        if (array_key_exists('message', $daiaArray)) {
            // log messages for debugging
            $this->logMessages($daiaArray['message'], 'document');
        }
        // if one or more items exist, iterate and build result-item
        if (array_key_exists('item', $daiaArray)) {
            $number = 0;
            foreach ($daiaArray['item'] as $item) {
                $result_item = [];
                $result_item['id'] = $id;
                $result_item['item_id'] = $item['id'];
                // custom DAIA field used in getHoldLink()
                $result_item['ilslink'] = $doc_href;
                // count items
                $number++;
                $result_item['number'] = $this->getItemNumber($item, $number);
                // set default value for barcode
                $result_item['barcode'] = $this->getItemBarcode($item);
                // set default value for reserve
                $result_item['reserve'] = $this->getItemReserveStatus($item);
                // get callnumber
                $result_item['callnumber'] = $this->getItemCallnumber($item);
                // get location
                $result_item['location'] = $this->getItemLocation($item);
                // get location link
                $result_item['locationhref'] = $this->getItemLocationLink($item);
                // check if summary is neccessary
                $check_key=false;
                if (isset($item[$this->summaryKey])) {
                    if (is_array($item[$this->summaryKey])) {
                        if (preg_match("/^$this->summaryValue/", $item[$this->summaryKey]["content"])) {
                            $check_key=true;
                        }
                    } else {
                        if (preg_match("/^$this->summaryValue/", $item[$this->summaryKey])) {
                            $check_key=true;
                        }
                    }
                }
                // status and availability will be calculated in own function
                if ($check_key) {
                    $result_item['summary'] = ["summary" => $number]; 
                } 
                // get callnumber
                if (isset($item["label"])) {
                    $result_item["callnumber"] = $item["label"];
                } else {
                    $result_item["callnumber"] = "Unknown";
                }
                // get location
                if (isset($item["storage"])) {
                    $result_item["location"] = $item["storage"]["content"];
                } else {
                    $result_item["location"] = "Unknown"; 
                }
                // status and availability will be calculated in own function
                $result_item = $this->getItemStatus($item) + $result_item;
                // add result_item to the result array
                $result[] = $result_item;
            } // end iteration on item
        }

        return $result;
    }


    /**
     * Calaculate Status and Availability of an item
     *
     * If availability is false the string of status will be shown in vufind
     *
     * @param string $item json DAIA item
     *
     * @return array("status"=>"only for VIPs" ... )
     */
    protected function getItemStatus($item)
    {
        $limitation=null;
        $message=null;
        $availability = false;
        $status = null;
        $duedate = null;

        if (isset($item["limitation"])) {
            if (isset($item["limitation"]["content"])) {
                $limitation = $item["limitation"]["content"];
            }
        }

        if (isset($item["message"])) {
            if (isset($item["message"][0]["content"])) {
                $message = $item["message"][0]["content"];
            }
        }

        if (array_key_exists("available", $item)) {
            $loan = 0;
            $presentation = 0;

            foreach ($item["available"] as $available) {
                if ($available["service"] == "loan") {
                    $loan=0;
                }
                if ($available["service"] == "presentation") {
                    $presentation=0;
                }
            }
        }

        if (array_key_exists("unavailable", $item)) {
            foreach ($item["unavailable"] as $unavailable) {
                if ($unavailable["service"] == "loan") {
                    $loan = 1;
                }
                if ($unavailable["service"] == "presentation") {
                    if (isset($unavailable["expected"])) {
                        $loan=2;
                        $duedate=$unavailable["expected"];
                    }
                    $presentation=1;
                }
            }
        }

        if ($loan == 0 && $presentation == 0) {
            if ($limitation == 'restricted') {
                $status="order";
            } else {
                $status="borrowable";
            }
        } else {
            if ($loan == 2) {
                $status="lent";
            } else {
                if ($loan == 1 && $presentation == 0) {
                    $status="present";
                } else {
                    $status="unknown";
                }
            }
        }
 
        if (array_key_exists("available", $item)) {
            // check if item is loanable or presentation
            foreach ($item["available"] as $available) {
                if ($available["service"] == "loan") {
                    $availability = true;
                }
                if ($available["service"] == "presentation") {
                    $availability = true;
                }
            }
        }
 
        return (["status" => $status,
            "availability" => $availability,
            "notes" => $message,
            "duedate" => $duedate]);
    }

}
