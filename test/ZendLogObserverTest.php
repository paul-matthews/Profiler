<?php
require_once(dirname(__FILE__) . '/../lib/ZendLogObserver.php');
require_once(dirname(__FILE__) . '/../lib/Profiler.php');

class ZendLogObserverTest extends PHPUnit_Framework_TestCase
{
    private $logAll;
    private $logWarn;
    private $profiler;

    public function setUp()
    {
        $this->logAll = Zend_Log::factory(array(
            array(
                'writerName'   => 'Stream',
                'writerParams' => array(
                    'stream'   => '/tmp/zend.log',
                ),
            ),
        ));
        $this->logWarn = Zend_Log::factory(array(
            array(
                'writerName'   => 'Stream',
                'writerParams' => array(
                    'stream'   => '/tmp/zend_warn.log',
                ),
                'filterName'   => 'Priority',
                'filterParams' => array(
                    'priority' => Zend_Log::WARN,
                ),
            ),
        ));
        $this->profiler = new Profiler(true);
    }

    public function tearDown()
    {
        unset($this->logAll);
        unset($this->logWarn);
        unset($this->profiler);
    }

    /**
     * @test
     */
    public function checkCreateZendLogObserverAndRegister()
    {
        $observer = new ZendLogObserver($this->logAll);

        $this->profiler->register($observer);

        $this->assertEquals($this->logAll, $observer->getLog());
    }

    /**
     * @test
     */
    public function testNotifyIsCalledOnce()
    {
        $observer = $this->getMock('ZendLogObserver',
            array('notify'),
            array($this->logAll)
        );

        $observer->expects($this->once())
            ->method('notify');

        $this->profiler->register($observer);

        $this->profiler->start('test', 'test');
    }

    /**
     * @test
     */
    public function testLogIsCalledOnce()
    {
        $log = $this->getMock('Zend_Log', array('log'));
        $log->expects($this->once())
            ->method('log');

        $observer = new ZendLogObserver($log);

        $this->profiler->register($observer);
        $this->profiler->start();
    }

    public function testObserveLogSummary()
    {
        $log = $this->getMock('Zend_Log', array('log'));
        $log->expects($this->once())
            ->method('log');

        $observer = new ZendLogObserver($log);

        $this->profiler->start();

        $this->profiler->register($observer);

        $this->profiler->triggerObserveSummary();
    }

    public function testObserveLogSummaryAllCallsLogMoreThanOnce()
    {
        $log = $this->getMock('Zend_Log', array('log'));
        $log->expects($this->exactly(2))
            ->method('log');

        $observer = new ZendLogObserver($log);

        $this->profiler->start('no group');
        $this->profiler->start('has group', 'test group');

        $this->profiler->register($observer);

        $this->profiler->triggerObserveSummaryAll();
    }
}
