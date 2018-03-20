<?php
namespace AdoreMe\Logger\Models\Monolog\Handler;

use AdoreMe\Logger\Jobs\SlackWebhook;
use AdoreMe\Logger\Models\Monolog\Handler\Slack\SlackRecord;

use Monolog\Formatter\FormatterInterface;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Container\Container;

class SlackWebhookHandler extends AbstractProcessingHandler
{
    /**
     * Slack Webhook token
     *
     * @var string
     */
    protected $webhookUrl;

    /**
     * Instance of the SlackRecord util class preparing data for Slack API.
     *
     * @var SlackRecord
     */
    protected $slackRecord;

    /**
     * Dispatcher queue name.
     *
     * @var string
     */
    protected $queue;

    /**
     * Dispatcher connection.
     *
     * @var mixed|null
     */
    protected $connection;

    /**
     * @param  string $webhookUrl Slack Webhook URL
     * @param  string|null $channel Slack channel (encoded ID or name)
     * @param  string $username Name of a bot
     * @param  bool $useAttachment Whether the message should be added to Slack as attachment (plain text otherwise)
     * @param  string|null $iconEmoji The emoji name to use (or null)
     * @param  bool $useShortAttachment Whether the the context/extra messages added to Slack as attachments are in a
     *     short style
     * @param  bool $includeContextAndExtra Whether the attachment should include context and extra data
     * @param int $level The minimum logging level at which this handler will be triggered
     * @param  bool $bubble Whether the messages that are handled can bubble up the stack or not
     * @param string $queue
     * @param mixed $connection
     */
    public function __construct(
        $webhookUrl,
        $channel = null,
        $username = 'Monolog',
        $useAttachment = true,
        $iconEmoji = null,
        $useShortAttachment = false,
        $includeContextAndExtra = false,
        $level = Logger::CRITICAL,
        $bubble = true,
        string $queue = 'slack_logger_queue',
        $connection = null
    ) {
        parent::__construct($level, $bubble);

        $this->webhookUrl = $webhookUrl;
        $this->queue      = $queue;
        $this->connection = $connection;

        $this->slackRecord = new SlackRecord(
            $channel,
            $username,
            $useAttachment,
            $iconEmoji,
            $useShortAttachment,
            $includeContextAndExtra,
            $this->formatter
        );
    }

    /**
     * @return SlackRecord
     */
    public function getSlackRecord()
    {
        return $this->slackRecord;
    }

    /**
     * {@inheritdoc}
     *
     * @param array $record
     */
    protected function write(array $record)
    {
        $postData = $this->slackRecord->getSlackData($record);

        // Create the job that sends the messages to slack.
        $job = new SlackWebhook(json_encode($postData), $this->webhookUrl);
        if ($this->connection != 'sync' && Container::getInstance()->bound(Dispatcher::class)) {
            if (! empty($this->connection) && $this->connection != 'default') {
                $job->onConnection($this->connection);
            }
            $job->onQueue($this->queue);
            Container::getInstance()->make(Dispatcher::class)->dispatch($job);

        // Send sync the message, if dispatched not instantiated, or the connection name is 'sync'.
        } else {
            $job->handle();
        }
    }

    /**
     * {@inheritdoc}
     *
     * @param FormatterInterface $formatter
     * @return $this
     */
    public function setFormatter(FormatterInterface $formatter)
    {
        parent::setFormatter($formatter);
        $this->slackRecord->setFormatter($formatter);

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @return FormatterInterface
     */
    public function getFormatter()
    {
        $formatter = parent::getFormatter();
        $this->slackRecord->setFormatter($formatter);

        return $formatter;
    }
}
