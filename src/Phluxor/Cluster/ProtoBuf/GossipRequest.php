<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# NO CHECKED-IN PROTOBUF GENCODE
# source: gossip.proto

namespace Phluxor\Cluster\ProtoBuf;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Generated from protobuf message <code>cluster.GossipRequest</code>
 */
class GossipRequest extends \Google\Protobuf\Internal\Message
{
    /**
     * Generated from protobuf field <code>string from_member_id = 2;</code>
     */
    protected $from_member_id = '';
    /**
     * Generated from protobuf field <code>.cluster.GossipState state = 1;</code>
     */
    protected $state = null;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type string $from_member_id
     *     @type \Phluxor\Cluster\ProtoBuf\GossipState $state
     * }
     */
    public function __construct($data = NULL) {
        \Phluxor\Cluster\Metadata\ProtoBuf\Gossip::initOnce();
        parent::__construct($data);
    }

    /**
     * Generated from protobuf field <code>string from_member_id = 2;</code>
     * @return string
     */
    public function getFromMemberId()
    {
        return $this->from_member_id;
    }

    /**
     * Generated from protobuf field <code>string from_member_id = 2;</code>
     * @param string $var
     * @return $this
     */
    public function setFromMemberId($var)
    {
        GPBUtil::checkString($var, True);
        $this->from_member_id = $var;

        return $this;
    }

    /**
     * Generated from protobuf field <code>.cluster.GossipState state = 1;</code>
     * @return \Phluxor\Cluster\ProtoBuf\GossipState|null
     */
    public function getState()
    {
        return $this->state;
    }

    public function hasState()
    {
        return isset($this->state);
    }

    public function clearState()
    {
        unset($this->state);
    }

    /**
     * Generated from protobuf field <code>.cluster.GossipState state = 1;</code>
     * @param \Phluxor\Cluster\ProtoBuf\GossipState $var
     * @return $this
     */
    public function setState($var)
    {
        GPBUtil::checkMessage($var, \Phluxor\Cluster\ProtoBuf\GossipState::class);
        $this->state = $var;

        return $this;
    }

}

