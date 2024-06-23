<?php

declare(strict_types=1);

namespace Phluxor\Metrics;

use OpenTelemetry\API\Globals;
use OpenTelemetry\API\Metrics\CounterInterface;
use OpenTelemetry\API\Metrics\HistogramInterface;
use OpenTelemetry\API\Metrics\MeterInterface;
use OpenTelemetry\API\Metrics\ObservableGaugeInterface;
use Swoole\Lock;

/**
 * The actor metrics.
 */
class ActorMetrics
{
    public const string METRICS_NAME = 'phluxor';

    private CounterInterface $actorFailureCount;
    private HistogramInterface $actorMessageReceiveHistogram;
    private CounterInterface $actorRestartedCounter;
    private CounterInterface $actorStoppedCounter;
    private CounterInterface $actorSpawnCounter;
    private CounterInterface $deadLetterCounter;
    private CounterInterface $futuresCompletedCount;
    private CounterInterface $futuresStartedCount;
    private CounterInterface $futuresTimedOutCount;
    private ObservableGaugeInterface $actorMailboxLength;
    private Lock $mutex;

    public function __construct()
    {
        $this->mutex = new Lock(Lock::MUTEX);
        $this->instruments();
    }

    /**
     * Initializes the metrics instruments.
     */
    private function instruments(): void
    {
        $meter = Globals::meterProvider()->getMeter(self::METRICS_NAME);
        $this->actorFailureCount = $this->actorFailureCounter($meter);
        $this->actorMessageReceiveHistogram = $this->actorMessageReceiveHistogram($meter);
        $this->actorRestartedCounter = $this->actorRestartedCounter($meter);
        $this->actorStoppedCounter = $this->actorStoppedCounter($meter);
        $this->actorSpawnCounter = $this->actorSpawnCounter($meter);
        $this->deadLetterCounter = $this->deadLetterCounter($meter);
        $this->futuresCompletedCount = $this->futuresCompletedCount($meter);
        $this->futuresStartedCount = $this->futuresStartedCount($meter);
        $this->futuresTimedOutCount = $this->futuresTimedOutCount($meter);
    }

    /**
     * Creates and returns an actor failure counter.
     *
     * @param MeterInterface $meter The meter to create the counter with.
     * @return CounterInterface The created actor failure counter.
     */
    private function actorFailureCounter(MeterInterface $meter): CounterInterface
    {
        return $meter->createCounter(
            name: 'phluxor_actor_failure_count',
            unit: '1',
            description: 'The number of actor failures'
        );
    }

    /**
     * Creates and returns an actor message receive histogram.
     *
     * @param MeterInterface $meter The meter to create the histogram with.
     * @return HistogramInterface The created actor message receive histogram.
     */
    private function actorMessageReceiveHistogram(MeterInterface $meter): HistogramInterface
    {
        return $meter->createHistogram(
            name: 'phluxor_actor_message_receive_duration',
            description: 'The duration of actor message receive'
        );
    }

    /**
     * Creates and returns an actor restarted counter.
     *
     * @param MeterInterface $meter The meter to create the counter with.
     * @return CounterInterface The created actor restarted counter.
     */
    private function actorRestartedCounter(MeterInterface $meter): CounterInterface
    {
        return $meter->createCounter(
            name: 'phluxor_actor_restarted_count',
            unit: '1',
            description: 'The number of actor restarts'
        );
    }

    /**
     * Creates and returns an actor stopped counter.
     *
     * @param MeterInterface $meter The meter to create the counter with.
     * @return CounterInterface The created actor stopped counter.
     */
    private function actorStoppedCounter(MeterInterface $meter): CounterInterface
    {
        return $meter->createCounter(
            name: 'phluxor_actor_stopped_count',
            unit: '1',
            description: 'The number of actor stopped'
        );
    }

    /**
     * Creates and returns an actor spawn counter.
     *
     * @param MeterInterface $meter The meter to create the counter with.
     * @return CounterInterface The created actor spawn counter.
     */
    private function actorSpawnCounter(MeterInterface $meter): CounterInterface
    {
        return $meter->createCounter(
            name: 'phluxor_actor_spawn_count',
            unit: '1',
            description: 'The number of actor spawned'
        );
    }

    /**
     * Creates and returns a dead letter counter.
     *
     * @param MeterInterface $meter The meter to create the counter with.
     * @return CounterInterface The created dead letter counter.
     */
    private function deadLetterCounter(MeterInterface $meter): CounterInterface
    {
        return $meter->createCounter(
            name: 'phluxor_dead_letter_count',
            unit: '1',
            description: 'The number of dead letters'
        );
    }

    /**
     * Creates and returns a futures completed counter.
     *
     * @param MeterInterface $meter The meter to create the counter with.
     * @return CounterInterface The created futures completed counter.
     */
    private function futuresCompletedCount(MeterInterface $meter): CounterInterface
    {
        return $meter->createCounter(
            name: 'phluxor_futures_completed_count',
            unit: '1',
            description: 'The number of completed futures'
        );
    }

    /**
     * Creates and returns a futures started counter.
     *
     * @param MeterInterface $meter The meter to create the counter with.
     * @return CounterInterface The created futures completed counter.
     */
    private function futuresStartedCount(MeterInterface $meter): CounterInterface
    {
        return $meter->createCounter(
            name: 'phluxor_futures_started_count',
            unit: '1',
            description: 'The number of stared futures'
        );
    }

    /**
     * Creates and returns a futures timed out counter.
     *
     * @param MeterInterface $meter The meter to create the counter with.
     * @return CounterInterface The created futures timed out counter.
     */
    private function futuresTimedOutCount(MeterInterface $meter): CounterInterface
    {
        return $meter->createCounter(
            name: 'phluxor_futures_timed_out_count',
            unit: '1',
            description: 'The number of timed out futures'
        );
    }

    /**
     * Returns the actor's mailbox length gauge.
     * use for openTelemetry
     * @return CounterInterface
     */
    public function getActorFailureCount(): CounterInterface
    {
        return $this->actorFailureCount;
    }

    /**
     * Retrieves the actor message receive histogram.
     *
     * @return HistogramInterface The actor message receive histogram.
     */
    public function getActorMessageReceiveHistogram(): HistogramInterface
    {
        return $this->actorMessageReceiveHistogram;
    }

    /**
     * Returns the actor restarted counter.
     *
     * @return CounterInterface The actor restarted counter.
     */
    public function getActorRestartedCounter(): CounterInterface
    {
        return $this->actorRestartedCounter;
    }

    /**
     * Get the actor stopped counter.
     *
     * @return CounterInterface The actor stopped counter.
     */
    public function getActorStoppedCounter(): CounterInterface
    {
        return $this->actorStoppedCounter;
    }

    /**
     * Get the actor spawn counter.
     *
     * @return CounterInterface The actor spawn counter.
     */
    public function getActorSpawnCounter(): CounterInterface
    {
        return $this->actorSpawnCounter;
    }

    public function getDeadLetterCounter(): CounterInterface
    {
        return $this->deadLetterCounter;
    }

    public function getFuturesCompletedCount(): CounterInterface
    {
        return $this->futuresCompletedCount;
    }

    public function getFuturesStartedCount(): CounterInterface
    {
        return $this->futuresStartedCount;
    }

    public function getFuturesTimedOutCount(): CounterInterface
    {
        return $this->futuresTimedOutCount;
    }

    /**
     * Registers an ObservableGaugeInterface as the actor's mailbox length gauge.
     *
     * @param ObservableGaugeInterface $gauge The gauge to use for measuring the actor's mailbox length.
     * @return void
     */
    public function registerActorMailboxLengthGauge(
        ObservableGaugeInterface $gauge
    ): void {
        $this->mutex->lock();
        $this->actorMailboxLength = $gauge;
        $this->mutex->unlock();
    }
}
