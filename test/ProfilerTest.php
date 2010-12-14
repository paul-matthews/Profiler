<?php
require_once(dirname(__FILE__) . '/../lib/Profiler.php');

class ProfilerTest extends PHPUnit_Framework_TestCase
{
    protected $profiler;

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

        $test = $this->getfirstprofile($this->profiler);

        $this->assertequals('', $test['group_name']);
    }

    /**
     * @test
     */
    public function checkGetProfilerByTokenReturnsProfile()
    {
        $token = $this->profiler->start('profile name');
        $this->profiler->stop($token);

        $profile = $this->profiler->getProfileByToken($token);

        $this->assertequals('profile name', $profile['name']);
    }

    /**
     * @test
     */
    public function testGroupIteratorReturnsCorrectGroup()
    {
        $this->profiler->start('profile name 1');
        $this->profiler->start('profile name 2', 'group name');

        $test = $this->getFirstProfile($this->profiler, 'group name');

        $this->assertEquals('profile name 2', $test['name']);
    }

    /**
     * @test
     */
    public function testSummaryContainsCountAndRunningCount()
    {
        $token1 = $this->profiler->start('profile name 1');
        $token2 = $this->profiler->start('profile name 2');

        $this->profiler->stop($token1);

        $summary = $this->profiler->getSummary();
        $this->assertEquals(2, $summary['count']);
        $this->assertEquals(1, $summary['count_running']);
    }

    /**
     * @test
     */
    public function testSummaryContainsLongest()
    {
        $token1 = $this->profiler->start('profile name 1');

        sleep(1);

        $token2 = $this->profiler->start('profile name 2');

        $this->profiler->stop($token1);
        $this->profiler->stop($token2);

        $summary = $this->profiler->getSummary();
        $profile1 = $this->profiler->getProfileByToken($token1);

        $this->assertEquals($profile1['duration'], $summary['longest']);
    }

    /**
     * @test
     */
    public function testSummaryContainsHighestMemory()
    {
        $token1 = $this->profiler->start('profile name 1');

        $testVar = array();
        for($i = 0; $i < 1000; $i++) {
            $testVar[] = $this->getLongText();
        }


        $token2 = $this->profiler->start('profile name 2');

        $this->profiler->stop($token1);
        $this->profiler->stop($token2);

        $summary = $this->profiler->getSummary();
        $profile1 = $this->profiler->getProfileByToken($token1);

        $this->assertEquals(
            $profile1['usage_mem'],
            $summary['highest_usage_mem']
        );
    }

    /**
     * @test
     */
    public function testSummaryContainsAvgAndTotalTime()
    {
        $token1 = $this->profiler->start('profile name 1');
        $token2 = $this->profiler->start('profile name 2');

        sleep(1);

        $this->profiler->stop($token1);
        $this->profiler->stop($token2);

        $summary = $this->profiler->getSummary();
        $profile1 = $this->profiler->getProfileByToken($token1);
        $profile2 = $this->profiler->getProfileByToken($token2);

        $this->assertTrue(is_numeric($summary['avg_time']));
        $this->assertTrue(is_numeric($summary['total_time']));

        $this->assertTrue(!empty($summary['avg_time']));
        $this->assertTrue(!empty($summary['total_time']));

        $total = $profile2['duration'] + $profile1['duration'];
        $avg = $total / 2;

        $this->assertEquals($avg, $summary['avg_time']);
        $this->assertEquals($total, $summary['total_time']);
    }

    protected function getFirstProfile($profiler, $groupName = null)
    {
        $profilers = $profiler->getIterator($groupName);
        return $profilers->current();
    }

    protected function getLongText()
    {
        return <<<EOF
        Lorem ipsum dolor sit amet, consectetur adipiscing elit. Quisque sed sapien lorem. Aliquam quis enim metus. Nulla quis augue at elit luctus faucibus vitae sollicitudin nulla. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nullam sed odio ante, sed pellentesque urna. Donec est nisi, congue et aliquam nec, varius eget magna. Morbi mattis sollicitudin nulla ac condimentum. Quisque ultrices, orci in suscipit convallis, diam enim rutrum nulla, ut ornare erat nisl sed velit. Vestibulum aliquet nisl eu orci egestas eu tincidunt diam euismod. Donec ante lorem, dignissim pharetra tristique vel, suscipit nec velit. Maecenas sit amet elit magna, ac tempor leo. Quisque purus diam, faucibus et feugiat imperdiet, rutrum a massa. Fusce at magna a lacus imperdiet dictum vitae vitae libero. Integer fermentum purus justo. Etiam iaculis dapibus faucibus. Vestibulum felis magna, auctor vitae lacinia ac, elementum sed arcu. Vivamus sed turpis at purus rutrum sodales. Ut consequat, lorem ac interdum tincidunt, eros velit consectetur erat, et dictum turpis lacus ut ligula. Sed at elit metus, non ullamcorper urna. Vestibulum vel sapien vitae diam luctus suscipit sed sit amet sapien.
EOF;
    }
}
