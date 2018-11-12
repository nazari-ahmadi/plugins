<?php 
namespace Rantiz\Gateway\Components;

use Cms\Classes\Page;
use Cms\Classes\ComponentBase;
use Gateway;
use Rantiz\Gateway\Models\Settings as GatewaySettings;
use Ls\Details\Models\Course;
use Ls\Details\Models\ProccessRegisterUserCourse;
use Redirect;
use Auth;
use Carbon\Carbon;
use ApplicationException;
use Session;


class Send extends ComponentBase
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
            'name'        => 'ارسال به درگاه',
            'description' => 'کامپوننت ارسال اطلاعات به درگاه بانک'
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
        ];
    }  

    public function onRun()
    {
        switch (GatewaySettings::get('enable_port')) {
            case GatewaySettings::MELLAT:
                $port = Gateway::mellat();
                break;
            case GatewaySettings::SADAD:
                $port = Gateway::sadad();
                break;
            case GatewaySettings::ZARINPAL:
                $port = Gateway::zarinpal();
                break;
            case GatewaySettings::PAYLINE:
                $port = Gateway::payline();
                break;
            case GatewaySettings::PARSIAN:
                $port = Gateway::parsian();
                break;
            case GatewaySettings::JAHANPAY:
                $port = Gateway::jahanpay();
                break;
            case GatewaySettings::PASARGAD:
                $port = Gateway::pasargad();
                break;
            case GatewaySettings::SAMAN:
                $port = Gateway::saman();
                break;
            default:
                $port = Gateway::mellat();
                break;
        }

        if(!$price = Session::get('price'))
        {
            //return Redirect::to(url(''));
            throw new ApplicationException("Error Processing Request", 1);            
        }

        try {
            $gateway = $port;
            $gateway->setCallback(url('payment'));
            $gateway->price($price)->ready();          
            return $gateway->redirect();

        } catch (Exception $e) {

            echo $e->getMessage();
        } 
    }
}
