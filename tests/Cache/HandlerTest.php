<?php

declare(strict_types=1);

/*
 * Content Additional Informations Bundle.
 *
 * @author    Florian ALEXANDRE
 * @copyright 2023-present Florian ALEXANDRE
 * @license   https://github.com/erdnaxelaweb/contentadditionalinformations/blob/main/LICENSE
 */

namespace ErdnaxelaWeb\ContentAdditionalInformations\Tests\Cache;

use ErdnaxelaWeb\ContentAdditionalInformations\Cache\Handler as CacheHandler;
use ErdnaxelaWeb\ContentAdditionalInformations\Entity\ContentAdditionalInformation;
use ErdnaxelaWeb\ContentAdditionalInformations\Persistence\Handler;
use ErdnaxelaWeb\ContentAdditionalInformations\Persistence\HandlerInterface;
use Ibexa\Core\Persistence\Cache\Adapter\TransactionalInMemoryCacheAdapter;
use Ibexa\Core\Persistence\Cache\CacheIndicesValidatorInterface;
use Ibexa\Core\Persistence\Cache\InMemory\InMemoryCache;
use Ibexa\Core\Persistence\Cache\PersistenceLogger;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\CacheItem;

class HandlerTest extends TestCase
{
    protected HandlerInterface $persistenceHandlerMock;
    protected HandlerInterface $persistenceCacheHandler;
    protected PersistenceLogger|MockObject $loggerMock;
    protected InMemoryCache|MockObject $inMemoryMock;
    protected TransactionalInMemoryCacheAdapter|MockObject $cacheMock;
    protected CacheIndicesValidatorInterface $cacheIndicesValidator;
    protected ?\Closure $cacheItemsClosure;

    protected function setUp(): void
    {
        parent::setUp();

        $this->persistenceHandlerMock = $this->createMock(Handler::class);
        $this->cacheMock = $this->createMock(TransactionalInMemoryCacheAdapter::class);
        $this->loggerMock = $this->createMock(PersistenceLogger::class);
        $this->inMemoryMock = $this->createMock(InMemoryCache::class);
        $this->cacheIndicesValidator = $this->createMock(CacheIndicesValidatorInterface::class);

        $this->persistenceCacheHandler = new CacheHandler(
            $this->cacheMock,
            $this->loggerMock,
            $this->inMemoryMock,
            $this->cacheIndicesValidator
        );
        $this->persistenceCacheHandler->setInnerHandler($this->persistenceHandlerMock);

        $this->cacheItemsClosure = \Closure::bind(
            static function ($key, $value, $isHit, $defaultLifetime = 0) {
                $item = new CacheItem();
                $item->key = $key;
                $item->value = $value;
                $item->isHit = $isHit;
                $item->defaultLifetime = $defaultLifetime;
                $item->isTaggable = true;

                return $item;
            },
            null,
            CacheItem::class
        );
    }

    public function getHandlerMethodName(): string
    {
        return 'contentAdditionalInformationHandler';
    }

    public function getHandlerClassName(): string
    {
        return HandlerInterface::class;
    }

    /**
     * @dataProvider providerForCachedLoadMethodsHit
     *
     * @param mixed $data
     * @param bool $multi Default false, set to true if method will lookup several cache items.
     */
    public function testLoadMethodsCacheHit(
        string $method,
        array $arguments,
        string $key,
        $data = null,
        bool $multi = false
    ): void {
        $cacheItem = $this->getCacheItem($key, $multi ? reset($data) : $data);

        $this->loggerMock->expects($this->once())->method('logCacheHit');
        $this->loggerMock->expects($this->never())->method('logCall');
        $this->loggerMock->expects($this->never())->method('logCacheMiss');

        if ($multi) {
            $this->cacheMock
                ->expects($this->once())
                ->method('getItems')
                ->with([$cacheItem->getKey()])
                ->willReturn([
                    $key => $cacheItem,
                ]);
        } else {
            $this->cacheMock
                ->expects($this->once())
                ->method('getItem')
                ->with($cacheItem->getKey())
                ->willReturn($cacheItem);
        }

        $this->persistenceHandlerMock
            ->expects($this->never())
            ->method($method);

        $return = call_user_func_array([$this->persistenceCacheHandler, $method], $arguments);

        $this->assertEquals($data, $return);
    }

    /**
     * @dataProvider providerForUnCachedMethods
     *
     * @param mixed $returnValue
     */
    public function testUnCachedMethods(
        string $method,
        array $arguments,
        array $tags = null,
        array $key = null,
        $returnValue = null,
    ): void {
        $this->loggerMock->expects($this->once())->method('logCall');
        $this->loggerMock->expects($this->never())->method('logCacheHit');
        $this->loggerMock->expects($this->never())->method('logCacheMiss');

        if ($tags || $key) {
            $this->cacheMock
                ->expects(!empty($tags) ? $this->once() : $this->never())
                ->method('invalidateTags')
                ->with($tags);

            $this->cacheMock
                ->expects(!empty($key) ? $this->once() : $this->never())
                ->method('deleteItems')
                ->with($key);
        } else {
            $this->cacheMock
                ->expects($this->never())
                ->method($this->anything());
        }

        $invocationMocker = $this->persistenceHandlerMock
            ->expects($this->once())
            ->method($method)
            ->with(...$arguments);

        if (null !== $returnValue) {
            $invocationMocker->willReturn($returnValue);
        }

        $actualReturnValue = call_user_func_array(
            [$this->persistenceCacheHandler, $method],
            $arguments
        );

        $this->assertEquals($returnValue, $actualReturnValue);
    }

    /**
     * @dataProvider providerForCachedLoadMethodsMiss
     *
     * @param null $data
     * @param bool $multi Default false, set to true if method will lookup several cache items.
     */
    public function testLoadMethodsCacheMiss(
        string $method,
        array $arguments,
        string $key,
        $data = null,
        bool $multi = false,
    ): void {
        $cacheItem = $this->getCacheItem($key, null);

        $this->loggerMock->expects($this->once())->method('logCacheMiss');
        $this->loggerMock->expects($this->never())->method('logCall');
        $this->loggerMock->expects($this->never())->method('logCacheHit');

        if ($multi) {
            $this->cacheMock
                ->expects($this->once())
                ->method('getItems')
                ->with([$cacheItem->getKey()])
                ->willReturn([
                    $key => $cacheItem,
                ]);
        } else {
            $this->cacheMock
                ->expects($this->once())
                ->method('getItem')
                ->with($cacheItem->getKey())
                ->willReturn($cacheItem);
        }

        $this->cacheMock
            ->expects($this->once())
            ->method('save')
            ->with($cacheItem);

        $this->persistenceHandlerMock
            ->expects($this->once())
            ->method($method)
            ->with(...$arguments)
            ->willReturn($data);

        $return = call_user_func_array([$this->persistenceCacheHandler, $method], $arguments);

        $this->assertEquals($data, $return);

        // Assert use of tags would probably need custom logic as internal property is [$tag => $tag] value, and we don't want to know that.
        //$this->assertAttributeEquals([], 'tags', $cacheItem);
    }

    public function providerForUnCachedMethods(): array
    {
        return [
            ['create', [2, 1, 'test', 'test-value']],
            ['update', [2, 1, 'test', 'test-value'], null, ['cai-2-1-test']],
            ['delete', [2, 1, 'test'], null, ['cai-2-1-test']],
            ['delete', [2, 1], ['cai-2-1']],
            ['purge', [[2, 3, 4]], ['cai-2', 'cai-3', 'cai-4']],
        ];
    }

    public function providerForCachedLoadMethodsHit(): array
    {
        $additionalInformation = new ContentAdditionalInformation(2, 1, 'test', 'test-value');
        return [
            ['load', [2, 1, 'test'], 'cai-2-1-test', $additionalInformation],
        ];
    }

    public function providerForCachedLoadMethodsMiss(): array
    {
        $additionalInformation = new ContentAdditionalInformation(2, 1, 'test', 'test-value');
        return [
            ['load', [2, 1, 'test'], 'cai-2-1-test', $additionalInformation],
        ];
    }
    /**
     * @param null $value If null the cache item will be assumed to be a cache miss here.
     */
    protected function getCacheItem(string $key, mixed $value = null, int $defaultLifetime = 0): CacheItem
    {
        $cacheItemsClosure = $this->cacheItemsClosure;

        return $cacheItemsClosure($key, $value, (bool)$value, $defaultLifetime);
    }
}
