<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# NO CHECKED-IN PROTOBUF GENCODE
# source: gossip.proto

namespace Phluxor\Cluster\ProtoBuf;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 *special datatype that is known by gossip actor
 *set key
 *remove key
 *get keys
 *
 * Generated from protobuf message <code>cluster.GossipMap</code>
 */
class GossipMap extends \Google\Protobuf\Internal\Message
{
    /**
     * Generated from protobuf field <code>map<string, .google.protobuf.Any> items = 1;</code>
     */
    private $items;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type array|\Google\Protobuf\Internal\MapField $items
     * }
     */
    public function __construct($data = NULL) {
        \Phluxor\Cluster\Metadata\ProtoBuf\Gossip::initOnce();
        parent::__construct($data);
    }

    /**
     * Generated from protobuf field <code>map<string, .google.protobuf.Any> items = 1;</code>
     * @return \Google\Protobuf\Internal\MapField
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * Generated from protobuf field <code>map<string, .google.protobuf.Any> items = 1;</code>
     * @param array|\Google\Protobuf\Internal\MapField $var
     * @return $this
     */
    public function setItems($var)
    {
        $arr = GPBUtil::checkMapField($var, \Google\Protobuf\Internal\GPBType::STRING, \Google\Protobuf\Internal\GPBType::MESSAGE, \Google\Protobuf\Any::class);
        $this->items = $arr;

        return $this;
    }

}
