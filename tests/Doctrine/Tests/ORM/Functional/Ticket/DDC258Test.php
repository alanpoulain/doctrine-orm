<?php

declare(strict_types=1);

namespace Doctrine\Tests\ORM\Functional\Ticket;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\DiscriminatorColumn;
use Doctrine\ORM\Mapping\DiscriminatorMap;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\InheritanceType;
use Doctrine\ORM\Mapping\Table;
use Doctrine\Tests\OrmFunctionalTestCase;

class DDC258Test extends OrmFunctionalTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->createSchemaForModels(
            DDC258Super::class,
            DDC258Class1::class,
            DDC258Class2::class,
            DDC258Class3::class
        );
    }

    /**
     * @group DDC-258
     */
    public function testIssue(): void
    {
        $c1              = new DDC258Class1();
        $c1->title       = 'Foo';
        $c1->description = 'Foo';

        $c2              = new DDC258Class2();
        $c2->title       = 'Bar';
        $c2->description = 'Bar';
        $c2->text        = 'Bar';

        $c3          = new DDC258Class3();
        $c3->apples  = 'Baz';
        $c3->bananas = 'Baz';

        $this->_em->persist($c1);
        $this->_em->persist($c2);
        $this->_em->persist($c3);
        $this->_em->flush();
        $this->_em->clear();

        $e2 = $this->_em->find(DDC258Super::class, $c2->id);

        self::assertInstanceOf(DDC258Class2::class, $e2);
        self::assertEquals('Bar', $e2->title);
        self::assertEquals('Bar', $e2->description);
        self::assertEquals('Bar', $e2->text);

        $all = $this->_em->getRepository(DDC258Super::class)->findAll();

        foreach ($all as $obj) {
            if ($obj instanceof DDC258Class1) {
                self::assertEquals('Foo', $obj->title);
                self::assertEquals('Foo', $obj->description);
            } elseif ($obj instanceof DDC258Class2) {
                self::assertSame($e2, $obj);
                self::assertEquals('Bar', $obj->title);
                self::assertEquals('Bar', $obj->description);
                self::assertEquals('Bar', $obj->text);
            } elseif ($obj instanceof DDC258Class3) {
                self::assertEquals('Baz', $obj->apples);
                self::assertEquals('Baz', $obj->bananas);
            } else {
                self::fail('Instance of DDC258Class1, DDC258Class2 or DDC258Class3 expected.');
            }
        }
    }
}

/**
 * @Entity
 * @Table(name="DDC258Super")
 * @InheritanceType("JOINED")
 * @DiscriminatorColumn(name="type", type="string")
 * @DiscriminatorMap({"class1" = "DDC258Class1", "class2" = "DDC258Class2", "class3"="DDC258Class3"})
 */
abstract class DDC258Super
{
    /**
     * @var int
     * @Id
     * @Column(name="id", type="integer")
     * @GeneratedValue(strategy="AUTO")
     */
    public $id;
}

/**
 * @Entity
 */
class DDC258Class1 extends DDC258Super
{
    /**
     * @var string
     * @Column(name="title", type="string", length=150)
     */
    public $title;

    /**
     * @var string
     * @Column(name="content", type="string", length=500)
     */
    public $description;
}

/**
 * @Entity
 */
class DDC258Class2 extends DDC258Super
{
    /**
     * @var string
     * @Column(name="title", type="string", length=150)
     */
    public $title;

    /**
     * @var string
     * @Column(name="content", type="string", length=500)
     */
    public $description;

    /**
     * @var string
     * @Column(name="text", type="text")
     */
    public $text;
}

/**
 * An extra class to demonstrate why title and description aren't in Super
 *
 * @Entity
 */
class DDC258Class3 extends DDC258Super
{
    /**
     * @var string
     * @Column(name="title", type="string", length=150)
     */
    public $apples;

    /**
     * @var string
     * @Column(name="content", type="string", length=500)
     */
    public $bananas;
}
