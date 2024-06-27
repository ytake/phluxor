<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# NO CHECKED-IN PROTOBUF GENCODE
# source: pubsub.proto

namespace Phluxor\Cluster\ProtoBuf;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Message sent from topic to delivery actor
 *
 * Generated from protobuf message <code>cluster.DeliverBatchRequestTransport</code>
 */
class DeliverBatchRequestTransport extends \Google\Protobuf\Internal\Message
{
    /**
     * Generated from protobuf field <code>.cluster.Subscribers subscribers = 1;</code>
     */
    protected $subscribers = null;
    /**
     * Generated from protobuf field <code>.cluster.PubSubBatchTransport batch = 2;</code>
     */
    protected $batch = null;
    /**
     * Generated from protobuf field <code>string topic = 3;</code>
     */
    protected $topic = '';

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type \Phluxor\Cluster\ProtoBuf\Subscribers $subscribers
     *     @type \Phluxor\Cluster\ProtoBuf\PubSubBatchTransport $batch
     *     @type string $topic
     * }
     */
    public function __construct($data = NULL) {
        \Phluxor\Cluster\Metadata\ProtoBuf\Pubsub::initOnce();
        parent::__construct($data);
    }

    /**
     * Generated from protobuf field <code>.cluster.Subscribers subscribers = 1;</code>
     * @return \Phluxor\Cluster\ProtoBuf\Subscribers|null
     */
    public function getSubscribers()
    {
        return $this->subscribers;
    }

    public function hasSubscribers()
    {
        return isset($this->subscribers);
    }

    public function clearSubscribers()
    {
        unset($this->subscribers);
    }

    /**
     * Generated from protobuf field <code>.cluster.Subscribers subscribers = 1;</code>
     * @param \Phluxor\Cluster\ProtoBuf\Subscribers $var
     * @return $this
     */
    public function setSubscribers($var)
    {
        GPBUtil::checkMessage($var, \Phluxor\Cluster\ProtoBuf\Subscribers::class);
        $this->subscribers = $var;

        return $this;
    }

    /**
     * Generated from protobuf field <code>.cluster.PubSubBatchTransport batch = 2;</code>
     * @return \Phluxor\Cluster\ProtoBuf\PubSubBatchTransport|null
     */
    public function getBatch()
    {
        return $this->batch;
    }

    public function hasBatch()
    {
        return isset($this->batch);
    }

    public function clearBatch()
    {
        unset($this->batch);
    }

    /**
     * Generated from protobuf field <code>.cluster.PubSubBatchTransport batch = 2;</code>
     * @param \Phluxor\Cluster\ProtoBuf\PubSubBatchTransport $var
     * @return $this
     */
    public function setBatch($var)
    {
        GPBUtil::checkMessage($var, \Phluxor\Cluster\ProtoBuf\PubSubBatchTransport::class);
        $this->batch = $var;

        return $this;
    }

    /**
     * Generated from protobuf field <code>string topic = 3;</code>
     * @return string
     */
    public function getTopic()
    {
        return $this->topic;
    }

    /**
     * Generated from protobuf field <code>string topic = 3;</code>
     * @param string $var
     * @return $this
     */
    public function setTopic($var)
    {
        GPBUtil::checkString($var, True);
        $this->topic = $var;

        return $this;
    }

}

