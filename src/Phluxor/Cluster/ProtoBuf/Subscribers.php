<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# NO CHECKED-IN PROTOBUF GENCODE
# source: pubsub.proto

namespace Phluxor\Cluster\ProtoBuf;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * A list of subscribers
 *
 * Generated from protobuf message <code>cluster.Subscribers</code>
 */
class Subscribers extends \Google\Protobuf\Internal\Message
{
    /**
     * Generated from protobuf field <code>repeated .cluster.SubscriberIdentity subscribers = 1;</code>
     */
    private $subscribers;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type array<\Phluxor\Cluster\ProtoBuf\SubscriberIdentity>|\Google\Protobuf\Internal\RepeatedField $subscribers
     * }
     */
    public function __construct($data = NULL) {
        \Phluxor\Cluster\Metadata\ProtoBuf\Pubsub::initOnce();
        parent::__construct($data);
    }

    /**
     * Generated from protobuf field <code>repeated .cluster.SubscriberIdentity subscribers = 1;</code>
     * @return \Google\Protobuf\Internal\RepeatedField
     */
    public function getSubscribers()
    {
        return $this->subscribers;
    }

    /**
     * Generated from protobuf field <code>repeated .cluster.SubscriberIdentity subscribers = 1;</code>
     * @param array<\Phluxor\Cluster\ProtoBuf\SubscriberIdentity>|\Google\Protobuf\Internal\RepeatedField $var
     * @return $this
     */
    public function setSubscribers($var)
    {
        $arr = GPBUtil::checkRepeatedField($var, \Google\Protobuf\Internal\GPBType::MESSAGE, \Phluxor\Cluster\ProtoBuf\SubscriberIdentity::class);
        $this->subscribers = $arr;

        return $this;
    }

}

