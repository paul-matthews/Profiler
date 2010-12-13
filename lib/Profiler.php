<?php

class Profiler implements IteratorAggregate
{
    const RUNNING = 'running';
    const STOPPED = 'stopped';

    private $profilers;

    public function start($name = null)
    {
        $this->profilers[] = array(
            'name' => $name,
            'status' => self::RUNNING,
            'start' => microtime(true),
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
        $profile['stop'] = microtime(true);
        $profile['duration'] = $profile['stop'] - $profile['start'];

        $this->profilers[$token] = $profile;

        return true;
    }

    public function getIterator()
    {
        return new ArrayIterator($this->profilers);
    }
}
