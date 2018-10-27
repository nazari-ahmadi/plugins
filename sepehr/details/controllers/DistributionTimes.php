<?php namespace Sepehr\Details\Controllers;

use Backend\Classes\Controller;
use BackendMenu;

class DistributionTimes extends Controller
{
    public $implement = [
      'Backend\Behaviors\ListController',
      'Backend\Behaviors\FormController'
    ];
    
    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';

    public $requiredPermissions = ["sepehr.details.service.access_distribution_time"];

    public function __construct()
    {
        parent::__construct();

        BackendMenu::setContext('Sepehr.Details', 'details', 'services');
    }
}
