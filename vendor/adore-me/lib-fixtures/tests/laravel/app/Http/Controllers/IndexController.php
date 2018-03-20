<?php
namespace laravel\AdoreMe\Library\Fixtures\Http\Controllers;

class IndexController extends Controller
{
    /**
     * Return the welcome response when accessing the website root.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getWelcomeJson()
    {
        return response()->json([
            'message' => 'Welcome to lib-fixtures'
        ]);
    }
}
