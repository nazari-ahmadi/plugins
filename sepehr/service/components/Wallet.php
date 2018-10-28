<?php namespace Sepehr\Service\Components;

use Auth;
use Cms\Classes\ComponentBase;
use Flash;
use Redirect;


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
    }

    public function onRun()
    {
        $this->preVars();
    }

    public function onWalletCharge()
    {
        $user = Auth::getUser();
        $wallet = $user->wallet_charge;
        $walletGet = post('wallet_charge') + $wallet;

        $user->wallet_charge = $walletGet;
        $user->save();
        Flash::success('عملیات شارژ با موفقیت انجام شد');
        return Redirect::refresh();
    }
}
