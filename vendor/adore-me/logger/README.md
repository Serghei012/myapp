# Library :: AdoreMe\Logger
If you find bugs, or have ideas to implement, please contact Core Engineering Team.
This library was designed to be used across all nawe projects, especially for new microservices.

#### This library was designed to be used with `Laravel` and `adore-me/common` library.

## Change log
See [CHANGELOG.md](/CHANGELOG.md).

## What it does?
Read [CHANGELOG.md](/CHANGELOG.md) to learn what this library can provide.
For extra info, dig deep into the code.

## Installation
Edit composer.json and add the following lines:
```json
"repositories": [
    {
        "type": "vcs",
        "url": "git@github.com:adore-me/logger.git",
        "no-api": true
    }
]
```
Run ```composer require adore-me/logger```, to install the latest version.

## Laravel configuration
Edit ```config/app.php```
```php
'providers' => [
    ...
    Sentry\SentryLaravel\SentryLaravelServiceProvider::class,
    AdoreMe\Logger\Providers\DefaultLogger::class,
    ...
],
...
'aliases' => [
    ...
    'Sentry' => Sentry\SentryLaravel\SentryFacade::class,
    ...
]
```

Edit ```.env``` file
```
LOG_HANDLER_FILE_ENABLED=false

LOG_HANDLER_SENTRY_ENABLED=true
SENTRY_DSN=sentry_dsn

LOG_HANDLER_SYSLOG_UDP_ENABLED=true
LOG_HANDLER_SYSLOG_UDP_HOST=127.0.0.1
LOG_HANDLER_SYSLOG_UDP_PORT=5567

LOG_HANDLER_SYSLOG_ENABLED=false

LOG_HANDLER_SLACK_ENABLED=true
LOG_HANDLER_SLACK_QUEUE_DRIVER=default
LOG_HANDLER_SLACK_QUEUE_NAME=slack_logger_queue
LOG_HANDLER_SLACK_WEBHOOK_URL=url
LOG_HANDLER_SLACK_CHANNEL=channel
LOG_HANDLER_SLACK_USERNAME=username
LOG_HANDLER_SLACK_ICON_EMOJI=emoji_icon

```
For `LOG_HANDLER_SLACK_QUEUE_DRIVER`:
- You can omit from `.env` file, or set value `null` or `default` or leave it empty, if you want to use `laravel` default queue driver set via `QUEUE_DRIVER`.
- Use `sync` if you want the message to be sent synchronously.

#### Do not forget to set a job to consume the queue for slack log handler, if you choose other driver than `sync`.

## Make use of `Exceptions/Handler.php` class, method `public function report(Exception $e)`. There you can define what to do with uncaught exceptions.
```php
`Laravel 5.2`

/**
 * Report or log an exception.
 *
 * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
 *
 * @param  \Exception  $e
 * @return void
 */
public function report(Exception $e)
{
    if ($this->shouldReport($e)) {
        LogHelper::logException($this->log, 'error', $e);
    }
}

...

`Laravel 5.4`
/**
     * Report or log an exception.
     *
     * @param  \Exception  $e
     * @return void
     *
     * @throws \Exception
     */
    public function report(Exception $e)
    {
        if ($this->shouldntReport($e)) {
            return;
        }

        try {
            $logger = $this->container->make(LoggerInterface::class);
            LogHelper::logException($logger, 'error', $e);
        } catch (Exception $ex) {
            throw $e; // throw the original exception
        }
    }
```

# Logstash via udp configuration example
```
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

# Logstash from syslog configuration example
```
input {
    file {
        type => "syslog"
        path => [ "/var/log/syslog" ]
    }
}
filter {
    if [type] == "syslog" {
        grok {
            match => { "message" => "%{SYSLOGTIMESTAMP:syslog_timestamp} %{SYSLOGHOST:syslog_hostname} %{DATA:syslog_program}(?:\[%{POSINT:syslog_pid}\])?: %{GREEDYDATA:syslog_message}" }
        }
        syslog_pri { }
        date {
            match => [ "syslog_timestamp", "MMM  d HH:mm:ss", "MMM dd HH:mm:ss" ]
        }
        if !("_grokparsefailure" in [tags]) {
            mutate {
                replace => [ "@host", "%{syslog_hostname}" ]
                replace => [ "@message", "%{syslog_message}" ]
            }
        }
        if [syslog_program] == "nawe_syslog_json" { #this if, is new, comparing to configuration from green.adoreme.com
            json {
                source => "syslog_message"
            }
        }
        mutate {
            remove_field => [ "syslog_hostname", "syslog_message", "syslog_timestamp" ]
            replace => [ "@time", "%{syslog_timestamp}" ]
        }
    }
}
```

## For more info please see [LogHelper.php](/src/Helpers/LogHelper.php).
