# CLAUDE.md — zabbix-module-hostgroups-tree

Frontend module for Zabbix that adds a host group tree panel to **Monitoring › Hosts**, **Monitoring › Problems**
and **Data collection › Hosts**. User-facing documentation: `README.md`. This file is the developer handoff.

The maintainer writes in Italian: reply in Italian. Code, comments, commit messages and README are in English.

## Branches

| Branch | Zabbix | Differences from `main` |
|---|---|---|
| `main` | 8.0 | — |
| `7.0` | 7.0 | Only: `manifest.json` version suffix `-zbx7.0` and description prefixed with `Zabbix 7.0. `; README title `(Zabbix 7.0)`, no "8.0 table customizations" mention, extra `## Version 7.0` section before `## Installation`. |

**The code is identical on both branches.** Every change is made on `main` and then applied to `7.0` (cherry-pick
and fix the manifest/README conflicts by hand). Version differences are handled at runtime, never by branching code.
Bump `manifest.json` `version` on both branches for every release (`X.Y.Z` / `X.Y.Z-zbx7.0`).

## Layout

```
manifest.json                  id hostgrouptree, namespace HostGroupTree, action hostgrouptree.data (layout.json), assets
config.json                    module configuration (see below)
Module.php                     empty CModule
actions/HostGroupTreeData.php  JSON endpoint: tree, counters, access flags, form modes, config
assets/js/hostgrouptree.js     everything client-side (one IIFE)
assets/css/hostgrouptree.css
```

Install folder name on the server must be `modules/hostgrouptree`. Package:
`git archive --prefix=hostgrouptree/ -o ../hostgrouptree-<version>.zip <branch>`.

## Architecture (keep these decisions)

- **Native pages are never replaced or copied.** The JS wraps the children of `<main>` (except the title header) in
  `.hgt-layout` = `aside.hgt-panel` + `.hgt-content`, and drives the native filter. Native list, paging, sorting,
  refresh and 8.0 table customizations keep working.
- Assets load on every page: the IIFE exits unless `zabbix.php?action=` is one of
  `host.view` (MODE `hosts`), `problem.view` (`problems`), `host.list` (`config`), plus early branches for
  `hostgroup.list`, `hostgroup.edit`, `maintenance.list`, `maintenance.edit` (form automation via `sessionStorage`).
  Nothing in kiosk mode.
- **Applying a group**: tab filter pages → set the active tab's `.multiselect[id^="groupids_"]` (jQuery
  `multiSelect('clean'|'addData')`) and click `.tabfilter-container button[name="filter_apply"]`; if nothing reacts in
  3 s, reload with the URL where only `groupids[]` is replaced. Data collection (classic filter) → resubmit the
  `form[name="zbx_filter"]` fields with `filter_groups[]` replaced and `filter_set=1`.
- Highlight follows the URL (`history.pushState/replaceState` are wrapped) → `computeSelectedPaths()`; `last_click`
  (localStorage) decides between a virtual parent and its only child, which produce the same filter.
- **Group tree**: nesting comes only from `/` in group names. Prefixes that are not groups (or not readable) are
  *virtual* nodes; a node's `cover` = its groupid, or the top-most real descendants for virtual nodes (the native
  filters include subgroups). Synthetic root `All` (`ROOT_PATH`) = empty filter.
- **Endpoint** (`hostgrouptree.data`, GET, CSRF disabled, `public function init()` — must be public for 7.0):
  counts are distinct hosts/events per subtree. Extra inputs: `problem_filter` (Problems page query → all filter
  fields applied through `CScreenProblem::getData()`, fallback `problem.get`), `mode=config` + `host_filter`
  (Data collection filter → host.get options; unavailable interfaces with the *Host availability* widget logic).
  Returns `sev_counts` and `host_masks` (bitmask of severities per host) so the JS applies the page Severity filter
  without another request.
- **Expand/collapse all** is one header button (`updateFoldButton()`: collapse while any non-root group is open,
  otherwise expand); Alt+click on an arrow and `*` act on a whole branch (`toggleSubtree()`).
- **Form mode detection**: `getFormMode(action, popup_since)` asks `CRouter` whether `hostgroup.edit` /
  `maintenance.edit` use `layout.json` (popup) or a page (7.0 host groups are a page).
- **Look**: `syncNativeStyle()` copies radius/border/shadow/background/header/row border from the native table into
  CSS variables (row border = most frequent visible `td` bottom border: Problems starts with timeline rows).
  Severity colors come from theme classes `na-bg … disaster-bg`, red text from `.red`, availability from
  `.status-red`; local fallbacks use `:where()` (zero specificity) so theme/custom colors always win.

## config.json

Keys: `pages` (object of booleans `monitoring_hosts`, `monitoring_problems`, `data_collection_hosts`),
`show_column_headers`, `show_expand_collapse_buttons`, `open_forms_in_place`, `refresh_interval`,
`maintenance_period_minutes`, `problem_limit`. The file is JSON **with comments** (`stripJsonComments()` before
`json_decode`); every key has a comment in the shipped file. Read by `readConfig()` (merged over `CONFIG_DEFAULTS`,
wrong types ignored, objects merged key by key) and sent to the JS (`applyModuleConfig()`). Adding a key = PHP
`CONFIG_DEFAULTS` + JS `CFG` and `applyModuleConfig()` + `config.json` (with its comment) + README table.

`pages`: the panel is built before the config arrives, so a disabled page is detected at the first data response
(`removeLayout()` puts the native children back) and cached in localStorage `hostgrouptree.disabled_pages`. Cached
disabled pages build nothing and only call the endpoint with `config_only=1`; if enabled again, `boot()` runs.

**Never let a test write the real `config.json`** (1.14.0 shipped a test file by mistake): tests work on a copy
(`readConfig()` accepts a file path for that).

## Pitfalls already paid for

1. **Native popups and dialog ids.** Native form scripts find their dialog with `overlays_stack.getById(<id>)`;
   the id differs between versions and is not known for 8.0. Opening `PopUp()` with an invented id breaks the form
   (`this.overlay is undefined`, disabled Add button).
   - Host group form: opened in place by `openFormInPlace()`; a hook on `overlays_stack.getById` answers an unknown
     `hostgroup…` id with the dialog just opened and stores the id in localStorage `hostgrouptree.dialogueids`.
     The hook must **only return an overlay still in the DOM** (1.15.0 returned the closed one → second open did
     nothing). `open_forms_in_place: false` → fallback through the native list page.
   - Maintenance: opened by clicking the native *Create maintenance period* button on `maintenance.list`, then filled
     (name, groups, period via the native period dialog). Could move to `openFormInPlace()` once verified on 8.0.
2. **Do not inject anything into the tab filter row**: positions there differ in 8.0 and every attempt overlapped
   the filter tabs. Hidden panel = 14 px vertical strip, whole strip clickable.
3. Hidden strip: add `hgt-strip` before the first `applyPanelGeometry()` and never set `inert` on the panel
   (reload-while-hidden made the strip unclickable).
4. CSS `display: flex` overrides the `hidden` attribute: add `[hidden] { display: none }` for such elements.
5. Panel top aligns with the filter box: tab filter → bottom of the tab label; classic filter → top of the first
   opaque wide block (`filterBoxTop()`).
6. Height: sticky panel, `height = innerHeight − top − gap` with an equal negative bottom margin, so it never adds a
   scrollbar.
7. Keep `hostgrouptree.js` **ASCII-only** (non-ASCII as `\uXXXX`); module assets may be served without charset.
8. Group names are user data: always `textContent`, never `innerHTML` (only static SVG from `ICONS` uses innerHTML).

## Assumptions not yet verified on a real 8.0

Menu links `hostinventories.php?filter_groups[]`, `host.list&filter_groups[]`, `maintenance.list&filter_groups[]`;
`filter_monitored_by` values (assumed equal to host `monitored_by`, `ZBX_MONITORED_BY_ANY` ignored);
`CScreenProblem::getData($filter, $limit)` signature; maintenance form selectors (period *Add* button, period dialog
fields `timeperiod_type`, `period_days|hours|minutes`). Warnings are logged as `[hostgrouptree] …` in the console.

## Testing

No real Zabbix is reachable from the cloud. Checks used so far:

- `php -l actions/HostGroupTreeData.php`, `node --check assets/js/hostgrouptree.js`, `json` parse of `config.json`.
- PHP units with stub classes (`CController`, `API`, … as empty classes) calling private static methods through
  `ReflectionMethod` (`problemFilter`, `getHostFilterOptions`, `readConfig` on a temp copy).
- Headless Chromium (Playwright) with mock pages that imitate the Zabbix DOM: `main > header.header-title`,
  `.tabfilter-container` with `li.tabfilter-item-label.selected[data-target]`, a fake jQuery exposing
  `multiSelect`, fake `PopUp` / `overlays_stack` reproducing the native behaviors (reuse of an open dialog by id,
  `getById` lookups at init/cancel/submit), and a route that serves `hostgrouptree.data` JSON. Reproduce a reported
  bug in the mock first, then fix.

The maintainer tests on Zabbix 8.0 (LAN, `http://192.168.64.9/zabbix/`) and 7.0 and reports with screenshots and
console output.

## Ideas not done yet

- Create maintenance in place (same mechanism as the host group form).
- Optionally make the host count follow the page Severity filter (counts are totals today).
