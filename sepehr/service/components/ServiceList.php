<?php namespace Sepehr\Service\Components;

use Cms\Classes\ComponentBase;
use Redirect;
use Sepehr\Service\Models\Service;
use Auth;

class ServiceList extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name' => 'ServiceList Component',
            'description' => 'No description provided yet...'
        ];
    }

    public function defineProperties()
    {
        return [];
    }

    public function preVars()
    {
        $user = Auth::getUser();
        $this->page['lists'] = Service::whereUserId($user->id)->orderBy('id', 'desc')->get();
        $this->page['service'] = new Service();
    }

    public function onRun()
    {
        $this->preVars();
    }

    public function onServiceDelete()
    {
        $user = Auth::getUser();
        $id = post('id');
        $service = Service::whereUserId($user->id)->find($id);
        if ($service->status_id > 1) {
            throw new \ApplicationException('با توجه به تایید سرویس شما، امکان حذف وجود ندارد');
        } else {
            $service->delete();
        }
        return Redirect::refresh();
    }


}
