<?php namespace Sepehr\Message\Controllers;

use Backend\Classes\Controller;
use BackendMenu;

class IndexPage extends Controller
{
	public $pageTitle = 'مدیریت پیام ها';

    public $requiredPermissions=['sepehr.message.*'];

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('Sepehr.Message', 'messages', '');
    }

    public function index()
    {
    }
}