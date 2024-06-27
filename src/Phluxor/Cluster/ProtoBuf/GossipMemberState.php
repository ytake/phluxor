<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# NO CHECKED-IN PROTOBUF GENCODE
# source: gossip.proto

namespace Phluxor\Cluster\ProtoBuf;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 *string key is the key of the gossip value, e.g. "heartbeat"
 *GossipKeyValue is the value of that key
 *
 * Generated from protobuf message <code>cluster.GossipMemberState</code>
 */
class GossipMemberState extends \Google\Protobuf\Internal\Message
{
    /**
     * Generated from protobuf field <code>map<string, .cluster.GossipKeyValue> values = 1;</code>
     */
    private $values;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type array|\Google\Protobuf\Internal\MapField $values
     * }
     */
    public function __construct($data = NULL) {
        \Phluxor\Cluster\Metadata\ProtoBuf\Gossip::initOnce();
        parent::__construct($data);
    }

    /**
     * Generated from protobuf field <code>map<string, .cluster.GossipKeyValue> values = 1;</code>
     * @return \Google\Protobuf\Internal\MapField
     */
    public function getValues()
    {
        return $this->values;
    }

    /**
     * Generated from protobuf field <code>map<string, .cluster.GossipKeyValue> values = 1;</code>
     * @param array|\Google\Protobuf\Internal\MapField $var
     * @return $this
     */
    public function setValues($var)
    {
        $arr = GPBUtil::checkMapField($var, \Google\Protobuf\Internal\GPBType::STRING, \Google\Protobuf\Internal\GPBType::MESSAGE, \Phluxor\Cluster\ProtoBuf\GossipKeyValue::class);
        $this->values = $arr;

        return $this;
    }

}

