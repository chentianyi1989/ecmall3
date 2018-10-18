<?php
//
namespace App\Http\Controllers\v2;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\v2\Theme;

class ThemeController extends Controller
{
    //POST  ecapi.theme.list
    public function index()
    {
       return $this->json(Theme::getList());
    }

    //
    public function view(Request $request)
    {
        $rules = [
            'theme' => 'required|integer|min:1',
        ];

        if ($error = $this->validateInput($rules)) {
            return $error;
        }

        $data = Theme::getThemeById($this->validated);

        return $this->json($data);
    }
}