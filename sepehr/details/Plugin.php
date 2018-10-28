<?php namespace Sepehr\Details;

use System\Classes\PluginBase;
use System\Classes\SettingsManager;
use RainLab\User\Models\User;
use Ls\Details\Models\ProccessRegisterUserCourse;
use Backend;
use ApplicationException;

class Plugin extends PluginBase
{
    public function pluginDetails()
    {
        return [
            "name" => 'sepehr.details::lang.plugin.name',
            "description" => 'sepehr.details::lang.plugin.description',
            "author" => "sepehr",
            "icon" => "oc-icon-database",
            "homepage" => ''
        ];
    }

    public function registerPermissions()
    {
        return [
            //users
            "sepehr.details.users.access_sex" => [
                "tab" => 'اطلاعات پایه اشخاص',
                "label" => 'مدیریت جنسیت ها',
                "order" => 1
            ],
            "sepehr.details.users.access_country_code" => [
                "tab" => 'اطلاعات پایه اشخاص',
                "label" => 'مدیریت کد کشورها',
                "order" => 1
            ],

            //financial
            "sepehr.details.financial.access_payment_type" => [
                "tab" => 'اطلاعات پایه مالی و حسابداری',
                "label" => 'مدیریت انواع پرداخت',
                "order" => 1
            ],

            //service
            "sepehr.details.service.access_package_type" => [
                "tab" => 'اطلاعات پایه سرویس پست',
                "label" => 'مدیریت انواع بسته ها',
                "order" => 1
            ],
            "sepehr.details.service.access_special_service" => [
                "tab" => 'اطلاعات پایه سرویس پست',
                "label" => 'مدیریت انواع سرویس ویژه',
                "order" => 2
            ],
            "sepehr.details.service.access_post_type" => [
                "tab" => 'اطلاعات پایه سرویس پست',
                "label" => 'مدیریت انواع سرویس پست',
                "order" => 3
            ],
            "sepehr.details.service.access_distribution_time" => [
                "tab" => 'اطلاعات پایه سرویس پست',
                "label" => 'مدیریت انواع زمان توزیع',
                "order" => 4
            ],
            "sepehr.details.service.access_status" => [
                "tab" => 'اطلاعات پایه سرویس پست',
                "label" => 'مدیریت انواع وضعیت ها',
                "order" => 5
            ],
            "sepehr.details.service.access_insurance_types" => [
                "tab" => 'اطلاعات پایه سرویس پست',
                "label" => 'مدیریت انواع بیمه',
                "order" => 6
            ],
            "sepehr.details.service.access_acceptance" => [
                "tab" => 'اطلاعات پایه سرویس پست',
                "label" => 'مدیریت انواع پذیرش',
                "order" => 7
            ],
            "sepehr.details.service.access_weight" => [
                "tab" => 'اطلاعات پایه سرویس پست',
                "label" => 'مدیریت انواع وزن ',
                "order" => 8
            ],
        ];
    }

    public function registerNavigation()
    {
        return [
            'details' => [
                'label' => 'اطلاعات پایه',
                'url' => Backend::url('sepehr/details/index'),
                'icon' => 'icon-database',
                'iconSvg' => 'plugins/sepehr/details/assets/images/checked.svg',
                'permissions' => ['sepehr.details.*'],
                'order' => 300,
                'sideMenu' => [
                    "users" => [
                        'label' => 'اشخاص',
                        'url' => 'javascript:;',
                        'icon' => 'icon-male',
                        'attributes' => ['data-menu-item' => 'users'],
                        'permissions' => ['sepehr.details.users.*']
                    ],
                    "financial" => [
                        'label' => 'مالی و حسابداری',
                        'url' => 'javascript:;',
                        'icon' => 'icon-calculator',
                        'attributes' => ['data-menu-item' => 'financial'],
                        'permissions' => ['sepehr.details.financial.*']
                    ],
                    "services" => [
                        'label' => 'خدمات ',
                        'url' => 'javascript:;',
                        'icon' => 'icon-diamond',
                        'attributes' => ['data-menu-item' => 'services'],
                        'permissions' => ['sepehr.details.services.*']
                    ],
                ]
            ]
        ];
    }

    public function registerComponents()
    {
        return [];
    }
}
