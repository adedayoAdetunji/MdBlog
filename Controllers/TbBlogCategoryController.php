<?php
namespace Lt\Modules\MdBlog\Controllers;

use Lt\Modules\MdBlog\Services\TbBlogCategoryService;

use Lt\Modules\MdLt\Services\LtResponse;
use Lt\Modules\MdLt\Services\LtRequest;
        
class TbBlogCategoryController
{

    public function index(){
        $service = new TbBlogCategoryService();
        $rows = $service->index();
        return LtResponse::json('success', 201, 200, $rows);
    }
    
    public function selectFilter(){
        $service = new TbBlogCategoryService();
        $rows = $service->selectFilter();
        return LtResponse::json('success', 201, 200, $rows);
    }

    public function store(){
        $service = new TbBlogCategoryService();
        $result = $service->store();
        return $result;
        exit();
    }

    public function update(){
        ob_end_clean();

        $request = new LtRequest;
        $ltId = $request->ltId;
        $service = new TbBlogCategoryService();
        $result = $service->update($ltId);

        return $result;

        exit();
    }

    public function toggleActive(){
        ob_end_clean();

        $request = new LtRequest;
        $ltId = $request->ltId;

        $service = new TbBlogCategoryService();
        $result = $service->toggleActive($ltId);

        return $result;
        exit();
    }

    public function destroy(){
        ob_end_clean();

        $request = new LtRequest;
        $ltId = $request->ltId;

        $service = new TbBlogCategoryService();
        $result = $service->destroy($ltId);

        return $result;

        exit();
    }
}

