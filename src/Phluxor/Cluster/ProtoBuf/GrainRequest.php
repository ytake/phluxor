<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# NO CHECKED-IN PROTOBUF GENCODE
# source: grain.proto

namespace Phluxor\Cluster\ProtoBuf;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Generated from protobuf message <code>cluster.GrainRequest</code>
 */
class GrainRequest extends \Google\Protobuf\Internal\Message
{
    /**
     * Generated from protobuf field <code>int32 method_index = 1;</code>
     */
    protected $method_index = 0;
    /**
     * Generated from protobuf field <code>bytes message_data = 2;</code>
     */
    protected $message_data = '';
    /**
     * Generated from protobuf field <code>string message_type_name = 3;</code>
     */
    protected $message_type_name = '';

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type int $method_index
     *     @type string $message_data
     *     @type string $message_type_name
     * }
     */
    public function __construct($data = NULL) {
        \Phluxor\Cluster\Metadata\ProtoBuf\Grain::initOnce();
        parent::__construct($data);
    }

    /**
     * Generated from protobuf field <code>int32 method_index = 1;</code>
     * @return int
     */
    public function getMethodIndex()
    {
        return $this->method_index;
    }

    /**
     * Generated from protobuf field <code>int32 method_index = 1;</code>
     * @param int $var
     * @return $this
     */
    public function setMethodIndex($var)
    {
        GPBUtil::checkInt32($var);
        $this->method_index = $var;

        return $this;
    }

    /**
     * Generated from protobuf field <code>bytes message_data = 2;</code>
     * @return string
     */
    public function getMessageData()
    {
        return $this->message_data;
    }

    /**
     * Generated from protobuf field <code>bytes message_data = 2;</code>
     * @param string $var
     * @return $this
     */
    public function setMessageData($var)
    {
        GPBUtil::checkString($var, False);
        $this->message_data = $var;

        return $this;
    }

    /**
     * Generated from protobuf field <code>string message_type_name = 3;</code>
     * @return string
     */
    public function getMessageTypeName()
    {
        return $this->message_type_name;
    }

    /**
     * Generated from protobuf field <code>string message_type_name = 3;</code>
     * @param string $var
     * @return $this
     */
    public function setMessageTypeName($var)
    {
        GPBUtil::checkString($var, True);
        $this->message_type_name = $var;

        return $this;
    }

}
