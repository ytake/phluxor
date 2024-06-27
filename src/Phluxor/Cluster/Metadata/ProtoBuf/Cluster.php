<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# NO CHECKED-IN PROTOBUF GENCODE
# source: cluster.proto

namespace Phluxor\Cluster\Metadata\ProtoBuf;

class Cluster
{
    public static $is_initialized = false;

    public static function initOnce() {
        $pool = \Google\Protobuf\Internal\DescriptorPool::getGeneratedPool();

        if (static::$is_initialized == true) {
          return;
        }
        \Phluxor\Metadata\ProtoBuf\Actor::initOnce();
        $pool->internalAddGeneratedFile(
            "\x0A\x95\x12\x0A\x0Dcluster.proto\x12\x07cluster\"\xF7\x01\x0A\x17IdentityHandoverRequest\x12C\x0A\x10current_topology\x18\x01 \x01(\x0B2).cluster.IdentityHandoverRequest.Topology\x12\x0F\x0A\x07address\x18\x02 \x01(\x09\x12A\x0A\x0Edelta_topology\x18\x03 \x01(\x0B2).cluster.IdentityHandoverRequest.Topology\x1AC\x0A\x08Topology\x12\x15\x0A\x0Dtopology_hash\x18\x01 \x01(\x04\x12 \x0A\x07members\x18\x03 \x03(\x0B2\x0F.cluster.Member\"\x8E\x01\x0A\x10IdentityHandover\x12#\x0A\x06actors\x18\x01 \x03(\x0B2\x13.cluster.Activation\x12\x10\x0A\x08chunk_id\x18\x02 \x01(\x05\x12\x0D\x0A\x05final\x18\x03 \x01(\x08\x12\x15\x0A\x0Dtopology_hash\x18\x04 \x01(\x04\x12\x0F\x0A\x07skipped\x18\x05 \x01(\x05\x12\x0C\x0A\x04sent\x18\x06 \x01(\x05\"\x9B\x01\x0A\x16RemoteIdentityHandover\x12*\x0A\x06actors\x18\x01 \x01(\x0B2\x1A.cluster.PackedActivations\x12\x10\x0A\x08chunk_id\x18\x02 \x01(\x05\x12\x0D\x0A\x05final\x18\x03 \x01(\x08\x12\x15\x0A\x0Dtopology_hash\x18\x04 \x01(\x04\x12\x0F\x0A\x07skipped\x18\x05 \x01(\x05\x12\x0C\x0A\x04sent\x18\x06 \x01(\x05\"\xDE\x01\x0A\x11PackedActivations\x12\x0F\x0A\x07address\x18\x01 \x01(\x09\x12/\x0A\x06actors\x18\x02 \x03(\x0B2\x1F.cluster.PackedActivations.Kind\x1AP\x0A\x04Kind\x12\x0C\x0A\x04name\x18\x01 \x01(\x09\x12:\x0A\x0Bactivations\x18\x02 \x03(\x0B2%.cluster.PackedActivations.Activation\x1A5\x0A\x0AActivation\x12\x10\x0A\x08identity\x18\x01 \x01(\x09\x12\x15\x0A\x0Dactivation_id\x18\x02 \x01(\x09\"\xAC\x01\x0A\x13IdentityHandoverAck\x12\x10\x0A\x08chunk_id\x18\x01 \x01(\x05\x12\x15\x0A\x0Dtopology_hash\x18\x02 \x01(\x04\x12<\x0A\x10processing_state\x18\x03 \x01(\x0E2\".cluster.IdentityHandoverAck.State\".\x0A\x05State\x12\x0D\x0A\x09processed\x10\x00\x12\x16\x0A\x12incorrect_topology\x10\x01\"1\x0A\x0FClusterIdentity\x12\x10\x0A\x08identity\x18\x01 \x01(\x09\x12\x0C\x0A\x04kind\x18\x02 \x01(\x09\"Y\x0A\x0AActivation\x12\x17\x0A\x03pid\x18\x01 \x01(\x0B2\x0A.actor.Pid\x122\x0A\x10cluster_identity\x18\x02 \x01(\x0B2\x18.cluster.ClusterIdentity\"d\x0A\x15ActivationTerminating\x12\x17\x0A\x03pid\x18\x01 \x01(\x0B2\x0A.actor.Pid\x122\x0A\x10cluster_identity\x18\x02 \x01(\x0B2\x18.cluster.ClusterIdentity\"c\x0A\x14ActivationTerminated\x12\x17\x0A\x03pid\x18\x01 \x01(\x0B2\x0A.actor.Pid\x122\x0A\x10cluster_identity\x18\x02 \x01(\x0B2\x18.cluster.ClusterIdentity\"r\x0A\x11ActivationRequest\x122\x0A\x10cluster_identity\x18\x01 \x01(\x0B2\x18.cluster.ClusterIdentity\x12\x12\x0A\x0Arequest_id\x18\x02 \x01(\x09\x12\x15\x0A\x0Dtopology_hash\x18\x03 \x01(\x04\"u\x0A\x16ProxyActivationRequest\x122\x0A\x10cluster_identity\x18\x01 \x01(\x0B2\x18.cluster.ClusterIdentity\x12'\x0A\x13replaced_activation\x18\x02 \x01(\x0B2\x0A.actor.Pid\"T\x0A\x12ActivationResponse\x12\x17\x0A\x03pid\x18\x01 \x01(\x0B2\x0A.actor.Pid\x12\x0E\x0A\x06failed\x18\x02 \x01(\x08\x12\x15\x0A\x0Dtopology_hash\x18\x03 \x01(\x04\"*\x0A\x11ReadyForRebalance\x12\x15\x0A\x0Dtopology_hash\x18\x01 \x01(\x04\"+\x0A\x12RebalanceCompleted\x12\x15\x0A\x0Dtopology_hash\x18\x01 \x01(\x04\"?\x0A\x06Member\x12\x0C\x0A\x04host\x18\x01 \x01(\x09\x12\x0C\x0A\x04port\x18\x02 \x01(\x05\x12\x0A\x0A\x02id\x18\x03 \x01(\x09\x12\x0D\x0A\x05kinds\x18\x04 \x03(\x09\"\x9B\x01\x0A\x0FClusterTopology\x12\x15\x0A\x0Dtopology_hash\x18\x01 \x01(\x04\x12 \x0A\x07members\x18\x02 \x03(\x0B2\x0F.cluster.Member\x12\x1F\x0A\x06joined\x18\x03 \x03(\x0B2\x0F.cluster.Member\x12\x1D\x0A\x04left\x18\x04 \x03(\x0B2\x0F.cluster.Member\x12\x0F\x0A\x07blocked\x18\x05 \x03(\x09\"Z\x0A\x1BClusterTopologyNotification\x12\x11\x0A\x09member_id\x18\x01 \x01(\x09\x12\x15\x0A\x0Dtopology_hash\x18\x02 \x01(\x0D\x12\x11\x0A\x09leader_id\x18\x03 \x01(\x09\"E\x0A\x0FMemberHeartbeat\x122\x0A\x10actor_statistics\x18\x01 \x01(\x0B2\x18.cluster.ActorStatistics\"\x83\x01\x0A\x0FActorStatistics\x12=\x0A\x0Bactor_count\x18\x01 \x03(\x0B2(.cluster.ActorStatistics.ActorCountEntry\x1A1\x0A\x0FActorCountEntry\x12\x0B\x0A\x03key\x18\x01 \x01(\x09\x12\x0D\x0A\x05value\x18\x02 \x01(\x03:\x028\x01B?\xCA\x02\x18Phluxor\\Cluster\\ProtoBuf\xE2\x02!Phluxor\\Cluster\\Metadata\\ProtoBufb\x06proto3"
        , true);

        static::$is_initialized = true;
    }
}
