<?php
//
namespace App\Http\Controllers\v2;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\BaseModel;
use App\Models\v2\Group;
use App\Models\v2\Page;
use App\Models\v2\Template;

class CardPageController extends Controller
{
    /**
    * POST ecapi.cardpage.get
    */
    public function view(Request $request)
    {
        $rules = [
            'name' => 'required|string|min:1',
        ];

        if ($error = $this->validateInput($rules)) {
            return $error;
        }

        extract($this->validated);

        $json = @file_get_contents(config('app.json_cdn').(isset($id) ? $id : $name).'.json');

        if (!$json) {
            self::json(BaseModel::formatError(BaseModel::NOT_FOUND));
        }

        $data = json_decode($json, true);

        return $this->json(['cardpage' => $data]);
    }
    /**
    * POST ecapi.cardpage.preview
    */
    public function preview(Request $request)
    {
        $rules = [
            'name' => 'required|string|min:1',
        ];

        if ($error = $this->validateInput($rules)) {
            return $error;
        }

        extract($this->validated);

        $data = json_decode(self::getPagedata($name));

        return $this->json(['cardpage' => $data]);
    }


    public function getPagedata($name)
    {
        $pageInfo = Page::findOneByName($name);

        if (!$pageInfo ) {
            return false;
        }
        $template_id = $pageInfo->card_template_id;
        $data = Template::findOne($template_id);
        if (!$data) {
            return false;
        }
        $data->title = $pageInfo->title;
        $data->content = $pageInfo->content;
        $data->name = $pageInfo->name;
        $data = $data->toArray();

        foreach ($data['groups'] as $key => $group) {
            if ($group['source_id'] != 1) {
                $data['groups'][$key]['cards'] = Group::apicards($group['source_id'],$group['id']);
            }
            foreach ( $data['groups'][$key]['cards'] as $ckey => $card) {
                $data['groups'][$key]['cards'][$ckey]['photo'] = is_string($data['groups'][$key]['cards'][$ckey]['photo'])
                ? formatPhoto(null, $data['groups'][$key]['cards'][$ckey]['photo']):$data['groups'][$key]['cards'][$ckey]['photo'] ;
            }
        }

        return json_encode($data);
    }

}
