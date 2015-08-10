<?php
/**
 * @link http://github.com/enebe-nb/phergie-irc-plugin-react-autorejoin for the canonical source repository
 * @license https://github.com/enebe-nb/phergie-irc-plugin-react-autorejoin/master/LICENSE Simplified BSD License
 * @package EnebeNb\Phergie\Plugin\AutoRejoin
 */

namespace EnebeNb\Phergie\Tests\Plugin\AutoRejoin;

use Phake;
use Phergie\Irc\Bot\React\EventQueueInterface;
use Phergie\Irc\Event\UserEventInterface;
use EnebeNb\Phergie\Plugin\AutoRejoin\Plugin;

/**
 * Tests for the Plugin class.
 *
 * @category Phergie
 * @package EnebeNb\Phergie\Plugin\AutoRejoin
 */
class PluginTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests specifying configuration without a channels list.
     */
    public function testInstantiateWithoutChannels()
    {
        try {
            $plugin = new Plugin(array());
            $this->fail('Expected exception was not thrown');
        } catch (\DomainException $e) {
            $this->assertSame('$config must contain a "channels" key', $e->getMessage());
        }
    }

    /**
     * Data provider for testRejoinChannels().
     *
     * @return array
     */
    public function dataProviderRejoinChannels()
    {
        $data = array();

        // Single Channel, no keys
        $data[] = array(
            array(
                'channels' => '#channel1',
            ),
            null,
        );

        // Many Channels string, keys string
        $data[] = array(
            array(
                'channels' => '#channel1,#channel2',
                'keys' => 'key1,#key2',
            ),
            'key1',
        );

        // Many Channels array, keys array
        $data[] = array(
            array(
                'channels' => array('#channel1', '#channel2'),
                'keys' => array('key1', 'key2'),
            ),
            'key1'
        );

        return $data;
    }

    /**
     * Tests rejoining channels on part and kick events.
     *
     * @param array $config Plugin configuration
     * @param string|null $key Expected parameter to ircJoin()
     * @dataProvider dataProviderRejoinChannels
     */
    public function testRejoinChannels(array $config, $key)
    {
        $connection = $this->getMockConnection('mynickname');
        $event = $this->getMockUserEvent('mynickname','#channel1', $connection);
        $queue = Phake::mock('\Phergie\Irc\Bot\React\EventQueueInterface');
        $plugin = new Plugin($config);
        $plugin->onPartChannels($event, $queue);
        $plugin->onKickChannels($event, $queue);
        Phake::verify($queue, Phake::times(2))->ircJoin('#channel1', $key);
    }

    /**
     * Tests ignore other users part and kick events.
     *
     * @param array $config Plugin configuration
     * @param string|null $key Expected parameter to ircJoin()
     * @dataProvider dataProviderRejoinChannels
     */
    public function testDontRejoinChannelsOnOtherUser(array $config, $key)
    {
        $connection = $this->getMockConnection('mynickname');
        $event = $this->getMockUserEvent('othernickname','#channel1', $connection);
        $queue = Phake::mock('\Phergie\Irc\Bot\React\EventQueueInterface');
        $plugin = new Plugin($config);
        $plugin->onPartChannels($event, $queue);
        $plugin->onKickChannels($event, $queue);
        Phake::verifyNoInteraction($queue);
    }

    /**
     * Tests ignore other channels part and kick events.
     *
     * @param array $config Plugin configuration
     * @param string|null $key Expected parameter to ircJoin()
     * @dataProvider dataProviderRejoinChannels
     */
    public function testDontRejoinChannelsOnOtherChannel(array $config, $key)
    {
        $connection = $this->getMockConnection('mynickname');
        $event = $this->getMockUserEvent('mynickname','#otherchannel', $connection);
        $queue = Phake::mock('\Phergie\Irc\Bot\React\EventQueueInterface');
        $plugin = new Plugin($config);
        $plugin->onPartChannels($event, $queue);
        $plugin->onKickChannels($event, $queue);
        Phake::verifyNoInteraction($queue);
    }

    /**
     * Data provider for testGetSubscribedEvents
     *
     * @return array
     */
    public function dataProviderGetSubscribedEvents()
    {
        return array(
            array(
                array(
                    'channels' => '#channel1',
                ),
                array(
                    'irc.received.part' => 'onPartChannels',
                    'irc.received.kick' => 'onKickChannels',
                ),
            ),
        );
    }

    /**
     * Tests that getSubscribedEvents() returns the correct event listeners.
     *
     * @param array $config
     * @param array $events
     * @dataProvider dataProviderGetSubscribedEvents
     */
    public function testGetSubscribedEvents(array $config, array $events)
    {
        $plugin = new Plugin($config);
        $this->assertEquals($events, $plugin->getSubscribedEvents());
    }

    /**
     * Returns a mock user event.
     *
     * @return \Phergie\Irc\Event\UserEventInterface
     */
    protected function getMockUserEvent($nickname, $channel, $connection)
    {
        $mock = Phake::mock('\Phergie\Irc\Event\UserEventInterface');
        Phake::when($mock)->getNick()->thenReturn($nickname);
        Phake::when($mock)->getSource()->thenReturn($channel);
        Phake::when($mock)->getConnection()->thenReturn($connection);
        Phake::when($mock)->getParams()->thenReturn(array(
            'channel' => $channel,
            'channels' => $channel,
            'user' => $nickname,
        ));
        return $mock;
    }

    /**
     * Returns a mock connection.
     *
     * @return \Phergie\Irc\ConnectionInterface
     */
    protected function getMockConnection($nickname)
    {
        $mock = Phake::mock('\Phergie\Irc\ConnectionInterface');
        Phake::when($mock)->getNickname()->thenReturn($nickname);
        return $mock;
    }
}
