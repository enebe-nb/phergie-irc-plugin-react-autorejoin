<?php
/**
 * @link http://github.com/enebe-nb/phergie-irc-plugin-react-autorejoin for the canonical source repository
 * @license https://github.com/enebe-nb/phergie-irc-plugin-react-autorejoin/master/LICENSE Simplified BSD License
 * @package EnebeNb\Phergie\Plugin\AutoRejoin
 */

namespace EnebeNb\Phergie\Plugin\AutoRejoin;

use Phergie\Irc\Bot\React\AbstractPlugin;
use Phergie\Irc\Bot\React\EventQueueInterface;
use Phergie\Irc\Event\UserEventInterface;

/**
 * Plugin for automatically rejoining channels on a PART or KICK event.
 *
 * @category Phergie
 * @package EnebeNb\Phergie\Plugin\AutoRejoin
 */
class Plugin extends AbstractPlugin
{
    /**
     * Array list of channels to rejoin
     *
     * @var array
     */
    protected $channels;

    /**
     * Array list of channel keys
     *
     * @var array|null
     */
    protected $keys = null;

    /**
     * Accepts plugin configuration.
     *
     * Supported keys:
     *
     * channels - required, either a comma-delimited string or array of names
     * of channels to rejoin
     *
     * keys - optional, either a comma-delimited string or array of keys
     * corresponding to the channels to rejoin
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        if (!isset($config['channels'])) {
            throw new \DomainException('$config must contain a "channels" key');
        }

        $this->channels = is_string($config['channels'])
            ? explode(',', $config['channels'])
            : $config['channels'];

        if (isset($config['keys'])) {
            $this->keys = is_string($config['keys'])
                ? explode(',', $config['keys'])
                : $config['keys'];
        }
    }

    /**
     * Indicates that the plugin monitors PART and KICK events.
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return array(
                'irc.received.part' => 'onPartChannels',
                'irc.received.kick' => 'onKickChannels',
            );
    }

    /**
     * Rejoins a channel in provided list of channels on a part event.
     *
     * @param \Phergie\Irc\Event\UserEventInterface $event
     * @param \Phergie\Irc\Bot\React\EventQueueInterface $queue
     */
    public function onPartChannels(UserEventInterface $event, EventQueueInterface $queue)
    {
        if ($event->getNick() == $event->getConnection()->getNickname()
            && ($index = array_search($event->getSource(), $this->channels)) !== false) {
            $queue->ircJoin($this->channels[$index],
                $this->keys ? $this->keys[$index] : null);
        }
    }

    /**
     * Rejoins a channel in provided list of channels on a kick event.
     *
     * @param \Phergie\Irc\Event\UserEventInterface $event
     * @param \Phergie\Irc\Bot\React\EventQueueInterface $queue
     */
    public function onKickChannels(UserEventInterface $event, EventQueueInterface $queue)
    {
        if ($event->getParams()['user'] == $event->getConnection()->getNickname()
            && ($index = array_search($event->getSource(), $this->channels)) !== false) {
            $queue->ircJoin($this->channels[$index],
                $this->keys ? $this->keys[$index] : null);
        }
    }
}
