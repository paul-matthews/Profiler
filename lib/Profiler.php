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

    public function getIterator()
    {
        return new ArrayIterator($this->profilers);
    }
}
