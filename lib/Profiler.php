<?php
require_once(dirname(__FILE__) . '/ProfilerException.php');
require_once(dirname(__FILE__) . '/ProfilerDisabledException.php');
require_once(dirname(__FILE__) . '/Observable.php');
require_once(dirname(__FILE__) . '/Observer.php');


class Profiler implements IteratorAggregate, Observable
{
    const START_MESSAGE_FORMAT = '[%d] Started profile: [%s] %s';
    const STOP_MESSAGE_FORMAT = '[%d] Stopped profile: [%s] %s (Duration: %0.3f, Mem: %db)';
    const SUMMARY_MESSAGE_FORMAT = 'Count: %d, Remaining: %d, Longest: %0.3f, Highest Mem: %db, Total Time: %0.3f, Average Time: %0.3f';


    const RUNNING = 'running';
    const STOPPED = 'stopped';

    private $enabled;
    private $profilers;
    private $groups;

    public function __construct($enabled = false)
    {
        $this->setEnabled($enabled);
        $this->groups = array();
        $this->profilers = array();
    }

    public function start($name = null, $groupName = null)
    {
        $this->ensureEnabled();

        $this->profilers[] = array(
            'name' => $name,
            'group_name' => $groupName,
            'status' => self::RUNNING,
            'start_time' => microtime(true),
            'start_mem' => memory_get_usage(true),
        );
        end($this->profilers);

        $key = key($this->profilers);

        $this->addGroup($groupName);

        $this->notify(sprintf(
            self::START_MESSAGE_FORMAT,
            $key,
            $groupName,
            $name
        ), Observer::DEBUG);

        return $key;
    }

    public function stop($token)
    {
        $this->ensureEnabled();

        if (!$this->enabled || $token === false) {
            return false;
        }

        if (!$this->profilers[$token]) {
            throw new Exception('Unknown Token');
        }

        $profile = $this->profilers[$token];

        $profile['status'] = self::STOPPED;

        $profile['stop_time'] = microtime(true);
        $profile['duration'] = $profile['stop_time'] - $profile['start_time'];

        $profile['stop_mem'] = memory_get_usage(true);
        $profile['usage_mem'] = $profile['stop_mem'] - $profile['start_mem'];

        $this->profilers[$token] = $profile;

        $message = "[$token] Stop profile: " .
            "[{$profile['name']}] {$profile['group_name']} " .
            "";
        $this->notify(sprintf(
            self::STOP_MESSAGE_FORMAT,
            $token,
            $profile['name'],
            $profile['group_name'],
            $profile['duration'],
            $profile['usage_mem']
        ), Observer::DEBUG);

        return true;
    }

    public function getProfileByToken($token)
    {
        $this->ensureEnabled();

        if (!isset($this->profilers[$token])) {
            throw new Exception("Profile not found with token: '$token'");
        }

        return $this->profilers[$token];
    }

    public function getGroups()
    {
        return $this->groups;
    }

    public function getSummary($groupName = null)
    {
        $this->ensureEnabled();

        $profiles = $this->getIterator($groupName);
        $summary = array(
            'count' => 0,
            'count_running' => 0,
            'longest' => 0,
            'highest_usage_mem' => 0,
            'total_time' => 0,
            'avg_time' => 0,
        );

        foreach ($profiles as $key => $profile) {
            $summary['count']++;

            if ($profile['status'] == self::RUNNING) {
                $summary['count_running']++;
            }
            else {
                $summary['total_time'] += $profile['duration'];
            }

            if ($profile['duration'] > $summary['longest']) {
                $summary['longest'] = $profile['duration'];
            }

            if ($profile['usage_mem'] > $summary['highest_usage_mem']) {
                $summary['highest_usage_mem'] = $profile['usage_mem'];
            }

        }
        $summary['finished'] = $summary['count'] - $summary['count_running'];

        if ($summary['finished'] > 0) {
            $summary['avg_time'] =
                $summary['total_time'] / $summary['finished'];
        }

        return $summary;
    }

    public function register(Observer $observer)
    {
        $this->observers[] = $observer;

        end($this->observers);
        return key($this->observers);
    }

    public function triggerObserveSummaryAll()
    {
        foreach ($this->getGroups() as $groupName)
        {
            $this->triggerObserveSummary($groupName);
        }
        $this->triggerObserveSummary();
    }

    public function triggerObserveSummary($groupName = null)
    {
        $summary = $this->getSummary($groupName);

        $this->notify(sprintf(
            self::SUMMARY_MESSAGE_FORMAT,
            $summary['count'],
            $summary['count_running'],
            $summary['longest'],
            $summary['highest_usage_mem'],
            $summary['total_time'],
            $summary['avg_time']
        ));
    }

    public function getIterator($groupName = null)
    {
        $this->ensureEnabled();

        $profilers = $this->profilers;

        if (!empty($groupName)) {
            foreach ($profilers as $key => $profile) {
                if ($profile['group_name'] != $groupName) {
                    unset($profilers[$key]);
                }
            }
        }

        return new ArrayIterator($profilers);
    }

    public function setEnabled($enabled = false)
    {
        $this->enabled = (boolean) $enabled;
    }

    public function getEnabled()
    {
        return $this->enabled;
    }

    protected function ensureEnabled()
    {
        if (!$this->enabled) {
            throw new ProfilerDisabledException('Profiler disabled');
        }

        return true;
    }

    protected function notify($message, $type = Observer::DEBUG)
    {
        if (!is_array($this->observers)) {
            $this->observers = array();
        }
        foreach ($this->observers as $observer)
        {
            $observer->notify($this, $message, $type);
        }
        return true;
    }

    protected function addGroup($groupName)
    {
        if (!empty($groupName) && !in_array($groupName, $this->groups)) {
            $this->groups[] = $groupName;
        }
        return $this;
    }
}
