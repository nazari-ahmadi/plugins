<?php namespace Sepehr\Service\FormWidgets;

use Backend\Classes\FormWidgetBase;
use Sepehr\Details\Models\DistributionTime;
use Sepehr\Details\Models\InsuranceType;
use Sepehr\Details\Models\PackageType;
use Sepehr\Details\Models\PostType;
use Sepehr\Details\Models\SpecialService;
use Sepehr\Details\Models\Weight;
use Sepehr\Service\Models\Service;
use ValidationException;


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
        $this->vars['packages'] = $this->model->packages;
        $this->vars['name'] = $this->formField->getName();
        $this->vars['value'] = $this->getLoadValue();
        $this->vars['model'] = $this->model;
        $this->vars['weight'] = Weight::orderBy('id')->get();
        $this->vars['postTypes'] = PostType::orderBy('id')->get();
        $this->vars['packageTypes'] = PackageType::orderBy('id')->get();
        $this->vars['insuranceTypes'] = InsuranceType::orderBy('id')->get();
        $this->vars['distributionTimes'] = DistributionTime::orderBy('id')->get();
        $this->vars['specialServices'] = SpecialService::orderBy('id')->get();
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

    public function onCreatePackage()
    {
        $id = post('package_id');
        $packages = $this->model->packages;
        if ($id!=null){
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
        $this->model->packages = $packages;
        $this->model->save();
        $this->vars['service'] = new Service();
        $this->vars['model'] = $this->model;

    }


    public function onDeletePackage()
    {
        $id = post('id');
        $packages = $this->model->packages;
        if ($id != null) {
            unset($packages[$id]);

        }
        $this->model->packages = $packages;
        $this->model->save();
        $this->vars['service'] = new Service();
        $this->vars['model']=$this->model;
    }


}
