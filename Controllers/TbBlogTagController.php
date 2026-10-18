<?php
namespace Lt\Modules\MdBlog\Controllers;

use Lt\Modules\MdBlog\Services\TbBlogTagService;

use Lt\Modules\MdLt\Services\LtResponse;
use Lt\Modules\MdLt\Services\LtRequest;
        
class TbBlogTagController
{

    public function index(){
        $service = new TbBlogTagService();
        $rows = $service->index();
        return LtResponse::json('success', 201, 200, $rows);
    }

    public function store(){
        $service = new TbBlogTagService();
        $result = $service->store();
        return $result;
    }

    public function update(){

        $service = new TbBlogTagService();
        $result = $service->update();

        return $result;

    }

    public function toggleActive(){
        ob_end_clean();

        $request = new LtRequest;
        $ltId = $request->ltId;

        $service = new TbBlogTagService();
        $result = $service->toggleActive($ltId);

        return $result;
        exit();
    }

    public function destroy(){
        ob_end_clean();

        $request = new LtRequest;
        $ltId = $request->ltId;

        $service = new TbBlogTagService();
        $result = $service->destroy($ltId);

        return $result;

        exit();
    }
}

