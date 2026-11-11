# Permanent offer deletion

The God-role offer management screen uses a CSRF-protected DELETE request with an irreversible-action confirmation. The “Previously hidden offers” link exposes status-2 offers left by the old Delete action, so they can also be permanently deleted individually. Nothing is automatically purged.

`App\Services\DeleteOffer` deletes the offer, its `rep_has_offer` assignments (including offer postback settings), bonus settings, offer/user caps, targeting rules, geo/device rules and their lists in one transaction. Child offers are detached and retained. Restrictions on other offers remain: cap fallback IDs become null and capped traffic receives HTTP 410; redirect rules and saved geo rules switch to deny until an operator chooses a replacement.

Clicks, conversions, deductions, adjustments, sale logs, payout/aggregate history are retained. `deleted_offers` stores only the original ID, a `Deleted offer #ID` label, the campaign ID, and deletion time. Reports use the read-only `report_offers` view to include those identities; report drill-downs continue to work while edit/assignment screens use live offers only. Offer IDs cannot be reused. Late conversion requests for deleted offers return HTTP 410, since their offer configuration and assignment payout no longer exist. Existing deductions can still use the original click's offer ID.

## Deployment

Apply `2026_11_11_000000_support_permanent_offer_deletion.php` to each tenant database before serving this code. Use the project's tenant migration commands (`php artisan migrate:single <company>` or the existing all-tenant deployment workflow). The migration account needs CREATE VIEW and TRIGGER privileges as well as schema alteration permissions. The migration removes the clicks-to-live-offer foreign key intentionally: historical clicks continue to reference their original IDs. Operational foreign keys remain enforced, and the deletion service performs ordered cleanup without disabling them.

The migration can roll back before any permanent deletion. Once a deletion has occurred, rollback stops explicitly rather than deleting financial history or recreating deleted offers. Do not drop the history table/view when rolling application code back.

## Verification

`tests/OfferDeletionTest.php` uses a disposable local MySQL server, never application connection settings. Each test creates and drops a uniquely named fixture database. Set `CTP_TEST_MYSQL_PORT` to the server's local port (root with an empty password), then run:

```sh
CTP_TEST_MYSQL_PORT=33387 php vendor/bin/phpunit --bootstrap vendor/autoload.php tests
```

Coverage includes foreign-key cleanup, unchanged historical rows and report totals for every role and advertiser, historical route binding, removal of dead edit links, rollback on failure, reserved IDs, migration rollback, authorization, CSRF, and late postbacks. Without the explicit test port, MySQL tests are skipped.
