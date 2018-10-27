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
use RainLab\User\Models\Settings as UserSettings;
use RainLab\User\Models\User;
use Rantiz\SMS\Models\Settings;
use Rantiz\SMS\Models\Template;
use Sepehr\Details\Models\CountryCode;
use Exception;
use Carbon\Carbon;
use Kavenegar;

class Register extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name'        => 'فرم ثبت نام',
            'description' => 'کامپوننت فرم ثبت نام'
        ];
    }

    public function defineProperties()
    {
        return [
            'redirect' => [
                'title'       => /*Redirect to*/'rainlab.user::lang.account.redirect_to',
                'description' => /*Page name to redirect to after update, sign in or registration.*/'rainlab.user::lang.account.redirect_to_desc',
                'type'        => 'dropdown',
                'default'     => ''
            ],
            'paramCode' => [
                'title'       => /*Activation Code Param*/'rainlab.user::lang.account.code_param',
                'description' => /*The page URL parameter used for the registration activation code*/ 'rainlab.user::lang.account.code_param_desc',
                'type'        => 'string',
                'default'     => 'code'
            ],
            'forceSecure' => [
                'title'       => /*Force secure protocol*/'rainlab.user::lang.account.force_secure',
                'description' => /*Always redirect the URL with the HTTPS schema.*/'rainlab.user::lang.account.force_secure_desc',
                'type'        => 'checkbox',
                'default'     => 0
            ],
        ];
    }

    public function getRedirectOptions()
    {
        return [''=>'- refresh page -', '0' => '- no redirect -'] + Page::sortBy('baseFileName')->lists('baseFileName', 'baseFileName');
    }

    /**
     * Executed when this component is initialized
     */
    public function prepareVars()
    {
        $this->page['user']                 = $this->user();
        $this->page['canRegister']          = $this->canRegister();
        $this->page['loginAttribute']       = $this->loginAttribute();
        $this->page['loginAttributeLabel']  = $this->loginAttributeLabel();
        $this->page['countryCodes']         = CountryCode::orderBy('name', 'ASC')->get(); 
        $this->page['countryCodeDefault']   = CountryCode::whereIsDefault(1)->first();         
    }    

    /**
     * Executed when this component is bound to a page or layout.
     */
    public function onRun()
    {
        /*
         * Redirect to HTTPS checker
         */
        if ($redirect = $this->redirectForceSecure()) {
            return $redirect;
        }

        /*
         * Activation code supplied
         */
        if ($code = $this->activationCode()) {
            $this->onActivate($code);
        }

        $this->prepareVars();
    }

    /**
     * Returns the logged in user, if available
     */
    public function user()
    {
        if (!Auth::check()) {
            return null;
        }

        return Auth::getUser();
    }

    /**
     * Flag for allowing registration, pulled from UserSettings
     */
    public function canRegister()
    {
        return UserSettings::get('allow_registration', true);
    }        

    /**
     * Returns the login model attribute.
     */
    public function loginAttribute()
    {
        return UserSettings::get('login_attribute', UserSettings::LOGIN_EMAIL);
    }

    /**
     * Returns the login label as a word.
     */
    public function loginAttributeLabel()
    {
        return Lang::get($this->loginAttribute() == UserSettings::LOGIN_EMAIL
            ? /*Email*/'rainlab.user::lang.login.attribute_email'
            : /*Username*/'rainlab.user::lang.login.attribute_username'
        );
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
     * Register the user
     */
    public function onRegister()
    {
        try {
            if (!$this->canRegister()) {
                throw new ApplicationException(Lang::get(/*Registrations are currently disabled.*/'rainlab.user::lang.account.registration_disabled'));
            }

            /*
             * Validate input
             */
            $data = post();
            
            if (!array_key_exists('password_confirmation', $data)) {
                $data['password_confirmation'] = post('password');
            }

            if(isset($data['username']) && !empty($data['username']))
            {
                if(!User::validationNationalCode($data['username']))
                {
                    throw new ValidationException(['username' => 'فرمت کد ملی وارد شده نامعتبر است.']);        
                }            
            }

            // $rules = [
            //     'firstName'             => 'required|max:50',
            //     'lastName'              => 'required|max:50',                
            //     'email'                 => 'required|between:6,50|email|unique:users',   
            //     'password'              => 'required:create|between:4,50',
            //     'mobile'                => ['required', 'regex:/^([9]{1})+[0-9]{9}$/i', 'unique:users'],
            //     'country_code'          => 'required',
            // ];

            // if ($this->loginAttribute() == UserSettings::LOGIN_USERNAME) {
            //     $rules['username'] = 'required|between:2,50|unique:users';
            // }            

            // $message = [
            //     'firstName.required'       => 'لطفا نام خود را وارد کنید.',
            //     'firstName.max'            => 'نام نمی تواند بیشتر از 50 حرف باشد.',
            //     'lastName.required'        => 'لطفا نام خانوادگی خود را وارد کنید.',
            //     'lastName.max'             => 'نام خانوادگی نمی تواند بیشتر از 50 حرف باشد.',
            //     'mobile.required'          => 'لطفا شماره موبایل خود را وارد کنید.',
            //     'mobile.regex'             => 'فرمت شماره موبایل وارد شده نامعتبر است.',
            //     'mobile.unique'            => 'شماره موبایل وارد شده تکراری می باشد.',
            //     'username.required'        => 'لطفا نام کاربری را وارد کنید.',
            //     'username.unique'          => 'نام کاربری وارد شده تکراری می باشد.',     
            //     'username.between'         => 'نام کاربری حداقل باید 2 کاراکتر و حداکثر 50 کاراکتر باشد.',  
            //     'email.required'           => 'لطفا پست الکترونیکی خود را وارد کنید.',
            //     'email.between'            => 'پست الکترونیکی باید مابین 6 و 50 کاراکتر باشد.',
            //     'email.email'              => 'قالب پست الکترونیکی نامعتبر است.',
            //     'email.unique'             => 'پست الکترونیکی وارد شده تکراری می باشد.',
            //     'password.required'        => 'لطفا رمز عبور خود را وارد کنید.',
            //     'password.between'         => 'رمز عبور باید مابین 4 و 50 کاراکتر باشد.',
            // ];

            // $validation = Validator::make($data, $rules, $message);
            // if ($validation->fails()) {
            //     throw new ValidationException($validation);
            // }         

            /*
             * Register user
             */
            Event::fire('rainlab.user.beforeRegister', [&$data]);

            $requireActivation = UserSettings::get('require_activation', true);
            $automaticActivation = UserSettings::get('activate_mode') == UserSettings::ACTIVATE_AUTO;
            $userActivationEmail = UserSettings::get('activate_mode') == UserSettings::ACTIVATE_USER_EMAIL;
            $userActivationSMS = UserSettings::get('activate_mode') == UserSettings::ACTIVATE_USER_SMS;
            $user = Auth::register($data, $automaticActivation);

            Event::fire('rainlab.user.register', [$user, $data]);

            /*
             * Activation is by the user, send the email
             */
            if ($userActivationEmail) {
                $this->sendActivationEmail($user);

                Flash::success(Lang::get(/*An activation email has been sent to your email address.*/'rainlab.user::lang.account.activation_email_sent'));
            }

            /*
             * Activation is by the user, send the sms
             */
            if ($userActivationSMS) {
                $this->sendActivationSMS($user);

                Flash::success(Lang::get(/*An activation sms has been sent to your mobile number.*/'rainlab.user::lang.account.activation_sms_sent'));

                return Redirect::to(url('activation'))->with(['mobile' => $user->mobile]);
            }

            /*
             * Automatically activated or not required, log the user in
             */
            if ($automaticActivation || !$requireActivation) {
                Auth::login($user);
            }            

            /*
             * Redirect to the intended page after successful sign in
             */
            $redirectUrl = $this->pageUrl($this->property('redirect'))
                ?: $this->property('redirect');

            if ($redirectUrl = post('redirect', $redirectUrl)) {
                Flash::success('حساب شما با موفقیت ایجاد شد. منتظر فعال سازی توسط مدیر سیستم باشید.');
                return Redirect::intended($redirectUrl);
            }

        }
        catch (Exception $ex) {
            if (Request::ajax()) throw $ex;
            else Flash::error($ex->getMessage());
        }
    }

    /**
     * Activate the user
     * @param  string $code Activation code
     */
    public function onActivate($code = null)
    {
        try {
            $code = post('code', $code);

            /*
             * Break up the code parts
             */
            $parts = explode('!', $code);
            if (count($parts) != 2) {
                throw new ValidationException(['code' => Lang::get('rainlab.user::lang.account.invalid_activation_code')]);
            }

            list($userId, $code) = $parts;

            if (!strlen(trim($userId)) || !($user = Auth::findUserById($userId))) {
                throw new ApplicationException(Lang::get('rainlab.user::lang.account.invalid_user'));
            }

            if (!$user->attemptActivation($code)) {
                throw new ValidationException(['code' => Lang::get('rainlab.user::lang.account.invalid_activation_code')]);
            }

            Flash::success(Lang::get('rainlab.user::lang.account.success_activation'));

            /*
             * Sign in the user
             */
            Auth::login($user);

        }
        catch (Exception $ex) {
            if (Request::ajax()) throw $ex;
            else Flash::error($ex->getMessage());
        }
    }

    /**
     * Trigger a subsequent activation email
     */
    public function onSendActivationEmail()
    {
        try {
            if (!$user = $this->user()) {
                throw new ApplicationException(Lang::get('rainlab.user::lang.account.login_first'));
            }

            if ($user->is_activated) {
                throw new ApplicationException(Lang::get('rainlab.user::lang.account.already_active'));
            }

            Flash::success(Lang::get('rainlab.user::lang.account.activation_email_sent'));

            $this->sendActivationEmail($user);

        }
        catch (Exception $ex) {
            if (Request::ajax()) throw $ex;
            else Flash::error($ex->getMessage());
        }

        /*
         * Redirect
         */
        if ($redirect = $this->makeRedirection()) {
            return $redirect;
        }
    }

    /**
     * Sends the activation email to a user
     * @param  User $user
     * @return void
     */
    protected function sendActivationEmail($user)
    {
        $code = implode('!', [$user->id, $user->getActivationCode()]);
        $link = $this->currentPageUrl([
            $this->property('paramCode') => $code
        ]);

        $data = [
            'name' => $user->name,
            'link' => $link,
            'code' => $code
        ];

        Mail::send('rainlab.user::mail.activate', $data, function($message) use ($user) {
            $message->to($user->email, $user->name);
        });
    }

    public function getActivationSMSCode($user)
    {
        $user->activation_code = $randomCode = mt_rand(10000,99999);
        $user->activation_send_code_time = strtotime(Carbon::now());
        $user->forceSave();

        return $randomCode;        
    }

    /**
     * Sends the activation sms to a user
     * @param  User $user
     * @return void
     */
    protected function sendActivationSMS($user)
    {
        $code = $this->getActivationSMSCode($user);

        $body = Template::whereCode('send-active-code')->first()->body;
        $bodyMessage = sprintf($body, $code);

        $sender = Settings::get('send_line');
        $receptor = $user->country_code . $user->mobile;
        $result = Kavenegar::Send($sender,$receptor,$bodyMessage);        
    }

    /**
     * Redirect to the intended page after successful update, sign in or registration.
     * The URL can come from the "redirect" property or the "redirect" postback value.
     * @return mixed
     */
    protected function makeRedirection($intended = false)
    {
        $method = $intended ? 'intended' : 'to';

        $property = $this->property('redirect');

        if (strlen($property) && !$property) {
            return;
        }

        $redirectUrl = $this->pageUrl($property) ?: $property;

        if ($redirectUrl = post('redirect', $redirectUrl)) {
            return Redirect::$method($redirectUrl);
        }
    }

    /**
     * Checks if the force secure property is enabled and if so
     * returns a redirect object.
     * @return mixed
     */
    protected function redirectForceSecure()
    {
        if (
            Request::secure() ||
            Request::ajax() ||
            !$this->property('forceSecure')
        ) {
            return;
        }

        return Redirect::secure(Request::path());
    }
}
