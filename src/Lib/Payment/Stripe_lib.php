<?php

namespace KarmaFW\Lib\Payment;

use \Stripe\Stripe;


class Stripe_lib
{

	public function paymentChargeByCard($card, $customer_id=null, $payment_label='', $payment_price=0, $save_customer=false, $new_customer_email=null, $new_customer_description=null)
	{
		/*
		$card = [
			'number' => '4242424242424242',
			'exp_month' => 12,
			'exp_year' => 2020,
			'cvc' => '314',
		];
		*/


		if (STRIPE_ENV == 'LIVE') {
			if (! defined('STRIPE_LIVE_PUBLIC_KEY') || ! defined('STRIPE_LIVE_SECRET_KEY')) {
				return false;
			}
			$stripe_public_key = STRIPE_LIVE_PUBLIC_KEY;
			$stripe_secret_key = STRIPE_LIVE_SECRET_KEY;

		} else {
			if (! defined('STRIPE_TEST_PUBLIC_KEY') || ! defined('STRIPE_TEST_SECRET_KEY')) {
				return false;
			}
			$stripe_public_key = STRIPE_TEST_PUBLIC_KEY;
			$stripe_secret_key = STRIPE_TEST_SECRET_KEY;
		}

		\Stripe\Stripe::setApiKey($stripe_secret_key);



		// Warning: l'API \Stripe\Token requiert que l'on ai vérifié son téléphone sur notre compte Stripe

		$client_secret = \Stripe\Token::create([
		  'card' => $card,
		]);

		$result = \KarmaFW\Lib\Payment\Stripe_lib::paymentCharge($client_secret, $customer_id, $payment_label, $payment_price, $save_customer, $new_customer_email, $new_customer_description);


		/*
		$result = Array
		(
		    [payment_accepted] => 1
		    [customer_id] => 
		    [stripe_token] => Stripe\Token Object
		        (
		            [id] => tok_1Fry1qBSCxevOCvKzN3APSvV
		            [object] => token
		            [card] => Stripe\Card Object
		                (
		                    [id] => card_1Fry1pBSCxevOCvKCxJk8v5u
		                    [object] => card
		                    [address_city] => 
		                    [address_country] => 
		                    [address_line1] => 
		                    [address_line1_check] => 
		                    [address_line2] => 
		                    [address_state] => 
		                    [address_zip] => 
		                    [address_zip_check] => 
		                    [brand] => Visa
		                    [country] => US
		                    [cvc_check] => unchecked
		                    [dynamic_last4] => 
		                    [exp_month] => 12
		                    [exp_year] => 2020
		                    [fingerprint] => hZOhEfHv06Xhjqll
		                    [funding] => credit
		                    [last4] => 4242
		                    [metadata] => Stripe\StripeObject Object
		                        (
		                        )

		                    [name] => 
		                    [tokenization_method] => 
		                )

		            [client_ip] => 82.64.213.186
		            [created] => 1576896558
		            [livemode] => 
		            [type] => card
		            [used] => 
		        )

		    [stripe_error] => 
		)
		*/

		return ! empty($result['payment_accepted']);
	}



	public static function paymentCharge($stripe_token, $customer_id=null, $payment_label='', $payment_price=0, $save_customer=false, $new_customer_email=null, $new_customer_description=null)
	{
		// Note: $stripe_token peut venir soit d'un \Stripe\Token (voir methode paymentChargeByCard), soit depuis Stripe.js

		if (empty($payment_price) || ! defined('STRIPE_ENV')) {
			return false;
		}

		// TEST CB valide  => visa: 4242 4242 4242 4242  /  mastercard: 5555 5555 5555 4444
		// TEST CB refusée => visa: 4242 4242 4242 1214  /  mastercard: 5555 5555 5555 7777

		if (STRIPE_ENV == 'LIVE') {
			if (! defined('STRIPE_LIVE_PUBLIC_KEY') || ! defined('STRIPE_LIVE_SECRET_KEY')) {
				return false;
			}
			$stripe_public_key = STRIPE_LIVE_PUBLIC_KEY;
			$stripe_secret_key = STRIPE_LIVE_SECRET_KEY;

		} else {
			if (! defined('STRIPE_TEST_PUBLIC_KEY') || ! defined('STRIPE_TEST_SECRET_KEY')) {
				return false;
			}
			$stripe_public_key = STRIPE_TEST_PUBLIC_KEY;
			$stripe_secret_key = STRIPE_TEST_SECRET_KEY;
		}

		$stripe_error = '';
		$payment_ok = false;
		\Stripe\Stripe::setApiKey($stripe_secret_key);

		try {
			if ($save_customer && empty($customer_id)) {
				$customer = \Stripe\Customer::create([
					'email' => $new_customer_email,
					'description' => $new_customer_description,
					'source'  => $stripe_token,
				]);
				$customer_id = $customer->id;
			}


			$charge_infos = [
			    'amount' => $payment_price * 100, // en centimes
			    'currency' => 'eur',
			    'description' => $payment_label,
			    //'source' => $stripe_token, // soit on utilise 'source' soit on utilise 'customer', mais pas les 2 en meme temps
			    //'customer' => $customer_id,
			];
			if ($save_customer) {
				$charge_infos['customer'] = $customer_id;

			} else {
				$charge_infos['source'] = $stripe_token;
			}
			$charge = \Stripe\Charge::create($charge_infos);
			
		} catch (\Stripe\Error\InvalidRequest $e) {
			$charge = null;
			$stripe_error = $e->getMessage();

		} catch (\Stripe\Error\Card $e) {
			$charge = null;
			$stripe_error = $e->getMessage();
		}


		if (empty($charge)) {
			// error: suite à une sortie en erreur du try/catch

		}else if ($charge->status !== 'succeeded') {
			// error: invalid charge status
			$stripe_error = "statut de transaction invalide";

		}else if ($charge->object !== 'charge') {
			// error: invalid transaction type
			$stripe_error = "type de transaction invalide";

		}else if ($charge->amount != intval($payment_price * 100)) {
			// error: invalid price
			$stripe_error = "prix de transaction invalide";

		} else {
			// OK
			$payment_ok = true;
		}

		return [
			'payment_accepted' => $payment_ok,
			'customer_id' => $customer_id,
			'stripe_token' => $stripe_token,
			'stripe_error' => $stripe_error,
		];
	}



	public function paymentIntentInit($unit, $quantity=1, $optionnal_data=[])
	{
		// Paiement en 2 temps, via Stripe.js
		// Methode appelée en ajax

		if (in_array(STRIPE_ENV, ['LIVE', 'PROD'])) {
			if (! defined('STRIPE_LIVE_PUBLIC_KEY') || ! defined('STRIPE_LIVE_SECRET_KEY')) {
				return false;
			}
			$stripe_secret_key = STRIPE_LIVE_SECRET_KEY;

		} else {
			if (! defined('STRIPE_TEST_PUBLIC_KEY') || ! defined('STRIPE_TEST_SECRET_KEY')) {
				return false;
			}
			$stripe_secret_key = STRIPE_TEST_SECRET_KEY;
		}
		

        \Stripe\Stripe::setApiKey($stripe_secret_key);

        $currency = 'eur';

        $intent = \Stripe\PaymentIntent::create([
            'amount' => $quantity * $unit * 100,
            'currency' => $currency,
        ]);

        $client_secret = $intent->client_secret;

        $_SESSION['stripe_payment'][$client_secret] = [
        	'unit' => $unit,
        	'quantity' => $quantity,
        	'currency' => $currency,
        ];

        if (! empty($optionnal_data)) {
        	$_SESSION['stripe_payment'][$client_secret] = array_merge($_SESSION['stripe_payment'][$client_secret], $optionnal_data);
        }

        //echo $client_secret; // renvoie le token au javascript de Stripe.js

        return $client_secret;
	}


	public function paymentIntentConfirm()
	{
		// Paiement en 2 temps, via Stripe.js
		// Methode appelée en ajax

        $payment_id = get('pid');
        $client_secret = get('sec');

        if (empty($client_secret)) {
            showError403('Paiment refusé. (Cause = error 1)');
        }

        if (empty($_SESSION['stripe_payment'][$client_secret])) {
            showError403('Paiment refusé. (Cause = error 2)');
        }

        $payment = $_SESSION['stripe_payment'][$client_secret];
        if (empty($payment)) {
            showError403('Paiment refusé. (Cause = error 3)');
        }

        $quantity = $payment['quantity'];
        $unit = $payment['unit'];
        $currency = $payment['currency'];

        if (empty($quantity)) {
            showError403('Paiment refusé. (Cause = error 4)');
        }

        if (empty($unit)) {
            showError403('Paiment refusé. (Cause = error 5)');
        }


        $_SESSION['stripe_payment'][$client_secret]['payment_id'] = $payment_id;

        //echo "ok"; // permet de dire au javascript de rediriger ensuite vers la page de confirmation de commande

        return $_SESSION['stripe_payment'][$client_secret];
	}

}

