<?php namespace Rantiz\Gateway\Controllers;

use Backend\Classes\Controller;
use BackendMenu;
use Flash;
use Lang;
use Rantiz\Gateway\Models\Log;
use Carbon\Carbon;

class Logs extends Controller
{
    public $implement = ['Backend\Behaviors\ListController','Backend\Behaviors\FormController'];
    
    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';

    public $requiredPermissions = ['ls.details.journal.access_logs'];

    public $bodyClass = 'compact-container';
    

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('Rantiz.Gateway', 'gateway','logs');
    }
}