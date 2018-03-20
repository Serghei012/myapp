<?php
namespace AdoreMe\Logger\Models\Monolog\Handler\Slack;

use AdoreMe\Common\Helpers\ProviderHelper;
use Monolog\Logger;
use Monolog\Formatter\LineFormatter;
use Monolog\Formatter\FormatterInterface;

class SlackRecord
{
    /**
     * Slack channel (encoded ID or name)
     *
     * @var string|null
     */
    protected $channel;

    /**
     * Name of a bot
     *
     * @var string
     */
    protected $username;

    /**
     * Emoji icon name
     *
     * @var string
     */
    protected $iconEmoji;

    /**
     * Whether the message should be added to Slack as attachment (plain text otherwise)
     *
     * @var bool
     */
    protected $useAttachment;

    /**
     * Whether the the context/extra messages added to Slack as attachments are in a short style
     *
     * @var bool
     */
    protected $useShortAttachment;

    /**
     * Whether the attachment should include context and extra data
     *
     * @var bool
     */
    protected $includeContextAndExtra;

    /**
     * @var FormatterInterface
     */
    protected $formatter;

    /**
     * @var LineFormatter
     */
    protected $lineFormatter;

    /** @noinspection PhpMissingParentConstructorInspection */
    /**
     * SlackRecord constructor.
     *
     * @param null $channel
     * @param string $username
     * @param bool $useAttachment
     * @param null $iconEmoji
     * @param bool $useShortAttachment
     * @param bool $includeContextAndExtra
     * @param FormatterInterface|null $formatter
     */
    public function __construct(
        $channel = null,
        $username = 'Monolog',
        $useAttachment = true,
        $iconEmoji = null,
        $useShortAttachment = false,
        $includeContextAndExtra = false,
        FormatterInterface $formatter = null
    ) {
        $this->channel                = $channel;
        $this->username               = $username;
        $this->iconEmoji              = trim($iconEmoji, ':');
        $this->useAttachment          = $useAttachment;
        $this->useShortAttachment     = $useShortAttachment;
        $this->includeContextAndExtra = $includeContextAndExtra;
        $this->formatter              = $formatter;

        if ($this->includeContextAndExtra) {
            $this->lineFormatter = new LineFormatter();
        }
    }

    /**
     * @param array $record
     * @return array
     */
    public function getSlackData(array $record)
    {
        $dataArray = [
            'username' => $this->username,
            'text'     => '',
        ];

        if ($this->channel) {
            $dataArray['channel'] = $this->channel;
        }

        if ($this->formatter) {
            $message = $this->formatter->format($record);
        } else {
            $message = $record['message'];
        }

        if ($this->useAttachment) {
            if (! is_null($serverName = ProviderHelper::env('SERVER_NAME'))) {
                $authorName = $serverName . ProviderHelper::env('REQUEST_URI');
                if (! is_null($requestMethod = ProviderHelper::env('REQUEST_METHOD'))) {
                    $authorName = $requestMethod . ' @ ' . $authorName;
                }
            } else {
                global $argv;
                if (isset($argv)) {
                    $authorName = implode(' ', $argv);
                } else {
                    $authorName = $_SERVER['PHP_SELF'] ?? $_SERVER['SCRIPT_NAME'];
                }
            }

            $footer = [
                // Set the channel. Usually is the logger name.
                array_key_exists('channel', $record) ? 'logger: ' . $record['channel'] : '',
                // Set the "from" server message.
                'server: ' . php_uname('n') ?? 'unknown',
            ];

            $attachment = [
                'author_name' => $authorName,
                'footer'      => implode(' | ', $footer),
                'fallback'    => $message,
                'text'        => $message,
                'color'       => $this->getAttachmentColor($record['level']),
                'ts'          => time(),
                'fields'      => [],
            ];

            $attachment['title'] = $record['level_name'];

            if ($this->includeContextAndExtra) {
                foreach (['extra', 'context'] as $key) {
                    if (empty($record[$key])) {
                        continue;
                    }

                    if ($this->useShortAttachment) {
                        $attachment['fields'][] = $this->generateAttachmentField(
                            ucfirst($key),
                            $this->stringify($record[$key]),
                            true
                        );
                    } else {
                        // Add all extra fields as individual fields in attachment
                        $attachment['fields'] = array_merge(
                            $attachment['fields'],
                            $this->generateAttachmentFields($record[$key])
                        );
                    }
                }
            }

            $dataArray['attachments'] = [$attachment];
        } else {
            $dataArray['text'] = $message;
        }

        if ($this->iconEmoji) {
            $dataArray['icon_emoji'] = ":{$this->iconEmoji}:";
        }

        return $dataArray;
    }

    /**
     * Returned a Slack message attachment color associated with
     * provided level.
     *
     * @param  int $level
     * @return string
     */
    public function getAttachmentColor($level)
    {
        switch ($level) {
            case Logger::EMERGENCY:
                return '#ff0000';
                break;

            case Logger::ALERT:
                return '#ff0000';
                break;

            case Logger::CRITICAL:
                return '#ff0000';
                break;

            case Logger::ERROR:
                return '#ffbaba';
                break;

            case Logger::WARNING:
                return '#feefb3';
                break;

            case Logger::NOTICE:
                return '#bde5f8';
                break;

            case Logger::INFO:
                return '#dff2bf';
                break;

            case Logger::DEBUG:
                return '#dedede';
                break;

            default:
                return '#e3e4e6';
                break;
        }
    }

    /**
     * Stringifies an array of key/value pairs to be used in attachment fields
     *
     * @param  array $fields
     * @return string|null
     */
    public function stringify($fields)
    {
        if (! $this->lineFormatter) {
            return null;
        }

        $string = '';
        foreach ($fields as $var => $val) {
            $string .= $var . ': ' . $this->lineFormatter->stringify($val) . " | ";
        }

        $string = rtrim($string, " |");

        return $string;
    }

    /**
     * Sets the formatter
     *
     * @param FormatterInterface $formatter
     */
    public function setFormatter(FormatterInterface $formatter)
    {
        $this->formatter = $formatter;
    }

    /**
     * Generates attachment field
     *
     * @param string $title
     * @param string|array $value
     * @param bool $short
     * @return array
     */
    protected function generateAttachmentField($title, $value, $short)
    {
        return [
            'title' => $title,
            'value' => is_array($value) ? $this->lineFormatter->stringify($value) : $value,
            'short' => $short,
        ];
    }

    /**
     * Generates a collection of attachment fields from array
     *
     * @param array $data
     * @return array
     */
    protected function generateAttachmentFields(array $data)
    {
        $fields = [];
        foreach ($data as $key => $value) {
            $fields[] = $this->generateAttachmentField($key, $value, false);
        }

        return $fields;
    }
}
