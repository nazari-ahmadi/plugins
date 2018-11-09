<?php namespace Sepehr\Service\FormWidgets;

use Backend\Classes\FormWidgetBase;
use Sepehr\Details\Models\PaymentType;
use Sepehr\Service\Models\Service;

/**
 * Payments Form Widget
 */
class Payments extends FormWidgetBase
{
    /**
     * @inheritDoc
     */
    protected $defaultAlias = 'sepehr_service_payments';

    /**
     * @inheritDoc
     */
    public function init()
    {
    }

    /**
     * @inheritDoc
     */
    public function render()
    {
        $this->prepareVars();
        return $this->makePartial('payments');
    }

    /**
     * Prepares the form widget view data
     */
    public function prepareVars()
    {
        $this->vars['service'] = new Service();
        $this->vars['name'] = $this->formField->getName();
        $this->vars['value'] = $this->getLoadValue();
        $this->vars['model'] = $this->model;
        $this->vars['paymentTypes'] = PaymentType::orderBy('id')->get();
    }

    /**
     * @inheritDoc
     */
    public function loadAssets()
    {
        $this->addCss('css/payments.css', 'sepehr.service');
        $this->addJs('js/payments.js', 'sepehr.service');
    }

    /**
     * @inheritDoc
     */
    public function getSaveValue($value)
    {
        return $value;
    }

    public function onPayment()
    {
        $id = post('payment_id');
        $payments = $this->model->payments;
        if ($id!=null){
            $payments[$id]["amount"]       = post('amount');
            $payments[$id]["payment_type_id"] = post('payment_type_id');
        }else{
            $payments[] = [
                'payment_type_id' => post('payment_type_id'),
                'amount' => post('amount'),
                'payment_date' => ''
            ];
        }
        $this->model->payments = $payments;
        $this->model->save();
        $this->vars['service'] = new Service();
        $this->vars['model'] = $this->model;

    }


    public function onDeletePayment()
    {
        $id = post('id');
        $payments = $this->model->payments;
        if ($id != null) {
            unset($payments[$id]);

        }
        $this->model->payments = $payments;
        $this->model->save();
        $this->vars['service'] = new Service();
        $this->vars['model']=$this->model;
    }

    public function CheckStatus()
    {
        //چک شود اگر پرداخت ها برابر بر قیمت کل سرویس شد وضعیت پرداخت تنظیم شود
    }

}
