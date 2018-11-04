<?php namespace Sepehr\Service\Controllers;

use Backend\Classes\Controller;
use BackendMenu;
use ApplicationException;

class Services extends Controller
{
    public $implement = [
        'Backend\Behaviors\ListController',
        'Backend\Behaviors\FormController'
    ];

    public $requiredPermissions = ['sepehr.service.access_service'];
    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('Sepehr.Service', 'services', 'service');
    }


    public function formBeforeCreate($model)
    {
        $model->manage_id = $this->user->id;
        foreach ($model->packages as $package) {
            $package->is_rejected = false;
        }
    }
}
