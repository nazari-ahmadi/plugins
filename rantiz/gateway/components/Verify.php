<?php namespace Rantiz\Gateway\Components;

use Cms\Classes\Page;
use Cms\Classes\ComponentBase;
use Gateway;
use Lang;
use Redirect;
use ApplicationException;
use Flash;
use Carbon\Carbon;
use Ls\Details\Models\ProccessRegisterUserCourse;
use Ls\Details\Models\Course;
use Auth;

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

            if($gateway->statusCode() == "0")
            {
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
}
