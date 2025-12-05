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
use ErdnaxelaWeb\ContentAdditionalInformations\Persistence\Gateway\DoctrineDatabase;
use Ibexa\Core\Base\Exceptions\NotFoundException as NotFound;

class Handler implements HandlerInterface
{
    public function __construct(
        protected DoctrineDatabase $gateway
    ) {
    }

    /**
     * @throws NotFound
     */
    public function load(int $contentId, int $contentVersionNo, string $identifier): ContentAdditionalInformation
    {
        $rows = $this->gateway->load(
            $contentId,
            $contentVersionNo,
            $identifier
        );
        if (empty($rows)) {
            throw new NotFound(
                'content additional information',
                "contentId: $contentId, identifier: $identifier"
            );
        }

        $results = $this->mapRows($rows);

        $result = reset($results);
        unset($rows, $results);

        return $result;
    }

    public function create(int $contentId, int $contentVersionNo, string $identifier, mixed $value): void
    {
        $this->gateway->insert(
            $contentId,
            $contentVersionNo,
            $identifier,
            $this->transformValue($value)
        );
    }

    public function update(int $contentId, int $contentVersionNo, string $identifier, mixed $value): void
    {
        $this->gateway->update(
            $contentId,
            $contentVersionNo,
            $identifier,
            $this->transformValue($value)
        );
    }

    public function delete(int $contentId, int $contentVersionNo, string $identifier = null): void
    {
        $this->gateway->delete(
            $contentId,
            $contentVersionNo,
            $identifier
        );
    }

    public function purge(array $contentIds): void
    {
        $this->gateway->purge($contentIds);
    }

    public function list(int $contentId, int $contentVersionNo): array
    {
        $rows = $this->gateway->load(
            $contentId,
            $contentVersionNo
        );
        return $this->mapRows($rows);
    }

    /**
     * @param array<string, scalar>[] $rows
     *
     * @return ContentAdditionalInformation[]
     */
    protected function mapRows(array $rows): array
    {
        $list = [];
        foreach ($rows as $row) {
            $list[] = $this->mapRow($row);
        }

        return $list;
    }

    /**
     * @param array<string, scalar> $row
     */
    protected function mapRow(array $row): ContentAdditionalInformation
    {
        return new ContentAdditionalInformation(
            $row['content_id'],
            $row['content_version_no'],
            $row['identifier'],
            $this->reverseTransformValue($row['value'])
        );
    }

    protected function transformValue(mixed $value): mixed
    {
        return json_encode($value);
    }

    protected function reverseTransformValue(mixed $value): mixed
    {
        return json_decode($value, true);
    }
}
