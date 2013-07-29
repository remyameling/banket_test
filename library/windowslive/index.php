<?php
/**
 * Index.php
 *
 * PHP version 5
 *
 * @category  OAuthWRAP_Callback
 * @package   OAuthWRAP
 * @author    Microsoft <microsfot@microsoft.com>
 * @copyright 2010 Microsoft Corporation. All rights reserved.
 * @license   http://www.php.net/license/3_01.txt  PHP License 3.01
 * @version   SVN: 1001
 * @link      GetStarted.htm
 */
require_once 'init.php';

$wrapper = new OAuthHandler();

$appId 
    = $wrapper->getReturnedParameter('consentAppId', WRAP_CLIENT_ID);
$appSecret
    = $wrapper->getReturnedParameter('accessAppSecret', WRAP_CLIENT_SECRET);
$callbackUrl
    = $wrapper->getReturnedParameter('consentCallbackUrl', WRAP_CALLBACK);
$options
    = $wrapper->getReturnedParameter('consentOptions', WRAP_SCOPE);

// Returned tokens that we use to make further requests
$verificationCode = $wrapper->getReturnedParameter('wrap_verification_code');
$accessToken = $wrapper->getReturnedParameter('wrap_access_token');
$refreshToken = $wrapper->getReturnedParameter('wrap_refresh_token');

// Placeholder variables that hold any returned response text from our requests
$accessResponse = $wrapper->getReturnedParameter('accessResponse', array());
$refreshResponse = array();
$consentResponse = array();

// CSS classes that will help determine how each of the differing sections
// should appear
$consentStyle = 'class="sample"';
$accessStyle = 'class="sample"';
$refreshStyle = 'class="sample"';


// If the user has clicked the "Request Consent" button then forward them to
// the Live Consent.aspx page.
if (isset($_REQUEST['consent'])) {
    $wrapper->getConsentToken($options);
} else if (isset($_REQUEST['access'])) {
    // If the user has clicked the "Request Access" button then attempt to get
    // authorization using the verification code received in the previous step.
    $accessResponse = $wrapper->getAuthorizationToken($verificationCode);
    $_SESSION['accessResponse'] = $accessResponse;
    $refreshToken = $wrapper->getReturnedParameter('wrap_refresh_token');

    $consentStyle = 'class="sampleActive"';
    $accessStyle = 'class="sampleActive"';
} else if (isset($_REQUEST['refresh'])) {
    // When the user has clicked "Refresh Token" then attempt to refresh the access
    // token.
    $refreshResponse = $wrapper->getRefreshedToken($refreshToken);

    $consentStyle = 'class="sampleActive"';
    $accessStyle = 'class="sampleActive"';
    $refreshStyle = 'class="sampleActive"';
}

// If the page is loading after consent has been given, either populate the
// verification code fields, or display the error that was returned - depending
// on the outcome of the consent process.
if (isset($_REQUEST['wrap_verification_code']) || isset($_REQUEST['error_code'])) {

    $consentResponse = $wrapper->parsePOSTResponse($_SERVER['REQUEST_URI']);
    $consentStyle = 'class="sampleActive"';
}

/**
 * Prints the returned parameter arry as a html table.
 *
 * @param array $returnedVariables An associative array
 *
 * @return null
 */
function printParameterTable($returnedVariables = array('' => ''))
{
    if (!is_array($returnedVariables)) {
        throw new InvalidArgumentException(
            'Parameter needs to be an associative array'
            );
    }
    foreach ($returnedVariables as $key => $value) {
        echo
            '<tr><td>' . str_replace('_', ' ', $key)
            . '</td><td><textarea>'
            . $value.'</textarea></td></tr>';
    }
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <title>PHP OAuth WRAP Example</title>
        <link rel="stylesheet" href="./style/live_sample.css" type="text/css" />
    </head>
    <body>
        <form method="post" action="">
            <div <?php echo $consentStyle;?> >
                <div class="title">Requesting Consent</div>
                <div class="code">
                    <table>
                        <tr>
                            <td>
                                AppId
                            </td>
                            <td>
                                <input 
                                    type="text"
                                    name="consentAppId"
                                    class="input"
                                    value="<?php echo $appId; ?>"/>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                Return Url
                            </td>
                            <td>
                                <input 
                                    type="text"
                                    name="consentCallbackUrl"
                                    class="input"
                                    value="<?php echo $callbackUrl; ?>"/>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                Scope
                            </td>
                            <td>
                                <input 
                                    type="text"
                                    name="consentOptions"
                                    class="input"
                                    value="<?php echo $options;  ?>"/>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2">
                                <input
                                    type="submit"
                                    value="Request Consent"
                                    name="consent"/>
                            </td>
                        </tr>
                        <?php echo printParameterTable($consentResponse); ?>
                    </table>
                </div>
                <div class="desc">
                    <p>When we first want to access a users information
                        we need
                        to request consent to access a certain scope of that
                        users information.</p>
                    <p>This is accomplished by issuing to the LIVE consent 
                        service, which prompts a user to give access to your
                        application.</p>
                </div>
            </div>

            <div <?php echo $accessStyle;?> >
                <div class="title">Access Token Request</div>
                <div class="code">
                    <table>
                        <tr>
                            <td>
                                AppId
                            </td>
                            <td>
                                <input 
                                    type="text"
                                    class="input"
                                    disabled="disabled"
                                    value="<?php echo $appId ?>" />
                            </td>
                        </tr>
                        <tr>
                            <td>
                                Secret Key
                            </td>
                            <td>
                                <input 
                                    type="text"
                                    name="accessAppSecret"
                                    class="input"
                                    value="<?php echo $appSecret ?>" />
                            </td>
                        </tr>
                        <tr>
                            <td>
                                Return url
                            </td>
                            <td>
                                <input 
                                    type="text"
                                    class="input"
                                    disabled="disabled"
                                    value="<?php echo $callbackUrl ?>" />
                            </td>
                        </tr
                        <tr>
                            <td>Verification Code</td>
                            <td><input
                                    type="text"
                                    class="input"
                                    disabled="disabled"
                                    value="<?php echo $verificationCode ?>" />
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2">
                                <input
                                    type="submit"
                                    value="Request Access"
                                    name="access"/>
                            </td>
                        </tr>
                        <?php echo printParameterTable($accessResponse);?>
                    </table>

                </div>
                <div class="desc">
                    <p>After our application has been granted consent by the
                        user using the Live services, we need to use the returned
                        consent token to request an access token. To access the data
                        that we have requested with our consent scope we need to get
                        an authoristation token.</p>
                    <p>To do this we submit another request to the live services,
                        this time passing in the verification code that we received
                        after the user consented to accessing their data.</p>
                </div>
            </div>

            <div <?php echo $refreshStyle;?>>
                <div class="title">Requesting a Refresh</div>
                <div class="code">
                    <table>
                        <tr>
                            <td>
                                AppId
                            </td>
                            <td>
                                <input 
                                    type="text"
                                    class="input"
                                    disabled="disabled"
                                    value="<?php echo $appId; ?>" />
                            </td>
                        </tr>
                        <tr>
                            <td>
                                Secret Key
                            </td>
                            <td>
                                <input
                                    type="text"
                                    class="input"
                                    value="<?php echo $appSecret; ?>" />
                            </td>
                        </tr>
                        <tr>
                            <td>
                                Refresh Token
                            </td>
                            <td>
                                <input 
                                    type="text"
                                    class="input"
                                    disabled="disabled"
                                    value="<?php echo $refreshToken; ?>" />
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2">
                                <input
                                    type="submit"
                                    value="Refresh Token"
                                    name="refresh"/>
                            </td>
                        </tr>
                        <?php echo printParameterTable($refreshResponse);?>
                    </table>

                </div>
                <div class="desc">
                    <p>An authorization token has an expiry. Once that expiry
                        period elapsed the token will no longer allow access to
                        a users data. </p>
                    <p>Once this happens we need to either
                        request that user consents to our application again
                        (redoing the first step), or we simply detect
                        that the authorization token has expired; then
                        using the refresh token request a new access token. </p>
                </div>
            </div>
        </form>
    </body>
</html>
