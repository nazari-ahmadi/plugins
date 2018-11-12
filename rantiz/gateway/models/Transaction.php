<?php namespace Rantiz\Gateway\Models;

use Model;
use Carbon\Carbon;
use Mail;
use Validator;
use ValidationException;
use ApplicationException;
use Backend\Models\User;

/**
 * Model
 */
class Transaction extends Model
{
    use \October\Rain\Database\Traits\Validation;

    // public $implement = ['@RainLab.Translate.Behaviors.TranslatableModel'];

    /**
     * Validation rules
     */
    public $rules = [

    ];

    /**
     * @var array The attributes that are mass assignable.
     */
    protected $fillable = [

    ];

    /*
     * Relations        

     */
    public $belongsTo = [
        'user' => ['RainLab\User\Models\User'],
        // 'order' => ['Ls\Details\Models\Course', 'key' => 'order_id'],
    ];

    /*
     * Disable timestamps by default.
     * Remove this line if timestamps are defined in the database table.
     */
    public $timestamps = true;

    protected $dates = [
        'payment_date',
        'created_at',
        'updated_at',
        'deleted_at',
    ];


    /**
     * @var string The database table used by the model.
     */
    public $table = 'gateway_transactions';
    
    /**
     * Limit visibility of the deleted-button
     * @return void
     */
    public function filterFields($fields, $context = null)
    {
        if (!isset($fields->deleted, $fields->deleted_at)) {
            return;
        }

        $user = BackendAuth::getUser();

        if (!$user->hasAnyAccess(['rantiz.gateway.access_delete'])) {
            $fields->deleted->hidden = true;
            $fields->deleted_at->hidden = true;
        }
        else {
            $fields->deleted->hidden = false;
            $fields->deleted_at->hidden = false;
        }
    }
}