<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# NO CHECKED-IN PROTOBUF GENCODE
# source: pubsub.proto

namespace Phluxor\Cluster\ProtoBuf;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * First request to initialize the actor.
 *
 * Generated from protobuf message <code>cluster.Initialize</code>
 */
class Initialize extends \Google\Protobuf\Internal\Message
{
    /**
     * Generated from protobuf field <code>.google.protobuf.Duration idleTimeout = 1;</code>
     */
    protected $idleTimeout = null;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type \Google\Protobuf\Duration $idleTimeout
     * }
     */
    public function __construct($data = NULL) {
        \Phluxor\Cluster\Metadata\ProtoBuf\Pubsub::initOnce();
        parent::__construct($data);
    }

    /**
     * Generated from protobuf field <code>.google.protobuf.Duration idleTimeout = 1;</code>
     * @return \Google\Protobuf\Duration|null
     */
    public function getIdleTimeout()
    {
        return $this->idleTimeout;
    }

    public function hasIdleTimeout()
    {
        return isset($this->idleTimeout);
    }

    public function clearIdleTimeout()
    {
        unset($this->idleTimeout);
    }

    /**
     * Generated from protobuf field <code>.google.protobuf.Duration idleTimeout = 1;</code>
     * @param \Google\Protobuf\Duration $var
     * @return $this
     */
    public function setIdleTimeout($var)
    {
        GPBUtil::checkMessage($var, \Google\Protobuf\Duration::class);
        $this->idleTimeout = $var;

        return $this;
    }

}

