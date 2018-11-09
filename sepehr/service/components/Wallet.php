<?php namespace Sepehr\Service\Components;

use Auth;
use Cms\Classes\ComponentBase;
use Flash;
use Redirect;
use Sepehr\Service\Models\Service;


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
    }

    public function onRun()
    {
        $this->preVars();
    }

    public function onWalletCharge()
    {
//        $user = Auth::getUser();
//        $wallet = $user->wallet_charge;
//        $walletGet = post('wallet_charge') + $wallet;
//
//        $user->wallet_charge = $walletGet;
//        $user->save();
//        Flash::success('عملیات شارژ با موفقیت انجام شد');
//        return Redirect::refresh();
        Flash::success('اتصال به درگاه بانک');
    }

    public function PlusWallet($amount)
    {

    }

    public function MinusWallet($amount)
    {

    }

}
