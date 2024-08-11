<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem\Metrics;

use GuzzleHttp\Client;
use Http\Discovery\Psr17FactoryDiscovery;
use OpenTelemetry\API\Globals;
use OpenTelemetry\API\Instrumentation\Configurator;
use OpenTelemetry\Context\Context;
use OpenTelemetry\Context\ContextStorage;
use OpenTelemetry\Contrib\Otlp\ContentTypes;
use OpenTelemetry\Contrib\Otlp\MetricExporter;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Common\Export\Http\PsrTransportFactory;
use OpenTelemetry\SDK\Common\Export\TransportFactoryInterface;
use OpenTelemetry\SDK\Metrics\Data\Temporality;
use OpenTelemetry\SDK\Metrics\MeterProvider;
use OpenTelemetry\SDK\Metrics\MeterProviderInterface;
use OpenTelemetry\SDK\Metrics\MetricReader\ExportingReader;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use OpenTelemetry\SDK\Sdk;
use OpenTelemetry\SemConv\ResourceAttributes;
use Phluxor\Swoole\OpenTelemetry\SwooleContextStorage;
use Psr\Http\Client\ClientInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpClient\Psr18Client;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class HttpJsonMeterProvider implements ProviderInterface
{
    /**
     * @var array<string, string>
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
        $transport = new PsrTransportFactory(
            $this->transportClient(),
            Psr17FactoryDiscovery::findRequestFactory(),
            Psr17FactoryDiscovery::findStreamFactory()
        );
        $meterProvider = MeterProvider::builder()
            ->addReader(
                new ExportingReader(
                    new MetricExporter(
                        $transport->create( // @phpstan-ignore-line
                            $this->url,
                            ContentTypes::JSON,
                            [],
                            TransportFactoryInterface::COMPRESSION_GZIP,
                        ),
                        Temporality::DELTA
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

    private function transportClient(): ClientInterface
    {
        return new Psr18Client(HttpClient::create([
            'timeout' => 90.0,
        ]));
    }
}
