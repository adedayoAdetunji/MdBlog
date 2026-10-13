<?php
namespace Lt\Modules\MdBlog\Controllers;

use Lt\Modules\MdBlog\Services\TbBlogUserService;
        
use Lt\Modules\MdLt\Services\LtResponse;
use Lt\Modules\MdLt\Services\LtRequest;
        
class TbBlogUserController
{

    public function index(){
        $service = new TbBlogUserService();
        $rows = $service->index();
        return LtResponse::json('success', 201, 200, $rows);
    }
    

    public function signup(){
        $service = new TbBlogUserService();
        $result = $service->store();
        return $result;
    }
    
    public function signin(){
        $service = new TbBlogUserService();
        $result = $service->signIn();
        return $result;
    }
    
    public function userRecord(){
        $service = new TbBlogUserService();
        $result = $service->userRecord();
        return $result;
    }

    public function update(){
        $service = new TbBlogUserService();
        $result = $service->update();

        return $result;
    }
    
    public function changePassword(){
        $service = new TbBlogUserService();
        $result = $service->changePassword();

        return $result;
    }


    public function deleteUser(){

        $service = new TbBlogUserService();
        $result = $service->destroy();

        return $result;

    }
}