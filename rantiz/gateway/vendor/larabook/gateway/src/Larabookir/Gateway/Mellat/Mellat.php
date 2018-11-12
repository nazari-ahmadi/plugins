<?php

namespace Larabookir\Gateway\Mellat;

use DateTime;
use Illuminate\Support\Facades\Input;
use Larabookir\Gateway\Enum;
use SoapClient;
use Larabookir\Gateway\PortAbstract;
use Larabookir\Gateway\PortInterface;
use Rantiz\Gateway\Models\Settings as GatewaySettings;

class Mellat extends PortAbstract implements PortInterface
{
	/**
	 * Address of main SOAP server
	 *
	 * @var string
	 */
	protected $serverUrl = 'https://bpm.shaparak.ir/pgwchannel/services/pgw?wsdl';

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
		$refId = $this->refId;

		return view('gateway::mellat-redirector')->with(compact('refId'));
	}

	/**
	 * {@inheritdoc}
	 */
	public function verify($transaction)
	{
		parent::verify($transaction);

		$this->userPayment();
		$this->verifyPayment();
		$this->settleRequest();

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
		return $this->makeCallback($this->callbackUrl, ['transaction_id' => $this->transactionId()]);
	}

	/**
	 * Send pay request to server
	 *
	 * @return void
	 *
	 * @throws MellatException
	 */
	protected function sendPayRequest()
	{
		$dateTime = new DateTime();

		$this->newTransaction();

		$fields = array(
			'terminalId' => GatewaySettings::get('mellat_terminalId'),
			'userName' => GatewaySettings::get('mellat_username'),
			'userPassword' => GatewaySettings::get('mellat_password'),
			'orderId' => $this->transactionId(),
			'amount' => $this->amount,
			'localDate' => $dateTime->format('Ymd'),
			'localTime' => $dateTime->format('His'),
			'additionalData' => '',
			'callBackUrl' => $this->getCallback(),
			'payerId' => 0,
		);

		try {
			$options = array(
			    'cache_wsdl' => 0,
			    'trace' => 1,
			    'stream_context' => stream_context_create(array(
			          'ssl' => array(
			               'verify_peer' => false,
			                'verify_peer_name' => false,
			                'allow_self_signed' => true
			          )
			    ))
			);

			$soap = new SoapClient($this->serverUrl, $options);
			$response = $soap->bpPayRequest($fields);

		} catch (\SoapFault $e) {
			$this->transactionFailed();
			$this->newLog('SoapFault', $e->getMessage());
			throw $e;
		}

		$response = explode(',', $response->return);

		if ($response[0] != '0') {
			$this->transactionFailed();
			$this->newLog($response[0], MellatException::$errors[$response[0]]);
			throw new MellatException($response[0]);
		}
		$this->refId = $response[1];
		$this->transactionSetRefId();
	}

	/**
	 * Check user payment
	 *
	 * @return bool
	 *
	 * @throws MellatException
	 */
	protected function userPayment()
	{
		$this->refId = Input::get('RefId');
		$this->trackingCode = Input::get('SaleReferenceId');
		$this->cardNumber = Input::get('CardHolderPan');
		$payRequestResCode = Input::get('ResCode');

		if ($payRequestResCode == '0') {
			return true;
		}

		$this->transactionFailed();
		$this->newLog($payRequestResCode, @MellatException::$errors[$payRequestResCode]);
		throw new MellatException($payRequestResCode);
	}

	/**
	 * Verify user payment from bank server
	 *
	 * @return bool
	 *
	 * @throws MellatException
	 * @throws SoapFault
	 */
	protected function verifyPayment()
	{
		$fields = array(
			'terminalId' => GatewaySettings::get('mellat_terminalId'),
			'userName' => GatewaySettings::get('mellat_username'),
			'userPassword' => GatewaySettings::get('mellat_password'),
			'orderId' => $this->transactionId(),
			'saleOrderId' => $this->transactionId(),
			'saleReferenceId' => $this->trackingCode()
		);

		try {
			$soap = new SoapClient($this->serverUrl);
			$response = $soap->bpVerifyRequest($fields);

		} catch (\SoapFault $e) {
			$this->transactionFailed();
			$this->newLog('SoapFault', $e->getMessage());
			throw $e;
		}

		if ($response->return != '0') {
			$this->transactionFailed();
			$this->newLog($response->return, MellatException::$errors[$response->return]);
			throw new MellatException($response->return);
		}

		return true;
	}

	/**
	 * Send settle request
	 *
	 * @return bool
	 *
	 * @throws MellatException
	 * @throws SoapFault
	 */
	protected function settleRequest()
	{
		$fields = array(
			'terminalId' => GatewaySettings::get('mellat_terminalId'),
			'userName' => GatewaySettings::get('mellat_username'),
			'userPassword' => GatewaySettings::get('mellat_password'),
			'orderId' => $this->transactionId(),
			'saleOrderId' => $this->transactionId(),
			'saleReferenceId' => $this->trackingCode
		);

		try {
			$soap = new SoapClient($this->serverUrl);
			$response = $soap->bpSettleRequest($fields);

		} catch (\SoapFault $e) {
			$this->transactionFailed();
			$this->newLog('SoapFault', $e->getMessage());
			throw $e;
		}

		if ($response->return == '0' || $response->return == '45') {
			$this->transactionSucceed();
			$this->newLog($response->return, Enum::TRANSACTION_SUCCEED_TEXT);
			return true;
		}

		$this->transactionFailed();
		$this->newLog($response->return, MellatException::$errors[$response->return]);
		throw new MellatException($response->return);
	}
}
