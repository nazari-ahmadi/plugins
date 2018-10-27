<?php namespace Sepehr\Details\Controllers;

use Backend\Classes\Controller;
use BackendMenu;

class Statuses extends Controller
{
    public $implement = [        'Backend\Behaviors\ListController',        'Backend\Behaviors\FormController'    ];

    protected $requiredPermissions=[
        'sepehr.details.service.access_status',
    ];

    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('Sepehr.Details','details','services');
    }
}
