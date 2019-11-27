<?php

namespace KarmaFW\Lib\Payment;

use \Stripe\Stripe;


class Stripe_lib
{

	public static function PaymentStripe($stripe_token, $customer_id=null, $payment_label='', $payment_price=0, $save_customer=false, $new_customer_email=null, $new_customer_description=null)
	{
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

}

