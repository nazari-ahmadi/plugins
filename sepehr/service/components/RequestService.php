<?php namespace Sepehr\Service\Components;

use Carbon\Carbon;
use Cms\Classes\ComponentBase;
use Flash;
use Auth;
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
use RainLab\User\Models\User as FrontendUser;
use Backend\Models\User as BackendUser;
use Session;
use ApplicationException;
use ValidationException;

class RequestService extends ComponentBase
{

    public function componentDetails()
    {
        return [
            'name' => 'درخواست سرویس پست',
            'description' => 'کاپوننت ثبت درخواست سرویس پستی برای مشتری'
        ];
    }


    public function defineProperties()
    {
        return [
            'id' => [
                'title' => 'get service id',
                'description' => 'get service id from service list component',
                'default' => '{{:id}}',
                'type' => 'string',

            ],
        ];
    }


    public function preVars()
    {

        $this->page['id'] = $this->property('id');
        $user = Auth::getUser();
        Session::forget('packages');
        $id = $this->property('id');
        if ($id != null) {
            $service = Service::whereUserId($user->id)->find($id);
//
            Session::put('packages', $service->packages);
            $this->page['packages'] = $service->packages;
        } else {
            $service = null;
        }

        if ($user->addresess[0]) {
            $this->page['address'] = $user->addresses[0];
        }

        $this->page['users'] = FrontendUser::orderBy('id')->get();
        $this->page['operators'] = BackendUser::orderBy('id')->get();
        $this->page['paymentTypes'] = PaymentType::orderBy('name')->get();
        $this->page['postTypes'] = PostType::orderbY('name')->get();
        $this->page['insurancesTypes'] = InsuranceType::orderBy('name')->get();
        $this->page['distributionTimes'] = DistributionTime::orderBy('name')->get();
        $this->page['specialServices'] = SpecialService::orderBy('name')->get();
        $this->page['packageTypes'] = PackageType::orderBy('name')->get();
        $this->page['statuses'] = Status::orderBy('id')->get();
        $this->page['weight'] = Weight::orderBy('id')->get();
        $this->page['lists'] = $service;
        $this->page['service'] = new Service();
    }

    public function onRun()
    {
        $this->preVars();
    }


    public function onCreatePackage()

    {
        if (!post('package_number')) {
            throw new ValidationException(['package_number' => 'لطفاتعداد بسته را وارد کنید.']);
        }
        if (!post('receiver_postal_code')) {
            throw new ValidationException(['receiver_postal_code' => 'لطفا کد پستی گیرنده کنید.']);
        }

        if (!post('receiver_address')) {
            throw new ValidationException(['receiver_address' => 'لطفا آدرس گیرنده را وارد کنید.']);
        }

        if (!$weight = post('weight_id')) {
            throw new ValidationException(['weight_id' => 'لطفا وزن مرسوله  را وارد کنید.']);
        }

        if (!trim(post('post_type_id'))) {
            throw new ValidationException(['post_type_id' => 'لطفا نوع ارسال را انتخاب کنید.']);
        }

        if (!trim(post('package_type_id'))) {
            throw new ValidationException(['package_type_id' => 'لطفا نوع بسته را انتخاب کنید.']);
        }

        if (!trim(post('insurance_type_id'))) {
            throw new ValidationException(['insurance_type_id' => 'لطفا نوع بیمه را انتخاب کنید.']);
        }

        $packages = Session::get("packages");

        if ($id = post('package_id')){
            $packages[$id]["package_number"]       = post('package_number');
            $packages[$id]["receiver_postal_code"] = post('receiver_postal_code');
            $packages[$id]['receiver_address']     = post('receiver_address');
            $packages[$id]['weight_id']            = post('weight_id');
            $packages[$id]['post_type_id']         = post('post_type_id');
            $packages[$id]['package_type_id']      = post('package_type_id');
            $packages[$id]['insurance_type_id']    = post('insurance_type_id');
            $packages[$id]['distribution_time_id'] = post('distribution_time_id');
            $packages[$id]['special_services_id']  = post('special_services_id');
        }else{
            $packages[] = [
                'is_rejected' => false,
                'package_number' => post('package_number'),
                'receiver_postal_code' => post('receiver_postal_code'),
                'receiver_address' => post('receiver_address'),
                'post_type_id' => post('post_type_id'),
                'distribution_time_id' => post('distribution_time_id'),
                'weight_id' => post('weight_id'),
                'special_services_id' => post('special_services_id'),
                'price' => 0,
                'package_type_id' => post('package_type_id'),
                'insurance_type_id' => post('insurance_type_id'),
                'transaction_code' => post('transaction_code'),
                'points' => post('points'),

            ];
        }

//        throw new \ApplicationException(print_r($packages));
        Session::put("packages", $packages);

        $this->page['packages'] = Session::get('packages');

        $this->page['service'] = new Service;
    }

    /**
     * @throws ValidationException
     */


    public function onPackageDelete()
    {
//        throw new ApplicationException(post('id'));
        $packages = Session::get('packages');
        if (post('id') !== null) {
            $id = post('id');
            unset($packages[$id]);
        }

        Session::put('packages', $packages);
        $this->page['packages'] = $packages;
        $this->page['service'] = new Service();
    }

//    public function onUpdatePackage()
//    {
//        if (!post('package_number')) {
//            throw new ValidationException(['package_number' => 'لطفاتعداد بسته را وارد کنید.']);
//        }
//        if (!post('receiver_postal_code')) {
//            throw new ValidationException(['receiver_postal_code' => 'لطفا کد پستی گیرنده کنید.']);
//        }
//
//        if (!post('receiver_address')) {
//            throw new ValidationException(['receiver_address' => 'لطفا آدرس گیرنده را وارد کنید.']);
//        }
//
//        if (!$weight = post('weight_id')) {
//            throw new ValidationException(['weight_id' => 'لطفا وزن مرسوله  را وارد کنید.']);
//        }
//
//        if (!trim(post('post_type_id'))) {
//            throw new ValidationException(['post_type_id' => 'لطفا نوع ارسال را انتخاب کنید.']);
//        }
//
//        if (!trim(post('package_type_id'))) {
//            throw new ValidationException(['package_type_id' => 'لطفا نوع بسته را انتخاب کنید.']);
//        }
//
//        if (!trim(post('insurance_type_id'))) {
//            throw new ValidationException(['insurance_type_id' => 'لطفا نوع بیمه را انتخاب کنید.']);
//        }
//
//        $id = post('package_id');
//        $packages = Session::get("packages");
//
//        $packages[$id]["package_number"]       = post('package_number');
//        $packages[$id]["receiver_postal_code"] = post('receiver_postal_code');
//        $packages[$id]['receiver_address']     = post('receiver_address');
//        $packages[$id]['weight_id']            = post('weight_id');
//        $packages[$id]['post_type_id']         = post('post_type_id');
//        $packages[$id]['package_type_id']      = post('package_type_id');
//        $packages[$id]['insurance_type_id']    = post('insurance_type_id');
//        $packages[$id]['distribution_time_id'] = post('distribution_time_id');
//        $packages[$id]['special_services_id']  = post('special_services_id');
//
//        Session::put("packages", $packages);
//
//        $this->page['packages'] = Session::get('packages');
//
//        $this->page['service'] = new Service();
//    }

    public function onSaveService()
    {
        if (!post('sender_postal_code')) {
            throw new ValidationException(['sender_postal_code' => 'لطفا کد پستی خود را وارد کنید.']);
        }

        if (!post('sender_address')) {
            throw new ValidationException(['sender_address' => 'لطفا آدرس فرستنده را وارد کنید.']);
        }

        if (!Session::get('packages')) {
            throw new ValidationException(['' => 'لطفا بسته های مورد نظر خود را وارد کنید.']);
        }
        $user = Auth::getUser();
        $id = $this->property('id');
        if ($id != null) {
            $service = Service::whereUserId($user->id)->find($id);
            if ($service->status_id > 1) {
                throw new ApplicationException('با توجه به تایید سرویس شما، امکان ویرایش وجود ندارد');
            }
        } else {
            $service = new Service();
        }


        $service->sender_address = post('sender_address');
        $service->sender_postal_code = post('sender_postal_code');

        $service->user_id = $user->id;
        $service->packages = Session::get('packages');
//
        $service->save();
        Flash::success('سرویس با موفقیت ذخیره گردید');
        return Redirect::to('/servicelist');
    }

}
