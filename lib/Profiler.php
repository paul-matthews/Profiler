<?php

class Profiler implements IteratorAggregate
{
    const RUNNING = 'running';
    const STOPPED = 'stopped';

    private $profilers;

    public function start($name = null, $groupName = null)
    {
        $this->profilers[] = array(
            'name' => $name,
            'group_name' => $groupName,
            'status' => self::RUNNING,
            'start_time' => microtime(true),
            'start_mem' => memory_get_usage(true),
        );
        end($this->profilers);

        return key($this->profilers);
    }

    public function stop($token)
    {
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

        return true;
    }

    public function getProfileByToken($token)
    {
        if (!isset($this->profilers[$token])) {
            throw new Exception("Profile not found with token: '$token'");
        }

        return $this->profilers[$token];
    }

    public function getSummary($groupName = null)
    {
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

    public function getIterator($groupName = null)
    {
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
}
