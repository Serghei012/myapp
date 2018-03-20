# Change log

## `1.0.6`
- Forced monolog version `1.23.0`, because it implemented `RFC5424` for `SyslogUdpHandler`, and the logstash configuration now works only for this version only.
NEW
```json
input {
    udp {
        port => 5568
        type => "syslog_5424line"
        buffer_size => 65536
    }
}
filter {
    if [type] == "syslog_5424line" {
        grok {
              match => { "message" => "%{SYSLOG5424LINE}" }
        }
        json {
            source => "syslog5424_msg"
        }
        mutate {
            remove_field => [ "syslog5424_proc", "syslog5424_msgid", "syslog5424_pri", "syslog5424_sd", "syslog5424_app", "syslog5424_ver", "syslog5424_msg" ]
        }
    }
}
output {
	elasticsearch {
		hosts => "127.0.0.1:9200"
	}
}
```

OLD
```json
input {
    udp {
        port => 5567
        type => "nawe"
        buffer_size => 65536
    }
}
filter {
    if [type] == "nawe" {
        grok {
              match => { "message" => "<%{POSINT:syslog_pri}>1%{SPACE}%{GREEDYDATA:syslog_logstash}" }
        }
        json {
            source => "syslog_logstash"
        }
        mutate {
            remove_field => [ "syslog_logstash", "syslog_pri" ]
        }
    }
}
output {
	elasticsearch {
		hosts => "127.0.0.1:9200"
	}
}
```

## `1.0.5`
- Upgraded sentry library to accept 0.7.* along with 0.6.*.

## `1.0.4`
- Added a new context to `LoggerTrait`: `exception`, which contains the instance of exception sent as message.
- To log an exception, either use $this->error($exception) when using `LoggerTrait`, or use `LogHelper::logException($logger, $level, $exception, $context)` when not using the `LoggerTrait`.
- The new `LogException` handler will allow Sentry to display a nice stack trace, and log stash can have stack trace.
- IMPORTANT: Do not use $logger->error($exception), because the exception will not be formatted.

## `1.0.3`
- Added context to slack logger, to send the logger name. ([LILG-4](https://jira.adoreme.com/browse/LILG-4)).

## `1.0.2`
- Fixed missing `$bubble` parameter from slack logger, that messed up queue name.

## `1.0.1`
- Updated composer dependency to allow `adore-me\common` version `^2.0.0`.

## `1.0.0`
- Backwards incompatible with `0.0.2`.
- Made compatible with `Laravel 5.4`.
- Updated to use version `^1.0` of `adore-me/common` library.
- Updated `sentry/sentry-laravel` library from `^0.5.0` to `^0.6.1`.
- Updated dependency in `composer.json` to `"laravel/framework": "5.4.*"`, instead of part of laravel packages. This is because this library is designed for laravel only.
- Renamed `AdoreMe\Logger\Models\Monolog\Handler\AsyncSlackWebhookHandler` to `SlackWebhookHandler`, because the naming was no longer correct, once with expanding configuration for slack handler to support defined queue driver. Based on configuration it can now run `sync` or whatever driver you choose to use.
- Added additional configuration possibilities in `.env` file for slack log handler, to be able to set the queue driver and queue name. Configuration documentation ca be found in [README.md](/README.md).

## `0.0.2`
- Backwards incompatible with `0.0.1`.
- Updated composer to let it know that the release is compatible only with `Laravel 5.2` and `Larave 5.3`.
- Updated to use version `0.0.2` of `adore-me/common` library.
- Update functions from `AdoreMe\Logger\Traits\LoggerNonPersistentModel` so it no longer returns self, but void. This was done to avoid issues with incompatible objects received via `return $this;`, compared to `self` return type hinting.

## `0.0.1`
- Initial commit
- Added models, helpers, interfaces, exceptions and traits that were common between Adore Me's laravel applications.
  - Helpers:
    - `AdoreMe\Logger\Helpers\HttpHelper`
      - Extends `AdoreMe\Common\Helpers\HttpHelper` to add functionality to handle log messages set via trait `AdoreMe\Logger\Traits\LoggerNonPersistentModel`.
    - `AdoreMe\Logger\Helpers\LogHelper`
      - Handles the configuration for `monolog`, its handlers and processors, for: file, sentry, logstash via udp, syslog file, slack.
  - Http:
    - `AdoreMe\Logger\Http\Controllers\AbstractResourceController`
  - Interfaces:
    - `AdoreMe\Logger\Interfaces\LoggerInterface`
      - Designed to be used in combination with trait `AdoreMe\Logger\Traits\LoggerTrait`.
  - Jobs:
    - `AdoreMe\Logger\Jobs\SlackWebhook`
      - Used by `monolog` slack handler, to send messages to slack, via the default laravel's queue driver.
  - Models:
    - `AdoreMe\Logger\Models\DefaultLogger`
      - Used by `AdoreMe\Logger\Providers\DefaultLogger` to init the `monolog` instance for default logger.
    - `AdoreMe\Logger\Models\Entry`
      - Used by `AdoreMe\Logger\Traits\LoggerNonPersistentModel` to store information inside the model.
    - `AdoreMe\Logger\Models\Log`
      - Used by `AdoreMe\Logger\Traits\LoggerNonPersistentModel` to store information inside the model.
    - `AdoreMe\Logger\Models\Monolog\Handler\AsyncSlackWebhookHandler`
      - Used by `monolog` when configured to use the slack handler.
    - `AdoreMe\Logger\Models\Monolog\Handler\Slack\SlackRecord`
      - Used by `monolog` when configured to use the slack handler.
  - Providers:
    - `AdoreMe\Logger\Providers\DefaultLogger`
      - Default logger for trait `AdoreMe\Logger\Traits\LoggerTrait`.
    - `AdoreMe\Logger\Providers\LoggerProviderAbstract`
      - Abstract logger providers, that should be used whe you need to create a new logger provider. Simply extend the new logger with this abstract class.
  - Traits:
    - `AdoreMe\Logger\Traits\LoggerNonPersistentModel`
      - Designed for models, to store log type messages inside it.
    - `AdoreMe\Logger\Traits\LoggerTrait`
      - Designed to be used in combination with interface `AdoreMe\Logger\Interfaces\LoggerInterface`.
      - Used to send log messages from model, for example: `$this->error('Test');`.
      - The log messages are sent to a `monolog` instance, previously configured, which will dump the messages to configured handlers: sentry, file, logstash, slack, etc.
- For mode info about models, just read them :)