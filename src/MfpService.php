<?php

namespace DLzer\MFP;

use StdClass;
use DOMDocument;
use DateTime;
use \GuzzleHttp\Client;

/** 
 *  MfpService
 * 
 *  Manages requests to MyFitnessPal
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
     * @var response
     */
    private $response;
    /**
     * @var
     */
    private $request;
    /**
     * @var parsedResponse
     */
    private $parsedResponse;
    /**
     * @var GuzzleHttpClient
     */
    private $httpClient;

    /**
     * @param username
     * @param date
     */
    public function __construct($username, $date) {

        $this->username = $username;
        $this->date = $date;
        $this->baseurl = "http://www.myfitnesspal.com/reports/printable_diary/";
        $this->httpClient = new Client();

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

        $this->request = $this->baseurl.(string)$this->username."?from=".(string)$this->date."&to=".(string)$this->date;

        try {
            $this->response = $this->httpClient->get($this->request, ['allow_redirects' => true]);
        } catch (Exception $e) {
            print('Threw exception: '.$e);
        }

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
        libxml_use_internal_errors(true); // Hush errors about invalid HTML. Invalid HTML is quite common, this just silences the warnings.
        $doc->loadHTML((string)$this->response->getBody());

        // Check to see if username exists
        $mtitle = $doc->getElementById('settings')->childNodes;
        if ($mtitle != null) {
            foreach( $mtitle as $node ) {
                if( $node->nodeValue == "This Username is Invalid") {
                    return ['error' => "This Username is Invalid"];
                }
            }
        }
    

        // Check if the diary is set to private
        $title = $doc->getElementById('settings')->childNodes;
        if ( $title != null ) {
            foreach( $title as $node ) {
                if ($node->nodeValue == "This Diary is Private") {
                    return ['error' => "Diary is Private"];
                }
            }
        }
        

        // Check if their are any entries for the date range
        $dateEl = $doc->getElementById('date');
        if( $dateEl != null ) {
            if($dateEl->textContent == "No diary entries were found for this date range.") {
                return ['error' => "No diary entries were found for this date."];
            }
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
        $this->parsedResponse->calories = (int)$this->parseLargeInt($macro_array[1]);
        $this->parsedResponse->carbs    = (int)$this->parseLargeInt(substr($macro_array[2], 0, -1));
        $this->parsedResponse->fat      = (int)$this->parseLargeInt(substr($macro_array[3], 0, -1));
        $this->parsedResponse->protein  = (int)$this->parseLargeInt(substr($macro_array[4], 0, -1));
        $this->parsedResponse->cholest  = (int)$this->parseLargeInt(substr($macro_array[5], 0, -2));
        $this->parsedResponse->sodium   = (int)$this->parseLargeInt(substr($macro_array[6], 0, -2));
        $this->parsedResponse->sugars   = (int)$this->parseLargeInt(substr($macro_array[7], 0, -1));
        $this->parsedResponse->fiber    = (int)$this->parseLargeInt(substr($macro_array[8], 0, -1));
        
        return ['success' => $this->parsedResponse];

    }

    /**
     * Using REGEX to confirm the date is in a correct format
     */
    protected function checkDateFormat()
    {

        if (preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $this->date)) {
            return true;
        } else {
            return ['error' => 'Incorrect date format'];
        }

    }

    /**
     * Convert large integers with a comma to INT
     * 
     * @param int
     */
    protected function parseLargeInt($int)
    {

        if(preg_match("/^[0-9,]+$/", $int)) {
            $int = str_replace(',', '', $int);
        }
        
        return $int;

    }

}