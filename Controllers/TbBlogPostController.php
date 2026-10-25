<?php
namespace Lt\Modules\MdBlog\Controllers;

use Lt\Modules\MdBlog\Services\TbBlogPostService;
use Lt\Modules\MdLt\Services\LtResponse;
use Lt\Modules\MdLt\Services\LtRequest;

class TbBlogPostController
{

    public function index(){
        $service = new TbBlogPostService();
        $rows = $service->index();
        return $rows;
        
    }
    
    public function dashboardMetrics(){
        $service = new TbBlogPostService();
        $rows = $service->dashboardMetrics();
        return $rows;
    }
    
    public function menuCategories(){
        $service = new TbBlogPostService();
        $rows = $service->categoryPostMenu();
        return $rows;
    }
    
    public function filterPosts(){
        $service = new TbBlogPostService();
        $rows = $service->filterPosts();
        return $rows;
    }
    public function widget(){
        $service = new TbBlogPostService();
        $rows = $service->widget();
        return $rows;
    }
    
    public function postSlug(){
        $service = new TbBlogPostService();
        $rows = $service->postSlug();
        return $rows;
    }
    
    public function postReaction(){
        $service = new TbBlogPostService();
        $rows = $service->postReaction();
        return $rows;
    }
    public function viewsUpdate(){
        $service = new TbBlogPostService();
        $rows = $service->updateViews();
        return $rows;
    }

    public function store(){
        $service = new TbBlogPostService();
        $result = $service->store();
        return $result;
    }

    public function update(){

        $request = new LtRequest;
        $ltId = $request->ltId;
        $service = new TbBlogPostService();
        $result = $service->update($ltId);

        return $result;

    }

    public function updateStatus(){
        $service = new TbBlogPostService();
        $result = $service->updateStatus();

        return $result;
    }

    public function destroy(){

        $request = new LtRequest;
        $ltId = $request->ltId;

        $service = new TbBlogPostService();
        $result = $service->destroy($ltId);

        return $result;

    }
}

