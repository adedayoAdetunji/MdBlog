<?php
namespace Lt\Modules\MdBlog\Controllers;

use Lt\Modules\MdBlog\Services\TbBlogSettingService;

use Lt\Modules\MdLt\Services\LtResponse;
use Lt\Modules\MdLt\Services\LtRequest;
        
class TbBlogSettingController
{

    public function index(){
        $service = new TbBlogSettingService();
        $rows = $service->index();
        return LtResponse::json('success', 201, 200, $rows);
    }
    
    public function selectFilter(){
        $service = new TbBlogSettingService();
        $rows = $service->selectFilter();
        return LtResponse::json('success', 201, 200, $rows);
    }

    public function store(){
        $service = new TbBlogSettingService();
        $result = $service->store();
        return $result;
        exit();
    }

    public function update(){
        $request = new LtRequest;
        $ltId = $request->ltId;
        $service = new TbBlogSettingService();
        $result = $service->update($ltId);

        return $result;
    }

    public function toggleActive(){
        $request = new LtRequest;
        $ltId = $request->ltId;

        $service = new TbBlogSettingService();
        $result = $service->toggleActive($ltId);

        return $result;
    }

    public function destroy(){

        $request = new LtRequest;
        $ltId = $request->ltId;

        $service = new TbBlogSettingService();
        $result = $service->destroy($ltId);

        return $result;

    }
}