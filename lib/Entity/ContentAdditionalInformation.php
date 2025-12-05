<?php

declare(strict_types=1);

/*
 * Content Additional Informations Bundle.
 *
 * @author    Florian ALEXANDRE
 * @copyright 2023-present Florian ALEXANDRE
 * @license   https://github.com/erdnaxelaweb/contentadditionalinformations/blob/main/LICENSE
 */

namespace ErdnaxelaWeb\ContentAdditionalInformations\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="content_additional_information")
 */
class ContentAdditionalInformation
{
    /**
     * @ORM\Id()
     * @ORM\Column(type="integer", name="content_id")
     */
    protected int $contentId;

    /**
     * @ORM\Id()
     * @ORM\Column(type="integer", name="content_version_no")
     */
    protected int $contentVersionNo;

    /**
     * @ORM\Id()
     * @ORM\Column(type="string")
     */
    protected string $identifier;

    /**
     * @ORM\Column(type="json")
     */
    protected mixed $value;

    public function __construct(
        int $contentId,
        int $contentVersionNo,
        string $identifier,
        mixed $value
    ) {
        $this->contentId = $contentId;
        $this->contentVersionNo = $contentVersionNo;
        $this->identifier = $identifier;
        $this->value = $value;
    }

    public function getContentId(): int
    {
        return $this->contentId;
    }

    public function getContentVersion(): int
    {
        return $this->contentVersionNo;
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function getValue(): mixed
    {
        return $this->value;
    }

    public function setValue(mixed $value): void
    {
        $this->value = $value;
    }
}
