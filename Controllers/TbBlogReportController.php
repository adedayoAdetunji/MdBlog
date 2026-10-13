<?php
namespace Lt\Modules\MdBlog\Controllers;

use Lt\Modules\MdBlog\Services\TbBlogReportService;

use Lt\Modules\MdLt\Services\LtResponse;
use Lt\Modules\MdLt\Services\LtRequest;
        
class TbBlogReportController
{

    public function createReport(){
        $service = new TbBlogReportService();
        $rows = $service->create();
        return $rows;
        
    }
      public function index(){
        $service = new TbBlogReportService();
        $rows = $service->index();
        return $rows;
    }
    
    public function destroy(){
        $service = new TbBlogReportService();
        $result = $service->destroy();

        return $result;

    }

    public function updateStatus(){
        $service = new TbBlogReportService();
        $result = $service->update();
        return $result;
    }
}