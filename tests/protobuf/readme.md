# Protocol Buffers Code Generator

This is a code generator for the [Protocol Buffers](https://developers.google.com/protocol-buffers) serialization format.  
It generates code in the target language of your choice from a `.proto` file.

## Usage

```bash
$ protoc --proto_path=protobuf --php_out=:./tests protobuf/persistence.proto
```
