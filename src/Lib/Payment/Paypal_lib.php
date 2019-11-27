<?php

namespace KarmaFW\Lib\Payment;

use \PayPal;


// SANDBOX DB valide => visa: 4556142984862862 / mastercard: 5583942466987606 / amex: 345343966611301
// SANDBOX DB refusee => visa: 4111111111111111 / mastercard: 5555555555554444 / amex: 378282246310005


class Paypal_lib
{
	// https://github.com/paypal/PayPal-PHP-SDK/wiki/Making-First-Call
	// https://www.grafikart.fr/tutoriels/paypal-express-checkout-rest-962
	
	public static function PaymentPaypal($payment_label='', $payment_product_label='', $payment_price=0, $customer_description='')
	{
		// Paypal STEP 1 (creating payment)

		if (empty($payment_price) || ! defined('PAYPAL_ENV')) {
			return false;
		}

		if (PAYPAL_ENV == 'PROD') {
			if (! defined('PAYPAL_PROD_CLIENT_ID') || ! defined('PAYPAL_PROD_SECRET')) {
				return false;
			}
			$paypal_public_key = PAYPAL_PROD_CLIENT_ID;
			$paypal_secret_key = PAYPAL_PROD_SECRET;

		} else {
			if (! defined('PAYPAL_SANDBOX_CLIENT_ID') || ! defined('PAYPAL_SANDBOX_SECRET')) {
				return false;
			}
			$paypal_public_key = PAYPAL_SANDBOX_CLIENT_ID;
			$paypal_secret_key = PAYPAL_SANDBOX_SECRET;
		}



		// Step 1 (auth)
		$apiContext = new \PayPal\Rest\ApiContext(
		        new \PayPal\Auth\OAuthTokenCredential(
		            $paypal_public_key,     // ClientID
		            $paypal_secret_key      // ClientSecret
		        )
		);

		$apiContext->setConfig([
			//'log.LogEnabled' => true,
			//'log.FileName' => '/tmp/PayPal.log',
			//'log.LogLevel' => 'FINE',
			'mode' => (PAYPAL_ENV == 'PROD') ? 'live' : 'sandbox',
		]);

		

		// (prepare data)
		$payer = new \PayPal\Api\Payer();
		$payer->setPaymentMethod('paypal');


		$amount = new \PayPal\Api\Amount();
		$amount->setTotal($payment_price);
		$amount->setCurrency('EUR');


        // Build transaction
		$transaction = new \PayPal\Api\Transaction();
		$transaction->setAmount($amount);
		
		if (! empty($payment_label)) {
			$transaction->setDescription($payment_label); // optionnal
		}

		if (! empty($payment_product_label)) {
			// specify products list
			$list = new \PayPal\Api\ItemList();
			$item = (new \PayPal\Api\Item())
	                ->setName($payment_product_label)
	                ->setPrice($payment_price)
	                ->setCurrency('EUR')
	                ->setQuantity(1);
	        $list->addItem($item);
			$transaction->setItemList($list); // optionnal
		}

		if (! empty($customer_description)) {
			// specify a "custom" value for this customer (ex: user_id or user_email)
			$transaction->setCustom($customer_description); // optionnal
		}


		// set redirections urls
		$scheme = ( (! empty($_SERVER['REQUEST_SCHEME']) && $_SERVER['REQUEST_SCHEME'] == 'https') || (! empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (! empty($_SERVER['REDIRECT_HTTPS']) && $_SERVER['REDIRECT_HTTPS'] == 'on') ) ? 'https' : 'http';
		$redirectUrls = new \PayPal\Api\RedirectUrls();
		$redirectUrls->setReturnUrl($scheme."://" . $_SERVER['HTTP_HOST'] . "/paypal_callback")->setCancelUrl($scheme."://" . $_SERVER['HTTP_HOST'] . "/paypal_canceled");


		// Build payment
		$payment = new \PayPal\Api\Payment();
		$payment->setIntent('sale')
		    ->setPayer($payer)
		    ->setTransactions(array($transaction))
		    ->setRedirectUrls($redirectUrls);


		// Create payment via API
		$approval_url = null;
		$paypal_error = '';
		$paymentId = null;
		try {
		    $payment->create($apiContext);
		    //echo "<pre>" . PHP_EOL . $payment . PHP_EOL . "</pre>" . PHP_EOL; exit;

		    $approval_url = $payment->getApprovalLink();
		    $paymentId = $payment->getId();
		    //echo '\n\nRedirect user to approval_url: <a href="' . $approval_url . '">' . $approval_url . '</a>';
		
		} catch (\PayPal\Exception\PayPalConnectionException $e) {
		    //echo "<pre>" . $e->getData() . "</pre>"; exit;
		    $error_data = json_decode($e->getData());
		    $paypal_error = $e->getMessage();
		    if (! empty($error_data->error_description)) {
		    	$paypal_error = $error_data->error_description;
		    }
		}


		return [
			'approval_url' => $approval_url,
			'paypal_error' => $paypal_error,
			'paymentId' => $paymentId,
		];
	}



	public static function PaymentPaypal_Callback()
	{
		// Paypal STEP 2 (validating payment)

		if (! defined('PAYPAL_ENV')) {
			return false;
		}

		if (PAYPAL_ENV == 'PROD') {
			if (! defined('PAYPAL_PROD_CLIENT_ID') || ! defined('PAYPAL_PROD_SECRET')) {
				return false;
			}
			$paypal_public_key = PAYPAL_PROD_CLIENT_ID;
			$paypal_secret_key = PAYPAL_PROD_SECRET;

		} else {
			if (! defined('PAYPAL_SANDBOX_CLIENT_ID') || ! defined('PAYPAL_SANDBOX_SECRET')) {
				return false;
			}
			$paypal_public_key = PAYPAL_SANDBOX_CLIENT_ID;
			$paypal_secret_key = PAYPAL_SANDBOX_SECRET;
		}

		$paypal_error = '';
		$payment_ok = false;

	    $paymentId = get('paymentId');
	    $token = get('token');
	    $PayerID = get('PayerID');

	    if (empty($paymentId) || empty($token) || empty($PayerID)) {
	    	$paypal_error = 'missing parameters';

	    } else {
			// Step 1 (auth)
			$apiContext = new \PayPal\Rest\ApiContext(
			        new \PayPal\Auth\OAuthTokenCredential(
			            $paypal_public_key,     // ClientID
			            $paypal_secret_key      // ClientSecret
			        )
			);

			// Step 2
	    	$payment = \PayPal\Api\Payment::get($paymentId, $apiContext);
			$execution = (new \PayPal\Api\PaymentExecution())->setPayerId($PayerID);

			try {
			    $payment->execute($execution, $apiContext);
			    //echo 'Merci pour votre paiement';
			    $payment_ok = true;

			} catch (\PayPal\Exception\PayPalConnectionException $e) {
			    $error_data = json_decode($e->getData());
			    //var_dump($error_data);
			    $paypal_error = $e->getMessage();
			    if (! empty($error_data->details[0]->issue)) {
			    	$paypal_error = $error_data->details[0]->issue;
			    }
			}
		}

		$paypal_result = [
			'payment_ok' => $payment_ok,
	    	'paymentId' => $paymentId,
	    	'token' => $token,
	    	'PayerID' => $PayerID,
	    	'payment' => $payment,
		];

		if (! $payment_ok) {
			return $paypal_result;
		}


		if (false) {
			$transaction = $paypal_result['payment']->getTransactions()[0];

			echo "paymentId: " . $paymentId . "<hr />";
			echo "Status: " . $paypal_result['payment']->getState() . "<hr />"; // expected: approved
			echo "Intent: " . $paypal_result['payment']->getIntent() . "<hr />"; // expected:  sale
			echo "Cart: " . $paypal_result['payment']->getCart() . "<hr />";
			echo "Description: " . $transaction->description . "<hr />";
			echo "Item: " . $transaction->item_list->getItems()[0]->name . "<hr />";
			echo "TotalPrice: " . $transaction->amount->total . "<hr />";

			echo "<pre>" . print_r($paypal_result['payment'], 1);
		}

		return $paypal_result;

	}

}
