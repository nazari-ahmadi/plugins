<?php namespace Rantiz\Gateway\Models;

use Lang;
use Model;
use Cms\Classes\Page;

class Settings extends Model
{
    public $implement = ['System.Behaviors.SettingsModel'];

    public $settingsCode = 'gateway_settings';
    public $settingsFields = 'fields.yaml';

    const MELLAT = 'MELLAT';
    const SADAD = 'SADAD';
    const ZARINPAL = 'ZARINPAL';
    const PAYLINE = 'PAYLINE';
    const JAHANPAY = 'JAHANPAY';
    const PARSIAN = 'PARSIAN';
    const PASARGAD = 'PASARGAD';
    const SAMAN = 'SAMAN';

    public function getEnablePortOptions()
    {
        return [
            self::MELLAT => ['rantiz.gateway::lang.form.setting.mellat.name', ''],
            self::SADAD => ['rantiz.gateway::lang.form.setting.sadad.name', ''],
            self::ZARINPAL => ['rantiz.gateway::lang.form.setting.zarinpal.name', ''],
            self::PAYLINE => ['rantiz.gateway::lang.form.setting.payline.name', ''],
            // self::JAHANPAY => ['rantiz.gateway::lang.form.setting.jahanpay.name', ''],
            self::PARSIAN => ['rantiz.gateway::lang.form.setting.parsian.name', ''],
            self::PASARGAD => ['rantiz.gateway::lang.form.setting.pasargad.name', ''],
            self::SAMAN => ['rantiz.gateway::lang.form.setting.saman.name', ''],
        ];
    }

    public function getCallbackUrlOptions()
    {
        $allPages = Page::sortBy('baseFileName')->lists('title', 'baseFileName');
        $pages = array(
            '' => Lang::get('benfreke.menumanager::lang.create.nolink')
        );
        foreach ($allPages as $key => $value) {
            $pages[$key] = "{$value} - (File: $key)";
        }

        return $pages;
    }
}