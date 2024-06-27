<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# NO CHECKED-IN PROTOBUF GENCODE
# source: cluster.proto

namespace Phluxor\Cluster\ProtoBuf;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Generated from protobuf message <code>cluster.RemoteIdentityHandover</code>
 */
class RemoteIdentityHandover extends \Google\Protobuf\Internal\Message
{
    /**
     * Generated from protobuf field <code>.cluster.PackedActivations actors = 1;</code>
     */
    protected $actors = null;
    /**
     * Generated from protobuf field <code>int32 chunk_id = 2;</code>
     */
    protected $chunk_id = 0;
    /**
     * Generated from protobuf field <code>bool final = 3;</code>
     */
    protected $final = false;
    /**
     * Generated from protobuf field <code>uint64 topology_hash = 4;</code>
     */
    protected $topology_hash = 0;
    /**
     * Generated from protobuf field <code>int32 skipped = 5;</code>
     */
    protected $skipped = 0;
    /**
     * Generated from protobuf field <code>int32 sent = 6;</code>
     */
    protected $sent = 0;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type \Phluxor\Cluster\ProtoBuf\PackedActivations $actors
     *     @type int $chunk_id
     *     @type bool $final
     *     @type int|string $topology_hash
     *     @type int $skipped
     *     @type int $sent
     * }
     */
    public function __construct($data = NULL) {
        \Phluxor\Cluster\Metadata\ProtoBuf\Cluster::initOnce();
        parent::__construct($data);
    }

    /**
     * Generated from protobuf field <code>.cluster.PackedActivations actors = 1;</code>
     * @return \Phluxor\Cluster\ProtoBuf\PackedActivations|null
     */
    public function getActors()
    {
        return $this->actors;
    }

    public function hasActors()
    {
        return isset($this->actors);
    }

    public function clearActors()
    {
        unset($this->actors);
    }

    /**
     * Generated from protobuf field <code>.cluster.PackedActivations actors = 1;</code>
     * @param \Phluxor\Cluster\ProtoBuf\PackedActivations $var
     * @return $this
     */
    public function setActors($var)
    {
        GPBUtil::checkMessage($var, \Phluxor\Cluster\ProtoBuf\PackedActivations::class);
        $this->actors = $var;

        return $this;
    }

    /**
     * Generated from protobuf field <code>int32 chunk_id = 2;</code>
     * @return int
     */
    public function getChunkId()
    {
        return $this->chunk_id;
    }

    /**
     * Generated from protobuf field <code>int32 chunk_id = 2;</code>
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
     * Generated from protobuf field <code>bool final = 3;</code>
     * @return bool
     */
    public function getFinal()
    {
        return $this->final;
    }

    /**
     * Generated from protobuf field <code>bool final = 3;</code>
     * @param bool $var
     * @return $this
     */
    public function setFinal($var)
    {
        GPBUtil::checkBool($var);
        $this->final = $var;

        return $this;
    }

    /**
     * Generated from protobuf field <code>uint64 topology_hash = 4;</code>
     * @return int|string
     */
    public function getTopologyHash()
    {
        return $this->topology_hash;
    }

    /**
     * Generated from protobuf field <code>uint64 topology_hash = 4;</code>
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
     * Generated from protobuf field <code>int32 skipped = 5;</code>
     * @return int
     */
    public function getSkipped()
    {
        return $this->skipped;
    }

    /**
     * Generated from protobuf field <code>int32 skipped = 5;</code>
     * @param int $var
     * @return $this
     */
    public function setSkipped($var)
    {
        GPBUtil::checkInt32($var);
        $this->skipped = $var;

        return $this;
    }

    /**
     * Generated from protobuf field <code>int32 sent = 6;</code>
     * @return int
     */
    public function getSent()
    {
        return $this->sent;
    }

    /**
     * Generated from protobuf field <code>int32 sent = 6;</code>
     * @param int $var
     * @return $this
     */
    public function setSent($var)
    {
        GPBUtil::checkInt32($var);
        $this->sent = $var;

        return $this;
    }

}
