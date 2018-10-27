<?php namespace Sepehr\Details\Models;

use Model;

/**
 * Model
 */
class CountryCode extends Model
{
    use \October\Rain\Database\Traits\Validation;
    
    /*
     * Disable timestamps by default.
     * Remove this line if timestamps are defined in the database table.
     */
    public $timestamps = false;

    /**
     * @var array Validation rules
     */
    public $rules = [
        'name'=>'required|max:50',
        'code'=>'required|max:10',
    ];
    public $customMessages=[
        'name.required'=>'لطفا نام کشور را وارد نمایید',
        'name.max'=>'تعداد کاراکتر کشور بیش از حد مجاز است',
        'code.required'=>'لطفا پیش شماره کشور را وارد نمایید',
        'code.max'=>'تعداد کاراکتر پیش شماره کشور بیش از حد مجاز است',
    ];

    /**
     * @var string The database table used by the model.
     */
    public $table = 'sepehr_details_country_code';
}
