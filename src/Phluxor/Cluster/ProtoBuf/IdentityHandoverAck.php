<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# NO CHECKED-IN PROTOBUF GENCODE
# source: cluster.proto

namespace Phluxor\Cluster\ProtoBuf;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Generated from protobuf message <code>cluster.IdentityHandoverAck</code>
 */
class IdentityHandoverAck extends \Google\Protobuf\Internal\Message
{
    /**
     * Generated from protobuf field <code>int32 chunk_id = 1;</code>
     */
    protected $chunk_id = 0;
    /**
     * Generated from protobuf field <code>uint64 topology_hash = 2;</code>
     */
    protected $topology_hash = 0;
    /**
     * Generated from protobuf field <code>.cluster.IdentityHandoverAck.State processing_state = 3;</code>
     */
    protected $processing_state = 0;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type int $chunk_id
     *     @type int|string $topology_hash
     *     @type int $processing_state
     * }
     */
    public function __construct($data = NULL) {
        \Phluxor\Cluster\Metadata\ProtoBuf\Cluster::initOnce();
        parent::__construct($data);
    }

    /**
     * Generated from protobuf field <code>int32 chunk_id = 1;</code>
     * @return int
     */
    public function getChunkId()
    {
        return $this->chunk_id;
    }

    /**
     * Generated from protobuf field <code>int32 chunk_id = 1;</code>
     * @param int $var
     * @return $this
     */
    public function setChunkId($var)
    {
        GPBUtil::checkInt32($var);
        $this->chunk_id = $var;

        return $this;
    }

    /**
     * Generated from protobuf field <code>uint64 topology_hash = 2;</code>
     * @return int|string
     */
    public function getTopologyHash()
    {
        return $this->topology_hash;
    }

    /**
     * Generated from protobuf field <code>uint64 topology_hash = 2;</code>
     * @param int|string $var
     * @return $this
     */
    public function setTopologyHash($var)
    {
        GPBUtil::checkUint64($var);
        $this->topology_hash = $var;

        return $this;
    }

    /**
     * Generated from protobuf field <code>.cluster.IdentityHandoverAck.State processing_state = 3;</code>
     * @return int
     */
    public function getProcessingState()
    {
        return $this->processing_state;
    }

    /**
     * Generated from protobuf field <code>.cluster.IdentityHandoverAck.State processing_state = 3;</code>
     * @param int $var
     * @return $this
     */
    public function setProcessingState($var)
    {
        GPBUtil::checkEnum($var, \Phluxor\Cluster\ProtoBuf\IdentityHandoverAck\State::class);
        $this->processing_state = $var;

        return $this;
    }

}
