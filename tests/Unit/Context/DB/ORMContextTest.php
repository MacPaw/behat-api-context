<?php

declare(strict_types=1);

namespace BehatApiContext\Tests\Unit\Context\DB;

use Behat\Gherkin\Node\PyStringNode;
use BehatApiContext\Context\ORMContext;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class ORMContextTest extends TestCase
{
    private const COUNT = 1;
    private const UUID = 'e809639f-011a-4ae0-9ae3-8fcb460fe950';

    public function testAndISeeCountInRepository(): void
    {
        $context = $this->createContext('App\Entity\SomeEntity', self::COUNT);
        $context->andISeeInRepository(self::COUNT, 'App\Entity\SomeEntity');
    }

    public function testAndISeeCountInRepositoryFailed(): void
    {
        $context = $this->createContext('App\Entity\SomeEntity', self::COUNT);
        self::expectException(RuntimeException::class);
        $context->andISeeInRepository(self::COUNT + 1, 'App\Entity\SomeEntity');
    }

    public function testThenISeeCountInRepository(): void
    {
        $context = $this->createContext('App\Entity\SomeEntity', self::COUNT);
        $context->thenISeeInRepository(self::COUNT, 'App\Entity\SomeEntity');
    }

    public function testThenISeeCountInRepositoryFailed(): void
    {
        $context = $this->createContext('App\Entity\SomeEntity', self::COUNT);
        self::expectException(RuntimeException::class);
        $context->thenISeeInRepository(self::COUNT + 1, 'App\Entity\SomeEntity');
    }

    public function testThenISeeCountInRepositoryWithId(): void
    {
        $context = $this->createContext(
            'App\Entity\SomeEntity',
            1,
            ['id' => self::UUID],
        );
        $context->thenISeeEntityInRepositoryWithId(
            'App\Entity\SomeEntity',
            self::UUID,
        );
    }

    public function testThenISeeCountInRepositoryWithIdFailed(): void
    {
        $context = $this->createContext(
            'App\Entity\SomeEntity',
            1,
            ['id' => self::UUID],
        );
        $context->andISeeEntityInRepositoryWithId(
            'App\Entity\SomeEntity',
            self::UUID,
        );
    }

    public function testThenISeeEntityInRepositoryWithProperties(): void
    {
        $context = $this->createContext(
            'App\Entity\SomeEntity',
            1,
            [
                'id' => self::UUID,
                'someProperty' => 'someValue',
                'otherProperty' => 'otherValue',
            ],
        );
        $context->andISeeEntityInRepositoryWithProperties(
            'App\Entity\SomeEntity',
            new PyStringNode([
                <<<'PSN'
                {
                    "id": "e809639f-011a-4ae0-9ae3-8fcb460fe950",
                    "someProperty": "someValue",
                    "otherProperty": "otherValue"
                }
                PSN
            ], 1),
        );
    }

    public function testThenISeeEntityInRepositoryWithPropertyNull(): void
    {
        $context = $this->createContext(
            'App\Entity\SomeEntity',
            1,
            [
                'id' => self::UUID,
                'someProperty' => null,
            ],
        );
        $context->andISeeEntityInRepositoryWithProperties(
            'App\Entity\SomeEntity',
            new PyStringNode([
                <<<'PSN'
                {
                    "id": "e809639f-011a-4ae0-9ae3-8fcb460fe950",
                    "someProperty": null
                }
                PSN
            ], 1),
        );
    }

    private function createContext(
        string $entityName,
        int $count = 1,
        ?array $properties = null
    ): ORMContext {
        $queryMock = $this->getMockBuilder(Query::class)
            ->disableOriginalConstructor()
            ->getMock();

        $queryMock->expects(self::once())
            ->method('getSingleScalarResult')
            ->willReturn($count);

        $entityManagerMock = $this->getMockBuilder(EntityManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $queryBuilderMock = $this->getMockBuilder(QueryBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $queryBuilderMock->expects(self::once())
            ->method('from')
            ->with(
                $entityName,
                'e',
            )->willReturn($queryBuilderMock);

        if (null !== $properties) {
            foreach ($properties as $name => $value) {
                $queryBuilderMock->expects(self::exactly(count($properties)))
                    ->method('andWhere')
                    ->willReturnSelf();
                $setParametersCount = count(array_filter($properties, function ($value) {
                    return !is_null($value);
                }));
                $queryBuilderMock->expects(self::exactly($setParametersCount))
                    ->method('setParameter')
                    ->willReturnSelf();
            }
        }

        $queryBuilderMock->expects(self::once())
            ->method('select')
            ->with('count(e)')
            ->willReturn($queryBuilderMock);

        $queryBuilderMock->expects(self::once())
            ->method('getQuery')
            ->willReturn($queryMock);

        $entityManagerMock->expects(self::once())
            ->method('createQueryBuilder')
            ->willReturn($queryBuilderMock);

        return new ORMContext($entityManagerMock);
    }
}
