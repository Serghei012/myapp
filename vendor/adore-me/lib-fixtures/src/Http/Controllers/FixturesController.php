<?php
namespace AdoreMe\Library\Fixtures\Http\Controllers;

use AdoreMe\Library\Fixtures\Services\LibraryFixturesService;
use AdoreMe\Logger\Http\Controllers\AbstractResourceController;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class FixturesController extends AbstractResourceController
{
    /** @var LibraryFixturesService */
    protected $libraryFixturesService;

    /**
     * FixturesController constructor.
     *
     * @param Request $request
     * @param ResponseFactory $response
     * @param LibraryFixturesService $libraryFixturesService
     */
    public function __construct(
        Request $request,
        ResponseFactory $response,
        LibraryFixturesService $libraryFixturesService
    ) {
        parent::__construct($request, $response);

        $this->libraryFixturesService = $libraryFixturesService;
    }

    /**
     * Apply fixtures.
     */
    public function applyTemplate()
    {
        $template = $this->request->route('template');

        try {
            list ($status, $debugInfo) = $this->libraryFixturesService->apply($template);
        } catch (ValidationException $e) {
            return $this->respondWithBadRequest($e->getResponse());
        } catch (\Exception $e) {
            return $this->respondWithInternalError($e->getMessage());
        }

        $response = ['status' => $status];
        if (env('APP_DEBUG')) {
            $response['debug'] = $debugInfo;
        }

        return $this->respondWithJson($response);
    }

    /**
     * Reset database.
     */
    public function resetDatabase()
    {
        try {
            $this->libraryFixturesService->resetDatabase();
        } catch (ValidationException $e) {
            return $this->respondWithBadRequest($e->getResponse());
        } catch (\Exception $e) {
            return $this->respondWithInternalError($e->getMessage());
        }

        $response = ['status' => true];

        return $this->respondWithJson($response);
    }
}
