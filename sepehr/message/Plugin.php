<?php namespace Sepehr\Message;

use System\Classes\PluginBase;
use Backend;

class Plugin extends PluginBase
{

	public function pluginDetails()
	{
		return [
			"name" 			=> 'sepehr.message::lang.plugin.name',
			"description" 	=> 'sepehr.message::lang.plugin.description',
		    "author" 		=> "sepehr",
		    "icon" 			=> "oc-icon-envelope-o",
		    "homepage" 		=> ''
			];
	}

    public function registerComponents()
    {
    }

    public function registerSettings()
    {
    }

    public function registerNavigation()
	{
		return [
            'messages' => [
                'label'       => 'پیام ها',
                'url'         => Backend::url('sepehr/message/indexPage'),
                'icon'        => 'oc-icon-bell',
                'iconSvg'     => 'plugins/sepehr/message/assets/images/email.svg',
                'permissions' => ['sepehr.message.*'],
                'order'       => 300,
                'sideMenu' 	  => [
                	"message" => [
                		'label' 		       => 'لیست پیام ها',
		                'url'   		       => Backend::url('sepehr/message/messages'),
		                'icon'  	  	     => 'oc-icon-list',
                    	'attributes'     => ['data-menu-item'=>'message'],
		                'permissions' 	   => ['sepehr.message.access_message']
                	]
                ]
			]
		];
	}

	public function registerPermissions()
	{
		return [
  			"sepehr.message.access_message" => [
  				"tab" 	=> 'پیام ها',
  				"label" => 'ممدیریت پیام ها',
  				"order" => 1
  			]
  		];
  	}
}
