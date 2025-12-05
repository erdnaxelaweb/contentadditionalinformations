<?php

declare(strict_types=1);

/*
 * Content Additional Informations Bundle.
 *
 * @author    Florian ALEXANDRE
 * @copyright 2023-present Florian ALEXANDRE
 * @license   https://github.com/erdnaxelaweb/contentadditionalinformations/blob/main/LICENSE
 */

namespace ErdnaxelaWeb\ContentAdditionalInformations\Persistence;

use ErdnaxelaWeb\ContentAdditionalInformations\Entity\ContentAdditionalInformation;

interface HandlerInterface
{
    /**
     * @throws \Ibexa\Core\Base\Exceptions\NotFoundException
     */
    public function load(int $contentId, int $contentVersionNo, string $identifier): ContentAdditionalInformation;

    public function create(int $contentId, int $contentVersionNo, string $identifier, mixed $value): void;

    public function update(int $contentId, int $contentVersionNo, string $identifier, mixed $value): void;

    public function delete(int $contentId, int $contentVersionNo, string $identifier = null): void;

    /**
     * @param int[] $contentIds
     */
    public function purge(array $contentIds): void;

    /**
     * @return ContentAdditionalInformation[]
     */
    public function list(int $contentId, int $contentVersionNo): array;
}
