<?php
/*******************************************************************************
 * EMOS PHP Bib 2
 * $Id: emos.php,v 1.15 2009/11/17 13:24:00 egaiser Exp $
 ********************************************************************************

 Copyright (c) 2004 - 2015 ECONDA GmbH Karlsruhe
 All rights reserved.

 ECONDA GmbH
 Eisenlohrstr. 43
 76135 Karlsruhe
 Tel.: 0721/663035-0
 Fax.: 0721 663035-10
 info@econda.de
 www.econda.de

 Redistribution and use in source and binary forms, with or without modification,
 are permitted provided that the following conditions are met:

 * Redistributions of source code must retain the above copyright notice,
 this list of conditions and the following disclaimer.
 * Redistributions in binary form must reproduce the above copyright notice,
 this list of conditions and the following disclaimer in the documentation
 and/or other materials provided with the distribution.
 * Neither the name of the ECONDA GmbH nor the names of its contributors may
 be used to endorse or promote products derived from this software without
 specific prior written permission.

 THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
 ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED.
 IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT,
 INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF
 LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.

 Changes:

 $Log: emos.php,v $
 Revision 1.15  2009/11/17 13:24:00 egaiser
 update to handle anchor tags and properties array
 added public function trackMode to switch between anchor tags and properties array
 added public function debugMode to show debug information
 added public function rmvCdata to remove CDATA tag for properties array
 added public function addMarker
 added public function addTarget
 added public function addGoal to set target conversion
 added public function trackOnLoad to set automatic request on site load
 added public function addScript for additional external Javascript integration
 several changes in structural output and string encoding

 Revision 1.14  2009/02/19 09:52:56  unaegele
 if public function not exists fix (Removed in Revision 1.15)

 Revision 1.13  2007/08/17 08:40:33  unaegele
 added public function addEMOSCustomPageArray
 added public function getEMOSCustomPageArray

 Revision 1.12  2007/05/16 08:24:09  unaegele
 fix wrong reference to htmlspecialchars_decodephp4()

 Revision 1.11  2007/05/11 07:52:42  unaegele
 Update ECONDA Tel Number, prepare Release 20070510

 Revision 1.10  2007/05/11 07:45:53  unaegele
 added \n to addSid

 Revision 1.9  2007/05/10 12:19:04  unaegele
 Fix php 4 compatibility for the call to htmlspecialchars_decode()
 Replace traslated &nbsp;=chr(0xa0) with real spaces

 Revision 1.8  2007/05/04 10:17:31  unaegele
 several bugfixes

 Revision 1.7  2007/05/04 09:59:01  unaegele
 source code formating

 Revision 1.6  2007/05/04 09:55:12  unaegele
 *** empty log message ***

 Revision 1.5  2007/05/04 09:49:08  unaegele
 *** empty log message ***

 Revision 1.4  2007/05/04 09:43:48  unaegele
 Added methods addSiteID($siteid), addLangID($langid), addPageID($pageID), addCountryID($countryid)

 Revision 1.2 added URL Encoding, Dataformat

 Revision 1.1 added 1st party session tracking

 */

/* PHP Helper Class to construct a ECONDA Monitor statement for the later
 * inclusion in a HTML/PHP Page.
 */
class Mage_Econda_Block_Emos
{

    /* Here we store the predefined parameter list */
    private $preScript = "";

    /* Here we store the additional script-files */
    private $inScript = "";

    /* Here we store additional parameters */
    private $postScript = "";

    /* path to the emos2.js script-file */
    private $pathToFile = "";

    /* Name of the script-file */
    private $scriptFileName = "emos2.js";

    /* session id for 1st party sessions*/
    private $emsid = "";

    /* visitor id for 1st partyx visitors */
    private $emvid = "";

    /* start js and init properties */
    private $jsStart = "<script type=\"text/javascript\">\n//<![CDATA[\n    var emospro = {};\n";

    /* end js and fire properties */
    private $jsEnd = "    if(typeof(window.emosPropertiesEvent) == 'function') {\n        window.emosPropertiesEvent(emospro);\n    }\n//]]>\n</script>\n";

    /* emos2 inclusion */
    private $emosBib = "";

    /* ec_event */
    private $ecString = "";

    /* remove cdata */
    private $rmvCdata = true;

    /* old style anchor tags*/
    private $anchorTags = true;

    /* count basket items */
    private $ecCounter = 0;

    /* send request on site load */
    private $emosFire = true;

    /* main out */
    private $retString = "";

    /* script to stopp request on site load */
    private $emosStopRequest = "<script type=\"text/javascript\">\n//<![CDATA[\n    window.emosTrackVersion = 2;\n//]]>\n</script>\n";

    /* Debug Mode */
    private $emosDebug = 0;

    /* CSS Style and Div for Debug */
    private $debugOut = "\n<script type=\"text/javascript\">\n   function hideEcondaDebug(){\n      document.getElementById(\"econdaDebugTxt\").style.visibility = \"hidden\";\n      document.getElementById(\"econdaDebugStat\").style.visibility = \"hidden\";\n      document.getElementById(\"econdaDebug\").style.width = \"35px\";\n      document.getElementById(\"econdaDebug\").style.height = \"15px\";\n      document.getElementById(\"econdaDebugShow\").style.visibility = \"visible\";\n   }\n   function showEcondaDebug(){\n      document.getElementById(\"econdaDebugTxt\").style.visibility = \"visible\";\n      document.getElementById(\"econdaDebugStat\").style.visibility = \"visible\";\n      document.getElementById(\"econdaDebug\").style.width = \"auto\";\n      document.getElementById(\"econdaDebug\").style.height = \"auto\";\n      document.getElementById(\"econdaDebugShow\").style.visibility = \"hidden\";\n   }\n   function econdaDebug(dbtxt){\n      document.getElementById(\"econdaDebugTxt\").innerHTML = dbtxt;\n   }\n</script>\n<div name=\"econdaDebug\" id=\"econdaDebug\" style=\"position:absolute; visibility: visible; font-family: sans-serif; font-size: 12px; color: #FFFFFF; background-color: #0088B2; left: 0px; top: 0px; width: auto; height: auto; padding: 3px; z-index: 1000;\">\n<textarea style=\"min-width: 760px; font-family: sans-serif; font-size: 13px; background-color: #FFFFFF;\" name=\"econdaDebugTxt\" id=\"econdaDebugTxt\" wrap=\"off\" cols=\"120\" rows=\"22\">\n";
    private $debugEnd = "</textarea>\n<div name=\"econdaDebugStat\" id=\"econdaDebugStat\" style=\"cursor: pointer; padding: 1px;\" align=\"right\" onClick=\"javascript:hideEcondaDebug();\">[econda debug mode]&nbsp;&nbsp;HIDE</div>\n<div name=\"econdaDebugShow\" id=\"econdaDebugShow\" style=\"position: absolute; visibility: hidden; top: 0px; left: 0px; cursor: pointer; z-index: 1001;\" onClick=\"javascript:showEcondaDebug();\">SHOW</div>\n</div>\n\n";

    public function __construct()
    {
    }
     
    /*
     * add compatibility public function for php < 5.1
     */
    public function htmlspecialchars_decode_php4($str) 
    {
        return strtr($str, array_flip(get_html_translation_table(HTML_SPECIALCHARS)));
    }

    /* Constructor
     * Sets the path to the emos2.js js-bib and prepares the later calls
     *
     * @param $pathToFile The path to the js-bib (/js)
     * @param $scriptFileName If we want to have annother Filename than
     * emos2.js you can set it here
     */
    public function emos($pathToFile , $scriptFileName = "emos2.js") 
    {
        $this->pathToFile = $pathToFile;
        if(substr($this->pathToFile,-8) == 'emos2.js') {
            $this->pathToFile = substr($this->pathToFile,0,strlen($this->pathToFile)-8);
        }
        if(substr($this->pathToFile,-1) != '/') {
            $this->pathToFile .= '/';
        }
        $this->scriptFileName = $scriptFileName;
        if($this->scriptFileName == '' || $this->scriptFileName == null) {
            $this->scriptFileName = 'emos2.js';
        }
        $this->prepareInScript();
    }

    /* formats data/values/params by eliminating named entities and xml-entities */
    public function emos_ItemFormat($item) 
    {
        $item->productID = $this->emos_DataFormat($item->productID);
        $item->productSku = $this->emos_DataFormat($item->productSku);
        $item->productName = $this->emos_DataFormat($item->productName);
        $item->productGroup = $this->emos_DataFormat($item->productGroup);
        $item->variant1 = $this->emos_DataFormat($item->variant1);
        $item->variant2 = $this->emos_DataFormat($item->variant2);
        $item->variant3 = $this->emos_DataFormat($item->variant3);
        return $item;
    }

    /* formats data/values/params by eliminating named entities and xml-entities */
    public function emos_DataFormat($str) 
    {
        if($this->anchorTags) {
            $str = urldecode($str);
            //2007-05-10 Fix incompatibility with php4
            if (function_exists('htmlspecialchars_decode')) {
                $str = htmlspecialchars_decode($str, ENT_QUOTES);
            } else {
                $str = $this->htmlspecialchars_decode_php4($str);
            }
            $str = html_entity_decode($str);
            $str = strip_tags($str);
            $str = trim($str);

            //2007-05-10 replace translated &nbsp; with spaces
            $nbsp = chr(0xa0);
            $str = str_replace($nbsp, " ", $str);
            $str = str_replace("\"", "", $str);
            $str = str_replace("'", "", $str);
            $str = str_replace("%", "", $str);
            $str = str_replace(",", "", $str);
            $str = str_replace(";", "", $str);
            /* remove unnecessary white spaces */
            while (true) {
                $str_temp = $str;
                $str = str_replace("  ", " ", $str);

                if ($str == $str_temp) {
                    break;
                }
            }
            $str = str_replace(" / ", "/", $str);
            $str = str_replace(" /", "/", $str);
            $str = str_replace("/ ", "/", $str);
            $str = substr($str, 0, 254);
            $str = rawurlencode($str);
        }
        else {
            $str = utf8_decode($str);
            $str = html_entity_decode($str);
            $str = strip_tags($str);
            $str = utf8_encode($str);
            $str = trim($str);
        }
        return $str;
    }

    /* set the 1st party session id */
    public function setSid($sid = "") 
    {
        if ($sid) {
            $this->emsid = $sid;
            $this->appendPreScript("<a name=\"emos_sid\" title=\"$sid\"></a>\n");
        }
    }

    /* set the 1st party visitor id */
    public function setVid($vid = "") 
    {
        if ($vid) {
            $this->emvid = $vid;
            $this->appendPreScript("<a name=\"emos_vid\" title=\"$vid\"></a>");
        }
    }

    /* nothing to do. */
    public function prettyPrint() 
    {
    }

    /* Concatenates the current command and the $inScript */
    public function appendInScript($stringToAppend) 
    {
        $this->inScript .= $stringToAppend;
    }

    /* Concatenates the current command and the $proScript */
    public function appendPreScript($stringToAppend) 
    {
        $this->preScript .= $stringToAppend;
    }

    /* Concatenates the current command and the $postScript */
    public function appendPostScript($stringToAppend) 
    {
        $this->postScript .= $stringToAppend;
    }

    /* returns the emos2.js inclusion */
    public function prepareInScript() 
    {
        $this->emosBib .= "<script type=\"text/javascript\" src=\"".$this->pathToFile.$this->scriptFileName."\"></script>\n";
    }

    /* returns a javascript extra inclusion at defined position */
    public function addScript($script) 
    {
        $this->emosBib .= "<script type=\"text/javascript\" src=\"".$script."\"></script>\n";
    }

    /* returns the whole statement */
    public function toString() 
    {
        if(!$this->anchorTags){
            if($this->ecString != ""){
                $this->ecString = substr($this->ecString,0,-2)."\n";
                $this->ecString .= "    ];\n";
            }
            else {
                $this->ecString = "";
            }
            if(!$this->emosFire) {
                $this->jsEnd = str_replace("    if(typeof(window.emosPropertiesEvent) == 'function') {\n        window.emosPropertiesEvent(emospro);\n    }\n","",$this->jsEnd);
            }
            if($this->rmvCdata) {
                $this->jsStart = str_replace("\n//<![CDATA[","",$this->jsStart);
                $this->jsEnd = str_replace("//]]>\n","",$this->jsEnd);
                $this->emosStopRequest = str_replace("\n//<![CDATA[","",$this->emosStopRequest);
                $this->emosStopRequest = str_replace("\n//]]>","",$this->emosStopRequest);
            }
        }
        if($this->anchorTags) { //anchor tags
            if($this->ecString != "") {
                $this->ecString .= "//]]>\n</script>\n";
            }
            if($this->rmvCdata) {
                $this->preScript = str_replace("\n//<![CDATA[","",$this->preScript);
                $this->preScript = str_replace("\n//]]>","",$this->preScript);
                $this->ecString = str_replace("\n//<![CDATA[","",$this->ecString);
                $this->ecString = str_replace("\n//]]>","",$this->ecString);
            }
            if($this->emosDebug > 0) {
                $this->retString .= $this->debugOut . $this->preScript . $this->postScript . $this->ecString . $this->emosBib . $this->inScript . $this->debugEnd;
            }
            if($this->emosDebug == 0 || $this->emosDebug == 2){
                $this->retString .= $this->preScript . $this->postScript . $this->ecString . $this->emosBib . $this->inScript;
            }
        }
        else {
            if($this->emosDebug > 0) {
                $this->retString .= $this->debugOut . $this->emosStopRequest . $this->emosBib . $this->jsStart . $this->preScript . $this->ecString . $this->postScript . $this->jsEnd . $this->inScript . $this->debugEnd;
            }
            if($this->emosDebug == 0 || $this->emosDebug == 2){
                $this->retString .= $this->emosStopRequest . $this->emosBib . $this->jsStart . $this->preScript . $this->ecString . $this->postScript . $this->jsEnd . $this->inScript;
            }
        }
        return $this->retString;
    }

    /* constructs anchor tags */
    public function getAnchorTag($title = "", $rel = "", $rev = "") 
    {
        $rel = $this->emos_DataFormat($rel);
        $rev = $this->emos_DataFormat($rev);
        $anchor = "<a name=\"emos_name\" title=\"".$title."\" rel=\"".$rel."\" rev=\"".$rev."\"></a>\n";
        return $anchor;
    }

    /* constructs a js property event */
    public function getProperty($title = "", $rel = "", $rev = "", $brck = false) 
    {
        if($this->anchorTags) {
            return $this->getAnchorTag($title, $rel, $rev);
        }
        $rel = $this->emos_DataFormat($rel);
        $rev = $this->emos_DataFormat($rev);
        $setRev = false;
        if(trim($rev) != "") {
            $setRev = true;
        }
        $out = "    emospro.".$title." = ";
        if($setRev) {
            $out .= "[[";
        }
        $out .= "'".$rel."'";
        if($setRev) {
            if($brck) {
                $out .= "]]";
            }
            else {
                $out .= ",'".$rev."']]";
            }
        }
        $out .= ";\n";
		
        return $out;
    }

    /* adds a property event for marker tracking
     *  emospro.marker = content
     */
    public function addMarker($content) 
    {
        $this->appendPreScript($this->getProperty("marker", $content, "", true));
    }

    /* adds a property event for target tracking
     *  emospro.Target = [[group,name]]
     */
    public function addTarget($group, $name) 
    {
        $this->appendPreScript($this->getProperty("Target", $group, $name));
    }

    /* adds a property event for target conversion
     *  emospro.cGoal = 0 or 1
     */
    public function addGoal($goal) 
    {
        $this->appendPreScript($this->getProperty("cGoal", $goal));
    }

    /* remove CDATA from script */
    public function addCdata() 
    {
        $this->rmvCdata = false;
    }

    /* set tracking mode
     * 2 = js properties, everything else = anchor tags
     */
    public function trackMode($mode) 
    {
        if($mode == 2) {
            $this->anchorTags = false;
        }
    }

    /* send request on site load
     * true or false
     */
    public function trackOnLoad($send) 
    {
        $this->emosFire = $send;
    }

    /* show debug informations inside a container
     *  1 = debug only, 2 = debug and send request
     */
    public function debugMode($send) 
    {
        $this->emosDebug = $send;
    }

    /* adds a property event for content tracking
     *   emospro.content = content
     */
    public function addContent($content) 
    {
        $this->appendPreScript($this->getProperty("content", $content));
    }

    /* adds a property event for orderprocess tracking
     *  emospro.orderProcess = processStep
     */
    public function addOrderProcess($processStep) 
    {
        $this->appendPreScript($this->getProperty("orderProcess", $processStep));
    }

    /* adds a property event for siteid tracking
     *  emospro.siteid = siteid
     */
    public function addSiteID($siteid) 
    {
        $this->appendPreScript($this->getProperty("siteid", $siteid));
    }

    /* adds a property event for language tracking
     *  emospro.langid = langid
     */
    public function addLangID($langid) 
    {
        $this->appendPreScript($this->getProperty("langid", $langid));
    }

    /* adds a property event for country tracking
     *  emospro.countryid = countryid
     */
    public function addCountryID($countryid) 
    {
        $this->appendPreScript($this->getProperty("countryid", $countryid));
    }

    /* adds a property event for pageid tracking
     *  emospro.pageid = pageID
     */
    public function addPageID($pageID) 
    {
        if(!$this->anchorTags) {
            $this->appendPreScript($this->getProperty("pageId", $pageID));
        }
        else {
            $this->appendPreScript("<script type=\"text/javascript\">\n//<![CDATA[\n    window.emosPageId = '$pageID';\n//]]>\n</script>\n");
        }
    }

    /* adds a property event for search tracking
     *  emospro.search = [[queryString,numberOfHits]]
     */
    public function addSearch($queryString, $numberOfHits) 
    {
        $this->appendPreScript($this->getProperty("search", $queryString, $numberOfHits));
    }

    /* adds a property event for registration tracking
     *  The userid gets a md5() to fullfilll german datenschutzgesetz
     *  emospro.register = [[userID,result]]       //(result: 0=true,1=false)
     */
    public function addRegister($userID, $result) 
    {
        $this->appendPreScript($this->getProperty("register", md5($userID), $result));
    }

    /* adds a property event for login tracking
     *  The userid gets a md5() to fullfilll german datenschutzgesetz
     *  emospro.login = [[userID,result]]       //(result: 0=true,1=false)
     */
    public function addLogin($userID, $result) 
    {
        $this->appendPreScript($this->getProperty("login", md5($userID), $result));
    }

    /* adds a property event for contact tracking
     *  emospro.scontact = contactType
     */
    public function addContact($contactType) 
    {
        $this->appendPreScript($this->getProperty("scontact", $contactType));
    }

    /* adds a property event for download tracking
     *  emospro.download = downloadLabel
     */
    public function addDownload($downloadLabel) 
    {
        $this->appendPreScript($this->getProperty("download", $downloadLabel));
    }

    /* constructs a emosECPageArray of given $event type
     *  @param $item a instance of class EMOS_Item
     *  @param $event Type of this event ("add","c_rmv","c_add")
     */
    public function getEmosECPageArray($item, $event) 
    {
        if(!$this->anchorTags){
            if($this->ecString == "") {
                $this->ecString .= "    emospro.ec_Event = [\n";
            }
            $this->ecString .= sprintf("%s, ", json_encode(
                $this->getProductEventTrackingData($item, $event)
            ));
        }
        else { //anchor tags
            $item = $this->emos_ItemFormat($item);
            if($this->ecCounter == 0) {
                $this->ecString .= "<script type=\"text/javascript\">\n//<![CDATA[\n";
                $this->ecString .= "    var emosECPageArray = new Array();\n";
            }
            $this->ecString .="    emosECPageArray[".$this->ecCounter."] = new Array();\n" .
            "    emosECPageArray[".$this->ecCounter."]['event'] = '".$event."';\n" .
            "    emosECPageArray[".$this->ecCounter."]['id'] = '".$item->productID."';\n" .
            // "    emosECPageArray[".$this->ecCounter."]['sku'] = '".$item->productSku."';\n" .
            "    emosECPageArray[".$this->ecCounter."]['name'] = '".$item->productName."';\n" .
            "    emosECPageArray[".$this->ecCounter."]['preis'] = '".$item->price."';\n" .
            "    emosECPageArray[".$this->ecCounter."]['group'] = '".$item->productGroup."';\n" .
            "    emosECPageArray[".$this->ecCounter."]['anzahl'] = '".$item->quantity."';\n" .
            "    emosECPageArray[".$this->ecCounter."]['var1'] = '".$item->variant1."';\n" .
            "    emosECPageArray[".$this->ecCounter."]['var2'] = '".$item->variant2."';\n" .
            "    emosECPageArray[".$this->ecCounter."]['var3'] = '".$item->variant3."';\n" ;
            $this->ecCounter += 1;
        }
    }

    /* adds a detailView to the preScript */
    public function addDetailView($item) 
    {
        $this->getEmosECPageArray($item, "view");
    }

    /* adds a removeFromBasket to the preScript */
    public function removeFromBasket($item) 
    {
        $this->getEmosECPageArray($item, "c_rmv");
    }

    /* adds a addToBasket to the preScript */
    public function addToBasket($item) 
    {
        $this->getEmosECPageArray($item, "c_add");
    }

    /* constructs a emosBillingPageArray of given $event type */
    public function addEmosBillingPageArray($billingID = "", $customerNumber = "", $total = 0, $country = "", $cip = "", $city = "") 
    {
        $out = $this->getEmosBillingArray($billingID, $customerNumber, $total, $country, $cip, $city, "emosBillingPageArray");
        $this->appendPreScript($out);
    }

    /* gets a emosBillingArray for a given ArrayName
     *  md5 the customer id to to fullfilll german datenschutzgesetz
     */
    public function getEmosBillingArray($billingID = "", $customerNumber = "", $total = 0, $country = "", $cip = "", $city = "", $arrayName = "") 
    {
        $customerNumber = md5($customerNumber);
        $country = $this->emos_DataFormat($country);
        $cip = $this->emos_DataFormat($cip);
        $city = $this->emos_DataFormat($city);

        /* get a / separated location string for later drilldown */
        $ort = "";
        if ($country) {
            $ort .= "$country/";
        }
        if ($cip) {
            $ort .= substr($cip, 0, 1) . "/" . substr($cip, 0, 2) . "/";
        }
        if ($city) {
            $ort .= "$city/";
        }
        if ($cip) {
            $ort .= $cip;
        }
        if(!$this->anchorTags){
            $out = "    emospro.billing = [['".$billingID."','".$customerNumber."','".$ort."','".$total."']];\n";
        }
        else { //anchor tags
            $out = "<script type=\"text/javascript\">\n//<![CDATA[\n" .
			"    var ".$arrayName." = new Array();\n" .
			"    ".$arrayName."[0] = '".$billingID."';\n" .
			"    ".$arrayName."[1] = '".$customerNumber."';\n" .
			"    ".$arrayName."[2] = '".$ort."';\n" .
			"    ".$arrayName."[3] = '".$total."';\n" .
			"//]]>\n</script>\n";			
        }

        return $out;
    }

    /* adds a emosBasket Page Array*/
    public function addEmosBasketPageArray($basket) 
    {
        if(!$this->anchorTags){
            $this->getEmosBasketPageArray($basket, "buy");
        }
        else {
            $this->getEmosBasketPageArray($basket, "emosBasketPageArray");
        }
    }

    /**
     * Accepts item object an returns an assoc array as required in JavaScript output 
     */
    private function getProductEventTrackingData($eventData, $eventName) {
        $cleanEventData = $this->emos_ItemFormat($eventData);
        return array(
            'type'  => $eventName,
            'pid'   => $cleanEventData->productID,
            'sku'   => "".$cleanEventData->productSku,
            'name'  => $cleanEventData->productName,
            'group' => $cleanEventData->productGroup,
            'price' => (float) $cleanEventData->price,
            'count' => (int) $cleanEventData->quantity,
            'var1'  => $cleanEventData->variant1 === 'NULL' ? null : $cleanEventData->variant1,
            'var2'  => $cleanEventData->variant2 === 'NULL' ? null : $cleanEventData->variant2,
            'var3'  => $cleanEventData->variant3 === 'NULL' ? null : $cleanEventData->variant3,
        );
    }

    /* returns a emosBasketArray of given Name */
    public function getEmosBasketPageArray($basket, $event) 
    {
        if(!$this->anchorTags){
            if($this->ecString == "") {
                $this->ecString .= "    emospro.ec_Event = [\n";
            }
            foreach ($basket as $item) {
                $this->ecString .= sprintf("%s, \n", json_encode(
                    $this->getProductEventTrackingData($item, $event))
                );
            }
        }
        else {
            $out = "<script type=\"text/javascript\">\n//<![CDATA[\n" .
			"    var ".$event." = new Array();\n";
            $count = 0;
            foreach ($basket as $item) {
                $item = $this->emos_ItemFormat($item);
                $out .= "    ".$event."[".$count."]=new Array();\n";
                $out .= "    ".$event."[".$count."][0]='".$item->productID."';\n";
                $out .= "    ".$event."[".$count."][1]='".$item->productName."';\n";
                $out .= "    ".$event."[".$count."][2]='".$item->price."';\n";
                $out .= "    ".$event."[".$count."][3]='".$item->productGroup."';\n";
                $out .= "    ".$event."[".$count."][4]='".$item->quantity."';\n";
                $out .= "    ".$event."[".$count."][5]='".$item->variant1."';\n";
                $out .= "    ".$event."[".$count."][6]='".$item->variant2."';\n";
                $out .= "    ".$event."[".$count."][7]='".$item->variant3."';\n";
                $count++;
            }
            $out .= "//]]>\n</script>\n";
            $this->appendPreScript($out);
        }
    }

    /*
     * constructs a generic EmosCustomPageArray from a PHP Array
     */
    public function getEmosCustomPageArray($listOfValues)
    {
        $out = "";
        if(!$this->anchorTags){
            $counter = 0;
            foreach ($listOfValues as $value) {
                $value = $this->emos_DataFormat($value);
                if($counter == 0) {
                    $out .= "    emospro.".$value." = [[";
                }
                else {
                    $out .= "'".$value."',";
                }
                $counter += 1;
            }
            $out = substr($out,0,-1);
            $out .= "]];\n";
        }
        else {
            $out .= "<script type=\"text/javascript\">\n";
            $out .= "    window.emosCustomPageArray = [";
            foreach ($listOfValues as $value) {
                $value = $this->emos_DataFormat($value);
                $out .= "'".$value."',";
            }
            $out = substr($out,0,-1);
            $out .= "];\n";
            $out .= "</script>\n";
        }
        $this->appendPreScript($out);
    }

    /* constructs a emosCustomPageArray with 8 Variables and shortcut
     * @param $cType Type of this event - shortcut in config
     * @param $cVar1 first variable of this custom event (optional)
     * @param $cVar2 second variable of this custom event (optional)
     * @param $cVar3 third variable of this custom event (optional)
     * @param $cVar4 fourth variable of this custom event (optional)
     * @param $cVar5 fifth variable of this custom event (optional)
     * @param $cVar6 sixth variable of this custom event (optional)
     * @param $cVar7 seventh variable of this custom event (optional)
     * @param $cVar8 eighth variable of this custom event (optional)
     * @param $cVar9 nineth variable of this custom event (optional)
     * @param $cVar10 tenth variable of this custom event (optional)
     * @param $cVar11 eleventh variable of this custom event (optional)
     * @param $cVar12 twelveth variable of this custom event (optional)
     * @param $cVar13 thirteenth variable of this custom event (optional)
     */
    public function addEmosCustomPageArray($cType=0, $cVar1=0, $cVar2=0, $cVar3=0, $cVar4=0, $cVar5=0, $cVar6=0, $cVar7=0, $cVar8=0, $cVar9=0, $cVar10=0, $cVar11=0, $cVar12=0, $cVar13=0) 
    {
        $values[0] = $cType;
        if($cVar1) $values[1] = $cVar1;
        if($cVar2) $values[2] = $cVar2;
        if($cVar3) $values[3] = $cVar3;
        if($cVar4) $values[4] = $cVar4;
        if($cVar5) $values[5] = $cVar5;
        if($cVar6) $values[6] = $cVar6;
        if($cVar7) $values[7] = $cVar7;
        if($cVar8) $values[8] = $cVar8;
        if($cVar9) $values[9] = $cVar9;
        if($cVar10) $values[10] = $cVar10;
        if($cVar11) $values[11] = $cVar11;
        if($cVar12) $values[12] = $cVar12;
        if($cVar13) $values[13] = $cVar13;
        $this->getEmosCustomPageArray($values);
    }
}
