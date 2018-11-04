<?php namespace Sepehr\Service\Components;

use Cms\Classes\ComponentBase;
use Sepehr\Service\Models\Service;
use Session;
use Auth;

class ServiceDelivery extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name' => 'کامپوننت تحویل بسته پستی',
            'description' => 'تحویل گرفتن بسته پستی توسط پستچی'
        ];
    }

    public function defineProperties()
    {
        return [
            'id' => [
                'title' => 'get service id',
                'description' => 'get service id from postman services component',
                'default' => '{{:id}}',
                'type' => 'string',

            ],
        ];
    }

    public function preVars()
    {
        $user = Auth::getUser();
        $id = $this->property('id');
        $service = Service::find($id);
        $list = null;
        foreach ($service->postmans as $postman) {
            if ($postman['postman_id'] == $user->id) {
                $list = $service;
            }
        }
        $this->page['lists'] = $list;
        Session::put('packages', $list->packages);
        $this->page['price'] = $this->calculatePrice($list->packages);
        $this->page['packages'] = $list->packages;
        $this->page['service'] = new Service();
    }

    public function onRun()
    {
        $this->preVars();
    }

    public function calculatePrice($packages)
    {
        $price = 0;
        foreach ($packages as $package) {
            if (!$package->is_rejected) {
                $price += $package['price'];
            }
        }
        return $price;
    }

    public function onPackageReject()
    {

//        $packages = (new RequestService)->onPackageDelete();
//        $this->page['price'] = $this->calculatePrice($packages);
//        $this->page['packages'] = $packages;


        $id = post('id');
        $this->page['service'] = new Service();
        $packages = Session::get('packages');
        $packages[$id]->is_rejected = true; 


    }


}
