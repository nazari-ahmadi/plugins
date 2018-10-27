<?php namespace Sepehr\Message\Models;

use Model;
use RainLab\User\Models\User;

/**
 * Model
 */
class Message extends Model
{
    use \October\Rain\Database\Traits\Validation;

    use \October\Rain\Database\Traits\SoftDelete;

    protected $dates = ['deleted_at'];


    protected $jsonable=['users'];
    /**
     * @var array Validation rules
     */
    public $rules = [
        'users' => 'required',
        'description' => 'required',

    ];
    public $customMessages = [
        'description.required' => 'لطفا متن پیام را وارد نمایید',
        'users.required' => 'لطفا کاربر را انتخاب نمایید',
    ];

    public function beforeValidate()
    {
        foreach ($this->users as $key => $value) {
            $this->rules['users.' . $key . '.user_id'] = 'required';

            $this->customMessages['users.' . $key . '.user_id.required'] = 'لطفا کاربر را انتخاب نمایید';

        }
    }

    /**
     * @var string The database table used by the model.
     */
    public $table = 'sepehr_message_index';

    public function getUserIdOptions()
    {
        $users = User::whereIsActivated(1)
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        $list = [' ' => 'انتخاب کنید'];
        if ($users->count()) {
            foreach ($users as $item) {
                $list[$item->id] = "$item->first_name ، $item->last_name" .
                    ($item->national_code ? ' - ' . $item->national_code : '');
            }
        }
        return $list;
    }
}
