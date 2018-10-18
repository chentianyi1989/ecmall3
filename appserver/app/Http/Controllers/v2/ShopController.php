<?php
//
namespace App\Http\Controllers\v2;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\v2\Shop;
use App\Models\v2\CollectShop;

class ShopController extends Controller {

    /**
    * POST ecapi.search.shop.list
    */
    public function search()
    {
        $rules = [
            'page'            => 'required|integer|min:1',
            'per_page'        => 'required|integer|min:1',
            'keyword'         => 'string|min:1',
            'sort_key'        => 'string|min:1',
            'sort_value'      => 'required_with:sort_key|string|min:1',
        ];

        if ($error = $this->validateInput($rules)) {
            return $error;
        }

        $data = Shop::getList($this->validated);

        return $this->json($data);
    }

    /**
    * POST ecapi.shop.list
    */
    public function index()
    {
        $rules = [
            'page'            => 'required|integer|min:1',
            'per_page'        => 'required|integer|min:1',
            'keyword'         => 'string|min:1',
            'sort_key'        => 'string|min:1',
            'sort_value'      => 'required_with:sort_key|string|min:1',
        ];

        if ($error = $this->validateInput($rules)) {
            return $error;
        }

        $data = Shop::getList($this->validated);

        return $this->json($data);
    }

    /**
    * POST ecapi.shop.get
    */
    public function info()
    {
        $rules = [
            'shop' => 'required|integer|min:1',
        ];

        if ($error = $this->validateInput($rules)) {
            return $error;
        }

        $data = Shop::getInfo($this->validated);

        return $this->json($data);
    }

    /**
    * POST ecapi.recommend.shop.list
    */
    public function recommand()
    {
        $rules = [
            'page'            => 'required|integer|min:1',
            'per_page'        => 'required|integer|min:1',
            'keyword'         => 'string|min:1',
            'sort_key'        => 'string|min:1',
            'sort_value'      => 'required_with:sort_key|string|min:1',
        ];

        if ($error = $this->validateInput($rules)) {
            return $error;
        }

        $data = Shop::getRecommand($this->validated);

        return $this->json($data);
    }    
    
    /**
    * POST ecapi.shop.watching.list
    */
    public function watchingList()
    {
        $rules = [
            'page'            => 'required|integer|min:1',
            'per_page'        => 'required|integer|min:1',
        ];

        if ($error = $this->validateInput($rules)) {
            return $error;
        }

        $data = CollectShop::getList($this->validated);

        return $this->json($data);
    }

    /**
    * POST ecapi.shop.watch
    */
    public function watch()
    {
        $rules = [
            'shop' => 'required|integer|min:1',
        ];

        if ($error = $this->validateInput($rules)) {
            return $error;
        }

        $data = CollectShop::setWatching($this->validated);

        return $this->json($data);
    }

    /**
    * POST ecapi.shop.unwatch
    */
    public function unwatch()
    {
        $rules = [
            'shop' => 'required|integer|min:1',
        ];

        if ($error = $this->validateInput($rules)) {
            return $error;
        }

        $data = CollectShop::setUnwatching($this->validated);

        return $this->json($data);
    }
}
