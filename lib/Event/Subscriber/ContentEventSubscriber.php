<?php

declare(strict_types=1);

/*
 * Content Additional Informations Bundle.
 *
 * @author    Florian ALEXANDRE
 * @copyright 2023-present Florian ALEXANDRE
 * @license   https://github.com/erdnaxelaweb/contentadditionalinformations/blob/main/LICENSE
 */

namespace ErdnaxelaWeb\ContentAdditionalInformations\Event\Subscriber;

use ErdnaxelaWeb\ContentAdditionalInformations\Service\ContentAdditionalInformationsService;
use Ibexa\Contracts\Core\Repository\Events\Content\CopyContentEvent;
use Ibexa\Contracts\Core\Repository\Events\Content\CreateContentDraftEvent;
use Ibexa\Contracts\Core\Repository\Events\Content\DeleteVersionEvent;
use Ibexa\Contracts\Core\Repository\Events\Trash\DeleteTrashItemEvent;
use Ibexa\Contracts\Core\Repository\Events\Trash\EmptyTrashEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ContentEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        protected ContentAdditionalInformationsService $contentAdditionalInformationsService
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CreateContentDraftEvent::class => ['onCreateContentDraft', -10],
            DeleteVersionEvent::class => ['onDeleteVersion', -10],
            CopyContentEvent::class => ['onCopyContent', -10],
            DeleteTrashItemEvent::class => ['onDeleteTrashItem', -10],
            EmptyTrashEvent::class => ['onEmptyTrash', -10],
        ];
    }

    public function onCreateContentDraft(CreateContentDraftEvent $event): void
    {
        $existingInfos = $this->contentAdditionalInformationsService->getAll(
            $event->getContentInfo()->id,
            $event->getContentInfo()->currentVersionNo
        );

        foreach ($existingInfos as $info) {
            $this->contentAdditionalInformationsService->set(
                $event->getContentInfo()->id,
                $event->getContentDraft()->versionInfo->versionNo,
                $info->getIdentifier(),
                $info->getValue()
            );
        }
    }

    public function onDeleteVersion(DeleteVersionEvent $event): void
    {
        $versionInfo = $event->getVersionInfo();
        $this->contentAdditionalInformationsService->delete(
            $versionInfo->getContentInfo()->id,
            $versionInfo->versionNo
        );
    }

    public function onCopyContent(CopyContentEvent $event): void
    {
        $existingInfos = $this->contentAdditionalInformationsService->getAll(
            $event->getContentInfo()->id,
            $event->getContentInfo()->currentVersionNo
        );

        foreach ($existingInfos as $info) {
            $this->contentAdditionalInformationsService->set(
                $event->getContent()->id,
                $event->getContent()->versionInfo->versionNo,
                $info->getIdentifier(),
                $info->getValue()
            );
        }
    }

    public function onDeleteTrashItem(DeleteTrashItemEvent $event): void
    {
        $this->contentAdditionalInformationsService->purge(
            [$event->getResult()->contentId],
        );
    }

    public function onEmptyTrash(EmptyTrashEvent $event): void
    {
        /** @var int[] $contentIds */
        $contentIds = [];
        /** @var \Ibexa\Contracts\Core\Repository\Values\Content\Trash\TrashItemDeleteResult $item */
        foreach ($event->getResultList() as $item) {
            $contentIds[] = $item->contentId;
        }

        $chunks = array_chunk($contentIds, 200);
        foreach ($chunks as $chunk) {
            $this->contentAdditionalInformationsService->purge($chunk);
        }
    }
}
