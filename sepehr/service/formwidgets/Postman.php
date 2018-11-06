<?php namespace Sepehr\Service\FormWidgets;

use Backend\Classes\FormWidgetBase;
use RainLab\User\Models\User;
use Sepehr\Service\Models\Service;
use Session;

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
//        Session::forget('postmen');
        $this->vars['name'] = $this->formField->getName();
        $this->vars['value'] = $this->getLoadValue();
        $this->vars['model'] = $this->model;
        $this->vars['postmen'] = $this->getPostman();

    }

    public function getPostman()
    {
        $service = new Service();
        $postmen = $service->getPostmanIdOptions();
        return $postmen;
    }

    public function onReferral()
    {
        $id = post('postman');
        $postman = User::find($id);

        $postmen = Session::get('postmen');
        $postmen['postman_id'] = $postman->id;
        $postmen['acceptance_id'] = 2;
        $postmen['accepted_at'] = null;
        $postmen['receive_at'] = null;

        Session::put('postmen', $postmen);


        throw new \ApplicationException(print_r(Session::get('postmen')));
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

    public function onSearch()
    {
        $this->vars['ehsan'] = 'salam';
    }

    public function getSaveValue($value)
    {
        return $value;
    }
}
