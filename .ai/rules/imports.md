---
paths:
  - 'app/Imports/**'
---

# Imports

## Spreadsheet uploads explain their failures, they don't just count them

A user who sees "Fails: 150" and nothing else uploads the same file again. Every spreadsheet
import (`App\Imports\*` with `WithImport`) writes one `upload_records` row per line, with the
line's `values`, its `errors` and its `status`, so the reason is always there to show.
Reuse what reads it instead of building another table of rows:

- `Upload::failReasons()` groups the failed rows by error, most frequent first, with a count
  and the first row numbers it hit. The live progress bar (`UploadProgressResource`,
  `@/Components/Utils/ProgressBar.vue`) already sends it once the import finishes and lets
  the user expand it. Never call it per row while the import runs: `upload_records` is large
  and `upload_id` is not indexed.
- To give a page an upload history, add an icon-only tab placed just left of History, with
  `'icon' => 'fal fa-upload'` and `'icon_badge' => 'fal fa-clock'`, fill it with
  `IndexUploadReports::run(parent: $parent, model: class_basename(Model::class), prefix: $tab)`
  and render `@/Components/Upload/UploadReports.vue` for it. Each upload becomes a card
  showing who uploaded which file, when, the outcome and the grouped reasons, and its rows
  load on demand by status from `grp.helpers.uploads.records.index`. The Prospects page
  (`IndexProspects`, `ProspectsTabsEnum::UPLOADS`) is the reference.
- So the progress bar can link to that tab, add the page to
  `UploadProgressResource::reportRoute()`. Leave `show_route` alone: `ModalUpload` fetches
  failed rows from it.
- An import that skips blank lines must set `number_rows` to the lines it will really
  process, as `ProspectImport::collection()` does. The default, `getHighestRow()`, also
  counts empty rows that only carry formatting, so the bar stops short of its total and
  looks like the import died.
