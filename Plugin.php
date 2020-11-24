<?php

namespace Kanboard\Plugin\WeeklyRecurringTasks;

use Kanboard\Core\Plugin\Base;
use Kanboard\Core\Translator;
use Kanboard\Plugin\WeeklyRecurringTasks\Action\WeeklyRecurringTask;

class Plugin extends Base
{
    public function initialize()
    {
		$this->actionManager->register(new WeeklyRecurringTask($this->container));
    }

    public function onStartup()
    {
        Translator::load($this->languageModel->getCurrentLanguage(), __DIR__.'/Locale');
    }

    public function getPluginName()
    {
        return 'Weekly Recurring Tasks';
    }

    public function getPluginDescription()
    {
        return t('Automatically clones Tasks with the DAILY/WEEKLY/BIWEEKLY or MONDAY/TUESDAY/WEDNESDAY/THURSDAY/FRIDAY/SATURDAY/SUNDAY tag');
    }

    public function getPluginAuthor()
    {
        return 'Sebastian Pape, Sebastien Diot';
    }

    public function getPluginVersion()
    {
        return '1.0.2';
    }

    public function getPluginHomepage()
    {
        return 'https://github.com/PapeCoding/WeeklyRecurringTasks';
    }
}

