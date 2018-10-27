<?php namespace Sepehr\Details\Components;

use Cms\Classes\ComponentBase;
use Sepehr\Details\Models\Sex;

class Ehsan extends ComponentBase
{


    public $ehsan = 'ehsan';

    public function componentDetails()
    {
        return [
            'name' => 'EhsanComponent',
            'description' => 'No description provided yet...'
        ];
    }

    public function defineProperties()
    {
        return [];
    }


    public function onRegister()
    {
        $name = new Sex();
        $name->name = post('name');
        $name->save();

        \Flash::success('نام با موفقیت ثبت شد');
    }

    public function onRun()
    {
        $this->ehsan;
    }
}
