<?php

namespace DLzer\MFP;

use GuzzleHttp\Client;
use StdClass;
use DOMDocument;

/** 
 *  MfpService
 * 
 *  Manages requests to MyFitnessPal
 * 
 *  - mfpRequest
 *  - fetch
 *  - parseResponse
 * 
 */
class MfpService {

    /**
     * @var username
     */
    private $username;
    /**
     * @var date
     */
    private $date;
    /**
     * @var baseurl
     */
    private $baseurl;
    /**
     * @var url
     */
    private $url;
    /**
     * @var response
     */
    private $response;
    /**
     * @var parsedResponse
     */
    private $parsedResponse;
    /**
     * @var guzzle
     */
    private $guzzle;

    /**
     * @param username
     * @param date
     */
    public function __construct($username, $date)
    {

        $this->username = $username;
        $this->date = $date;
        $this->baseurl = "http://www.myfitnesspal.com/reports/printable_diary/";
        $this->guzzle = new Client();

    }

    /**
     * Process a request for a single date of MFP macro data
     */
    public function fetch()
    {

        $this->checkDateFormat();
        $this->mfpRequest();
        return $this->parseResponse();

    }

    /**
     * Craft a URL to send via Guzzle, and set the response to a class variable
     */
    private function mfpRequest()
    {

        $this->url      = $this->baseurl.(string)$this->username."?from=".(string)$this->date."&to=".(string)$this->date;
        $this->response = $this->guzzle->get( $this->url, ['allow_redirects' => true] );
        $this->response = (string)$this->response->getBody();

    }

    /**
     * Load the response variable into DOMDocument and navigate through the DOM elements
     * to craft the parsedResponse return. If the response contains a privacy strings, return
     * and error about the "Diary is Private".
     */
    private function parseResponse()
    {

        $macro_array = array();
        $this->parsedResponse = new StdClass;

        $doc = new DOMDocument;
        $doc->preserveWhiteSpace = false;
        $doc->loadHTML($this->response);

        // Check to see if username exists
        $mtitle = $doc->getElementById('settings')->childNodes;
        foreach( $mtitle as $node ) {
            if( $node->nodeValue == "This Username is Invalid");
                return ['error' => "This Username is Invalid"];
        }

        // Check if the diary is set to private
        $title = $doc->getElementById('settings')->childNodes;
        foreach( $title as $node ) {
            if ($node->nodeValue == "This Diary is Private") {
                return ['error' => "Diary is Private"];
            }
        }

        // Check if their are any entries for the date range
        $dateEl = $doc->getElementById('date');
        if($dateEl->textContent == "No diary entries were found for this date range.") {
            return ['error' => "No diary entries were found for this date."];
        }

        // Find the macro table row
        $tables = $doc->getElementsByTagName('tfoot');
        $rows = $tables->item(0)->getElementsByTagName('tr');
        foreach ( $rows as $row ) {
            $cols = $row->getElementsByTagName('td');
            foreach ( $cols as $col ) {
                array_push($macro_array, $col->textContent);
            }
        }

        $this->parsedResponse->username = $this->username;
        $this->parsedResponse->date     = $this->date;
        $this->parsedResponse->calories = (int)$macro_array[1];
        $this->parsedResponse->fat      = (int)substr($macro_array[2], 0, -1);
        $this->parsedResponse->carbs    = (int)substr($macro_array[3], 0, -1);
        $this->parsedResponse->protein  = (int)substr($macro_array[4], 0, -1);
        $this->parsedResponse->cholest  = (int)substr($macro_array[5], 0, -2);
        $this->parsedResponse->sodium   = (int)substr($macro_array[6], 0, -2);
        $this->parsedResponse->sugars   = (int)substr($macro_array[7], 0, -1);
        $this->parsedResponse->fiber    = (int)substr($macro_array[8], 0, -1);
        
        return ['success' => $this->parsedResponse];

    }

    /**
     * Check the formatting of a date with more a modern solutions then 'checkdate'. Validates input
     * and uses an array sum trick which is a terse way of ensuring PHP did not do "month shifting".
     * For more info reference: https://www.php.net/manual/en/datetime.getlasterrors.php
     */
    protected function checkDateFormat()
    {

        $this->date = DateTime::createFromFormat("Y-m-d", $date);
        return $this->date !== false && !array_sum($dt::getLastErrors());

    }

}