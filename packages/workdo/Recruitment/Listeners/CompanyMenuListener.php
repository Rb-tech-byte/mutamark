<?php

namespace Modules\Recruitment\Listeners;

use App\Events\CompanyMenuEvent;

class CompanyMenuListener
{
    /**
     * Handle the event.
     */
    public function handle(CompanyMenuEvent $event): void
    {
        $module = 'Recruitment';
        $menu = $event->menu;
        $menu->add([
            'category' => 'General',
            'title' => __('Recruitment Dashboard'),
            'icon' => '',
            'name' => 'recruitment-dashboard',
            'parent' => 'dashboard',
            'order' => 35,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'recruitment.dashboard',
            'module' => $module,
            'permission' => 'recruitment dashboard manage'
        ]);
        $menu->add([
            'category' => 'HR',
            'title' => __('Recruitment'),
            'icon' => 'user-plus',
            'name' => 'recruitment',
            'parent' => '',
            'order' => 453,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => '',
            'module' => $module,
            'permission' => 'recruitment manage'
        ]);
        $menu->add([
            'category' => 'HR',
            'title' => __('Jobs'),
            'icon' => '',
            'name' => 'jobs',
            'parent' => 'recruitment',
            'order' => 10,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'job.index',
            'module' => $module,
            'permission' => 'job manage'
        ]);
        $menu->add([
            'category' => 'HR',
            'title' => __('Job Create'),
            'icon' => '',
            'name' => 'job-create',
            'parent' => 'recruitment',
            'order' => 15,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'job.create',
            'module' => $module,
            'permission' => 'job create'
        ]);
        $menu->add([
            'category' => 'HR',
            'title' => __('Job Application'),
            'icon' => '',
            'name' => 'job-application',
            'parent' => 'recruitment',
            'order' => 20,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'job-application.index',
            'module' => $module,
            'permission' => 'jobapplication manage'
        ]);
        $menu->add([
            'category' => 'HR',
            'title' => __('Job Archived'),
            'icon' => '',
            'name' => 'job-archived',
            'parent' => 'recruitment',
            'order' => 25,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'job.application.archived',
            'module' => $module,
            'permission' => 'jobapplication archived manage'
        ]);
        $menu->add([
            'category' => 'HR',
            'title' => __('Job Candidate'),
            'icon' => '',
            'name' => 'job-candidate',
            'parent' => 'recruitment',
            'order' => 30,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'job-candidates.index',
            'module' => $module,
            'permission' => 'jobapplication candidate manage'
        ]);
        $menu->add([
            'category' => 'HR',
            'title' => __('Job On-boarding'),
            'icon' => '',
            'name' => 'job-on-boarding',
            'parent' => 'recruitment',
            'order' => 35,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'job.on.board',
            'module' => $module,
            'permission' => 'jobonboard manage'
        ]);
        $menu->add([
            'category' => 'HR',
            'title' => __('Custom Question'),
            'icon' => '',
            'name' => 'custom-question',
            'parent' => 'recruitment',
            'order' => 40,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'custom-question.index',
            'module' => $module,
            'permission' => 'custom question manage'
        ]);
        $menu->add([
            'category' => 'HR',
            'title' => __('Interview Schedule'),
            'icon' => '',
            'name' => 'interview-schedule',
            'parent' => 'recruitment',
            'order' => 45,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'interview-schedule.index',
            'module' => $module,
            'permission' => 'interview schedule manage'
        ]);
        $menu->add([
            'category' => 'HR',
            'title' => __('System Setup'),
            'icon' => '',
            'name' => 'system-setup',
            'parent' => 'recruitment',
            'order' => 50,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'job-category.index',
            'module' => $module,
            'permission' => 'branch manage'
        ]);
        $menu->add([
            'category' => 'HR',
            'title' => __('Career'),
            'icon' => '',
            'name' => 'career',
            'parent' => 'recruitment',
            'order' => 55,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'career',
            'module' => $module,
            'permission' => 'career manage'
        ]);
    }
}
