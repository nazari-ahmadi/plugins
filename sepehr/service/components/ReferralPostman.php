<?php namespace Sepehr\Service\Components;

use Auth;
use Carbon\Carbon;
use Session;
use Cms\Classes\ComponentBase;
use Sepehr\Service\Models\Service;
use function Sodium\add;

class ReferralPostman extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name' => 'ReferralPostman Component',
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
        $services = Service::whereStatusId(2)->orderBy('id')->get();
        $list[] = null;
        $cnt = -1;
        foreach ($services as $service) {

            foreach ($service->postmans as $postman) {

                if ($postman['postman_id'] == $user->id) {
                    if ($postman['acceptance_id'] <= 1) {
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

    public function onAcceptService()
    {
        $counter = -1;
        $user = Auth::getUser();
        $id = post('id');
        $service = Service::find($id);
        Session::put('postMans', $service->postmans);
        foreach ($service->postmans as $postman) {
            $counter++;
            if ($postman['postman_id'] == $user->id) {
                $man = Session::get("postMans");
                $man[$counter]['acceptance_id'] = 2;
//                $man[$counter]['accepted_at'] = Carbon::now();
                Session::put('postMans', $man);
            }

        }

        $service->postmans = Session::get('postMans');
        $service->status_id = 3;
        $service->save();
        return \Redirect::refresh();
    }

    public function onRejectService()
    {
        $counter = -1;
        $user = Auth::getUser();
        $id = post('id');
        $service = Service::find($id);
        Session::put('postMans', $service->postmans);
        foreach ($service->postmans as $postman) {
            $counter++;
            if ($postman['postman_id'] == $user->id) {
                $man = Session::get("postMans");
                $man[$counter]['acceptance_id'] = 3;
//                $man[$counter]['accepted_at'] = Carbon::now();
                Session::put('postMans', $man);
            }
        }

        $service->postmans = Session::get('postMans');
        $service->save();
        return \Redirect::refresh();
    }

}
