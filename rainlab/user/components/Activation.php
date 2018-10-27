<?php namespace RainLab\User\Components;

use Lang;
use Auth;
use Mail;
use Event;
use Flash;
use Input;
use Request;
use Redirect;
use Validator;
use ValidationException;
use ApplicationException;
use Cms\Classes\Page;
use Cms\Classes\ComponentBase;
use RainLab\User\Models\User;
use Exception;
use Session;
use Carbon\Carbon;
use Rantiz\SMS\Models\Settings;
use Rantiz\SMS\Models\Template;
use Sepehr\Details\Models\CountryCode;
use Kavenegar;

class Activation extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name'        => 'فعال سازی حساب کاربری',
            'description' => 'کامپوننت فعال سازی حساب کاربری'
        ];
    }

    public function defineProperties()
    {
        return [];
    } 

    /**
     * Executed when this component is bound to a page or layout.
     */
    public function onRun()
    {
        if(!$mobile = Session::get("mobile"))
        {
            $this->page["send"] = false;
        }
        else
        {
            $this->page["send"] = true;
            $this->page["mobile"] = $mobile;
        }

        $this->page['countryCodes']         = CountryCode::orderBy('name', 'ASC')->get(); 
        $this->page['countryCodeDefault']   = CountryCode::whereIsDefault(1)->first();         
    }

    /**
     * Looks for the activation code from the URL parameter. If nothing
     * is found, the GET parameter 'activate' is used instead.
     * @return string
     */
    public function activationCode()
    {
        $routeParameter = $this->property('paramCode');

        if ($code = $this->param($routeParameter)) {
            return $code;
        }

        return get('activate');
    }


    /**
     * Activate the user
     * @param  string $code Activation code
     */
    public function onSMSActivate()
    {
        try {

            if (!$code = post('code')) {
                throw new ValidationException(['code' => 'لطفا کد تأیید را وارد کنید.']);
            }

            $mobile = post("mobile");

            if(!$user = User::whereMobile($mobile)->first())
            {
                throw new ValidationException(['23432' => 'شماره موبایل وارد شده در سیستم سیستم ثبت نشده است.']);
            }

            if($user->is_activated)
            {
                throw new ValidationException(['3254324234' => 'حساب کاربری شما فعال است. لطفا از قسمت فرم ورود وارد سیستم شوید.']);
            }

            if($user->activation_code != $code)
            {
                throw new ValidationException(['code' => 'کد وارد شده صحیح نمی باشد.']);
            }

            if((strtotime(Carbon::now()) - $user->activation_send_code_time) > 60)
            {
                throw new ValidationException(['code' => 'کد تأییدیه منقضی شده است. <br> لطفا عملیات ارسال مجدد کد فعال سازی را انجام دهید.']);
            }

            $user->is_activated = 1;
            $user->activated_at = Carbon::now();
            $user->forceSave();

            Flash::success(Lang::get('rainlab.user::lang.account.success_activation'));

            /*
             * Sign in the user
             */
            Auth::login($user);

            return Redirect::to(url('profile'));
        }
        catch (Exception $ex) {
            if (Request::ajax()) throw $ex;
            else Flash::error($ex->getMessage());
        }
    }

    public function onSendSMSActivateCode()
    {       
        if(!$mobile = post('mobile'))
        {
            throw new ValidationException(['mobile' => 'لطفا شماره موبایل خود را وارد کنید.']);        
        } 

        if(!preg_match('/^([9]{1})+[0-9]{9}$/i',$mobile))
        {
            throw new ValidationException(['mobileNumber' => 'فرمت شماره موبایل وارد شده نامعتبر است.']);        
        }

        if(!$user = User::whereMobile($mobile)->first())
        {
            throw new ValidationException(['mobile' => 'شماره موبایل وارد شده در سیستم سیستم ثبت نشده است.']);
        }

        if($user->is_activated)
        {
            throw new ValidationException(['3254324234' => 'حساب کاربری شما فعال است. لطفا از قسمت فرم ورود وارد سیستم شوید.']);
        }

        $user->activation_code = $randomCode = mt_rand(10000,99999);
        $user->activation_send_code_time = strtotime(Carbon::now());
        $user->forceSave();

        $body = Template::whereCode('send-active-code')->first()->body;
        $bodyMessage = sprintf($body, $randomCode);

        $sender = Settings::get('send_line');
        $receptor = $user->country_code . $user->mobile;
        $result = Kavenegar::Send($sender,$receptor,$bodyMessage); 

        return Redirect::to(url('activation'))->with(['mobile' => $user->mobile]);      
    }  
}
