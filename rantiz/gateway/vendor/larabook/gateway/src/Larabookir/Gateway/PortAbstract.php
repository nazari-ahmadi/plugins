<?php
namespace Larabookir\Gateway;


use Illuminate\Support\Facades\Request;
use Larabookir\Gateway\Enum;
use Rantiz\Gateway\Models\Transaction;
use Rantiz\Gateway\Models\Log;
use Carbon\Carbon;
use Auth;
use DB;
use ApplicationException;

abstract class PortAbstract
{
	/**
	 * Transaction id
	 *
	 * @var null|int
	 */
	protected $transactionId = null;

	/**
	 * Transaction row in database
	 */
	protected $transaction = null;

	/**
	 * Customer card number
	 *
	 * @var string
	 */
	protected $cardNumber = '';

	/**
	 * @var Config
	 */
	protected $config;

	/**
	 * Port id
	 *
	 * @var int
	 */
	protected $portName;

	/**
	 * Reference id
	 *
	 * @var string
	 */
	protected $refId;

	/**
	 * Amount in Rial
	 *
	 * @var int
	 */
	protected $amount;

	protected $userInfo;
	protected $helpTypeId;

	/**
	 * callback URL
	 *
	 * @var url
	 */
	protected $callbackUrl;

	/**
	 * Tracking code payment
	 *
	 * @var string
	 */
	protected $trackingCode;

	/**
	 * Status code payment
	 *
	 * @var string
	 */
	protected $statusCode;

	/**
	 * Initialize of class
	 *
	 * @param Config $config
	 * @param DataBaseManager $db
	 * @param int $port
	 */
	function __construct()
	{
		$this->db = app('db');
	}

	/** bootstraper */
	function boot(){
		date_default_timezone_set('Asia/Tehran');
	}

	function setConfig($config)
	{
		$this->config = $config;
	}

	/**
	 * Get port id, $this->port
	 *
	 * @return int
	 */
	function getPortName()
	{
		return $this->portName;
	}

	/**
	 * Get port id, $this->port
	 *
	 * @return int
	 */
	function setPortName($name)
	{
		$this->portName = $name;
	}

	/**
	 * Return card number
	 *
	 * @return string
	 */
	function cardNumber()
	{
		return $this->cardNumber;
	}

	/**
	 * Return tracking code
	 */
	function trackingCode()
	{
		return $this->trackingCode;
	}

	/**
	 * Return status code
	 */
	function statusCode()
	{
		return $this->statusCode;
	}

	/**
	 * Get transaction id
	 *
	 * @return int|null
	 */
	function transactionId()
	{
		return $this->transactionId;
	}

	/**
	 * Return reference id
	 */
	function refId()
	{
		return $this->refId;
	}

	/**
	 * Sets price
	 * @param $price
	 * @return mixed
	 */
	function price($price)
	{
		return $this->set($price);
	}

	/**
	 * Return amount
	 */
	function amount()
	{
		return $this->amount;
	}

	/**
	 * Return result of payment
	 * If result is done, return true, otherwise throws an related exception
	 *
	 * This method must be implements in child class
	 *
	 * @param object $transaction row of transaction in database
	 *
	 * @return $this
	 */
	function verify($transaction)
	{
		$this->transaction = $transaction;
		$this->transactionId = $transaction->id;
		$this->amount = intval($transaction->price);
		$this->refId = $transaction->ref_id;
	}

	function getTimeId()
	{
		$genuid = function(){
			return substr(str_pad(str_replace('.','', microtime(true)),10,0),0,10);
		};
		$uid=$genuid();
		while (Transaction::whereId($uid)->first())
			$uid = $genuid();
		return $uid;
	}

	/**
	 * Insert new transaction to poolport_transactions table
	 *
	 * @return int last inserted id
	 */
	protected function newTransaction()
	{
		$userID = NULL;
		if($user = Auth::getUser())
		{
			$userID = $user->id;
		}

		$transaction = new Transaction;
		// $transaction->id = $uid;
		$transaction->user_id 	= $userID;
		$transaction->port 		= $this->getPortName();
		$transaction->price 	= $this->amount;
		$transaction->status 	= Enum::TRANSACTION_INIT;
		$transaction->ip 		= Request::getClientIp();
		$this->transactionId 	= $transaction->save() ? $transaction->id : null;
		return $this->transactionId;
	}

	/**
	 * Commit transaction
	 * Set status field to success status
	 *
	 * @return bool
	 */
	protected function transactionSucceed()
	{
		$transaction = Transaction::whereId($this->transactionId)->first();
		if($transaction)
		{
			$transaction->status = Enum::TRANSACTION_SUCCEED;
			$transaction->tracking_code = $this->trackingCode;
			$transaction->card_number = $this->cardNumber;
			$transaction->payment_date = Carbon::now();
			$transaction->updated_at = Carbon::now();
			return $transaction->save();
		}
		return false;
	}

	/**
	 * Failed transaction
	 * Set status field to error status
	 *
	 * @return bool
	 */
	protected function transactionFailed()
	{

		$transaction = Transaction::whereId($this->transactionId)->first();
		if($transaction)
		{
			$transaction->status = Enum::TRANSACTION_FAILED;
			$transaction->updated_at = Carbon::now();
			return $transaction->save();
		}
		return false;
	}

	/**
	 * Update transaction refId
	 *
	 * @return void
	 */
	protected function transactionSetRefId()
	{
		$transaction = Transaction::whereId($this->transactionId)->first();
		if($transaction)
		{
			$transaction->ref_id = $this->refId;
			$transaction->updated_at = Carbon::now();
			return $transaction->save();
		}
		return false;	
	}

	/**
	 * New log
	 *
	 * @param string|int $statusCode
	 * @param string $statusMessage
	 */
	protected function newLog($statusCode, $statusMessage)
	{
		$log = new Log;
		$log->transaction_id = $this->transactionId;
		$log->result_code = $statusCode;
		$log->result_message = $statusMessage;
		$log->log_date = Carbon::now();
		return $log->save();
	}

	/**
	 * Add query string to a url
	 *
	 * @param string $url
	 * @param array $query
	 * @return string
	 */
	protected function makeCallback($url, array $query)
	{
		return $this->url_modify(array_merge($query), url($url));
	}

	/**
	 * manipulate the Current/Given URL with the given parameters
	 * @param $changes
	 * @param  $url
	 * @return string
	 */
	protected function url_modify($changes, $url)
	{
		// Parse the url into pieces
		$url_array = parse_url($url);

		// The original URL had a query string, modify it.
		if (!empty($url_array['query'])) {
			parse_str($url_array['query'], $query_array);
			$query_array = array_merge($query_array, $changes);
		} // The original URL didn't have a query string, add it.
		else {
			$query_array = $changes;
		}

		return (!empty($url_array['scheme']) ? $url_array['scheme'] . '://' : null) .
		(!empty($url_array['host']) ? $url_array['host'] : null) .
		$url_array['path'] . '?' . http_build_query($query_array);
	}
}
