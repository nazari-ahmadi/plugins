<?php namespace Sepehr\Details\Models;

use Model;

/**
 * Model
 */
class Acceptance extends Model
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
    ];

    public $customMassage=[
        'name.required'=>'لطفا عنوان را وارد کنید',
        'name.max'=>'عنوان نباید بیش از 50 کاراکتر باشد'
    ];

    /**
     * @var string The database table used by the model.
     */
    public $table = 'sepehr_details_acceptance';
}
