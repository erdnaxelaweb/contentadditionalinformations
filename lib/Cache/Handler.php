<?php

declare(strict_types=1);

/*
 * Content Additional Informations Bundle.
 *
 * @author    Florian ALEXANDRE
 * @copyright 2023-present Florian ALEXANDRE
 * @license   https://github.com/erdnaxelaweb/contentadditionalinformations/blob/main/LICENSE
 */

namespace ErdnaxelaWeb\ContentAdditionalInformations\Cache;

use ErdnaxelaWeb\ContentAdditionalInformations\Entity\ContentAdditionalInformation;
use ErdnaxelaWeb\ContentAdditionalInformations\Persistence\HandlerInterface;
use Ibexa\Core\Persistence\Cache\AbstractInMemoryHandler;
use Ibexa\Core\Persistence\Cache\Adapter\TransactionAwareAdapterInterface;
use Ibexa\Core\Persistence\Cache\CacheIndicesValidatorInterface;
use Ibexa\Core\Persistence\Cache\InMemory\InMemoryCache;
use Ibexa\Core\Persistence\Cache\PersistenceLogger;

class Handler extends AbstractInMemoryHandler implements HandlerInterface
{
    protected HandlerInterface $innerHandler;

    public function __construct(
        TransactionAwareAdapterInterface $cache,
        PersistenceLogger                $logger,
        InMemoryCache                    $inMemory,
        ?CacheIndicesValidatorInterface  $cacheIndicesValidator = null
    ) {
        parent::__construct($cache, $logger, $inMemory, $cacheIndicesValidator);
    }

    public function setInnerHandler(HandlerInterface $innerHandler): void
    {
        $this->innerHandler = $innerHandler;
    }

    public function load(int $contentId, int $contentVersionNo, string $identifier): ContentAdditionalInformation
    {
        $keySuffix = "-{$contentVersionNo}-{$identifier}";
        return $this->getCacheValue(
            $contentId,
            'cai-',
            function () use ($contentId, $contentVersionNo, $identifier) {
                return $this->innerHandler->load(
                    $contentId,
                    $contentVersionNo,
                    $identifier
                );
            },
            function (ContentAdditionalInformation $contentAdditionalInformation) {
                return [
                    vsprintf(
                        'cai-%d',
                        [
                            $contentAdditionalInformation->getContentId(),
                        ]
                    ),
                    vsprintf(
                        'cai-%d-%d',
                        [
                            $contentAdditionalInformation->getContentId(),
                            $contentAdditionalInformation->getContentVersion(),
                        ]
                    ),
                    vsprintf(
                        'cai-%d-%d-%s',
                        [
                            $contentAdditionalInformation->getContentId(),
                            $contentAdditionalInformation->getContentVersion(),
                            $contentAdditionalInformation->getIdentifier(),
                        ]
                    ),
                ];
            },
            function (ContentAdditionalInformation $contentAdditionalInformation) use ($keySuffix) {
                return [
                    vsprintf(
                        'cai-%d',
                        [$contentAdditionalInformation->getContentId()]
                    ) . $keySuffix,
                ];
            },
            $keySuffix,
            [
                'content' => $contentId,
                'identifier' => $identifier,
            ]
        );
    }

    public function list(int $contentId, int $contentVersionNo): array
    {
        return $this->getListCacheValue(
            vsprintf('cai-%d-%d', [$contentId, $contentVersionNo]),
            function () use ($contentId, $contentVersionNo) {
                return $this->innerHandler->list(
                    $contentId,
                    $contentVersionNo
                );
            },
            function (ContentAdditionalInformation $contentAdditionalInformation) {
                return [
                    vsprintf(
                        'cai-%d',
                        [
                            $contentAdditionalInformation->getContentId(),
                        ]
                    ),
                    vsprintf(
                        'cai-%d-%d',
                        [
                            $contentAdditionalInformation->getContentId(),
                            $contentAdditionalInformation->getContentVersion(),
                        ]
                    ),
                    vsprintf(
                        'cai-%d-%d-%s',
                        [
                            $contentAdditionalInformation->getContentId(),
                            $contentAdditionalInformation->getContentVersion(),
                            $contentAdditionalInformation->getIdentifier(),
                        ]
                    ),
                ];
            },
            function (ContentAdditionalInformation $contentAdditionalInformation) {
                return [
                    vsprintf(
                        'cai-%d-%d',
                        [
                            $contentAdditionalInformation->getContentId(),
                            $contentAdditionalInformation->getContentVersion(),
                        ]
                    ),
                ];
            },
            null,
            [
                'content' => $contentId,
            ]
        );
    }

    public function create(int $contentId, int $contentVersionNo, string $identifier, mixed $value): void
    {
        $this->logger->logCall(
            __METHOD__,
            [
                'contentId' => $contentId,
                'contentVersionNo' => $contentVersionNo,
                'identifier' => $identifier,
            ]
        );

        $this->innerHandler->create($contentId, $contentVersionNo, $identifier, $value);
    }

    /**
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function update(int $contentId, int $contentVersionNo, string $identifier, mixed $value): void
    {
        $this->logger->logCall(
            __METHOD__,
            [
                'contentId' => $contentId,
                'contentVersionNo' => $contentVersionNo,
                'identifier' => $identifier,
            ]
        );

        $this->innerHandler->update($contentId, $contentVersionNo, $identifier, $value);
        $this->cache->deleteItems(
            [vsprintf('cai-%d-%d-%s', [$contentId, $contentVersionNo, $identifier])]
        );
    }

    /**
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function delete(int $contentId, int $contentVersionNo, string $identifier = null): void
    {
        $this->logger->logCall(
            __METHOD__,
            [
                'contentId' => $contentId,
                'contentVersionNo' => $contentVersionNo,
                'identifier' => $identifier,
            ]
        );

        $this->innerHandler->delete($contentId, $contentVersionNo, $identifier);
        if ($identifier) {
            $this->cache->deleteItems(
                [vsprintf('cai-%d-%d-%s', [$contentId, $contentVersionNo, $identifier])]
            );
        } else {
            $this->cache->invalidateTags(
                [vsprintf('cai-%d-%d', [$contentId, $contentVersionNo])]
            );
        }
    }

    /**
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function purge(array $contentIds): void
    {
        $this->logger->logCall(__METHOD__, [
            'contentIds' => $contentIds,
        ]);

        $this->innerHandler->purge($contentIds);

        $cacheTags = array_map(
            fn (int $contentId) => vsprintf('cai-%d', [$contentId]),
            $contentIds
        );
        $this->cache->invalidateTags($cacheTags);
    }
}
