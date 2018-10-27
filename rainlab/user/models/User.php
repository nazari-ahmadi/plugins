<?php namespace RainLab\User\Models;

use Str;
use Auth;
use Mail;
use Event;
use October\Rain\Auth\Models\User as UserBase;
use RainLab\User\Models\Settings as UserSettings;
use October\Rain\Auth\AuthException;
use Sepehr\Details\Models\Sex;
use Sepehr\Details\Models\CountryCode;

class User extends UserBase
{
    use \October\Rain\Database\Traits\SoftDelete;

    /**
     * @var string The database table used by the model.
     */
    protected $table = 'users';

    public $jsonable = ['addresses'];

    /**
     * Validation rules
     */
    public $rules = [
        'first_name'            => 'required',
        'last_name'             => 'required',    
        'country_code'          => 'required',    
        'mobile'                => ['required', 'regex:/^([9]{1})+[0-9]{9}$/i', 'unique:users'],
        'national_code'         => 'unique:users',
        'email'                 => 'required|between:6,255|email|unique:users',
        'username'              => 'required|between:2,255|unique:users',
        'password'              => 'required:create|between:4,255|confirmed',
        'password_confirmation' => 'required_with:password|between:4,255',
        'avatar'                => 'nullable|image|max:4000',
    ];

    public $customMessages = [
        'first_name.required'       => 'لطفا نام را وارد کنید.',
        'last_name.required'        => 'لطفا نام خانوادگی را وارد کنید.',
        'national_code.required'    => 'لطفا کد ملی را وارد کنید.',
        'national_code.unique'      => 'کد ملی وارد شده تکراری می باشد.',
        'mobile.required'           => 'لطفا موبایل را وارد کنید.',
        'mobile.unique'             => 'موبایل وارد شده تکراری می باشد.',   
        'mobile.regex'              => 'فرمت شماره موبایل وارد شده نامعتبر است.', 
        'country_code.required'     => 'لطفا کد کشور را انتخاب کنید.'    
    ];

    /**
     * @var array Relations
     */
    public $belongsToMany = [
        'groups' => [UserGroup::class, 'table' => 'users_groups']
    ];

    public $attachOne = [
        'avatar' => \System\Models\File::class
    ];

    /**
     * @var array The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'surname',
        'login',
        'username',
        'email',
        'password',
        'password_confirmation'
    ];

    /**
     * Purge attributes from data set.
     */
    protected $purgeable = ['password_confirmation', 'send_invite'];

    protected $dates = [
        'last_seen',
        'deleted_at',
        'created_at',
        'updated_at',
        'activated_at',
        'last_login'
    ];

    public static $loginAttribute = null;

    /**
     * Sends the confirmation email to a user, after activating.
     * @param  string $code
     * @return bool
     */
    public function attemptActivation($code)
    {
        $result = parent::attemptActivation($code);
        if ($result === false) {
            return false;
        }

        Event::fire('rainlab.user.activate', [$this]);

        return true;
    }

    /**
     * Converts a guest user to a registered one and sends an invitation notification.
     * @return void
     */
    public function convertToRegistered($sendNotification = true)
    {
        // Already a registered user
        if (!$this->is_guest) {
            return;
        }

        if ($sendNotification) {
            $this->generatePassword();
        }

        $this->is_guest = false;
        $this->save();

        if ($sendNotification) {
            $this->sendInvitation();
        }
    }

    //
    // Constructors
    //

    /**
     * Looks up a user by their email address.
     * @return self
     */
    public static function findByEmail($email)
    {
        if (!$email) {
            return;
        }

        return self::where('email', $email)->first();
    }

    //
    // Getters
    //

    /**
     * Gets a code for when the user is persisted to a cookie or session which identifies the user.
     * @return string
     */
    public function getPersistCode()
    {
        $block = UserSettings::get('block_persistence', false);

        if ($block || !$this->persist_code) {
            return parent::getPersistCode();
        }

        return $this->persist_code;
    }

    /**
     * Returns the public image file path to this user's avatar.
     */
    public function getAvatarThumb($size = 25, $options = null)
    {
        if (is_string($options)) {
            $options = ['default' => $options];
        }
        elseif (!is_array($options)) {
            $options = [];
        }

        // Default is "mm" (Mystery man)
        $default = array_get($options, 'default', 'mm');

        if ($this->avatar) {
            return $this->avatar->getThumb($size, $size, $options);
        }
        else {
            return '//www.gravatar.com/avatar/'.
                md5(strtolower(trim($this->email))).
                '?s='.$size.
                '&d='.urlencode($default);
        }
    }

    /**
     * Returns the name for the user's login.
     * @return string
     */
    public function getLoginName()
    {
        if (static::$loginAttribute !== null) {
            return static::$loginAttribute;
        }

        return static::$loginAttribute = UserSettings::get('login_attribute', UserSettings::LOGIN_EMAIL);
    }

    //
    // Scopes
    //

    public function scopeIsActivated($query)
    {
        return $query->where('is_activated', 1);
    }

    public function scopeFilterByGroup($query, $filter)
    {
        return $query->whereHas('groups', function($group) use ($filter) {
            $group->whereIn('id', $filter);
        });
    }

    //
    // Events
    //

    /**
     * Before validation event
     * @return void
     */
    public function beforeValidate()
    {
        if($this->national_code)
        {
            if(!$this->validationNationalCode($this->national_code))
            {
                throw new \ValidationException(['national_code' => 'فرمت کد ملی وارد شده نامعتبر است.']);        
            }            
        }

        /*
         * Guests are special
         */
        if ($this->is_guest && !$this->password) {
            $this->generatePassword();
        }

        /*
         * When the username is not used, the email is substituted.
         */
        if (
            (!$this->username) ||
            ($this->isDirty('email') && $this->getOriginal('email') == $this->username)
        ) {
            $this->username = $this->email;
        }
    }

    static function validationNationalCode($code)
    {
        if(!preg_match('/^[0-9]{10}$/',$code))
            return false;
        for($i=0;$i<10;$i++)
            if(preg_match('/^'.$i.'{10}$/',$code))
                return false;
        for($i=0,$sum=0;$i<9;$i++)
            $sum+=((10-$i)*intval(substr($code, $i,1)));
        $ret=$sum%11;
        $parity=intval(substr($code, 9,1));
        if(($ret<2 && $ret==$parity) || ($ret>=2 && $ret==11-$parity))
            return true;
        return false;
    }

    /**
     * After create event
     * @return void
     */
    public function afterCreate()
    {
        $this->restorePurgedValues();

        if ($this->send_invite) {
            $this->sendInvitation();
        }
    }

    /**
     * Before login event
     * @return void
     */
    public function beforeLogin()
    {
        if ($this->is_guest) {
            $login = $this->getLogin();
            throw new AuthException(sprintf(
                'Cannot login user "%s" as they are not registered.', $login
            ));
        }

        parent::beforeLogin();
    }

    /**
     * After login event
     * @return void
     */
    public function afterLogin()
    {
        $this->last_login = $this->freshTimestamp();

        if ($this->trashed()) {
            $this->restore();

            Mail::sendTo($this, 'rainlab.user::mail.reactivate', [
                'name' => $this->name
            ]);

            Event::fire('rainlab.user.reactivate', [$this]);
        }
        else {
            parent::afterLogin();
        }

        Event::fire('rainlab.user.login', [$this]);
    }

    /**
     * After delete event
     * @return void
     */
    public function afterDelete()
    {
        if ($this->isSoftDelete()) {
            Event::fire('rainlab.user.deactivate', [$this]);
            return;
        }

        $this->avatar && $this->avatar->delete();

        parent::afterDelete();
    }

    //
    // Banning
    //

    /**
     * Ban this user, preventing them from signing in.
     * @return void
     */
    public function ban()
    {
        Auth::findThrottleByUserId($this->id)->ban();
    }

    /**
     * Remove the ban on this user.
     * @return void
     */
    public function unban()
    {
        Auth::findThrottleByUserId($this->id)->unban();
    }

    /**
     * Check if the user is banned.
     * @return bool
     */
    public function isBanned()
    {
        $throttle = Auth::createThrottleModel()->where('user_id', $this->id)->first();
        return $throttle ? $throttle->is_banned : false;
    }

    //
    // Last Seen
    //

    /**
     * Checks if the user has been seen in the last 5 minutes, and if not,
     * updates the last_seen timestamp to reflect their online status.
     * @return void
     */
    public function touchLastSeen()
    {
        if ($this->isOnline()) {
            return;
        }

        $oldTimestamps = $this->timestamps;
        $this->timestamps = false;

        $this
            ->newQuery()
            ->where('id', $this->id)
            ->update(['last_seen' => $this->freshTimestamp()])
        ;

        $this->last_seen = $this->freshTimestamp();
        $this->timestamps = $oldTimestamps;
    }

    /**
     * Returns true if the user has been active within the last 5 minutes.
     * @return bool
     */
    public function isOnline()
    {
        return $this->getLastSeen() > $this->freshTimestamp()->subMinutes(5);
    }

    /**
     * Returns the date this user was last seen.
     * @return Carbon\Carbon
     */
    public function getLastSeen()
    {
        return $this->last_seen ?: $this->created_at;
    }

    //
    // Utils
    //

    /**
     * Returns the variables available when sending a user notification.
     * @return array
     */
    public function getNotificationVars()
    {
        $vars = [
            'name'     => $this->name,
            'email'    => $this->email,
            'username' => $this->username,
            'login'    => $this->getLogin(),
            'password' => $this->getOriginalHashValue('password')
        ];

        /*
         * Extensibility
         */
        $result = Event::fire('rainlab.user.getNotificationVars', [$this]);
        if ($result && is_array($result)) {
            $vars = call_user_func_array('array_merge', $result) + $vars;
        }

        return $vars;
    }

    /**
     * Sends an invitation to the user using template "rainlab.user::mail.invite".
     * @return void
     */
    protected function sendInvitation()
    {
        Mail::sendTo($this, 'rainlab.user::mail.invite', $this->getNotificationVars());
    }

    /**
     * Assigns this user with a random password.
     * @return void
     */
    protected function generatePassword()
    {
        $this->password = $this->password_confirmation = Str::random(6);
    }

    public function getSexIdOptions()
    {
        $lists = Sex::lists('name','id');
        $lists  = ['' => "انتخاب کنید"] + $lists;
        return $lists;
    }

    public function getCountryCodeOptions()
    {
        $lists = CountryCode::orderBy('name')->lists('name','code');
        $lists  = ['' => "انتخاب کنید"] + $lists;
        return $lists;
    }

}
