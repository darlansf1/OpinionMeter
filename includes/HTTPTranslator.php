<?php
include_once 'psl-config.php';
include_once 'AccessTokenAuthentication.php';
include_once 'lpbhn.php';

class HTTPTranslator {
    /*
     * Create and execute the HTTP CURL request.
     *
     * @param string $url        HTTP Url.
     * @param string $authHeader Authorization Header string.
     * @param string $postData   Data to post.
     *
     * @return string.
	 * 
     * Most of the code was taken from https://msdn.microsoft.com/en-us/library/ff512422.aspx#phpexample
     */
    function curlRequest($url, $authHeader, $postData=''){
        //Initialize the Curl Session.
        $ch = curl_init();
        //Set the Curl url.
        curl_setopt ($ch, CURLOPT_URL, $url);
        //Set the HTTP HEADER Fields.
        curl_setopt ($ch, CURLOPT_HTTPHEADER, array($authHeader,"Content-Type: text/xml"));
        //CURLOPT_RETURNTRANSFER- TRUE to return the transfer as a string of the return value of curl_exec().
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, TRUE);
        //CURLOPT_SSL_VERIFYPEER- Set FALSE to stop cURL from verifying the peer's certificate.
        curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, False);
        if($postData) {
            //Set HTTP POST Request.
            curl_setopt($ch, CURLOPT_POST, TRUE);
            //Set data to POST in HTTP "POST" Operation.
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        }
        //Execute the  cURL session.
        $curlResponse = curl_exec($ch);
        //Get the Error Code returned by Curl.
        $curlErrno = curl_errno($ch);
        if ($curlErrno) {
            $curlError = curl_error($ch);
            throw new Exception($curlError);
        }
        //Close a cURL session.
        curl_close($ch);
        return $curlResponse;
    }


    /*
     * Create Request XML Format.
     *
     * @param string $fromLanguage   Source language Code.
     * @param string $toLanguage     Target language Code.
     * @param string $contentType    Content Type.
     * @param string $inputStrArr    Input String Array.
     *
     * @return string.
     */
    function createReqXML($fromLanguage,$toLanguage,$contentType,$inputStrArr) {
        //Create the XML string for passing the values.
        $requestXml = "<TranslateArrayRequest>".
            "<AppId/>".
            "<From>$fromLanguage</From>". 
            "<Options>" .
             "<Category xmlns=\"http://schemas.datacontract.org/2004/07/Microsoft.MT.Web.Service.V2\" />" .
              "<ContentType xmlns=\"http://schemas.datacontract.org/2004/07/Microsoft.MT.Web.Service.V2\">$contentType</ContentType>" .
              "<ReservedFlags xmlns=\"http://schemas.datacontract.org/2004/07/Microsoft.MT.Web.Service.V2\" />" .
              "<State xmlns=\"http://schemas.datacontract.org/2004/07/Microsoft.MT.Web.Service.V2\" />" .
              "<Uri xmlns=\"http://schemas.datacontract.org/2004/07/Microsoft.MT.Web.Service.V2\" />" .
              "<User xmlns=\"http://schemas.datacontract.org/2004/07/Microsoft.MT.Web.Service.V2\" />" .
            "</Options>" .
            "<Texts>";
        foreach ($inputStrArr as $inputStr)
        $requestXml .=  "<string xmlns=\"http://schemas.microsoft.com/2003/10/Serialization/Arrays\">$inputStr</string>" ;
        $requestXml .= "</Texts>".
            "<To>$toLanguage</To>" .
          "</TranslateArrayRequest>";
        return $requestXml;
    }
	
	function translateSpecial($originalText, $fromLanguage, $tried){
		$result = array();
		try {
			//Client ID of the application.
			$clientID       = CLIENT_ID;
			//Client Secret key of the application.
			$clientSecret = CLIENT_SECRET;
			//OAuth Url.
			$authUrl      = "https://datamarket.accesscontrol.windows.net/v2/OAuth2-13/";
			//Application Scope Url
			$scopeUrl     = "http://api.microsofttranslator.com";
			//Application grant type
			$grantType    = "client_credentials";

			//Create the AccessTokenAuthentication object.
			$authObj      = new AccessTokenAuthentication();
			//Get the Access token.
			$accessToken  = $authObj->getTokens($grantType, $scopeUrl, $clientID, $clientSecret, $authUrl);
			//Create the authorization Header string.
			$authHeader = "Authorization: Bearer ". $accessToken;

			//Set the params.//
			$fromLanguage = $fromLanguage;
			$toLanguage   = "en";
			$originalText = utf8_encode($originalText);
			
			$inputStrArr  = explode(" ", $originalText);
			$contentType  = 'text/plain';
			//Create the Translator Object.
			$translatorObj = new HTTPTranslator();

			//Get the Request XML Format.
			$requestXml = $translatorObj->createReqXML($fromLanguage,$toLanguage,$contentType,$inputStrArr);

			//HTTP TranslateMenthod URL.
			$translateUrl = "http://api.microsofttranslator.com/v2/Http.svc/TranslateArray";

			//Call HTTP Curl Request.
			$curlResponse = $translatorObj->curlRequest($translateUrl, $authHeader, $requestXml);

			//Interprets a string of XML into an object.
			$xmlObj = simplexml_load_string($curlResponse);
			$i=0;
			
			foreach($xmlObj->TranslateArrayResponse as $translatedArrObj){
				$translation = $translatedArrObj->TranslatedText;
				$exploded = explode(" ", $translation);
				if(count($exploded) > 1){
					$translation = $exploded[0];
					for($j = 1; $j < count($exploded);$j++)
						$translation = $translation."/".$exploded[$j];
				}
				array_push($result, $translation);
				$i++;
			}
			
		} catch (Exception $e) {
			echo "Exception: " . $e->getMessage() . PHP_EOL;
		}
		
		if(count($result) == 0 && $tried === false){
			$cleanText = removeSpecialChars(utf8_encode(strtoupper(removeSpecialChars(utf8_decode($originalText)))));
			//echo "/////cleanText: $cleanText/////";
			$result = $this->translateSpecial($cleanText, $fromLanguage, true);
		}
		return $result;
	}
	
	function translate($originalText, $fromLanguage){
		return $this->translateSpecial($originalText, $fromLanguage, false);
	}
}
?>