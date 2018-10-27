<?php namespace Sepehr\Details\Controllers;

use Backend\Classes\Controller;
use BackendMenu;

class Index extends Controller
{
	public $pageTitle = 'اطلاعات پایه';

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('Sepehr.Details', 'details', '');
    }

    public $requiredPermissions = ['sepehr.details.*'];

    public function index()
    {
    }
}
