<?php

declare(strict_types=1);

namespace BehatApiContext\Tests\Unit\Service\ResetManager;

use BehatApiContext\Service\ResetManager\DoctrineResetManager;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\KernelInterface;

class DoctrineResetManagerTest extends TestCase
{
    private DoctrineResetManager $resetManager;

    protected function setUp(): void
    {
        $this->resetManager = new DoctrineResetManager();
    }

    public function testNeedsResetReturnsFalseForGetMethod(): void
    {
        $this->assertFalse($this->resetManager->needsReset(Request::METHOD_GET));
        $this->assertFalse($this->resetManager->needsReset('get'));
    }

    public function testNeedsResetReturnsTrueForOtherMethods(): void
    {
        $this->assertTrue($this->resetManager->needsReset(Request::METHOD_POST));
        $this->assertTrue($this->resetManager->needsReset(Request::METHOD_PUT));
        $this->assertTrue($this->resetManager->needsReset('delete'));
    }

    public function testResetDoesNothingIfNoEntityManagersParameter(): void
    {
        $kernel = $this->createMock(KernelInterface::class);
        $container = $this->createMock(ContainerInterface::class);

        $kernel->expects($this->once())
            ->method('getContainer')
            ->willReturn($container);

        $container->expects($this->once())
            ->method('hasParameter')
            ->with('doctrine.entity_managers')
            ->willReturn(false);

        $container->expects($this->never())->method('getParameter');
        $container->expects($this->never())->method('initialized');
        $container->expects($this->never())->method('get');

        $this->resetManager->reset($kernel);
    }

    public function testResetClearsEntityManagersAndClosesConnections(): void
    {
        $kernel = $this->createMock(KernelInterface::class);
        $container = $this->createMock(ContainerInterface::class);
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $connection = $this->createMock(Connection::class);

        $entityManagers = ['em1', 'em2'];

        $kernel->method('getContainer')->willReturn($container);
        $container->method('hasParameter')->with('doctrine.entity_managers')->willReturn(true);
        $container->method('getParameter')->with('doctrine.entity_managers')->willReturn($entityManagers);

        $container->method('initialized')
            ->willReturnMap([
                ['em1', true],
                ['em2', false],
            ]);

        $container->method('get')->willReturnCallback(function ($id) use ($entityManager) {
            if ($id === 'em1') {
                return $entityManager;
            }
            return null;
        });

        $entityManager->expects($this->once())->method('clear');

        $entityManager->method('getConnection')->willReturn($connection);

        $connection->method('isConnected')->willReturn(true);
        $connection->expects($this->once())->method('close');

        $this->resetManager->reset($kernel);
    }

    public function testResetDoesNotCloseConnectionIfNotConnected(): void
    {
        $kernel = $this->createMock(KernelInterface::class);
        $container = $this->createMock(ContainerInterface::class);
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $connection = $this->createMock(Connection::class);

        $entityManagers = ['em1'];

        $kernel->method('getContainer')->willReturn($container);
        $container->method('hasParameter')->with('doctrine.entity_managers')->willReturn(true);
        $container->method('getParameter')->with('doctrine.entity_managers')->willReturn($entityManagers);

        $container->method('initialized')->with('em1')->willReturn(true);
        $container->method('get')->with('em1')->willReturn($entityManager);

        $entityManager->expects($this->once())->method('clear');

        $entityManager->method('getConnection')->willReturn($connection);

        $connection->method('isConnected')->willReturn(false);
        $connection->expects($this->never())->method('close');

        $this->resetManager->reset($kernel);
    }
}
