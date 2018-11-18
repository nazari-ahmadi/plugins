<?php namespace Rantiz\Gateway\Components;

use Cms\Classes\ComponentBase;
use Exception;
use Flash;
use Gateway;
use Lang;
use Redirect;
use Auth;
use Sepehr\Service\Components\Wallet;
use Sepehr\Service\Models\Service;
use Session;

class Verify extends ComponentBase
{
    /**
     * @var RainLab\Blog\Models\Post The post model used for display.
     */
    public $post;

    /**
     * @var string Reference to the page name for linking to categories.
     */
    public $categoryPage;

    public function componentDetails()
    {
        return [
            'name'        => 'نتیجه درگاه',
            'description' => 'مشاهده نتیجه برگشتی از سمت درگاه بانک'
        ];
    }

    public function defineProperties()
    {
        return [
            // 'id' => [
            //     'title'       => 'مشخصه سفارش',
            //     'description' => 'سفارش با مشخصه وارد شده جستجو میشود.',
            //     'default'     => '{{ :id }}',
            //     'type'        => 'string'
            // ],
            // 'panel' => [
            //     'title'       => 'نوع سفارش',
            //     'description' => 'انتخاب نوع سفارشی که قرار است نتیجه آن مشاهده شود',
            //     'type'        => 'dropdown',
            //     'default'     => '1',
            //     'placeholder' => 'انتخاب کنید',
            //     'options'     => ['1'=>'ثبت نام در دوره', '2'=>'یادگیری الکترونیک']
            // ],            
        ];
    }  

    public function onRun()
    {
        $id = $_GET['transaction_id'];

        try {
     
            $gateway = Gateway::verify($id);

           // throw new \ApplicationException($gateway->statusCode());

            if($gateway == "refresh")
            {
                return Redirect::to(url(""));
            }
            else if($gateway == "InvalidRequestException")
            {
                return Redirect::to(url("bankError"))->withErrors(["code" => 2, "title" => "اطلاعات بازگشتی از بانک صحیح نمی باشد"]);
            }
            else if($gateway == "NotFoundTransactionException")
            {
                return Redirect::to(url("bankError"))->withErrors(["code" => 3, "title" => "چنین رکورد پرداختی موجود نمی باشد"]);                                        
            }
            if($gateway->statusCode() == null)
            {
                $this->checkIfPay($gateway);
                $this->page['trackingCode']  = $gateway->trackingCode();
                $this->page['refId']         = $gateway->refId();
                $this->page['cardNumber']    = $gateway->cardNumber();
                $this->page['amount']        = $gateway->amount();
                $this->page['transactionID'] = $gateway->transactionId();
                $this->page['port']          = $gateway->getPortName();
                $this->page['message']       = Lang::get('rantiz.gateway::lang.components.success_message');
            }
            else if($gateway->statusCode() == "-1")
            {
                return Redirect::to(url("bankError"))->withErrors(["code" => 1, "title" => "انصراف از پرداخت"]);  
            }
            else
            {
                return Redirect::to(url(""));
            }

        } catch (Exception $e) {
     
            echo $e->getMessage();
        }  
    }

    public function checkIfPay($gateway)
    {
        $wallet=new Wallet();
        $user=Auth::getUser();
        $id=Session::get('servicePay');
        $service=Service::find($id);
        if ($id!=null){
            if ($wallet->PayService($service)){
                Session::forget('servicePay');
                Flash::success('پرداخت با موفقیت انجام شد');
            }else{
            $amount=$gateway->amount();
            $payList=$service->payments;
            $payList[]=['payment_status_id'=>4 , 'amount'=>$amount,'payment_date'=>''];
            $service->payments=$payList;
            $service->payment_status=1;
            $service->forceSave();
            $user->wallet_charge = 0;
            $user->forceSave();
            Session::forget('servicePay');
            Flash::success('پرداخت با موفقیت انجام شد');
            }
        }else{
            $user->wallet_charge += $gateway->amount();
            $user->forceSave();
        }
    }
}
