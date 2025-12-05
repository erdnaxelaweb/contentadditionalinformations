# Content Additional Informations Bundle

Add a service to manage per‑content, per‑version additional informations in the database for Ibexa DXP (Symfony).

This repository provides:
- A Symfony bundle exposing a `ContentAdditionalInformationsService` to get/set/list/delete additional information entries for an Ibexa Content.
- A Doctrine DBAL gateway and persistence handler, with a cache layer built using Ibexa persistence cache.
- An event subscriber that keeps additional informations in sync when content is drafted, copied, or deleted.

## Requirements
- PHP 8.1+
- An Ibexa DXP installation (v4.x)

## Installation
1) Require the package in your Ibexa/Symfony project:
```
composer require erdnaxelaweb/contentadditionalinformations
```

2) Register the bundle (if not using Flex auto‑registration):
```
// config/bundles.php
return [
    // ...
    ErdnaxelaWeb\ContentAdditionalInformationsBundle\ContentAdditionalInformationsBundle::class => ['all' => true],
];
```

3) Configure Ibexa ORM mapping is auto‑prepended by the bundle extension, pointing to the `lib` namespace with Doctrine annotations. No manual config should be necessary.

### Database schema
The table creation can be handled using the doctrine schema update command:
```
bin/console d:s:u --dump-sql --em=ibexa_default
```

## Usage
Inject and use the service `ErdnaxelaWeb\ContentAdditionalInformations\Service\ContentAdditionalInformationsService` in your application code.

Public API (from `lib/Service/ContentAdditionalInformationsService.php`):
- `get(int $contentId, int $contentVersionNo, string $identifier): ContentAdditionalInformation`
- `getAll(int $contentId, int $contentVersionNo): ContentAdditionalInformation[]`
- `set(int $contentId, int $contentVersionNo, string $identifier, mixed $value): void`
- `delete(int $contentId, int $contentVersionNo, ?string $identifier = null): void` (deletes one or all identifiers for that content/version)
- `purge(int[] $contentIds): void` (bulk delete across all versions)

Notes:
- Values are stored as JSON (encoded/decoded automatically by the persistence handler).
- A cache layer wraps the persistence handler using Ibexa’s `AbstractInMemoryHandler`.

### Event subscriber behavior
`ErdnaxelaWeb\ContentAdditionalInformations\Event\Subscriber\ContentEventSubscriber` subscribes to Ibexa repository events to keep entries consistent:
- On `CreateContentDraftEvent`: copies existing entries from the current version to the new draft version.
- On `CopyContentEvent`: copies entries from source content current version to the newly created content.
- On `DeleteVersionEvent`: deletes entries for the deleted version.
- On `DeleteTrashItemEvent` and `EmptyTrashEvent`: purges entries for affected content IDs (in chunks of 200 for EmptyTrash).
- 
## License
MIT — see [`LICENSE`](./LICENSE).
