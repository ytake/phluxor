<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# NO CHECKED-IN PROTOBUF GENCODE
# source: pubsub.proto

namespace Phluxor\Cluster\ProtoBuf;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Message sent from delivery actor to topic to notify of subscribers that fail to process the messages
 *
 * Generated from protobuf message <code>cluster.NotifyAboutFailingSubscribersRequest</code>
 */
class NotifyAboutFailingSubscribersRequest extends \Google\Protobuf\Internal\Message
{
    /**
     * Generated from protobuf field <code>repeated .cluster.SubscriberDeliveryReport invalid_deliveries = 1;</code>
     */
    private $invalid_deliveries;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type array<\Phluxor\Cluster\ProtoBuf\SubscriberDeliveryReport>|\Google\Protobuf\Internal\RepeatedField $invalid_deliveries
     * }
     */
    public function __construct($data = NULL) {
        \Phluxor\Cluster\Metadata\ProtoBuf\Pubsub::initOnce();
        parent::__construct($data);
    }

    /**
     * Generated from protobuf field <code>repeated .cluster.SubscriberDeliveryReport invalid_deliveries = 1;</code>
     * @return \Google\Protobuf\Internal\RepeatedField
     */
    public function getInvalidDeliveries()
    {
        return $this->invalid_deliveries;
    }

    /**
     * Generated from protobuf field <code>repeated .cluster.SubscriberDeliveryReport invalid_deliveries = 1;</code>
     * @param array<\Phluxor\Cluster\ProtoBuf\SubscriberDeliveryReport>|\Google\Protobuf\Internal\RepeatedField $var
     * @return $this
     */
    public function setInvalidDeliveries($var)
    {
        $arr = GPBUtil::checkRepeatedField($var, \Google\Protobuf\Internal\GPBType::MESSAGE, \Phluxor\Cluster\ProtoBuf\SubscriberDeliveryReport::class);
        $this->invalid_deliveries = $arr;

        return $this;
    }

}

