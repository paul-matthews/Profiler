<?php
require_once(dirname(__FILE__) . '/../lib/Profiler.php');

class ProfilerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function startingProfilerShouldCreateProfile()
    {
        $profiler = new Profiler();

        $profiler->start();

        $profilers = $profiler->getIterator();
        $this->assertEquals(1, count($profilers));
    }

    /**
     * @test
     */
    public function startingProfilerWithNameShouldCreateProfilerWithName()
    {
        $profiler = new Profiler();

        $profiler->start('profile');

        $profilers = $profiler->getIterator();
        $test = $profilers->current();

        $this->assertEquals('profile', $test['name']);
    }

    /**
     * @test
     */
    public function startAndStopProfilerShouldReturnSuccess()
    {
        $profiler = new Profiler();

        $token = $profiler->start('profile');

        $this->assertTrue($profiler->stop($token));
    }

    /**
     * @test
     */
    public function startProfilerIsRunning()
    {
        $profiler = new Profiler();

        $token = $profiler->start();

        $profilers = $profiler->getIterator();
        $test = $profilers->current();

        $this->assertEquals(Profiler::RUNNING, $test['status']);
    }

    /**
     * @test
     */
    public function startAndStopProfilerIsStopped()
    {
        $profiler = new Profiler();

        $token = $profiler->start();
        $profiler->stop($token);

        $profilers = $profiler->getIterator();
        $test = $profilers->current();

        $this->assertEquals(Profiler::STOPPED, $test['status']);
    }

    /**
     * @test
     */
    public function startProfilerHasStartTime()
    {
        $profiler = new Profiler();

        $token = $profiler->start();

        $profilers = $profiler->getIterator();
        $test = $profilers->current();

        $this->assertTrue(!empty($test['start']));
    }

    /**
     * @test
     */
    public function startAndStopProfilerHasEndTime()
    {
        $profiler = new Profiler();

        $token = $profiler->start();
        $profiler->stop($token);

        $profilers = $profiler->getIterator();
        $test = $profilers->current();

        $this->assertTrue(!empty($test['stop']));
    }

    /**
     * @test
     */
    public function startAndStopProfilerHasDuration()
    {
        $profiler = new Profiler();

        $token = $profiler->start();
        $profiler->stop($token);

        $profilers = $profiler->getIterator();
        $test = $profilers->current();

        $this->assertTrue(isset($test['duration']));
    }

    /**
     * @test
     */
    public function startAndStopProfilerHasValidDuration()
    {
        $profiler = new Profiler();

        $token = $profiler->start();
        sleep(1);
        $profiler->stop($token);

        $profilers = $profiler->getIterator();
        $test = $profilers->current();

        $this->assertEquals(1, (int) $test['duration']);
    }

}
