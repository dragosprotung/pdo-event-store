<?php
/**
 * This file is part of the prooph/pdo-event-store.
 * (c) 2016-2016 prooph software GmbH <contact@prooph.de>
 * (c) 2016-2016 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace ProophTest\EventStore\PDO\Container;

use Interop\Container\ContainerInterface;
use PHPUnit\Framework\TestCase;
use Prooph\Common\Messaging\FQCNMessageFactory;
use Prooph\Common\Messaging\NoOpMessageConverter;
use Prooph\EventStore\ActionEventEmitterEventStore;
use Prooph\EventStore\PDO\Container\MySQLEventStoreFactory;
use Prooph\EventStore\PDO\Exception\InvalidArgumentException;
use Prooph\EventStore\PDO\MySQLEventStore;
use Prooph\EventStore\PDO\PersistenceStrategy;
use Prooph\EventStore\TransactionalActionEventEmitterEventStore;
use ProophTest\EventStore\PDO\TestUtil;

final class MySQLEventStoreFactoryTest extends TestCase
{
    /**
     * @test
     */
    public function it_creates_adapter_via_connection_service(): void
    {
        $config['prooph']['event_store']['default'] = [
            'connection_service' => 'my_connection',
            'persistence_strategy' => PersistenceStrategy\MySQLAggregateStreamStrategy::class,
            'wrap_action_event_emitter' => false,
        ];

        $connection = TestUtil::getConnection();

        $container = $this->prophesize(ContainerInterface::class);

        $container->get('my_connection')->willReturn($connection)->shouldBeCalled();
        $container->get('config')->willReturn($config)->shouldBeCalled();
        $container->get(FQCNMessageFactory::class)->willReturn(new FQCNMessageFactory())->shouldBeCalled();
        $container->get(NoOpMessageConverter::class)->willReturn(new NoOpMessageConverter())->shouldBeCalled();
        $container->get(PersistenceStrategy\MySQLAggregateStreamStrategy::class)->willReturn(new PersistenceStrategy\MySQLAggregateStreamStrategy())->shouldBeCalled();

        $factory = new MySQLEventStoreFactory();
        $eventStore = $factory($container->reveal());

        $this->assertInstanceOf(MySQLEventStore::class, $eventStore);
    }

    /**
     * @test
     */
    public function it_creates_adapter_via_connection_options(): void
    {
        $config['prooph']['event_store']['custom'] = [
            'connection_options' => TestUtil::getConnectionParams(),
            'persistence_strategy' => PersistenceStrategy\MySQLAggregateStreamStrategy::class,
            'wrap_action_event_emitter' => false,
        ];

        $container = $this->prophesize(ContainerInterface::class);

        $container->get('config')->willReturn($config)->shouldBeCalled();
        $container->get(FQCNMessageFactory::class)->willReturn(new FQCNMessageFactory())->shouldBeCalled();
        $container->get(NoOpMessageConverter::class)->willReturn(new NoOpMessageConverter())->shouldBeCalled();
        $container->get(PersistenceStrategy\MySQLAggregateStreamStrategy::class)->willReturn(new PersistenceStrategy\MySQLAggregateStreamStrategy())->shouldBeCalled();

        $eventStoreName = 'custom';
        $eventStore = MySQLEventStoreFactory::$eventStoreName($container->reveal());

        $this->assertInstanceOf(MySQLEventStore::class, $eventStore);
    }

    /**
     * @test
     */
    public function it_wraps_action_event_emitter(): void
    {
        $config['prooph']['event_store']['custom'] = [
            'connection_options' => TestUtil::getConnectionParams(),
            'persistence_strategy' => PersistenceStrategy\MySQLAggregateStreamStrategy::class,
        ];

        $container = $this->prophesize(ContainerInterface::class);

        $container->get('config')->willReturn($config)->shouldBeCalled();
        $container->get(FQCNMessageFactory::class)->willReturn(new FQCNMessageFactory())->shouldBeCalled();
        $container->get(NoOpMessageConverter::class)->willReturn(new NoOpMessageConverter())->shouldBeCalled();
        $container->get(PersistenceStrategy\MySQLAggregateStreamStrategy::class)->willReturn(new PersistenceStrategy\MySQLAggregateStreamStrategy())->shouldBeCalled();

        $eventStoreName = 'custom';
        $eventStore = MySQLEventStoreFactory::$eventStoreName($container->reveal());

        $this->assertInstanceOf(ActionEventEmitterEventStore::class, $eventStore);
    }

    /**
     * @test
     */
    public function it_throws_exception_when_invalid_container_given(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $eventStoreName = 'custom';
        MySQLEventStoreFactory::$eventStoreName('invalid container');
    }
}
