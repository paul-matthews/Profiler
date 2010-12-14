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
        $count = count($profiles);
        $running = 0;
        $longest = 0;
        $highestUsageMem = 0;
        $totalTime = 0;

        foreach ($profiles as $key => $profile) {
            if ($profile['status'] == self::RUNNING) {
                $running++;
            }
            else {
                $totalTime += $profile['duration'];
            }

            if ($profile['duration'] > $longest) {
                $longest = $profile['duration'];
            }

            if ($profile['usage_mem'] > $highestUsageMem) {
                $highestUsageMem = $profile['usage_mem'];
            }

        }
        $finished = $count - $running;

        $averageTime = 0;
        if ($finished > 0) {
            $averageTime = $totalTime / $finished;
        }

        return array(
            'count' => $count,
            'count_running' => $running,
            'longest' => $longest,
            'highest_usage_mem' => $highestUsageMem,
            'total_time' => $totalTime,
            'avg_time' => $averageTime,
        );
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
