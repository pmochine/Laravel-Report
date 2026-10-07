# Changelog

This file lists the changes in each release of `pmochine/laravel-report`.
The project uses [Semantic Versioning](https://semver.org).

## 4.0.0 (2026-10-07)

Version 4.0.0 supports Laravel 11, 12 and 13.
It is a major release because it removes support for old Laravel and PHP versions.
It also renames one relation.
New in 4.0.0 are query scopes for open reports, a check for earlier reports, a trait for reporters and two events.

### Upgrade from 3.x

1. Update your application to PHP 8.2 and Laravel 11 or higher. Laravel 13 needs PHP 8.3.
2. Run `composer require pmochine/laravel-report:^4.0`.
3. You do not need to publish the migration again. Your tables stay as they are.
4. Replace calls to `$conclusion->conclusion()` with `$conclusion->report()`.
5. If you use a morph map, read the section "Morph maps" below.
6. If you want conclusions without `action_taken`, add the migration from the section "Optional action_taken" below.

#### Morph maps

Version 3.x stored the class name in `reporter_type` and `judge_type`. It ignored the morph map of the application.
The column `reportable_type` already used the morph map.
Version 4.0.0 stores the morph class, which is the alias from your morph map.

If you use `Relation::morphMap()`, Eloquent loads the old rows and the new rows.
But queries such as `whereMorphedTo()` compare with the alias, so they do not find the old rows.

If you use `Relation::enforceMorphMap()`, Laravel 13.34 and later refuse to load rows with a class name.
Older Laravel releases still load them.

Update the old rows once in a migration. Then queries find them, and every Laravel version loads them:

```php
DB::table('reports')->where('reporter_type', App\Models\User::class)->update(['reporter_type' => 'user']);
DB::table('reports_conclusions')->where('judge_type', App\Models\User::class)->update(['judge_type' => 'user']);
```

Use the class names and aliases of your own morph map.
If your application has no morph map, nothing changes.

#### Optional action_taken

The README of 3.x called `action_taken` optional, but the published 3.x migration made the column required.
New installations get a nullable column.
For an existing installation, add this migration:

```php
Schema::table('reports_conclusions', function (Blueprint $table) {
    $table->text('action_taken')->nullable()->change();
});
```

### Added

- `Report::reporter()` returns the model that sent the report. The idea comes from pull request #1 by Tamás Béres.
- `Conclusion::report()` returns the report of a conclusion.
- `Report::conclusions()` returns all conclusions of a report.
- The query scopes `Report::pending()` and `Report::concluded()` find reports without and with a conclusion.
- `isReportedBy()` in the trait `HasReports` checks whether a given model already reported the model.
- The trait `SubmitsReports` adds the relation `submittedReports()` to the model that sends reports.
- The events `ReportCreated` and `ReportConcluded`, for example to notify moderators about a new report.
- The README explains how to use the package with UUID or ULID keys.
- A test suite with Orchestra Testbench for Laravel 11, 12 and 13 and PHPUnit 10 to 13.
- A GitHub Actions workflow for PHP 8.2 to 8.5.

### Changed

- The package needs PHP 8.2 or higher and Laravel 11, 12 or 13.
- The package requires `illuminate/queue` for the events. Laravel applications already have it, because `laravel/framework` contains it.
- `report()` and `conclude()` store the reporter and the judge with `associate()`. The stored type is the morph class, and the stored ID is the primary key.
- The `$data` parameter of `report()` and `conclude()` has the type `array`.
- `Report::conclusion()` returns the latest conclusion of a report. In 3.x, a second `conclude()` added a row, and the database decided which conclusion the relation returned.
- `Report::allJudges()` returns each judge once and skips judges that no longer exist. It loads all judges with one query per judge type.
- The migration is an anonymous class. The published file gets the current date as prefix, so it runs before your later migrations.
- If the migration is already published, `vendor:publish` keeps the file and adds no second migration. This includes `create_reports_table.php` from 3.x.
- The migration uses `id()` and `foreignId()` and drops the tables in reverse order.
- The models define their casts in a `casts()` method.

### Fixed

- `report()` and `conclude()` failed for models with a primary key other than `id`.
- `report()` and `conclude()` ignored the morph map of the application.
- `Report::judge()` failed for a report without a conclusion. It now returns `null`.
- `Report::judge()` returned an old value directly after `conclude()`.
- The migration made `action_taken` required, so a conclusion without it failed.
- The migration used the `Schema` alias without an import.

### Removed

- Support for Laravel 5.4 to 8 and for PHP 7. Use version 3.x for these versions.
- `Conclusion::conclusion()`. Use `Conclusion::report()`.
- The Travis CI configuration.

## 3.2.0 (2020-11-08)

- Support for Laravel 8.

## 3.1.0 (2020-03-19)

- Support for Laravel 7.

## 3.0.0 (2019-09-21)

- Support for Laravel 6.

## 2.3.0 (2019-04-02)

- Support for Laravel 5.8.

Older releases are listed on the [GitHub releases page](https://github.com/pmochine/Laravel-Report/releases) and in the Git tags.
