<?php namespace Sepehr\Service\Components;

use Cms\Classes\ComponentBase;
use Auth;
use Sepehr\Service\Models\Service;

class PostmanServices extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name'        => 'PostmanServices Component',
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
        $services = Service::whereStatusId(3)->orderBy('id')->get();
        $list[]=null;
        $cnt=-1;
        foreach ($services as $service) {

            foreach ($service->postmans as $postman) {

                if ($postman['postman_id'] == $user->id) {
                    if ($postman['acceptance_id'] == 2){
                        $cnt++;
                        $list[$cnt]=$service;
                    }
                }
            }
        }
        $this->page['lists'] = $list;
        $this->page['service'] = new Service();
    }

    public function onRun()
    {
        $this->preVars();
    }
}
