<?php
namespace Lt\Modules\MdBlog\Services;

use Lt\Modules\MdBlog\Models\TbBlogMedia;
use Lt\Modules\MdLt\Services\LtResponse;
use Lt\Modules\MdLt\Services\LtRequest;
use Lt\Modules\MdLt\Services\LtSession;
        
class TbBlogMediaService
{
    protected $blogMediaModel;
    protected $request;
    protected $ltId;
    protected $userId;
    protected $ltNow;
    protected $sqlConnect;
    protected $blogMedia;

    public function __construct(){
        $this->blogMediaModel = new TbBlogMedia();
        $this->request = new LtRequest;
        $this->ltNow = date('Y-m-d H:i:s');
        $this->ltId = ltId();
        $this->userId = LtSession::get('ltUid');
    }
    
    public function store($media){
        // $media = (object)$media;
        $record = [];
            foreach ($media as $key => $value) {
                $record->$key = $value;
                // $this->blogMediaModel->$key = $value;
            }        
        // // 
        
        return $record;
        // $originalName = $record->originalName;
        // $postId = $record->postId;
        
        // return "$originalName -- $posreturn $record;tId";
        // check for existing media 
        $exist = $this->blogMediaModel->select()->where('originalName','=', $originalName)->andWhere('postId', '=', $postId)->get();
        
        if(count($exist) > 0){
            return  LtResponse::json("Media {$originalName} record already exist", 3503, 100); 
        }
    
       
        $this->blogMediaModel->ltId = $this->ltId;
        $this->blogMediaModel->postId = $postId;
        $this->blogMediaModel->uploadedBy = $media->uploadedBy;
        $this->blogMediaModel->mimeType = $media->mimeType;
        $this->blogMediaModel->fileSize = $media->fileSize;
        $this->blogMediaModel->filename = $media->filename;
        $this->blogMediaModel->mediaCategory = $media->mediaCategory;
        $this->blogMediaModel->altText = $media->altText;
        $this->blogMediaModel->storagePath = $media->storagePath;
        $this->blogMediaModel->originalName = $originalName;
        $this->blogMediaModel->updatedAt = $this->ltNow;
        $this->blogMediaModel->createdAt = $this->ltNow;
        $this->blogMediaModel->insert();
        //  return $this->blogMediaModel;
        return $this->blogMediaModel->responseJson();
    }
}
