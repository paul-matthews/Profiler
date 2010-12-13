<?php
require_once(dirname(__FILE__) . '/../lib/Profiler.php');

class ProfilerTest extends PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        $this->profiler = new Profiler();
    }

    public function tearDown()
    {
        unset($this->profiler);
    }

    /**
     * @test
     */
    public function startingProfilerShouldCreateProfile()
    {
        $this->profiler->start();

        $profilers = $this->profiler->getIterator();
        $this->assertEquals(1, count($profilers));
    }

    /**
     * @test
     */
    public function startingProfilerWithNameShouldCreateProfilerWithName()
    {
        $this->profiler->start('profile');

        $test = $this->getFirstProfile($this->profiler);

        $this->assertEquals('profile', $test['name']);
    }

    /**
     * @test
     */
    public function startAndStopProfilerShouldReturnSuccess()
    {
        $token = $this->profiler->start('profile');

        $this->assertTrue($this->profiler->stop($token));
    }

    /**
     * @test
     */
    public function startProfilerIsRunning()
    {
        $token = $this->profiler->start();

        $test = $this->getFirstProfile($this->profiler);

        $this->assertEquals(Profiler::RUNNING, $test['status']);
    }

    /**
     * @test
     */
    public function startAndStopProfilerIsStopped()
    {
        $token = $this->profiler->start();
        $this->profiler->stop($token);

        $test = $this->getFirstProfile($this->profiler);

        $this->assertEquals(Profiler::STOPPED, $test['status']);
    }

    /**
     * @test
     */
    public function startProfilerHasStartTime()
    {
        $token = $this->profiler->start();

        $test = $this->getFirstProfile($this->profiler);

        $this->assertTrue(!empty($test['start_time']));
    }

    /**
     * @test
     */
    public function startAndStopProfilerHasEndTime()
    {
        $token = $this->profiler->start();
        $this->profiler->stop($token);

        $test = $this->getFirstProfile($this->profiler);

        $this->assertTrue(!empty($test['stop_time']));
    }

    /**
     * @test
     */
    public function startAndStopProfilerHasDuration()
    {
        $token = $this->profiler->start();
        $this->profiler->stop($token);

        $test = $this->getFirstProfile($this->profiler);

        $this->assertTrue(isset($test['duration']));
    }

    /**
     * @test
     */
    public function startAndStopProfilerHasValidDuration()
    {
        $token = $this->profiler->start();
        sleep(1);
        $this->profiler->stop($token);

        $test = $this->getFirstProfile($this->profiler);

        $this->assertEquals(1, (int) $test['duration']);
    }

    /**
     * @test
     */
    public function startProfilerHasInitialMemory()
    {
        $token = $this->profiler->start();

        $test = $this->getFirstProfile($this->profiler);

        $this->assertTrue(is_numeric($test['start_mem']));
        $this->assertTrue($test['start_mem'] > 0);
    }

    /**
     * @test
     */
    public function startAndStopProfilerHasStopMemory()
    {
        $token = $this->profiler->start();
        $this->profiler->stop($token);

        $test = $this->getFirstProfile($this->profiler);

        $this->assertTrue(is_numeric($test['stop_mem']));
        $this->assertTrue($test['stop_mem'] > 0);
    }

    /**
     * @test
     */
    public function startAndStopProfilerWithGroup()
    {
        $token = $this->profiler->start('profile name', 'group name');
        $this->profiler->stop($token);

        $test = $this->getFirstProfile($this->profiler);

        $this->assertEquals(Profiler::STOPPED, $test['status']);
        $this->assertEquals('group name', $test['group_name']);
        $this->assertEquals('profile name', $test['name']);
    }

    /**
     * @test
     */
    public function checkDefaultNameIsBlank()
    {
        $token = $this->profiler->start();
        $this->profiler->stop($token);

        $test = $this->getFirstProfile($this->profiler);

        $this->assertEquals('', $test['name']);
    }

    /**
     * @test
     */
    public function checkDefaultGroupNameIsBlank()
    {
        $token = $this->profiler->start('profile name');
        $this->profiler->stop($token);

        $test = $this->getFirstProfile($this->profiler);

        $this->assertEquals('', $test['group_name']);
    }

    protected function getFirstProfile($profiler)
    {
        $profilers = $profiler->getIterator();
        return $profilers->current();
    }
}
