<?php

declare(strict_types=1);

namespace BehatApiContext\Context;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use RuntimeException;

final class ORMContext implements Context
{
    private EntityManagerInterface $manager;

    public function __construct(EntityManagerInterface $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @And I see :count entities :entityClass
     */
    public function andISeeInRepository(int $count, string $entityClass): void
    {
        $this->seeInRepository($count, $entityClass);
    }

    /**
     * @Then I see :count entities :entityClass
     */
    public function thenISeeInRepository(int $count, string $entityClass): void
    {
        $this->seeInRepository($count, $entityClass);
    }

    /**
     * @And I see entity :entity with id :id
     */
    public function andISeeEntityInRepositoryWithId(string $entityClass, string $id): void
    {
        $this->seeInRepository(1, $entityClass, ['id' => $id]);
    }

    /**
     * @Then I see entity :entity with id :id
     */
    public function thenISeeEntityInRepositoryWithId(string $entityClass, string $id): void
    {
        $this->seeInRepository(1, $entityClass, ['id' => $id]);
    }

    /**
     * @Then I see entity :entity with properties:
     */
    public function andISeeEntityInRepositoryWithProperties(string $entityClass, PyStringNode $string): void
    {
        $expectedProperties = json_decode(trim($string->getRaw()), true, 512, JSON_THROW_ON_ERROR);
        $this->seeInRepository(1, $entityClass, $expectedProperties);
    }

    /**
     * @param array<string, mixed> $params
     *
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    private function seeInRepository(int $count, string $entityClass, ?array $params = null): void
    {
        $query = $this->manager->createQueryBuilder()
            ->from($entityClass, 'e')
            ->select('count(e)');

        if (null !== $params) {
            foreach ($params as $columnName => $columnValue) {
                if ($columnValue === null) {
                    $query->andWhere(sprintf('e.%s IS NULL', $columnName));
                } else {
                    $query->andWhere(sprintf('e.%s = :%s', $columnName, $columnName))
                        ->setParameter($columnName, $columnValue);
                }
            }
        }

        $realCount = $query->getQuery()
            ->getSingleScalarResult();

        if ($count !== $realCount) {
            throw new RuntimeException(
                sprintf('Real count is %d, not %d', $realCount, $count),
            );
        }
    }
}
