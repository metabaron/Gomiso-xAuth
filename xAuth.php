<?php
//The oauth_callback is not necessary
//You might need it in another project involving OAuth (the code can be adapted to handle OAuth)
$oauth = array('oauth_callback' => 'http://yourdomain.com/callback_page',
              'oauth_consumer_key' => 'Kw03CXXXXXXXXXU5lX0gw',
              'oauth_nonce' => $nonce,
			  //You can select "HMAC-SHA1" as ecnryption scheme or "PLAINTEXT" (not supported by Gomiso)
              'oauth_signature_method' => 'HMAC-SHA1',
			  //'oauth_signature_method' => 'PLAINTEXT',
              'oauth_timestamp' => $timestamp,
              'oauth_version' => '1.0');

$baseURI = "https://gomiso.com/oauth/access_token?x_auth_username=XXXXX&x_auth_password=YYYYY&x_auth_mode=client_auth"; //Your URI for xAuth. Change XXXXX by user login and YYYYY by user password
$consumerSecret = 'LsP5sIZSnLvsXXXXXX4zjm4rYYJXkqBc'; //put your actual consumer secret here, it will look something like 'MCD8BKwGdgPHvAuvXXXXXXXXAtx89grbuNMRd7Eh98'

$nonce = time();
$timestamp = time();

/**
 * Method for creating a base string from an array and base URI.
 * @param string $baseURI the URI of the request to twitter
 * @param array $params the OAuth associative array
 * @return string the encoded base string
**/
function buildBaseString($baseURI, $params){
 
$r = array(); //temporary array
    ksort($params); //sort params alphabetically by keys
    foreach($params as $key=>$value){
        $r[] = "$key=" . rawurlencode($value); //create key=value strings
    }//end foreach                
 
    return "POST&" . rawurlencode($baseURI) . '&' . rawurlencode(implode('&', $r)); //return complete base string
}//end buildBaseString()

/**
 * Method for creating the composite key.
 * @param string $consumerSecret the consumer secret authorized by Twitter
 * @param string $requestToken the request token from Twitter
 * @return string the composite key.
**/
function getCompositeKey($consumerSecret, $requestToken){
    return rawurlencode($consumerSecret) . '&' . rawurlencode($requestToken);
}//end getCompositeKey()

/**
 * Method for building the OAuth header.
 * @param array $oauth the oauth array.
 * @return string the authorization header.
**/
function buildAuthorizationHeader($oauth){
    $r = 'Authorization: OAuth '; //header prefix
 
    $values = array(); //temporary key=value array
    foreach($oauth as $key=>$value)
        $values[] = "$key=\"" . rawurlencode($value) . "\""; //encode key=value string
 
    $r .= implode(', ', $values); //reassemble
    return $r; //return full authorization header
}//end buildAuthorizationHeader()

/**
 * Method for sending a request to an OAuth/xAuth server.
 * @param array $oauth the oauth array
 * @param string $baseURI the request URI
 * @return string the response from an OAuth/xAuth server
**/
function sendRequest($oauth, $baseURI){
    $header = array( buildAuthorizationHeader($oauth), 'Expect:'); //create header array and add 'Expect:'
 
    $options = array(CURLOPT_HTTPHEADER => $header, //use our authorization and expect header
                           CURLOPT_HEADER => false, //don't retrieve the header back from Twitter
                           CURLOPT_URL => $baseURI, //the URI we're sending the request to
                           CURLOPT_POST => true, //this is going to be a POST - required
                           CURLOPT_RETURNTRANSFER => true, //return content as a string, don't echo out directly
                           CURLOPT_SSL_VERIFYPEER => false); //don't verify SSL certificate, just do it
 
    $ch = curl_init(); //get a channel
    curl_setopt_array($ch, $options); //set options
    $response = curl_exec($ch); //make the call
    curl_close($ch); //hang up
 
    return $response;
}//end sendRequest() 


/***EXAMPLE***/
$baseString = buildBaseString($baseURI, $oauth);
$compositeKey = getCompositeKey($consumerSecret, null);
$oauth_signature = base64_encode(hash_hmac('sha1', $baseString, $compositeKey, true)); //sign the base string using "HMAC-SHA1"
//$oauth_signature = urlencode($consumerSecret); //"PLAINTEXT" version
$oauth['oauth_signature'] = $oauth_signature; //add the signature to our oauth array
$response = sendRequest($oauth, $baseURI); //make the call
print_r($response);
?>