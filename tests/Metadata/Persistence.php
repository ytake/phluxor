<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: persistence.proto

namespace Test\Metadata;

class Persistence
{
    public static $is_initialized = false;

    public static function initOnce() {
        $pool = \Google\Protobuf\Internal\DescriptorPool::getGeneratedPool();

        if (static::$is_initialized == true) {
          return;
        }
        $pool->internalAddGeneratedFile(
            "\x0A\xA5\x01\x0A\x11persistence.proto\x12\x19Test.Persistence.ProtoBuf\"\x1E\x0A\x0BTestMessage\x12\x0F\x0A\x07message\x18\x01 \x01(\x09\"\x1F\x0A\x0CTestSnapshot\x12\x0F\x0A\x07message\x18\x01 \x01(\x09B,\xCA\x02\x19Test\\Persistence\\ProtoBuf\xE2\x02\x0DTest\\Metadatab\x06proto3"
        , true);

        static::$is_initialized = true;
    }
}
