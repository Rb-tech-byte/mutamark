<?php

namespace Modules\Contract\Listeners;
use App\Events\CompanySettingEvent;

class CompanySettingListener
{
    /**
     * Handle the event.
     */
    public function handle(CompanySettingEvent $event): void
    {
        $module = 'Contract';
        if(in_array($module,$event->html->modules))
        {
            $methodName = 'index';
            $controllerClass = "Modules\\Contract\\Http\\Controllers\\Company\\SettingsController";
            if (class_exists($controllerClass)) {
                $controller = \App::make($controllerClass);
                if (method_exists($controller, $methodName)) {
                    $html = $event->html;
                    $settings = $html->getSettings();
                    $output =  $controller->{$methodName}($settings);
                    $html->add([
                        'html' => $output->toHtml(),
                        'order' => 310,
                        'module' => $module,
                        'permission' => 'contract manage'
                    ]);
                }
            }
        }
    }
}
