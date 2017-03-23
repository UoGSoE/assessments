# Student Assessment Feedback System

This is a web based system to allow the recording of student assessment feedback.

## User Roles

### Students

Students can see a list of all of their assessments, view their details and report
late feedback if it was late.

### Staff

Staff can see a list of all assessments, but only view their own.  Staff also have
to log in and indicate when they gave the feedback.

### Admins

Admins can see all assessments.  They can also see various reports and stats.  They can
also import a spreadsheet with the details of assessments, export the current assessment
data as a spreadsheet and clear all assessment data from the system.

## Tech stuff

The application is written ontop of the [laravel](https://laravel.com/) PHP framework.

### Installing

If you clone this repository then do :

* `cp .env.example .env`
* Edit the `.env` file to suit your setup (database name etc)
* `composer install`
* `php artisan key:generate`
* `php artisan migrate`

That should get you going.

### Tests/spec

There are a bunch of tests under the `./test/` directory which is the closest thing there
is to a spec for the project.  You can run them using `vendor/bin/phpunit`.  Some of the tests
are ignored by default as they talk to a local HTTP API - you can run those by `vendor/bin/phpunit --group=integration`.


