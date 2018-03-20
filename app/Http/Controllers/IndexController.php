<?php

namespace AdoreMe\MsTest\Http\Controllers;

class IndexController extends Controller
{
    /**
     * Return the welcome response when accessing the website root.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getWelcomeJson()
    {
        $data            = parse_ini_file('../.env', false);
        $data['headers'] = request()->headers->all();

        return response()->json($data);
    }
}
