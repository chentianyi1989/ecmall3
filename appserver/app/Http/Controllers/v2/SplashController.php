<?php
//
namespace App\Http\Controllers\v2;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\v2\Splash;

class SplashController extends Controller
{
    //POST  ecapi.splash.list
    public function index()
    {
       return $this->json(Splash::getList());
    }

    //
    public function view(Request $request)
    {
        $rules = [
            'splash' => 'required|integer|min:1',
        ];

        if ($error = $this->validateInput($rules)) {
            return $error;
        }

        $data = Splash::getSplashById($this->validated);

        return $this->json($data);
    }
}