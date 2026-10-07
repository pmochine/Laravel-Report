# Laravel Report

[![Tests](https://github.com/pmochine/Laravel-Report/actions/workflows/tests.yml/badge.svg)](https://github.com/pmochine/Laravel-Report/actions/workflows/tests.yml)
[![Latest Version](https://img.shields.io/packagist/v/pmochine/laravel-report.svg?style=flat-square)](https://packagist.org/packages/pmochine/laravel-report)
[![PHP from Packagist](https://img.shields.io/packagist/php-v/pmochine/laravel-report.svg?style=flat-square)](https://packagist.org/packages/pmochine/laravel-report)

Laravel Report adds reports to Eloquent models.
Your users can report a model, for example a post or a comment.
A report stores the reported model, the model that sent the report, a reason and optional meta data.
You can then add a conclusion to the report and record which model made the decision.

## Requirements

| Package version | Laravel      | PHP                                  | Status          |
|-----------------|--------------|--------------------------------------|-----------------|
| 4.x             | 11, 12, 13   | 8.2 or higher, 8.3 or higher for 13  | Maintained      |
| 3.x             | 5.4 to 8     | 7.2 or higher                        | Not maintained  |

Laravel 11 gets no more security fixes since March 12, 2026.
Version 4.x still supports it, so that you can update this package before you update Laravel.

## Installation

Install the package with [Composer](https://getcomposer.org/):

```bash
composer require pmochine/laravel-report
```

Laravel finds the service provider automatically.
Publish the migration and run it:

```bash
php artisan vendor:publish --provider="Pmochine\Report\ReportServiceProvider"
php artisan migrate
```

The migration creates the tables `reports` and `reports_conclusions`.

If the migration is already published, `vendor:publish` keeps your file and adds no second migration.
This also applies to the file `create_reports_table.php` from version 3.x.
If you update from 3.x, read [CHANGELOG.md](CHANGELOG.md) for the upgrade steps.

## Usage

### Make a model reportable

Add the `HasReports` trait to each model that users can report:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Pmochine\Report\Traits\HasReports;

class Post extends Model
{
    use HasReports;
}
```

### Report a model

The second argument is the model that sends the report, usually the user:

```php
$report = $post->report([
    'reason' => 'Spam',
    'meta' => ['comment' => 'Optional data, for example notes'],
], $user);
```

### Read reports

```php
$post->reports;           // All reports for the post
$report->reportable;      // The reported model
$report->reporter;        // The model that sent the report
```

### Conclude a report

The second argument is the judge, the model that made the decision.
The keys `action_taken` and `meta` are optional:

```php
$report->conclude([
    'conclusion' => 'Your report was valid. We removed the post.',
    'action_taken' => 'Record deleted.',
    'meta' => ['comment' => 'Optional data, for example notes'],
], $user);
```

If you conclude a report again, the package adds a second conclusion and keeps the first one.
`$report->conclusion` returns the latest conclusion, and `$report->conclusions` returns all of them.

### Read conclusions and judges

```php
$report->conclusion;          // The latest conclusion, or null
$report->judge();             // Shortcut for $report->conclusion->judge, null without a conclusion
$conclusion->report;          // The report of a conclusion
Report::allJudges();          // Each model that concluded a report, once
```

### Find open reports

Use the query scopes `pending()` and `concluded()` to build a moderation queue:

```php
Report::pending()->get();                  // Reports without a conclusion
Report::concluded()->get();                // Reports with at least one conclusion
$post->reports()->pending()->count();      // Open reports for one post
$report->conclusions;                      // All conclusions of the report
```

The class `Report` is `Pmochine\Report\Models\Report`.

### Morph maps

The package stores the morph class of each model.
If your application uses a morph map, the tables contain your aliases instead of class names.

## Testing

```bash
composer test
```

## Security

If you find a security issue, send an email to avidofood@protonmail.com.
Do not open a public issue for it.

## Credits

This package is based on [Brian Faust's Laravel-Reportable](https://github.com/faustbrian/Laravel-Reportable).

- [Brian Faust](https://github.com/faustbrian)
- [Philipp Mochine](https://github.com/pmochine)
- [All contributors](../../contributors)

## License

[MIT](LICENSE) © [Brian Faust](https://brianfaust.me)
