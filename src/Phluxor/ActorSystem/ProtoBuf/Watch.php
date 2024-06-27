<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# NO CHECKED-IN PROTOBUF GENCODE
# source: actor.proto

namespace Phluxor\ActorSystem\ProtoBuf;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 *system messages
 *
 * Generated from protobuf message <code>actor.Watch</code>
 */
class Watch extends \Google\Protobuf\Internal\Message
{
    /**
     * Generated from protobuf field <code>.actor.Pid watcher = 1;</code>
     */
    protected $watcher = null;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type \Phluxor\ActorSystem\ProtoBuf\Pid $watcher
     * }
     */
    public function __construct($data = NULL) {
        \Phluxor\Metadata\ProtoBuf\Actor::initOnce();
        parent::__construct($data);
    }

    /**
     * Generated from protobuf field <code>.actor.Pid watcher = 1;</code>
     * @return \Phluxor\ActorSystem\ProtoBuf\Pid|null
     */
    public function getWatcher()
    {
        return $this->watcher;
    }

    public function hasWatcher()
    {
        return isset($this->watcher);
    }

    public function clearWatcher()
    {
        unset($this->watcher);
    }

    /**
     * Generated from protobuf field <code>.actor.Pid watcher = 1;</code>
     * @param \Phluxor\ActorSystem\ProtoBuf\Pid $var
     * @return $this
     */
    public function setWatcher($var)
    {
        GPBUtil::checkMessage($var, \Phluxor\ActorSystem\ProtoBuf\Pid::class);
        $this->watcher = $var;

        return $this;
    }

}

