<?php

declare(strict_types=1);

/*
 * Content Additional Informations Bundle.
 *
 * @author    Florian ALEXANDRE
 * @copyright 2023-present Florian ALEXANDRE
 * @license   https://github.com/erdnaxelaweb/contentadditionalinformations/blob/main/LICENSE
 */

namespace ErdnaxelaWeb\ContentAdditionalInformations\Service;

use ErdnaxelaWeb\ContentAdditionalInformations\Entity\ContentAdditionalInformation;
use ErdnaxelaWeb\ContentAdditionalInformations\Persistence\HandlerInterface;
use Ibexa\Core\Base\Exceptions\NotFoundException;

class ContentAdditionalInformationsService
{
    public function __construct(
        protected HandlerInterface $handler
    ) {
    }

    /**
     * @throws NotFoundException
     */
    public function get(int $contentId, int $contentVersionNo, string $identifier): ContentAdditionalInformation
    {
        return $this->handler->load($contentId, $contentVersionNo, $identifier);
    }

    /**
     * @return array<ContentAdditionalInformation>
     */
    public function getAll(int $contentId, int $contentVersionNo): array
    {
        return $this->handler->list($contentId, $contentVersionNo);
    }


    public function set(int $contentId, int $contentVersionNo, string $identifier, mixed $value): void
    {
        try {
            $this->get($contentId, $contentVersionNo, $identifier);
            $this->handler->update(
                $contentId,
                $contentVersionNo,
                $identifier,
                $value
            );
        } catch (NotFoundException $e) {
            $this->handler->create(
                $contentId,
                $contentVersionNo,
                $identifier,
                $value
            );
        }
    }

    public function delete(int $contentId, int $contentVersionNo, string $identifier = null): void
    {
        $this->handler->delete($contentId, $contentVersionNo, $identifier);
    }

    /**
     * @param int[] $contentIds
     */
    public function purge(array $contentIds): void
    {
        $this->handler->purge($contentIds);
    }
}
