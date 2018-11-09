<?php namespace Sepehr\Service\Models;
use Backend\Models\User as BackendUser;
use ApplicationException;
use RainLab\User\Models\User as FrontUser;
use Model;
use RainLab\User\Models\UserGroup;
use Sepehr\Details\Models\DistributionTime;
use Sepehr\Details\Models\InsuranceType;
use Sepehr\Details\Models\PackageType;
use Sepehr\Details\Models\PaymentType;
use Sepehr\Details\Models\PostType;
use Sepehr\Details\Models\Status;
use Sepehr\Details\Models\Acceptance;
use Sepehr\Details\Models\SpecialService;
use Sepehr\Details\Models\Weight;
/**
 * Model
 */
class Service extends Model
{
    use \October\Rain\Database\Traits\Validation;
    use \October\Rain\Database\Traits\SoftDelete;
    protected $dates = ['deleted_at'];
    protected $jsonable = ['packages', 'postmans','payments'];
    /**
     * @var array Validation rules
     */
    public $rules = [
        'user_id' => 'required',
//    'payment_type_id' => 'required',
//    'operator_id' => 'required',
        'packages' => 'required',
    ];
    public $customMessages = [
        'user_id.required' => 'لطفا کاربر را انتخاب نمایید',
//    'payment_type_id.required' => 'لطفا نوع پرداخت را انتخاب نمایید',
//    'operator_id.required' => 'لطفا اپراتور را انتخاب نمایید',
        'packages.required' => 'لطفا بسته های پستی خود را انتخاب نمایید'
    ];
    public function beforeValidate()
    {
        foreach ($this->packages as $key => $value) {
            $this->rules['packages.' . $key . '.receiver_postal_code'] = 'required';
            $this->rules['packages.' . $key . '.receiver_address'] = 'required';
            $this->rules['packages.' . $key . '.post_type_id'] = 'required';
            $this->rules['packages.' . $key . '.weight_id'] = 'required';
            $this->rules['packages.' . $key . '.package_type_id'] = 'required';
            $this->rules['packages.' . $key . '.insurance_type_id'] = 'required';
            $this->customMessages['packages.' . $key . '.receiver_postal_code.required'] = 'لطفا کد پستی گیرنده را وارد نمایید';
            $this->customMessages['packages.' . $key . '.receiver_address.required'] = 'لطفا آدرس گیرنده را وارد نمایید';
            $this->customMessages['packages.' . $key . '.post_type_id.required'] = 'لطفا نوع پست را انتخاب نمایید';
            $this->customMessages['packages.' . $key . '.weight_id.required'] = 'لطفا وزن مرسوله را انتخاب نمایید';
            $this->customMessages['packages.' . $key . '.package_type_id.required'] = 'لطفا نوع بسته پستی را انتخاب نمایید';
            $this->customMessages['packages.' . $key . '.insurance_type_id.required'] = 'لطفا نوع بیمه را انتخاب نمایید.';
        }
        if ($this->postmans) {
            foreach ($this->postmans as $key => $value) {
            }
        }
    }
    public function getPostType($id)
    {
        return PostType::find($id)->name;
    }
    public function getPackageType($id)
    {
        return PackageType::find($id)->name;
    }
    public function getInsuranceType($id)
    {
        return InsuranceType::find($id)->name;
    }
    public function getPaymentType($id)
    {
        return PaymentType::find($id)->name;
    }
    public function getSpecialService($id)
    {
        if ($id != 0) {
            return SpecialService::find($id)->name;
        } else {
            return 'انتخاب کنید';
        }
    }
    public function getWeight($id)
    {
        if ($id != 0) {
            return Weight::find($id)->name;
        } else {
            return 'انتخاب کنید';
        }
    }
    public function getDistributionTime($id)
    {
        if ($id != 0) {
            return DistributionTime::find($id)->name;
        } else {
            return 'انتخاب کنید';
        }
    }
    public function getUserIdOptions()
    {
        $users = FrontUser::whereIsActivated(1)
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();
        $list = [' ' => 'انتخاب کنید'];
        if ($users->count()) {
            foreach ($users as $item) {
                $list[$item->id] = "$item->first_name ، $item->last_name" .
                    ($item->national_code ? ' - ' . $item->national_code : '');
            }
        }
        return $list;
    }
    /*public function getManagerIdOptions()
    {
        $lists = BackendUser::lists('first_name', 'id');
        $list = [' ' => 'انتخاب کنید'] + $lists;
        return $list;
    }*/
    public function getOperatorIdOptions()
    {
        $lists = BackendUser::lists('first_name', 'id');
        $list = [' ' => 'انتخاب کنید'] + $lists;
        return $list;
    }
    public function getPaymentTypeIdOptions()
    {
        $lists = PaymentType::lists('name', 'id');
        $list = [' ' => 'انتخاب کنید'] + $lists;
        return $list;
    }
    public function getDistributionTimeIdOptions()
    {
        $lists = DistributionTime::lists('name', 'id');
        $list = [' ' => 'انتخاب کنید'] + $lists;
        return $list;
    }
    public function getPostTypeIdOptions()
    {
        $lists = PostType::lists('name', 'id');
        $list = [' ' => 'انتخاب کنید'] + $lists;
        return $list;
    }
    public function getSpecialServicesIdOptions()
    {
        $lists = SpecialService::lists('name', 'id');
        $list = [' ' => 'انتخاب کنید'] + $lists;
        return $list;
    }
    public function getPackageTypeIdOptions()
    {
        $lists = PackageType::lists('name', 'id');
        $list = [' ' => 'انتخاب کنید'] + $lists;
        return $list;
    }
    public function getInsuranceTypeIdOptions()
    {
        $lists = InsuranceType::lists('name', 'id');
        $list = [' ' => 'انتخاب کنید'] + $lists;
        return $list;
    }
    public function getPostmanIdOptions()
    {
        $group = UserGroup::whereName('postmans')->get()->first();
//        $lists = $group->users->lists('first_name', 'id');
        $lists = $group->users;
        $list = ['0' => 'انتخاب کنید'];
        foreach ($lists as $item){
            $list[$item->id]= $item->first_name . ' ' . $item->last_name;
        }
        return $list;
    }
    public function getWeightIdOptions()
    {
        $lists = Weight::lists('name', 'id');
        $list = [' ' => 'انتخاب کنید'] + $lists;
        return $list;
    }
    public function getStatusIdOptions()
    {
        $lists = Status::lists('name', 'id');
        $list = [' ' => 'انتخاب کنید'] + $lists;
        return $list;
    }
    public function getAcceptanceIdOptions()
    {
        $list = Acceptance::lists('name', 'id');
        return $list;
    }
    /**
     * @var string The database table used by the model.
     */
    public $table = 'sepehr_service_index';
    public function getStatus($id)
    {
        return Status::find($id)->name;
    }
    public function getAcceptance($id)
    {
        return Acceptance::find($id)->name;
    }
    public function getUserPostMans($postmanId)
    {
        $user=FrontUser::whereId($postmanId)->get()->first();
        return ($user->first_name . ' ' . $user->last_name);
    }

    public function getPay($id)
    {
        $pay=0;
        $payments=Service::find($id);
        if ($payments!=null){
            foreach ($payments as $payment){
                $pay += $payment["amount"];
            }
        }
        return $pay;
    }

    public function getNotPay($id)
    {
        $total=0;
        $packages=Service::find($id);
        if ($packages!=null){
            foreach ($packages as $package){
                $total += $package["price"];
            }
        }
        $notPay = $total - $this->getPay($id);
        return $notPay;
    }

    public $belongsTo = [
        'user' => 'RainLab\User\Models\User',
        'status' => Status::class
    ];
}