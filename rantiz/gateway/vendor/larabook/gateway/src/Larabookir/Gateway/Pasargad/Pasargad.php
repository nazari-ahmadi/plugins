<?php

namespace Larabookir\Gateway\Pasargad;

use Illuminate\Support\Facades\Input;
use Larabookir\Gateway\Enum;
use SoapClient;
use Larabookir\Gateway\PortAbstract;
use Larabookir\Gateway\PortInterface;
use Symfony\Component\VarDumper\Dumper\DataDumperInterface;
use Rantiz\Gateway\Models\Settings as GatewaySettings;

class Pasargad extends PortAbstract implements PortInterface
{
	/**
	 * Url of parsian gateway web service
	 *
	 * @var string
	 */

	protected $checkTransactionUrl = 'https://pep.shaparak.ir/CheckTransactionResult.aspx';
	protected $verifyUrl = 'https://pep.shaparak.ir/VerifyPayment.aspx';
	protected $refundUrl = 'https://pep.shaparak.ir/doRefund.aspx';

	/**
	 * Address of gate for redirect
	 *
	 * @var string
	 */
	protected $gateUrl = 'https://pep.shaparak.ir/gateway.aspx';

	/**
	 * {@inheritdoc}
	 */
	public function set($amount)
	{
		$this->amount = intval($amount);
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
		
        $processor = new RSAProcessor(storage_path(GatewaySettings::get('pasargad_certificate_path')),RSAKeyType::XMLFile);

		$url = $this->gateUrl;
		$redirectUrl = $this->getCallback();
        $invoiceNumber = $this->transactionId();
        $amount = $this->amount;
        $terminalCode = GatewaySettings::get('pasargad_terminalId');
        $merchantCode = GatewaySettings::get('pasargad_merchant');
        $timeStamp = date("Y/m/d H:i:s");
        $invoiceDate = date("Y/m/d H:i:s");
        $action = 1003;
        $data = "#". $merchantCode ."#". $terminalCode ."#". $invoiceNumber ."#". $invoiceDate ."#". $amount ."#". $redirectUrl ."#". $action ."#". $timeStamp ."#";
        $data = sha1($data,true);
        $data =  $processor->sign($data); // امضاي ديجيتال
        $sign =  base64_encode($data); // base64_encode

		return view('gateway::pasargad-redirector')->with(compact('url','redirectUrl','invoiceNumber','invoiceDate','amount','terminalCode','merchantCode','timeStamp','action','sign'));
	}

	/**
	 * {@inheritdoc}
	 */
	public function verify($transaction)
	{
		parent::verify($transaction);

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
		return $this->callbackUrl;
	}

	/**
	 * Send pay request to parsian gateway
	 *
	 * @return bool
	 *
	 * @throws ParsianErrorException
	 */
	protected function sendPayRequest()
	{
		$this->newTransaction();
	}

	/**
	 * Verify payment
	 *
	 * @throws ParsianErrorException
	 */
	protected function verifyPayment()
	{
        $fields = array(
            'invoiceUID' => Input::get('tref'),
        );

        $result = Parser::post2https($fields, $this->checkTransactionUrl);
        $array = Parser::makeXMLTree($result);


		if ($array['result'] != "True") {
			$this->newLog(-1, Enum::TRANSACTION_FAILED_TEXT);
			$this->transactionFailed();
			throw new PasargadErrorException(Enum::TRANSACTION_FAILED_TEXT, -1);
		}

        $this->refId = $array['transactionReferenceID'];
        $this->transactionSetRefId();

		$this->trackingCode = $array['traceNumber'];
		$this->transactionSucceed();
		$this->newLog($array['result'], Enum::TRANSACTION_SUCCEED_TEXT);
	}
}
