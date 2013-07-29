<?php
/**
 * OAuthHandler.php
 *
 * Utility class that can be used to process requests to and from the Windows
 * Live WRAP framework.
 *
 * PHP version 5
 *
 * @category  OAuthWRAP_Handler
 * @package   OAuthWRAP
 * @author    Microsoft <microsoft@microsoft.com>
 * @copyright 2010 Microsoft Corporation. All rights reserved.
 * @license   http://www.php.net/license/3_01.txt  PHP License 3.01
 * @version   Release: 1.1
 * @link      GetStarted.htm
 */
class OAuthHandler
{
    /**
     * Issues a request to the LIVE services for a consent token by redirecting the
     * user to the LIVE WRAP authentication page and at the same time setting the
     * application ID and callback url.
     *
     * @param string $wrapScope   The scope of access you wish to request for
     *                            this application, the default value is an empty
     *                            string.
     *
     * @return null
     */
    public function getConsentToken($config,$wrapScope = '') {
        $wrap_consent_request = $config['requestURL']  .'?'
                . 'wrap_client_id=' . urlencode($config['consentAppId'])
                . '&wrap_callback=' . urlencode($config['consentCallbackUrl'])
                . '&wrap_scope=' . urlencode($wrapScope);
                
        
                
        $this->_redirect($wrap_consent_request);
    }

    /**
     * Issues an synchronous authorization request by generating a https POST
     * request to the LIVE authentication servers. The result, whether successful
     * or unsuccessful is returned to the calling function, parsed and displayed
     * to the user and saved to the session.
     *
     * @param string $verificationCode The verification code that was returned
     * as part of the consent request, which has an expiry.
     *
     * @return null
     */
    public function getAuthorizationToken($config,$verificationCode) {
        // Using the returned verification code build a query to the
        // authorization url that will return the authorized verification code

        $tokenRequest = 'wrap_client_id=' . urlencode($config['consentAppId'])
                . '&wrap_client_secret=' . urlencode($config['accessAppSecret'])
                . '&wrap_callback=' . urlencode($config['accessCallbackUrl'])
                . '&wrap_verification_code=' . urlencode($verificationCode);
        $response = $this->_postWRAPRequest(WRAP_ACCESS_URL, $tokenRequest);
        return $this->parsePOSTResponse($response);
    }

    /**
     * Called to refresh an authorization token using the refresh token that
     * is returned after a consent token is first authorized.
     *
     * @param string $refreshToken The token that is returned as part of the
     *                             authorization request.
     *
     * @return null
     */
    public function getRefreshedToken($refreshToken) {
        // Using the returned verification code build a query to the
        // authorization url that will return the authorised verification code

        $tokenRequest = 'wrap_refresh_token=' . urlencode($refreshToken)
                . '&wrap_client_id=' . urlencode(WRAP_CLIENT_ID)
                . '&wrap_client_secret=' . urlencode(WRAP_CLIENT_SECRET);

        $response = $this->_postWRAPRequest(WRAP_REFRESH_URL, $tokenRequest);
        return $this->parsePOSTResponse($response);
    }

    /**
     * Redirects the users browser to a provided url. Whilst saving the seesion
     * state and preventing any furthur response to the browser.
     *
     * @param string $url The web url that you wish to redirect to.
     *
     * @return null
     */
    private function _redirect($url)
    {
        // Close the session so that information is saved and
        // persisted accross the redirects.
        session_write_close();
        // Set the http redirect header to redirect to the LIVE pages.
        header("Location: ". $url);
        // Close the page so that no information is sent to the client browser.
        // If any information is sent then the redirect will not work.
        exit;
    }

    /**
     * Issues a synchronous http POST request to a url and returns the response
     * headers as well as the response content in a single string.
     *
     * @param string $posturl  The web url where the POST will be directed.
     * @param string $postvars The post variables that you are issuing as part
     *                         of the POST request. They must be in the format
     *                         var1=val1&var2=va12.
     *                         Note that there is no leading '?' and also not
     *                         that the individual values
     *                         need to be urlencoded but the $postvars string
     *                         itself must not be.
     *
     * @return null
     */
    private function _postWRAPRequest($posturl, $postvars)
    {
        $ch = curl_init($posturl);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postvars);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        // On Windows machines this prevents cURL from falling over when
        // requesting SSL urls
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $rec_Data = curl_exec($ch);
        curl_close($ch);

        return urldecode($rec_Data);
    }

    /**
     * Extract from the POST response any returned variables whether they be error
     * values or expected values.
     *
     * @param string $response The HTTP response string or query string that
     *                         contains the header and html string to format.
     * 
     * @return null
     */
    public function parsePOSTResponse($response)
    {
        // Firstly remove any extraneous header information from the returned
        // HTML
        if (strpos($response, '?') === false) {
            $pos = strpos($response, 'wrap_access_token=');

            if ($pos === false) {
                $pos = strpos($response, 'wrap_error_reason=');
            }
            if ($pos !== false) {
                $response = '?' . substr($response, $pos, strlen($response));
            }
        }
        $returnedVariables = array();
        // RegEx the string to separate out the variables and their values
        if (preg_match_all('/[?&]([^&=]+)=([^&=]+)/', $response, $matches)) {
            $contents = '';
            for ($i =0; $i < count($matches[1]); $i++) {
                $_SESSION[urldecode($matches[1][$i])]
                    = urldecode($matches[2][$i]);
                $returnedVariables[urldecode($matches[1][$i])]
                    = urldecode($matches[2][$i]);
            }
        } else {
            throw new UnexpectedValueException(
                    'There are no matches for the regular expression used
                        against the OAuth response.');
        }
        return $returnedVariables;
    }

    /**
     * Retreives the value of a parameter whether it has stored in the session or
     * has been passed as a $_REQUEST parameter. If the parameter is not found
     * in either the session or $_REQUEST array then a default value is returned .
     *
     * @param string $parameterName The name of parameter to be searched.
     * @param string $returnDefault The default value you want returned if there
     *                              is no value present in either
     *                              $_REQUEST or $_SESSION.
     *
     * @return The value of the parameter present in either the $_REQUEST,
     * $_SESSION or a default value.
     */
    public function getReturnedParameter($parameterName, $returnDefault = '')
    {
        $value = $returnDefault;
        if (isset($_REQUEST[$parameterName]) && $_REQUEST[$parameterName] != '') {
            $value = $_REQUEST[$parameterName];
            $_SESSION[$parameterName] = $value;
        } else if (isset($_SESSION[$parameterName])) {
            $value = $_SESSION[$parameterName];
        }
        return $value;
    }  
}
?>
