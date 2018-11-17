<?php namespace Sepehr\Service\FormWidgets;

use Backend\Classes\FormWidgetBase;
use Sepehr\Service\Models\Service;


/**
 * Postman Form Widget
 */
class Postman extends FormWidgetBase
{
    /**
     * @inheritDoc
     */
    protected $defaultAlias = 'sepehr_service_postman';

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
        return $this->makePartial('postman');
    }

    /**
     * Prepares the form widget view data
     */
    public function prepareVars()
    {
        $this->vars['name'] = $this->formField->getName();
        $this->vars['value'] = $this->getLoadValue();
        $this->vars['model'] = $this->model;
        $this->vars['postmen'] = $this->getPostman();
        $this->vars['service'] = new Service();

    }



    public function getPostman()
    {
        $service = new Service();
        $postmen = $service->getPostmanIdOptions();
        return $postmen;
    }

    public function onReferral()
    {
        if ($this->checkAcceptance($this->model->postmans)){
            throw new \ApplicationException("این سرویس توسط مامور دیگری پذیرش شده لذا امکان ارجاع وجود ندارد");
        }else{
            $id = post('postman');
            $postmans=$this->model->postmans;
            $postmans[]=['postman_id'=> $id,'acceptance_id' => 1];
            $this->model->postmans= $postmans;
            $this->model->save();
            $this->vars['service'] = new Service();
            $this->vars['model']=$this->model;
        }
    }
    private function checkAcceptance($postmans){
        if($postmans){
            foreach ($postmans as $package){
                if ($package['acceptance_id']==2){
                    return true;
                }
            }
        }
        return false;
    }
    /**
     * @inheritDoc
     */
    public function loadAssets()
    {
        $this->addCss('css/postman.css', 'sepehr.service');
        $this->addJs('js/postman.js', 'sepehr.service');
    }

    /**
     * @inheritDoc
     */

    public function getSaveValue($value)
    {
        return $value;
    }
}
