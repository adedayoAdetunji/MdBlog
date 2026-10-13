<?php
namespace Lt\Modules\MdBlog\Services;

use Lt\Modules\MdBlog\Models\TbBlogTag;

use Lt\Modules\MdLt\Services\LtResponse;
use Lt\Modules\MdLt\Services\LtRequest;
use Lt\Modules\MdLt\Services\LtSession;
        
class TbBlogTagService
{
    protected $blogTag;
    protected $request;
    protected $ltId;
    protected $userId;
    protected $ltNow;

    public function __construct(){
        $this->blogTag = new TbBlogTag();
        $this->request = new LtRequest;
        $this->ltNow = date('Y-m-d H:i:s');
        $this->ltId = ltId();
        $this->userId = LtSession::get('ltUid');
    }

    public function index(){
        $status = $this->request->status;

        $query = $this->blogTag->select();

        if($status !== null && $status !== ''){
            $query->where('status','=', $status);
        }
        return $query->get();
    }

    public function store(){
        $this->blogTag->processRequest();
        
        $tagName = trim((string)$this->blogTag->tagName);
        if($tagName === ''){
            return LtResponse::json('Tag name and code is required', 3004, 100);
        }
        
        $exist = $this->blogTag->select()->where('tagName','=', $tagName)->get();
        if(count($exist) > 1){
            return LtResponse::json('Tag already exist', 3004, 100);
        }

        $this->blogTag->ltId = $this->ltId;
        $this->blogTag->createdAt = $this->ltNow;
        $this->blogTag->updatedAt = $this->ltNow;


        $this->blogTag->insert();
        return $this->blogTag->responseJson();
    }

    public function update(){
        $ltId= $this->request->ltId;
        $exist = $this->blogTag->select()->where('ltId','=', $ltId)->get();
        if(count($exist) < 1){
            return LtResponse::json('Tag not found', 3004, 100);
        }
        
        $this->blogTag->processRequest();

        $this->blogTag->updatedAt = $this->ltNow;

        $this->blogTag->update('ltId','=',$ltId);
        return $this->blogTag->responseJson();
    }

    public function toggleActive($ltId){
        $row = $this->blogTag->select()->where('ltId','=',$ltId)->get();
        if(count($row) < 1){
            return LtResponse::json('Tag not found', 3004, 100);
        }

        $current = $row[0]->status;
        $newVal = ($current == 'isActive') ? 'isNotActive' : 'isActive';

        $this->blogTag->status = $newVal;
        $this->blogTag->updatedAt = $this->ltNow;
        $this->blogTag->update('ltId','=',$ltId);

        return LtResponse::json('Updated', 201, 200, ['ltId'=>$ltId,'isActive'=>$newVal]);
    }

    public function destroy($ltId){
        $exist = $this->blogTag->select()->where('ltId','=',$ltId)->get();
        if(count($exist) < 1){
            return LtResponse::json('Tag not found', 3004, 100);
        }

        $this->blogTag->delete('ltId','=',$ltId);
        return LtResponse::json('Deleted', 201, 200);
    }
}

