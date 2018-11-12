<?php

namespace Larabookir\Gateway\Payline;

use Illuminate\Support\Facades\Input;
use Larabookir\Gateway\Enum;
use Larabookir\Gateway\PortAbstract;
use Larabookir\Gateway\PortInterface;
use Rantiz\Gateway\Models\Settings as GatewaySettings;

class Payline extends PortAbstract implements PortInterface
{
	/**
	 * Address of main CURL server
	 *
	 * @var string
	 */
	protected $serverUrl = 'https://pay.ir/payment/send';

	/**
	 * Address of CURL server for verify payment
	 *
	 * @var string
	 */
	protected $serverVerifyUrl = 'https://pay.ir/payment/verify';

	/**
	 * Address of gate for redirect
	 *
	 * @var string
	 */
	protected $gateUrl = 'https://pay.ir/payment/gateway/';

	/**
	 * {@inheritdoc}
	 */
	public function set($amount)
	{
		$this->amount = $amount;

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function ready()
	{
		$this->sendPayRequest();

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function redirect()
	{
		return redirect()->to($this->gateUrl . $this->trackingCode);
	}

	/**
	 * {@inheritdoc}
	 */
	public function verify($transaction)
	{
		parent::verify($transaction);

		$this->userPayment();
		$this->verifyPayment();

		return $this;
	}

	/**
	 * Sets callback url
	 * @param $url
	 */
	function setCallback($url)
	{
		$this->callbackUrl = $url;
		return $this;
	}

	function setOrderId($id)
	{
		$this->orderId = $id;
		return $this;
	}
	
	/**
	 * Gets callback url
	 * @return string
	 */
	function getCallback()
	{
        if (!$this->callbackUrl)
            $this->callbackUrl = GatewaySettings::get('callback_url');

		return urlencode($this->makeCallback($this->callbackUrl, ['transaction_id' => $this->transactionId()]));
	}

	/**
	 * Send pay request to server
	 *
	 * @return void
	 *
	 * @throws PaylineSendException
	 */
	protected function sendPayRequest()
	{
		$this->newTransaction();

		$fields = array(
			'api' => GatewaySettings::get('payline_api'),
			'amount' => $this->amount,
			'redirect' => $this->getCallback(),
		);

		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $this->serverUrl);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($fields));
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		$response = curl_exec($ch);
		curl_close($ch);
		$response = json_decode($response);

		if (is_numeric($response->status) && $response->status> 0) {
			$this->trackingCode = $response->transId;
			$this->transactionSetRefId();

			return true;
		}

		$this->transactionFailed();
		$this->newLog($response->status, PaylineSendException::$errors[$response->status]);
		throw new PaylineSendException($response->status);
	}

	/**
	 * Check user payment with GET data
	 *
	 * @return bool
	 *
	 * @throws PaylineReceiveException
	 */
	protected function userPayment()
	{
		$trackingCode = Input::get('transId');

		if (is_numeric($trackingCode) && $trackingCode > 0) {
			$this->trackingCode = $trackingCode;
			return true;
		}

		$this->transactionFailed();
		$this->newLog(-4, PaylineReceiveException::$errors[-4]);
		throw new PaylineReceiveException(-4);
	}

	/**
	 * Verify user payment from zarinpal server
	 *
	 * @return bool
	 *
	 * @throws PaylineReceiveException
	 */
	protected function verifyPayment()
	{
		// echo 'error';exit;
		$fields = array(
			'api' => GatewaySettings::get('payline_api'),
			'transId' => $this->trackingCode()
		);

		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $this->serverVerifyUrl);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($fields));
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		$response = curl_exec($ch);
		curl_close($ch);

		$response = json_decode($response);

		if ($response->status == 1) {
			$this->transactionSucceed();
			$this->newLog($response->status, Enum::TRANSACTION_SUCCEED_TEXT);
			return true;
		}

		$this->transactionFailed();
		$this->newLog($response->status, PaylineReceiveException::$errors[$response->status]);
		throw new PaylineReceiveException($response->status);
	}
}
