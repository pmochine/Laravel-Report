# Laravel Report 📢

[![PHP from Packagist](https://img.shields.io/packagist/php-v/pmochine/laravel-report.svg?style=flat-square)]()
[![Latest Version](https://img.shields.io/github/release/pmochine/Laravel-Report.svg?style=flat-square)](https://github.com/pmochine/Laravel-Report/releases)

## Installation

Require this package, with [Composer](https://getcomposer.org/), in the root directory of your project.

``` bash
$ composer require pmochine/laravel-report
```

To get started, you'll need to publish the vendor assets and migrate:

```
php artisan vendor:publish --provider="Pmochine\Report\ReportServiceProvider" && php artisan migrate
```

## Usage

## Setup a Model
``` php
<?php

namespace App;

use Pmochine\Report\HasReports;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasReports;
}
```

## Examples

#### The User Model reports the Post Model
``` php
$post->report([
    'reason' => \Str::random(10),
    'meta' => ['some more optional data, can be notes or something'],
], $user);
```

#### Create a conclusion for a Report and add the User Model as "judge" (useful to later see who or what came to this conclusion)
``` php
$report->conclude([
    'conclusion' => 'Your report was valid. Thanks! We\'ve taken action and removed the entry.',
    'action_taken' => 'Record has been deleted.' // This is optional but can be useful to see what happend to the record
    'meta' => ['some more optional data, can be notes or something'],
], $user);
```

#### Get the conclusion for the Report Model
``` php
$report->conclusion;
```

#### Get the judge for the Report Model (only available if there is a conclusion)
``` php
$report->judge(); // Just a shortcut for $report->conclusion->judge
```

#### Get an array with all Judges that have ever "judged" something
``` php
Report::allJudges();
```

## Testing

``` bash
$ phpunit
```

## Security

If you discover any security related issues, please don't email me. I'm afraid 😱. avidofood@protonmail.com

## Credits

Now comes the best part! 😍
This package is based on

- [Brian Faust's Laravel-Reportable](https://github.com/faustbrian/Laravel-Reportable)
- [All Contributors](../../contributors)

## License

[MIT](LICENSE) © [Brian Faust](https://brianfaust.me)
