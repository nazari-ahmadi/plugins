<?php namespace Sepehr\Service\FormWidgets;

use Backend\Classes\FormWidgetBase;
use Sepehr\Service\Models\Service;

/**
 * Packages Form Widget
 */
class Packages extends FormWidgetBase
{
    /**
     * @inheritDoc
     */
    protected $defaultAlias = 'sepehr_service_packages';

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
        return $this->makePartial('packages');
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
    }

    /**
     * @inheritDoc
     */
    public function loadAssets()
    {
        $this->addCss('css/packages.css', 'sepehr.service');
        $this->addJs('js/packages.js', 'sepehr.service');
    }

    /**
     * @inheritDoc
     */
    public function getSaveValue($value)
    {
        return $value;
    }
}
