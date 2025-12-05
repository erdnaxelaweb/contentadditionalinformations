<?php

declare(strict_types=1);

/*
 * Content Additional Informations Bundle.
 *
 * @author    Florian ALEXANDRE
 * @copyright 2023-present Florian ALEXANDRE
 * @license   https://github.com/erdnaxelaweb/contentadditionalinformations/blob/main/LICENSE
 */

namespace ErdnaxelaWeb\ContentAdditionalInformations\Persistence\Gateway;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;

class DoctrineDatabase
{
    public const TABLE_NAME = 'content_additional_information';

    public function __construct(
        protected Connection $connection
    ) {
    }

    /**
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function load(int $contentId, int $contentVersionNo, string $identifier = null): mixed
    {
        $query = $this->connection->createQueryBuilder();
        $query->select('*')
            ->from(self::TABLE_NAME)
            ->where('content_id = :contentId')
            ->andWhere('content_version_no = :contentVersionNo')
            ->setParameter('contentId', $contentId, ParameterType::INTEGER)
            ->setParameter('contentVersionNo', $contentVersionNo, ParameterType::INTEGER);

        if ($identifier !== null) {
            $query->andWhere('identifier = :identifier')
                  ->setParameter('identifier', $identifier, ParameterType::STRING);
        }

        return $query->execute()->fetchAllAssociative();
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function insert(int $contentId, int $contentVersionNo, string $identifier, string $value): void
    {
        $query = $this->connection->createQueryBuilder();
        $query->insert(self::TABLE_NAME)
            ->values([
                'content_id' => $query->createPositionalParameter(
                    $contentId,
                    ParameterType::INTEGER
                ),
                'content_version_no' => $query->createPositionalParameter(
                    $contentVersionNo,
                    ParameterType::INTEGER
                ),
                'identifier' => $query->createPositionalParameter(
                    $identifier,
                    ParameterType::STRING
                ),
                'value' => $query->createPositionalParameter(
                    $value,
                    ParameterType::STRING
                ),
            ]);

        $query->execute();
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function update(int $contentId, int $contentVersionNo, string $identifier, string $value): void
    {
        $query = $this->connection->createQueryBuilder();
        $query->update(self::TABLE_NAME)
            ->set('value', ':value')
            ->where('content_id = :contentId')
            ->andWhere('content_version_no = :contentVersionNo')
            ->andWhere('identifier = :identifier')
            ->setParameter('value', $value, ParameterType::STRING)
            ->setParameter('contentId', $contentId, ParameterType::INTEGER)
            ->setParameter('contentVersionNo', $contentVersionNo, ParameterType::INTEGER)
            ->setParameter('identifier', $identifier, ParameterType::STRING);

        $query->execute();
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function delete(int $contentId, int $contentVersionNo, string $identifier = null): void
    {
        $query = $this->connection->createQueryBuilder();
        $query->delete(self::TABLE_NAME)
            ->where('content_id = :contentId')
            ->andWhere('content_version_no = :contentVersionNo')
            ->setParameter('contentId', $contentId, ParameterType::INTEGER)
            ->setParameter('contentVersionNo', $contentVersionNo, ParameterType::INTEGER);

        if ($identifier) {
            $query->andWhere('identifier = :identifier')
                  ->setParameter('identifier', $identifier, ParameterType::STRING);
        }

        $query->execute();
    }

    /**
     * @param int[] $contentIds
     *
     * @throws \Doctrine\DBAL\Exception
     */
    public function purge(array $contentIds): void
    {
        $query = $this->connection->createQueryBuilder();
        $query->delete(self::TABLE_NAME)
            ->where(
                $query->expr()->in(
                    'content_id',
                    $contentIds
                )
            );

        $query->execute();
    }
}
