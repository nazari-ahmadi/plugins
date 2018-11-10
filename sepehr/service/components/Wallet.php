<?php namespace Sepehr\Service\Components;

use Auth;
use Cms\Classes\ComponentBase;
use Flash;
use Redirect;
use Sepehr\Service\Models\Service;
use RainLab\User\Models\User;

class Wallet extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name' => 'کامپوننت کیف پول',
            'description' => 'کامپوننت کنترل کیف پول کاربر'
        ];
    }

    public function defineProperties()
    {
        return [];
    }


    public function preVars()
    {
        $user = Auth::getUser();
        $wallet = $user->wallet_charge;
        $this->page['wallet'] = $wallet;
        $serviceLists=Service::whereUserId($user->id)->whereStatusId(4)->where('payment_status','=','0')->get();
        $this->page['lists'] =$serviceLists ;
        $this->page['service']=new Service();
        $this->page['Wallet']=new Wallet();
    }

    public function onRun()
    {
        $this->preVars();
    }

    public function onWalletCharge()
    {
        Flash::success('اتصال به درگاه بانک');
        return true;
    }

    public function onServicePayment()
    {
        $id=post('id');
        $service=Service::find($id);
        if ($this->PayService($service)){
            Flash::success('پرداخت با موفقیت انجام شد');
        }else{
            if ($this->onWalletCharge()){
                if ($this->PayService($service)){
                    Flash::success('پرداخت با موفقیت انجام شد');
                }
            }
        }

    }
    
    public function PlusWallet($amount,$service,$boolPlus)
    {
        if ($boolPlus){
            $service->wallet_charge += $amount;
        }else{
            $service->wallet_charge -= $amount;
        }
        $service->save();
    }

    public function getPay($service)
    {
        $pay=0;
        if ($service->payments!=null){
            foreach ($service->payments as $payment){
                $pay += $payment["amount"];
            }
        }
        return $pay;
    }

    public function getNotPay($service)
    {
        $total=0;
        if ($service->packages!=null){
            foreach ($service->packages as $package){
                $total += $package["price"];
            }
        }
        $notPay = $total - $this->getPay($service);
        return $notPay;
    }

    public function PayService($service)
    {
        $notPayment=$this->getNotPay($service);
        if ($notPayment==0){
            $service->payment_status=1;
            $service->save();
            return true;
        }else{
            $user=User::getUser($service->user_id);
            if ($user->wallet_charge>=$notPayment){
                $user->wallet_charge -= $notPayment;
                $payList=$service->payments;
                $payList[]=['payment_status_id'=>1 , 'amount'=>$notPayment,'payment_date'=>''];
                $service->payments=$payList;
                $service->payment_status=1;
                $service->save();
                $user->save();
                return true;
            }else{
                if ($user->wallet_charge>=1000){
                    $amount=$user->wallet_charge;
                    $user->wallet_charge = 0;
                    $payList=$service->payments;
                    $payList[]=['payment_status_id'=>1 , 'amount'=>$amount,'payment_date'=>''];
                    $service->payments=$payList;
                    $service->save();
                    $user->save();
                    return false;
                }
                return false;
            }
        }
    }
}
