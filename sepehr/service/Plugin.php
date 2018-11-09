<?php namespace Sepehr\Service;

use Backend;
use Sepehr\Service\Components\PostmanServices;
use Sepehr\Service\Components\ReferralPostman;
use Sepehr\Service\Components\RequestService;
use Sepehr\Service\Components\ServiceDelivery;
use Sepehr\Service\Components\ServiceList;
use Sepehr\Service\Components\Wallet;
use Sepehr\Service\FormWidgets\Packages;
use Sepehr\Service\FormWidgets\Payments;
use Sepehr\Service\FormWidgets\Postman;
use System\Classes\PluginBase;

class Plugin extends PluginBase
{

    public function pluginDetails()
    {
        return [
            "name" => 'sepehr.service::lang.plugin.name',
            "description" => 'sepehr.service::lang.plugin.description',
            "author" => "sepehr",
            "icon" => "oc-icon-database",
            "homepage" => ''
        ];
    }


    public function registerPermissions()
    {
        return [
            "sepehr.service.access_service" => [
                "tab" => 'سرویس',
                "label" => 'مدیریت سرویس',
                "order" => 1
            ],
        ];
    }

    public function registerNavigation()
    {
        return [
            'services' => [
                'label' => 'سرویس',
                'url' => Backend::url('sepehr/service/index'),
                'iconSvg' => 'plugins/sepehr/service/assets/images/service.svg',
                'permissions' => ['sepehr.service.*'],
                'order' => 300,
                'sideMenu' => [
                    'service' => [
                        'label' => 'سرویس',
                        'url' => Backend::url('sepehr/service/services'),
                        'icon' => 'icon-envelope',
                        'permissions' => ['sepehr.service.access_service'],
                        'order' => 300,
                    ],

                ]
            ]
        ];
    }

    public function registerComponents()
    {
        return [
            RequestService::class => 'requestService',
            ServiceList::class => 'serviceList',
            ReferralPostman::class => 'referralPostman',
            PostmanServices::class => 'PostmanServices',
            ServiceDelivery::class => 'ServiceDelivery',
            Wallet::class => 'walletComponent'
        ];
    }

    public function registerSettings()
    {

    }

    public function registerFormWidgets()
    {
        return
            [
                Postman::class => [
                    'label' => 'پستچی',
                    'code' => 'postman'
                ],
                Payments::class => [
                    'label' => 'پرداخت',
                    'code' => 'payment'
                ],
                Packages::class => [
                    'label' => 'بسته ها',
                    'code' => 'servicePackages'
                ]
            ];
    }

}
