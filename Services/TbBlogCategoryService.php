<?php
namespace Lt\Modules\MdBlog\Services;

use Lt\Modules\MdBlog\Models\TbBlogCategory;
use Lt\Modules\MdLt\Services\LtResponse;
use Lt\Modules\MdLt\Services\LtRequest;
use Lt\Modules\MdLt\Services\LtSession;
        
class TbBlogCategoryService
{
    protected $blogModel;
    protected $request;
    protected $ltId;
    protected $userId;
    protected $ltNow;

    public function __construct(){
        $this->blogModel = new TbBlogCategory();
        $this->request = new LtRequest;
        $this->ltNow = date('Y-m-d H:i:s');
        $this->ltId = ltId();
        $this->userId = LtSession::get('ltUid');
    }

    public function index(){
        $status = $this->request->status;

        $query = $this->blogModel->select();

        if($status !== null && $status !== ''){
            $query->where('status','=', $status);
        }
        return $query->get();
    }
    
    public function selectFilter(){
        $status = $this->request->status;

        $query = $this->blogModel->select('lt_id, category_name');

        if($status !== null && $status !== ''){
            $query->where('status','=', $status);
        }
        return $query->get();
    }

    public function store(){
        $this->blogModel->processRequest();
        
        $categoryName = trim((string)$this->blogModel->categoryName);
        if($categoryName === ''){
            return LtResponse::json('Category name and code is required', 3004, 100);
        }
        
        $exist = $this->blogModel->select()->where('categoryName','=', $categoryName)->get();
        if(count($exist) > 1){
            return LtResponse::json('Category already exist', 3004, 100);
        }

        $this->blogModel->ltId = $this->ltId;
        $this->blogModel->createdAt = $this->ltNow;
        $this->blogModel->updatedAt = $this->ltNow;


        $this->blogModel->insert();
        return $this->blogModel->responseJson();
    }

    public function update($ltId){
        $exist = $this->blogModel->select()->where('ltId','=', $ltId)->get();
        if(count($exist) < 1){
            return LtResponse::json('Category not found', 3004, 100);
        }
        
        $this->blogModel->processRequest();

        $this->blogModel->updatedAt = $this->ltNow;

        $this->blogModel->update('ltId','=',$ltId);
        return $this->blogModel->responseJson();
    }

    public function toggleActive($ltId){
        $row = $this->blogModel->select()->where('ltId','=',$ltId)->get();
        if(count($row) < 1){
            return LtResponse::json('Category not found', 3004, 100);
        }

        $current = $row[0]->status;
        $newVal = ($current == 'isActive') ? 'isNotActive' : 'isActive';

        $this->blogModel->status = $newVal;
        $this->blogModel->updatedAt = $this->ltNow;
        $this->blogModel->update('ltId','=',$ltId);

        return LtResponse::json('Updated', 201, 200, ['ltId'=>$ltId,'isActive'=>$newVal]);
    }

    public function destroy($ltId){
        $exist = $this->blogModel->select()->where('ltId','=',$ltId)->get();
        if(count($exist) < 1){
            return LtResponse::json('Category not found', 3004, 100);
        }

        $this->blogModel->delete('ltId','=',$ltId);
        return LtResponse::json('Deleted', 201, 200);
    }
}

