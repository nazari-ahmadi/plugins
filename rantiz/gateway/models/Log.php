<?php namespace Rantiz\Gateway\Models;

use Model;
use Carbon\Carbon;
use Mail;
use Validator;
use ValidationException;
use ApplicationException;

/**
 * Model
 */
class Log extends Model
{
    use \October\Rain\Database\Traits\Validation;

    public $implement = ['@RainLab.Translate.Behaviors.TranslatableModel'];

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
    ];

    /*
     * Disable timestamps by default.
     * Remove this line if timestamps are defined in the database table.
     */
    public $timestamps = false;

    protected $dates = [
        'log_date',
    ];


    /**
     * @var string The database table used by the model.
     */
    public $table = 'gateway_transactions_logs';
}