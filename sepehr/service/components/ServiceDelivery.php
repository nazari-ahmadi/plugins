<?php namespace Sepehr\Service\Components;

use Cms\Classes\ComponentBase;
use Redirect;
use Sepehr\Details\Models\DistributionTime;
use Sepehr\Details\Models\InsuranceType;
use Sepehr\Details\Models\PackageType;
use Sepehr\Details\Models\PaymentType;
use Sepehr\Details\Models\PostType;
use Sepehr\Details\Models\SpecialService;
use Sepehr\Details\Models\Status;
use Sepehr\Details\Models\Weight;
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
        $this->page['paymentTypes'] = PaymentType::orderBy('name')->get();
        $this->page['postTypes'] = PostType::orderbY('name')->get();
        $this->page['insurancesTypes'] = InsuranceType::orderBy('name')->get();
        $this->page['distributionTimes'] = DistributionTime::orderBy('name')->get();
        $this->page['specialServices'] = SpecialService::orderBy('name')->get();
        $this->page['packageTypes'] = PackageType::orderBy('name')->get();
        $this->page['statuses'] = Status::orderBy('id')->get();
        $this->page['weight'] = Weight::orderBy('id')->get();
        $this->page['payments']=$list->payments;
    }

    public function onRun()
    {
        $this->preVars();
    }

    public function calculatePrice($packages)
    {
        $price = 0;
        foreach ($packages as $package) {
            if ($package['is_rejected'] == false && $package['price']!=null) {
                $price += $package['price'];
            }
        }
        return $price;
    }

    public function onCashPayment()
    {
        $id = $this->property('id');
        $service=Service::find($id);
        $payments=$service->payments;
        $payments[]=['payment_type_id'=>2, 'amount' => post('cashPayment'),'payment_date' => ''];
        $service->payments=$payments;

        //در صورتی که کامل پرداخت شده وضعیت پرداخت تنظیم شود

        $service->save();
        $this->page['service']=new Service();
        $this->page['payments']=$service->payments;

    }
    public function onPackageReject()
    {
        $id = post('id');
        $packages = Session::get('packages');
        $packages[$id]['is_rejected'] = true;
        Session::put('packages', $packages);
        $this->page['packages'] = $packages;
        $this->page['price'] = $this->calculatePrice($packages);
        $this->page['service'] = new Service();
    }

    public function onDeliveredService()
    {
        $id = $this->property('id');
        $service=Service::find($id);
        $service->status_id=4;
        $service->save();
        return Redirect::to('/postman-services');
    }

}
