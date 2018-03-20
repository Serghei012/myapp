<?php
namespace spec\AdoreMe\Common\Helpers;

use AdoreMe\Common\Helpers\HttpHelper;
use AdoreMe\Common\Models\HeaderBag;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\MessageBag;
use PhpSpec\ObjectBehavior;
use PhpSpec\Wrapper\Subject;

/** @var HttpHelper $this */
class HttpHelperSpec extends ObjectBehavior
{
    /** @noinspection PhpMethodNamingConventionInspection */
    function it_getRemoteIp_works_as_intended_and_return_null()
    {
        /** @var HttpHelper $this */
        /** @var Subject $result */
        $result = $this->getRemoteIp();
        $result->shouldBeNull();
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_getXForwardedFor_works_as_intended_and_return_null()
    {
        /** @var HttpHelper $this */
        /** @var Subject $result */
        $result = $this->getXForwardedFor();
        $result->shouldBeNull();
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_getDeviceCode_works_as_intended_and_return_null()
    {
        /** @var HttpHelper $this */
        /** @var Subject $result */
        $result = $this->getDeviceCode();
        $result->shouldBeNull();
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_getUserAgent_works_as_intended_and_return_null()
    {
        /** @var HttpHelper $this */
        /** @var Subject $result */
        $result = $this->getUserAgent();
        $result->shouldBeNull();
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_getRemoteIp_works_as_intended_from_header_bag()
    {
        /** @var HttpHelper $this */
        /** @var Subject $result */
        $headers = new \Symfony\Component\HttpFoundation\HeaderBag(
            [
                'x-forwarded-for' => [
                    'some ip',
                ],
            ]
        );
        /** @var Request $request */
        $request          = $this->getRequest();
        $request->headers = $headers;
        $result           = $this->getRemoteIp($request);
        $result->shouldBe('some ip');
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_getXForwardedFor_works_as_intended_from_header_bag()
    {
        /** @var HttpHelper $this */
        /** @var Subject $result */
        $headers = new \Symfony\Component\HttpFoundation\HeaderBag(
            [
                'x-forwarded-for' => [
                    'some ip',
                ],
            ]
        );
        /** @var Request $request */
        $request          = $this->getRequest();
        $request->headers = $headers;
        $result           = $this->getXForwardedFor($request);
        $result->shouldBe('some ip');
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_getDeviceCode_works_as_intended_from_header_bag()
    {
        /** @var HttpHelper $this */
        /** @var Subject $result */
        $headers = new \Symfony\Component\HttpFoundation\HeaderBag(
            [
                'device-code' => [
                    'some device code',
                ],
            ]
        );
        /** @var Request $request */
        $request          = $this->getRequest();
        $request->headers = $headers;
        $result           = $this->getDeviceCode($request);
        $result->shouldBe('some device code');
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_getUserAgent_works_as_intended_and_return_test_from_global_variable_server()
    {
        /** @var HttpHelper $this */
        /** @var Subject $result */
        $_SERVER['HTTP_USER_AGENT'] = 'test';
        $result                     = $this->getUserAgent();
        $result->shouldBe('test');
        unset ($_SERVER['HTTP_USER_AGENT']);
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_getUserAgent_works_as_intended_and_return_test_string_from_header_bag()
    {
        /** @var HttpHelper $this */
        /** @var Subject $result */
        $headers = new \Symfony\Component\HttpFoundation\HeaderBag(
            [
                'user-agent' => [
                    'some user agent',
                ],
            ]
        );
        /** @var Request $request */
        $request          = $this->getRequest();
        $request->headers = $headers;
        $result           = $this->getUserAgent($request);
        $result->shouldBe('some user agent');
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_getRawRequestContent_works_as_intended()
    {
        /** @var HttpHelper $this */
        /** @var Subject $result */
        $result = $this->getRawRequestContent();
        $result->shouldBeString();
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_getRequest_works_as_intended()
    {
        /** @var HttpHelper $this */
        /** @var Subject $result */
        $result = $this->getRequest();
        $result->shouldBeAnInstanceOf(Request::class);
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_handleErrorMessage_works_as_intended_when_MessageBag()
    {
        /** @var HttpHelper $this */
        $messageBag = new MessageBag(
            [
                [
                    'error 1',
                ],
                [
                    'error 2',
                ],
                'error 3',
            ]
        );
        /** @var Subject $result */
        $result = $this->handleErrorMessage($messageBag, 404);
        $result->shouldIterateAs(
            [
                'error' => [
                    'code'    => 404,
                    'message' => 'error 1',
                    'errors'  => [
                        'error 1',
                        'error 2',
                        'error 3',
                    ],
                ],
            ]
        );
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_handleErrorMessage_works_as_intended_when_simple_Collection()
    {
        /** @var HttpHelper $this */
        /** @var Subject $result */
        $result = $this->handleErrorMessage(
            Collection::make(
                [
                    'error 1',
                    'error 2',
                ]
            ),
            404
        );
        $result->shouldIterateAs(
            [
                'error' => [
                    'code'    => 404,
                    'message' => 'error 1',
                    'errors'  => [
                        'error 1',
                        'error 2',
                    ],
                ],
            ]
        );
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_handleErrorMessage_works_as_intended_when_composed_Collection()
    {
        /** @var HttpHelper $this */
        /** @var Subject $result */
        $result = $this->handleErrorMessage(
            Collection::make(
                [
                    [
                        'error 1',
                    ],
                    [
                        'error 2',
                    ],
                    'error 3',
                ]
            ),
            404
        );
        $result->shouldIterateAs(
            [
                'error' => [
                    'code'    => 404,
                    'message' => 'error 1',
                    'errors'  => [
                        'error 1',
                        'error 2',
                        'error 3',
                    ],
                ],
            ]
        );
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_handleErrorMessage_works_as_intended_when_string()
    {
        /** @var HttpHelper $this */
        /** @var Subject $result */
        $result = $this->handleErrorMessage('string', 404);
        $result->shouldIterateAs(
            [
                'error' => [
                    'code'    => 404,
                    'message' => 'string',
                    'errors'  => [
                        'string',
                    ],
                ],
            ]
        );
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_handleErrorMessage_works_as_intended_when_empty_array()
    {
        /** @var HttpHelper $this */
        /** @var Subject $result */
        $result = $this->handleErrorMessage([], 404);
        $result->shouldIterateAs(
            [
                'error' => [
                    'code'    => 404,
                    'message' => '',
                    'errors'  => [],
                ],
            ]
        );
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_handleErrorMessage_works_as_intended_when_filled_array()
    {
        /** @var HttpHelper $this */
        /** @var Subject $result */
        $result = $this->handleErrorMessage(
            [
                'error 1',
                'error 2',
            ],
            404
        );
        $result->shouldIterateAs(
            [
                'error' => [
                    'code'    => 404,
                    'message' => 'error 1',
                    'errors'  => [
                        'error 1',
                        'error 2',
                    ],
                ],
            ]
        );
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_getHeaderBag_works_as_intended()
    {
        /** @var HttpHelper $this */
        /** @var Subject $result */
        $result = $this->getHeaderBag();
        $result->shouldBeAnInstanceOf(HeaderBag::class);
    }
}
