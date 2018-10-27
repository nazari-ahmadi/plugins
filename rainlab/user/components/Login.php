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
use RainLab\User\Models\AccessLog;
use Cms\Classes\Page;
use Cms\Classes\ComponentBase;
use RainLab\User\Models\Settings as UserSettings;
use Exception;

class Login extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name'        => 'فرم ورود',
            'description' => 'کامپوننت فرم ورود به نرم افزار'
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
        $this->page['user'] = $this->user();
        $this->page['canRegister'] = $this->canRegister();
        $this->page['loginAttribute'] = $this->loginAttribute();
        $this->page['loginAttributeLabel'] = $this->loginAttributeLabel();
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
     * Sign in the user
     */
    public function onSignin()
    {
        try {        
            /*
             * Validate input
             */
            $data = post();
            $rules = [];

            // $rules['login'] = $this->loginAttribute() == UserSettings::LOGIN_USERNAME
            //     ? 'required|between:2,255'
            //     : 'required|email|between:6,255';

            if($this->loginAttribute() == UserSettings::LOGIN_EMAIL)
                $rules['email'] = 'required|email';

            if($this->loginAttribute() == UserSettings::LOGIN_USERNAME)
                $rules['username'] = 'required';

            $rules['password'] = 'required';

            if (!array_key_exists('login', $data)) {
                $data['login'] = post('username', post('email'));
            }

            $message = [
                'username.required'        => 'لطفا کد ملی خود را وارد کنید.',
                'email.required'           => 'لطفا پست الکترونیکی خود را وارد کنید.',
                'email.email'              => 'قالب پست الکترونیکی نامعتبر است.',
                'password.required'        => 'لطفا رمز عبور خود را وارد کنید.',
            ];

            $validation = Validator::make($data, $rules, $message);
            if ($validation->fails()) {
                throw new ValidationException($validation);
            }

            /*
             * Authenticate user
             */
            $credentials = [
                'login'    => array_get($data, 'login'),
                'password' => array_get($data, 'password')
            ];

            Event::fire('rainlab.user.beforeAuthenticate', [$this, $credentials]);

            $user = Auth::authenticate($credentials, false);

            if ($user->isBanned()) {
                Auth::logout();
                throw new AuthException(/*Sorry, this user is currently not activated. Please contact us for further assistance.*/'rainlab.user::lang.account.banned');
            }

            if(!$user->first_login)
            {
                $user->first_login = 1;
                $user->forceSave();

                return Redirect::to("profile");
            }            
            
            /*
             * Redirect
             */
            if ($redirect = $this->makeRedirection(true)) {
                return $redirect;
            }
        }
        catch (Exception $ex) {
            if (Request::ajax()) throw $ex;
            else Flash::error($ex->getMessage());
        }            
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
