<?php namespace Sepehr\Service\Controllers;

use Backend\Classes\Controller;
use BackendMenu;

class Index extends Controller
{
	public $pageTitle = 'مدیریت سرویس';

    public $requiredPermissions=['sepehr.sercive.*'];

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('Sepehr.Service', 'services', '');
    }

    public function index()
    {
    }
}