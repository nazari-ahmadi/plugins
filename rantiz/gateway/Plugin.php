<?php namespace Rantiz\Gateway;

use App;
use Backend;
use System\Classes\PluginBase;
use Illuminate\Foundation\AliasLoader;
use System\Classes\SettingsManager;
use Illuminate\Support\Facades\Storage;
use File;

class Plugin extends PluginBase
{

    /**
     * @var array Plugin dependencies
     */
    public $require = [
        // 'Rainlab.User'
    ];

    public function boot()
    {
        // Register service providers
        App::register('Larabookir\Gateway\GatewayServiceProvider');

        // Register facades
        $facade = AliasLoader::getInstance();
        $facade->alias('Gateway', '\Larabookir\Gateway\Gateway');

        File::copy(base_path('plugins/rantiz/gateway/assets/layouts/form-gateway.htm'),base_path('modules/backend/layouts/form-gateway.htm'));
    }


    public function pluginDetails()
    {
        return [
            'name'        => 'rantiz.gateway::lang.plugin.name',
            'description' => 'rantiz.gateway::lang.plugin.description',
            'author'      => 'javad kh',
            'icon'        => 'icon-credit-card',
            'homepage'    => ''
        ];
    }

    public function registerComponents()
    {
        return [
            'Rantiz\Gateway\Components\Send'       => 'sendGateway',
            'Rantiz\Gateway\Components\Verify'       => 'verifyGateway',
            'Rantiz\Gateway\Components\Transactions'       => 'transactionsGateway',
        ];
    }

    public function registerPermissions()
    {
        return [
            'rantiz.gateway.main.access_lists' => [
                'tab' => 'rantiz.gateway::lang.permission.tab', 
                'label' => 'rantiz.gateway::lang.permission.access_lists'
            ],
            'rantiz.gateway.main.access_logs'    => [
                'tab' => 'rantiz.gateway::lang.permission.tab', 
                'label' => 'rantiz.gateway::lang.permission.access_logs'
            ],
            'rantiz.gateway.main.access_delete'    => [
                'tab' => 'rantiz.gateway::lang.permission.tab', 
                'label' => 'rantiz.gateway::lang.permission.access_delete'
            ],
            'rantiz.gateway.access_settings'    => [
                'tab' => 'rantiz.gateway::lang.permission.tab', 
                'label' => 'مدیریت تنظیمات درگاه بانک'
            ],            
        ];
    }

    public function registerNavigation()
    {
        return [
            'gateway' => [
                'label'       => 'rantiz.gateway::lang.menu.gateway',
                'url'         => Backend::url('rantiz/gateway/lists'),
                'icon'        => 'icon-credit-card',
                'iconSvg'     => 'plugins/rantiz/gateway/assets/images/Circle-icons-creditcard.svg',
                'permissions' => ['rantiz.gateway.main.*'],
                'order'       => 300,

                'sideMenu' => [
                    'lists' => [
                        'label'       => 'rantiz.gateway::lang.menu.lists',
                        'icon'        => 'icon-list',
                        'url'         => Backend::url('rantiz/gateway/lists'),
                        'permissions' => ['rantiz.gateway.main.access_lists']
                    ],
                    'logs' => [
                        'label'       => 'rantiz.gateway::lang.menu.logs',
                        'icon'        => 'icon-list-ul',
                        'url'         => Backend::url('rantiz/gateway/logs'),
                        'permissions' => ['rantiz.gateway.main.access_logs']
                    ]
                ]
            ]
        ];
    }

    public function registerSettings()
    {
        return [
            'settings' => [
                'label'       => 'rantiz.gateway::lang.menu.gateway',
                'description' => 'rantiz.gateway::lang.menu.gateway_setting_desc',
                'category'    => 'درگاه پرداخت',
                'icon'        => 'icon-cog',
                'class'       => 'Rantiz\Gateway\Models\Settings',
                'order'       => 500,
                'permissions' => ['rantiz.gateway.access_settings'],
            ]
        ];
    }

}
