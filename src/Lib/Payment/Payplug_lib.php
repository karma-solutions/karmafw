<?php

namespace KarmaFW\Lib\Payment;

//use \Payplug\Payplug;


class Payplug_lib
{
	// infos API: https://docs.payplug.com/api/paymentpage.html

	public static function getKeys($type=null)
	{
		if (in_array(PAYPLUG_ENV, ['LIVE', 'PROD'])) {
			if (! defined('PAYPLUG_LIVE_PUBLIC_KEY') || ! defined('PAYPLUG_LIVE_SECRET_KEY')) {
				return false;
			}
			$payplug_public_key = PAYPLUG_LIVE_PUBLIC_KEY;
			$payplug_secret_key = PAYPLUG_LIVE_SECRET_KEY;

		} else {
			if (! defined('PAYPLUG_TEST_PUBLIC_KEY') || ! defined('PAYPLUG_TEST_SECRET_KEY')) {
				return false;
			}
			$payplug_public_key = PAYPLUG_TEST_PUBLIC_KEY;
			$payplug_secret_key = PAYPLUG_TEST_SECRET_KEY;
		}
		if ($type == 'public') 	{
			return $payplug_public_key;
		}
		if ($type == 'secret') 	{
			return $payplug_secret_key;
		}
		return [
			'public' => $payplug_public_key,
			'secret' => $payplug_secret_key,
		];
	}


	public static function getCustomerData($client, $shipping_address, $delivery_type=null)
	{
		$payment_data = [
			'title'               => ($client['gender'] == 'female') ? 'mrs' : 'mr',
			'first_name'          => $client['firstname'],
			'last_name'           => $client['firstname'],
			'mobile_phone_number' => $client['phone'],
			'email'               => $client['email'],
			'address1'            => $shipping_address['address'],
			'postcode'            => $shipping_address['zipcode'],
			'city'                => $shipping_address['city'],
			'country'             => 'FR',
			'language'            => 'fr'
		];

		if ($delivery_type) {
			$payment_data['delivery_type'] = $delivery_type;
		}

		return $payment_data;
	}


	/* API Hosted page */

	public static function paymentPayplugHosted($client, $shipping_address, $total_price_ttc, $order_id=null)
	{

		if (substr($client['phone'], 0, 1) == '0') {
			$client['phone'] = '+33' . substr($client['phone'], 1);
		}
		//pre($client, 1);

		$payplug_secret_key = self::getKeys('secret');
		\Payplug\Payplug::setSecretKey($payplug_secret_key);

		$payment_data = [
			'amount'           => $total_price_ttc * 100,
			'currency'         => 'EUR',
			'save_card'        => false,
			'billing'          => self::getCustomerData($client, $shipping_address),
			'shipping'        => self::getCustomerData($client, $shipping_address, 'BILLING'),
			'hosted_payment' => [
				'return_url'   => 'http://' . $_SERVER['SERVER_NAME'] . '/payment/payplug/payment-ok?id=' . $order_id,
				'cancel_url'   => 'http://' . $_SERVER['SERVER_NAME'] . '/payment/payplug/payment-cancel?id=' . $order_id,
			],
			'notification_url' => 'http://' . $_SERVER['SERVER_NAME'] . '/payment/payplug/payment-notification?id=' . $order_id,
			'metadata'      => [
				'order_id' => $order_id,
			]
		];
		//pre($payment_data, 1);

		$payment = \Payplug\Payment::create($payment_data);

		$payment_url = $payment->hosted_payment->payment_url;
		$payment_id = $payment->id;

		return [
			'payment_url' => $payment_url,
			'payment_id' => $payment_id,
		];
	}



	/* API Lightbox */
	public static function paymentPayplugLightbox($client, $shipping_address, $total_price_ttc, $order_id=null)
	{

		if (substr($client['phone'], 0, 1) == '0') {
			$client['phone'] = '+33' . substr($client['phone'], 1);
		}
		//pre($client, 1);

		$payplug_secret_key = self::getKeys('secret');
		\Payplug\Payplug::setSecretKey($payplug_secret_key);


		$payment = \Payplug\Payment::create(
			[
			    'amount'         => $total_price_ttc * 100,
			    'currency'       => 'EUR',
			    'billing'        => self::getCustomerData($client, $shipping_address),
			    'shipping'       => self::getCustomerData($client, $shipping_address, 'BILLING'),
			    'hosted_payment' => array(
			        'return_url' => 'http://' . $_SERVER['SERVER_NAME'] . '/payment/payplug/payment-ok?id=' . $order_id,
			        'cancel_url' => 'http://' . $_SERVER['SERVER_NAME'] . '/payment/payplug/payment-cancel?id=' . $order_id,
			    ),
			    'notification_url' => 'http://' . $_SERVER['SERVER_NAME'] . '/payment/payplug/payment-notification?id=' . $order_id,
			]
		);

		return $payment;
	}


	/* API payplug.js */
	public static function paymentPayplugPayplugJs($token, $client, $shipping_address, $total_price_ttc, $order_id=null)
	{
		if (substr($client['phone'], 0, 1) == '0') {
			$client['phone'] = '+33' . substr($client['phone'], 1);
		}

		$payplug_secret_key = self::getKeys('secret');
		\Payplug\Payplug::setSecretKey($payplug_secret_key);

		$payment = \Payplug\Payment::create(array(
			'amount'         => $total_price_ttc * 100,
			'currency'       => 'EUR',
			'payment_method' => $token,
			'billing'        => self::getCustomerData($client, $shipping_address),
			'shipping'       => self::getCustomerData($client, $shipping_address, 'BILLING'),
			'notification_url' => 'http://karma:dev@' . $_SERVER['SERVER_NAME'] . '/payment/payplug/payment-notification?id=' . $order_id,
		));

		return $payment;
	}



}

