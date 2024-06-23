<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem\Metrics;

use OpenTelemetry\API\Globals;
use OpenTelemetry\API\Instrumentation\Configurator;
use OpenTelemetry\Context\Context;
use OpenTelemetry\Context\ContextStorage;
use OpenTelemetry\Contrib\Context\Swoole\SwooleContextStorage;
use OpenTelemetry\Contrib\Otlp\ContentTypes;
use OpenTelemetry\Contrib\Otlp\MetricExporter;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Common\Export\Http\PsrTransportFactory;
use OpenTelemetry\SDK\Metrics\MeterProvider;
use OpenTelemetry\SDK\Metrics\MeterProviderInterface;
use OpenTelemetry\SDK\Metrics\MetricReader\ExportingReader;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use OpenTelemetry\SDK\Sdk;
use OpenTelemetry\SemConv\ResourceAttributes;

class HttpJsonMeterProvider implements ProviderInterface
{
    /**
     * @var array{string, string}
     */
    private array $resourceAttributes = [];

    /**
     * @param string $serviceName for example 'phluxor'
     * @param string $url for example 'http://127.0.0.1:4318/v1/metrics'
     */
    public function __construct(
        private readonly string $serviceName,
        private readonly string $url
    ) {
    }

    /**
     * Add a resource attribute.
     *
     * @param string $key
     * @param string $value
     * @return self
     */
    public function addResourceAttribute(string $key, string $value): self
    {
        $this->resourceAttributes[$key] = $value;
        return $this;
    }

    protected function resource(): ResourceInfo
    {
        return ResourceInfo::create(
            Attributes::create(
                array_merge([
                    ResourceAttributes::SERVICE_NAME => $this->serviceName,
                    ResourceAttributes::SERVICE_VERSION => '1.0.0',
                ], $this->resourceAttributes)
            )
        );
    }

    public function provide(): MeterProviderInterface
    {
        // Use Swoole context storage
        Context::setStorage(new SwooleContextStorage(new ContextStorage()));
        $meterProvider = MeterProvider::builder()
            ->addReader(
                new ExportingReader(
                    new MetricExporter(
                        PsrTransportFactory::discover()->create(
                            $this->url,
                            ContentTypes::JSON
                        )
                    )
                )
            )
            ->setResource($this->resource())
            ->build();
        Globals::registerInitializer(
            fn(Configurator $configurator) => $configurator->withMeterProvider($meterProvider)
        );
        Sdk::builder()
            ->setMeterProvider($meterProvider)
            ->setAutoShutdown(true)
            ->buildAndRegisterGlobal();
        return $meterProvider;
    }
}
