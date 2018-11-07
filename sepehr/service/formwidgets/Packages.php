<?php namespace Sepehr\Service\FormWidgets;

use Backend\Classes\FormWidgetBase;
use Sepehr\Details\Models\DistributionTime;
use Sepehr\Details\Models\InsuranceType;
use Sepehr\Details\Models\PackageType;
use Sepehr\Details\Models\PostType;
use Sepehr\Details\Models\SpecialService;
use Sepehr\Details\Models\Weight;
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
        $this->vars['weight']=Weight::orderBy('id')->get();
        $this->vars['postTypes']=PostType::orderBy('id')->get();
        $this->vars['packageTypes']=PackageType::orderBy('id')->get();
        $this->vars['insuranceTypes']=InsuranceType::orderBy('id')->get();
        $this->vars['distributionTimes']=DistributionTime::orderBy('id')->get();
        $this->vars['specialServices']=SpecialService::orderBy('id')->get();
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
