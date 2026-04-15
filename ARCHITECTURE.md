# hypeAttachments — Architecture (Elgg 4.x)

## Summary

hypeAttachments adds file attachment support to any Elgg entity type. Attachments are stored as `ElggFile` entities with subtype `file` and linked to the subject entity via a `attached` relationship. Access control is settings-driven: the admin enables attachments per `type:subtype` pair. Compatible with hypeDropzone for drag-and-drop upload (optional dep).

## Entity Model

No custom entity subtypes. Uses stock Elgg `file` entities (`ElggFile`, subtype `file`) as attachment objects. Linked to subject entities via `attached` relationship (`addRelationship()`/`removeRelationship()`).

## Directory Structure

```
hypeattachments/
├── actions/attachments/          Action files (attach, detach, upload)
├── classes/hypeJunction/
│   ├── AttachmentService.php     Singleton service: attach, detach, getAttachments, upload
│   └── Attachments/
│       ├── AddAttachmentsModule.php  Hook: adds sidebar module via 'modules','object'
│       ├── AddFormField.php          Hook: injects attachments field via 'fields','object'
│       ├── AttachmentsField.php      Field class extending hypeJunction\Fields\Field
│       ├── CMS.php                   Legacy field integration (unused in active flow)
│       ├── Events.php                Entity event handlers (create/update on object)
│       ├── Menus.php                 Hook handlers for entity/social menus
│       ├── Notifications.php         Runtime notification hook registration
│       └── Permissions.php           Hook handlers for allow_attachments and permissions_check
├── lib/functions.php             Global function wrappers around AttachmentService
├── views/default/
│   ├── css/input/attachments.css     Attachment input styling
│   ├── forms/attachments/upload.php  Upload form view
│   ├── input/attachments.php         Custom input type renderer
│   ├── object/elements/attachments/  full.php, summary.php — entity attachment displays
│   ├── object/format/attachment.php  Single attachment item format view
│   ├── output/attachments.php        Output view for attachments list
│   ├── plugins/hypeattachments/settings.php  Admin settings (per-subtype enable/disable)
│   ├── post/module/attachments.php   Post module view
│   ├── post/modules/attachments.php  Post modules listing view
│   └── resources/attachments/
│       ├── upload.php               Upload page resource
│       └── view.php                 View attachments page resource
```

## Registered Hooks (elgg-plugin.php)

| Hook | Type | Handler | Purpose |
|------|------|---------|---------|
| `register` | `menu:entity` | `Menus::setupEntityMenu` | Adds attach/detach menu items to entity menus |
| `register` | `menu:social` | `Menus::setupEntitySocialMenu` | Adds paperclip count badge to social menus |
| `allow_attachments` | `all` | `Permissions::allowsAttachments` | Checks plugin settings to allow/deny attachments |
| `permissions_check` | `object` | `Permissions::protectMessageAttachments` | Blocks edit/delete on attachments linked to >1 message |
| `fields` | `object` | `AddFormField` | Injects attachment field into entity forms |
| `modules` | `object` | `AddAttachmentsModule` | Injects attachments sidebar module |

## Registered Events (elgg-plugin.php)

| Event | Type | Handler | Purpose |
|-------|------|---------|---------|
| `create` | `object` | `Events::saveCommentAttachments` | Attaches uploaded files when comment/discussion_reply is created |
| `create` | `object` | `Events::saveMessageAttachments` | Attaches uploaded files when messages entity is created |
| `update` | `object` | `Events::saveCommentAttachments` | Attaches uploaded files when comment/discussion_reply is updated |
| `update` | `object` | `Events::saveMessageAttachments` | Attaches uploaded files when message is updated |
| `update` | `object` | `Events::syncAttachmentAccess` | Syncs attachment access_id with subject entity on update |

## Routes

| Route | Path | Resource view |
|-------|------|--------------|
| `attachments:upload` | `/attachments/upload/{guid}` | `resources/attachments/upload` |
| `attachments:view` | `/attachments/view/{guid}` | `resources/attachments/view` |

## Actions

| Action | Access | Purpose |
|--------|--------|---------|
| `attachments/attach` | (default) | Link existing entity as attachment |
| `attachments/detach` | (default) | Remove attachment relationship (optionally delete) |
| `attachments/upload` | (default) | Upload files and attach to entity |

## Dependencies

| Plugin | Required | Notes |
|--------|---------|-------|
| `file` | Yes | Core Elgg file plugin — provides ElggFile/subtype infrastructure |
| `hypeDropzone` | No (optional) | Drag-and-drop upload UI; detected at runtime |

## Plugin Settings

Stored as plugin settings keyed `object:<subtype>` (value `1`=enabled). Managed via admin settings page at `plugins/hypeattachments/settings`.

## Migration Notes (3.x → 4.x)

- Removed `manifest.xml` (replaced by `plugin` key in `elgg-plugin.php`)
- Updated `composer.json`: lowercase `installer-name`, added `elgg/elgg: ^4.0`, bumped `composer/installers` to `^2.0`, raised PHP minimum to `>=7.4`
- Renamed settings view directory from `plugins/hypeAttachments/` to `plugins/hypeattachments/` (lowercase plugin ID required in 4.x)
- Replaced `forward()` with `elgg_redirect_response()` in action and `throw new \Elgg\Exceptions\HttpException()` in resource view
- Fixed camelCase plugin ID in `elgg_get_plugin_setting()` call — Elgg 4.x lowercases all plugin IDs
- Removed unused `elgg_get_config('dbprefix')` call from settings view
- Entity event callbacks (`Events.php`) use `\Elgg\Event` type hint (correct for 4.x entity events)
- Hook callbacks use `\Elgg\Hook` type hint (unchanged from 3.x)
- `AttachmentService::saveUploadedFiles()` uses `_elgg_services()->hooks->trigger('upload', 'file')` and `elgg_trigger_after_event()` — both valid 4.x APIs
