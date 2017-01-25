<?php

namespace Superbalist\EventPubSub\Events;

use Composer\Semver\Semver;
use Superbalist\EventPubSub\EventInterface;

class TopicEvent extends SimpleEvent implements EventInterface
{
    /**
     * @var string
     */
    protected $topic;

    /**
     * @var string
     */
    protected $version;

    /**
     * @param string $topic
     * @param string $name
     * @param string $version
     * @param array $attributes
     */
    public function __construct($topic, $name, $version, array $attributes = [])
    {
        parent::__construct($name, $attributes);

        $this->topic = $topic;
        $this->version = $version;
    }

    /**
     * Return the topic name.
     *
     * @return string
     */
    public function getTopic()
    {
        return $this->topic;
    }

    /**
     * Return the version of the event.
     *
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Return the event in a message format ready for publishing.
     *
     * @return mixed
     */
    public function toMessage()
    {
        return array_merge($this->attributes, [
            'topic' => $this->topic,
            'event' => $this->name,
            'version' => $this->version,
        ]);
    }

    /**
     * Check whether or not the event matches the given expression.
     *
     * @param string $expr
     * @return bool
     */
    public function matches($expr)
    {
        $params = self::parseEventExpr($expr);

        if ($params['topic'] === '*') {
            return true;
        } elseif ($this->topic !== $params['topic']) {
            return false;
        }

        if ($params['event'] === '*') {
            return true;
        } elseif ($this->name !== $params['event']) {
            return false;
        }

        if ($params['version'] === '*') {
            return true;
        } else {
            return Semver::satisfies($this->version, $params['version']);
        }
    }

    /**
     * @param string $expr
     * @return array
     */
    public static function parseEventExpr($expr)
    {
        if (!preg_match('#^([\w*.,]+)(/([\w*.,]+)(/([\w*.,]+))?)?$#i', $expr, $matches)) {
            throw new \InvalidArgumentException('The expression must be in the format "topic/event?/version?"');
        }

        $topic = $matches[1];
        $event = isset($matches[3]) ? $matches[3] : '*';
        $version = isset($matches[5]) ? $matches[5] : '*';

        if ($topic === '*') {
            $event = $version = '*';
        } elseif ($event === '*') {
            $version = '*';
        }

        return [
            'topic' => $topic,
            'event' => $event,
            'version' => $version,
        ];
    }
}
