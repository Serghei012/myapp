<?php
namespace AdoreMe\Logger\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Monolog\Handler\Curl\Util;

class SlackWebhook implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels, Queueable;

    /** @var string */
    protected $payload;
    /** @var string */
    protected $webhookUrl;

    /**
     * SlackWebhook constructor.
     *
     * @param string $payload
     * @param string $webhookUrl
     */
    public function __construct(string $payload, string $webhookUrl)
    {
        $this->payload    = $payload;
        $this->webhookUrl = $webhookUrl;
    }

    /**
     * Handle the job.
     */
    public function handle()
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->webhookUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if (defined('CURLOPT_SAFE_UPLOAD')) {
            curl_setopt($ch, CURLOPT_SAFE_UPLOAD, true);
        }
        curl_setopt($ch, CURLOPT_POSTFIELDS, ['payload' => $this->payload]);

        Util::execute($ch);
    }
}
