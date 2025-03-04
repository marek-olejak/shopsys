<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\DataFixture;

use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\DataFixture\Exception\EntityNotFoundException;
use Shopsys\FrameworkBundle\Component\DataFixture\Exception\MethodGetIdDoesNotExistException;
use Shopsys\FrameworkBundle\Component\DataFixture\Exception\PersistentReferenceNotFoundException;
use Shopsys\FrameworkBundle\Component\DataFixture\PersistentReference;
use Shopsys\FrameworkBundle\Component\DataFixture\PersistentReferenceFacade;
use Shopsys\FrameworkBundle\Component\DataFixture\PersistentReferenceFactory;
use Shopsys\FrameworkBundle\Component\DataFixture\PersistentReferenceRepository;
use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use Shopsys\FrameworkBundle\Model\Product\Product;
use stdClass;

class PersistentReferenceFacadeTest extends TestCase
{
    public function testCannotPersistReferenceToEntityWithoutGetIdMethod()
    {
        $emMock = $this->getMockBuilder(EntityManager::class)
            ->onlyMethods(['__construct', 'persist', 'flush'])
            ->disableOriginalConstructor()
            ->getMock();
        $emMock->expects($this->never())->method('persist');
        $emMock->expects($this->never())->method('flush');

        $persistentReferenceRepositoryMock = $this->getMockBuilder(PersistentReferenceRepository::class)
            ->onlyMethods(['__construct'])
            ->disableOriginalConstructor()
            ->getMock();

        $persistentReferenceFacade = new PersistentReferenceFacade(
            $emMock,
            $persistentReferenceRepositoryMock,
            new PersistentReferenceFactory(new EntityNameResolver([])),
        );
        $this->expectException(MethodGetIdDoesNotExistException::class);
        $persistentReferenceFacade->persistReference('referenceName', new stdClass());
    }

    public function testCanPersistNewReference()
    {
        $emMock = $this->getMockBuilder(EntityManager::class)
            ->onlyMethods(['__construct', 'persist', 'flush'])
            ->disableOriginalConstructor()
            ->getMock();
        $emMock->expects($this->atLeastOnce())->method('persist');
        $emMock->expects($this->atLeastOnce())->method('flush');

        $persistentReferenceRepositoryMock = $this->getMockBuilder(PersistentReferenceRepository::class)
            ->onlyMethods(['__construct', 'getByReferenceName'])
            ->disableOriginalConstructor()
            ->getMock();

        $expectedException = new PersistentReferenceNotFoundException('newReferenceName');
        $persistentReferenceRepositoryMock->method('getByReferenceName')->willThrowException($expectedException);

        $productMock = $this->getMockBuilder(Product::class)
            ->onlyMethods(['getId'])
            ->disableOriginalConstructor()
            ->getMock();

        $productMock->expects($this->any())->method('getId')->willReturn(1);

        $persistentReferenceFacade = new PersistentReferenceFacade(
            $emMock,
            $persistentReferenceRepositoryMock,
            new PersistentReferenceFactory(new EntityNameResolver([])),
        );
        $persistentReferenceFacade->persistReference('newReferenceName', $productMock);
    }

    public function testGetReference()
    {
        $persistentReference = new PersistentReference('referenceName', 'entityName', 1);
        $expectedObject = new stdClass();

        $emMock = $this->getMockBuilder(EntityManager::class)
            ->onlyMethods(['__construct', 'find'])
            ->disableOriginalConstructor()
            ->getMock();
        $emMock->expects($this->once())->method('find')->willReturn($expectedObject);

        $persistentReferenceRepositoryMock = $this->getMockBuilder(PersistentReferenceRepository::class)
            ->onlyMethods(['__construct', 'getByReferenceName'])
            ->disableOriginalConstructor()
            ->getMock();
        $persistentReferenceRepositoryMock
            ->expects($this->once())
            ->method('getByReferenceName')
            ->willReturn($persistentReference);

        $persistentReferenceFacade = new PersistentReferenceFacade(
            $emMock,
            $persistentReferenceRepositoryMock,
            new PersistentReferenceFactory(new EntityNameResolver([])),
        );

        $this->assertSame($expectedObject, $persistentReferenceFacade->getReference('referenceName'));
    }

    public function testGetReferenceNotFound()
    {
        $persistentReference = new PersistentReference('referenceName', 'entityName', 2);

        $emMock = $this->getMockBuilder(EntityManager::class)
            ->onlyMethods(['__construct', 'find'])
            ->disableOriginalConstructor()
            ->getMock();
        $emMock->expects($this->once())->method('find')->willReturn(null);

        $persistentReferenceRepositoryMock = $this->getMockBuilder(PersistentReferenceRepository::class)
            ->onlyMethods(['__construct', 'getByReferenceName'])
            ->disableOriginalConstructor()
            ->getMock();
        $persistentReferenceRepositoryMock
            ->expects($this->once())
            ->method('getByReferenceName')
            ->willReturn($persistentReference);

        $persistentReferenceFacade = new PersistentReferenceFacade(
            $emMock,
            $persistentReferenceRepositoryMock,
            new PersistentReferenceFactory(new EntityNameResolver([])),
        );

        $this->expectException(EntityNotFoundException::class);
        $persistentReferenceFacade->getReference('referenceName');
    }
}
