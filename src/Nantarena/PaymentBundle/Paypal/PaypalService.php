<?php

namespace Nantarena\PaymentBundle\Paypal;

use PayPal\Rest\ApiContext;

use PayPal\Auth\OAuthTokenCredential;

use PayPal\Api\Payer;
use PayPal\Api\Details;
use PayPal\Api\Amount;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\Transaction;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;

use PayPal\Exception\PPConnectionException;
use PayPal\Exception\PPConfigurationException;
use PayPal\Exception\PPInvalidCredentialException;
use PayPal\Exception\PPMissingCredentialException;
use PayPal\Exception\PPTransformerException;


class PaypalService
{

    private $clientID;

    private $clientSecret;

    private $parameters;

    public function __construct($paypal)
    {
        $credentials_clientid = $paypal['credentials']['clientid'];
        $credentials_secret = $paypal['credentials']['secret'];
        $http_connection_timeout = $paypal['service']['http_connection_timeout'];
        $http_retry = $paypal['service']['http_retry'];
        $http_proxy = $paypal['service']['http_proxy'];
        $service_mode = $paypal['service']['mode'];
        $log_enable = $paypal['service']['log_enable'];
        $log_file = $paypal['service']['log_file'];
        $log_level = $paypal['service']['log_level'];

        // set service parameters
        $this->clientID = $credentials_clientid;
        $this->clientSecret = $credentials_secret;
        $this->parameters = array(
            'mode' => $service_mode,
            'http.ConnectionTimeOut' => $http_connection_timeout,
            'http.Retry' => $http_retry,
            'log.LogEnabled' => $log_enable,
            'log.FileName' => $log_file,
            'log.LogLevel' => $log_level
        );

        if(!empty($http_proxy))
        {
         $this->parameters['http.Proxy'] = $http_proxy;
        }
    }

    /**
     * Get a context for paypal operation
     */
    function getApiContext()
    {
        // Use OAuth to retreive a token
        $auth = new OAuthTokenCredential(
            $this->clientID,
            $this->clientSecret
        );

        // create a new context
        $apiContext = new ApiContext($auth);
    
        // sdk_config.ini configuration key
        $apiContext->setConfig($this->parameters);

        return $apiContext;
    }

    /**
     * Create a new item with provided parameters
     *
     * @param string $name 127 char maximum
     *
     * @param decimal $quantity item quantity
     *
     * @param float $price price for one item
     * 
     */
    function createItem($name, $quantity, $price)
    {
        $item = new Item();

        $item
            ->setName($name)
            ->setQuantity(strval($quantity))
            ->setPrice(strval($price))
            ->setCurrency("EUR");

        return $item;
    }


    /**
     * Create a payment using the buyer's paypal
     * account as the funding instrument. Your app
     * will have to redirect the buyer to the paypal 
     * website, obtain their consent to the payment
     * and subsequently execute the payment using
     * the execute API call. 
     * 
     */
    function paypalPaymentApproval($total, $payment_desc, $item_array, $url_succes, $url_cancel)
    {
        // Payer
        $payer = new Payer();
        $payer->setPaymentMethod("paypal");

        // Amount details
        $amountDetails = new Details();
        $amountDetails->setSubtotal(strval($total));
        $amountDetails->setTax('0');
        $amountDetails->setShipping('0');

        // Amount
        $amount = new Amount();
        $amount->setCurrency("EUR");
        $amount->setTotal(strval($total));
        $amount->setDetails($amountDetails);

        // Item_list
        $item_list = new ItemList();
        $item_list->setItems($item_array);

        // Transaction
        $transaction = new Transaction();
        $transaction->setAmount($amount);
        $transaction->setDescription($payment_desc);
        $transaction->setItemList($item_list);

        // Redirect urls
        $redirectUrls = new RedirectUrls();
        $redirectUrls->setReturnUrl($url_succes);
        $redirectUrls->setCancelUrl($url_cancel);

        // Payment
        $payment = new Payment();
        $payment->setIntent("sale");
        $payment->setPayer($payer);
        $payment->setRedirectUrls($redirectUrls);
        $payment->setTransactions(array($transaction));

        // Create Payment
        $payment->create($this->getApiContext());
        
        return $payment;
    }


    function getPaymentLink($payment)
    {
        foreach($payment->getLinks() as $link) 
        {
            if($link->getRel() == 'approval_url') 
            {
                $redirectUrl = $link->getHref();
                return $redirectUrl;
            }
        }
        return "";
    }


    /**
     * Completes the payment once buyer approval has been
     * obtained. Used only when the payment method is 'paypal'
     * 
     * @param string $paymentId id of a previously created
     *      payment that has its payment method set to 'paypal'
     *      and has been approved by the buyer.
     * 
     * @param string $payerId PayerId as returned by PayPal post
     *      buyer approval.
     */
    function executePayment($paymentId, $payerId)
    {
        // retrieve payment on paypal
        $payment = Payment::get($paymentId, $this->getApiContext());

        // Apply the payment
        $paymentExecution = new PaymentExecution();
        $paymentExecution->setPayerId($payerId);
        $payment->execute($paymentExecution, $this->getApiContext());  
        
        return $payment;
    }


    /**
     * handle paypal exception
     * @param string $errorJson
     * @return string
     */
    function ApiErrorHandle(\Exception $ex) {

        $msg = '';

        if ($ex instanceof PPConnectionException) {
            $msg .= 'Connection Error';
        } elseif ($ex instanceof PPConfigurationException) {
            $msg .= 'Configuration Error';
        } elseif ($ex instanceof PPInvalidCredentialException || $ex instanceof PPMissingCredentialException) {
            $msg .= 'Credentials Error';
        } elseif ($ex instanceof PPTransformerException) {
            $msg .= 'Transformer Error';
        }

        // empty if not Paypal exception
        return $msg;
    }


    /**
     * Utility function to pretty print API error data
     * @param string $errorJson
     * @return string
     */
    function parseApiError($errorJson) {
        $msg = '';
        
        $data = json_decode($errorJson, true);
        if(isset($data['name']) && isset($data['message'])) {
            $msg .= $data['name'] . " : " .  $data['message'] . "<br/>";
        }
        if(isset($data['details'])) {
            $msg .= "<ul>";
            foreach($data['details'] as $detail) {
                $msg .= "<li>" . $detail['field'] . " : " . $detail['issue'] . "</li>";
            }
            $msg .= "</ul>";
        }
        if($msg == '') {
            $msg = $errorJson;
        }
        return $msg;
    }

    /**
     * Utility function to pretty print API error data
     * @param string $errorJson
     * @return string
     */
    function ApiErrorMessage($errorJson) {
        $msg = '';
        
        $data = json_decode($errorJson, true);
        if(isset($data['name']) && isset($data['message'])) {
            $msg .= $data['name'] . " : " .  $data['message'] . " -- ";
        }
        if(isset($data['details'])) {
            $msg .= "details :";
            foreach($data['details'] as $detail) {
                $msg .= "" . $detail['field'] . " : " . $detail['issue'] . " - ";
            }
            $msg .= "";
        }
        if($msg == '') {
            $msg = $errorJson;
        }
        return $msg;
    }
}
