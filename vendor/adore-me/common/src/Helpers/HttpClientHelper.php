<?php
namespace AdoreMe\Common\Helpers;

use GuzzleHttp\Client;

class HttpClientHelper
{
    /**
     * Create and init guzzle client, used for http communications.
     *
     * @param string $baseUri
     * @return Client
     */
    public static function createAndInitGuzzleClient(string $baseUri): Client
    {
        return new Client(
            [
                'base_uri' => $baseUri,
            ]
        );
    }
}
