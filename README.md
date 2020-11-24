Weekly Recurring Tasks
==============================

Automatically clones Tasks with the DAILY/WEEKLY/BIWEEKLY or MONDAY/TUESDAY/WEDNESDAY/THURSDAY/FRIDAY/SATURDAY/SUNDAY tag.

Author
------

- Sebastian Pape, Sebastien Diot
- License MIT

Requirements
------------

- Kanboard >= 1.2.13

Installation
------------

You have the choice between 3 methods:

1. Install the plugin from the Kanboard plugin manager in one click
2. Download the zip file and decompress everything under the directory `plugins/WeeklyRecurringTasks`
3. Clone this repository into the folder `plugins/WeeklyRecurringTasks`

Note: Plugin folder is case-sensitive.

Documentation
-------------

Tags (SHOULD) works as follow:

- DAILY: Clone a task, annotated with this tag and reaching due date, to the next day.
- WEEKLY: Clone a task, annotated with this tag and reaching due date, to the next week (in 7 days).
- BIWEEKLY: Clone a task, annotated with this tag and reaching due date, to the over next week (in 14 days).
- MONDAY/TUESDAY/WEDNESDAY/THURSDAY/FRIDAY/SATURDAY/SUNDAY: Clone a task, annotated with this tag and reaching due date, to the next week (in 7 days).
