<?php
namespace Lt\Modules\MdBlog\Controllers;

use Lt\Modules\MdBlog\Services\TbBlogCommentService;
use Lt\Modules\MdLt\Services\LtResponse;
use Lt\Modules\MdLt\Services\LtRequest;
        
class TbBlogCommentController
{

    public function create(){
        $service = new TbBlogCommentService();
        $rows = $service->create();
        return $rows;
        
    }
    
    public function commentsLike(){
        $service = new TbBlogCommentService();
        $rows = $service->commentsLike();
        return $rows;
    }
    
    public function index(){
        $service = new TbBlogCommentService();
        $rows = $service->index();
        return $rows;
    }
    
    public function destroy(){
        $service = new TbBlogCommentService();
        $result = $service->destroy();

        return $result;

    }

    public function updateStatus(){
        $service = new TbBlogCommentService();
        $result = $service->update();
        return $result;
    }

}

