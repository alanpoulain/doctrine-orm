<?php

declare(strict_types=1);

namespace Doctrine\Tests\ORM\Functional\Ticket;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\PreFlushEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;
use Doctrine\Tests\OrmFunctionalTestCase;

/**
 * @group DDC-2692
 */
class DDC2692Test extends OrmFunctionalTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->createSchemaForModels(DDC2692Foo::class);

        $this->_em->clear();
    }

    public function testIsListenerCalledOnlyOnceOnPreFlush(): void
    {
        $listener = $this->getMockBuilder(DDC2692Listener::class)
                         ->setMethods(['preFlush'])
                         ->getMock();

        $listener->expects(self::once())->method('preFlush');

        $this->_em->getEventManager()->addEventSubscriber($listener);

        $this->_em->persist(new DDC2692Foo());
        $this->_em->persist(new DDC2692Foo());

        $this->_em->flush();
        $this->_em->clear();
    }
}
/**
 * @Entity
 * @Table(name="ddc_2692_foo")
 */
class DDC2692Foo
{
    /**
     * @var int
     * @Id
     * @Column(type="integer")
     * @GeneratedValue
     */
    public $id;
}

class DDC2692Listener implements EventSubscriber
{
    /**
     * {@inheritDoc}
     */
    public function getSubscribedEvents(): array
    {
        return [Events::preFlush];
    }

    public function preFlush(PreFlushEventArgs $args): void
    {
    }
}
