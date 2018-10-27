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
use Charity\Details\Models\Sex;
use Charity\Details\Models\Location;
use Charity\Details\Models\CountryCode;
use Exception;

/**
 * Account component
 *
 * Allows users to register, sign in and update their account. They can also
 * deactivate their account and resend the account verification email.
 */
class Account extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name'        => /*Account*/'rainlab.user::lang.account.account',
            'description' => /*User management form.*/'rainlab.user::lang.account.account_desc'
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
        $this->page['userGroups']  = $this->user()->groups->lists('code');
        $this->page['user']                 = $this->user();
        $this->page['canRegister']          = $this->canRegister();
        $this->page['loginAttribute']       = $this->loginAttribute();
        $this->page['loginAttributeLabel']  = $this->loginAttributeLabel();
        $this->page['sex']                  = Sex::orderBy('name')->get();
        $this->page['locations']            = Location::get();      
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

    //
    // Properties
    //

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

    //
    // AJAX
    //

    /**
     * Update the user
     */
    public function onUpdate()
    {
        if (!$user = $this->user()) {
            return;
        }

        if (Input::hasFile('avatar')) {
            $user->avatar = Input::file('avatar');
        }

        if(post('password'))
        {
            if(!post('current_password'))
            {
                throw new ValidationException(['current_password' => Lang::get('rainlab.user::lang.account.current_password')]);
            }

            if (!$user->checkHashValue('password', post('current_password'))) {
                throw new ValidationException(['current_password' => Lang::get('rainlab.user::lang.account.current_password_error')]);
            }    
        }

        $user->fill(post());
        $user->isAdmin = true;
        $user->save();

        Flash::success(post('flash', Lang::get(/*Settings successfully saved!*/'rainlab.user::lang.account.success_saved')));

        /*
         * Password has changed, reauthenticate the user
         */
        if (strlen(post('password'))) {
            Auth::login($user->reload(), true);
        }

        /*
         * Redirect
         */
        if ($redirect = $this->makeRedirection()) {
            return $redirect;
        }

        $this->prepareVars();
    }

    /**
     * Deactivate user
     */
    public function onDeactivate()
    {
        if (!$user = $this->user()) {
            return;
        }

        if (!$user->checkHashValue('password', post('deactive_password'))) {
            throw new ValidationException(['deactive_password' => Lang::get('rainlab.user::lang.account.invalid_deactivation_pass')]);
        }

        Auth::logout();
        $user->delete();

        Flash::success(post('flash', Lang::get(/*Successfully deactivated your account. Sorry to see you go!*/'rainlab.user::lang.account.success_deactivation')));

        /*
         * Redirect
         */
        if ($redirect = $this->makeRedirection()) {
            return $redirect;
        }
    }

    /**
     * Trigger a subsequent activation email
     */
    public function onSendActivationEmail()
    {
        try {
            if (!$user = $this->user()) {
                throw new ApplicationException(Lang::get(/*You must be logged in first!*/'rainlab.user::lang.account.login_first'));
            }

            if ($user->is_activated) {
                throw new ApplicationException(Lang::get(/*Your account is already activated!*/'rainlab.user::lang.account.already_active'));
            }

            Flash::success(Lang::get(/*An activation email has been sent to your email address.*/'rainlab.user::lang.account.activation_email_sent'));

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

    //
    // Helpers
    //

    /**
     * Returns a link used to activate the user account.
     * @return string
     */
    protected function makeActivationUrl($code)
    {
        $params = [
            $this->property('paramCode') => $code
        ];

        if ($pageName = $this->property('activationPage')) {
            $url = $this->pageUrl($pageName, $params);
        }
        else {
            $url = $this->currentPageUrl($params);
        }

        if (strpos($url, $code) === false) {
            $url .= '?activate=' . $code;
        }

        return $url;
    }

    /**
     * Sends the activation email to a user
     * @param  User $user
     * @return void
     */
    protected function sendActivationEmail($user)
    {
        $code = implode('!', [$user->id, $user->getActivationCode()]);

        $link = $this->makeActivationUrl($code);

        $data = [
            'name' => $user->name,
            'link' => $link,
            'code' => $code
        ];

        Mail::send('rainlab.user::mail.activate', $data, function($message) use ($user) {
            $message->to($user->email, $user->name);
        });
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
