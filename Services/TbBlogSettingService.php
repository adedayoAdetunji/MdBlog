<?php
namespace Lt\Modules\MdBlog\Services;

use Lt\Modules\MdBlog\Models\TbBlogSetting;

use Lt\Modules\MdLt\Services\LtResponse;
use Lt\Modules\MdLt\Services\LtRequest;
use Lt\Modules\MdLt\Services\LtSession;
        
class TbBlogSettingService
{
    protected $blogModel;
    protected $request;
    protected $ltId;
    protected $userId;
    protected $ltNow;

    public function __construct(){
        $this->blogModel = new TbBlogSetting();
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
    
    public function defaultSettings($settingKey = ""){
        $defaultSettingsKey = $settingKey ?? 'blog_menu';
        $query = $this->blogModel->select('lt_id, setting_key, setting_value')->where('settingKey', '=', $defaultSettingsKey)->get()[0];
        return $query;
    }

    public function store(){
        $this->blogModel->processRequest();
        
        $settingKey = trim($this->blogModel->settingName);
        $settingValue = trim($this->blogModel->settingValue);
        if($settingKey === '' && $settingValue === ''){
            return LtResponse::json('Setting key and value is required', 3004, 100);
        }
        
        $exist = $this->blogModel->select()->where('settingKey','=', $settingKey)->get();
        if(count($exist) > 1){
            return LtResponse::json('Setting already exist', 3004, 100);
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
            return LtResponse::json('Setting not found', 3004, 100);
        }
        
        $this->blogModel->processRequest();

        $this->blogModel->updatedAt = $this->ltNow;

        $this->blogModel->update('ltId','=',$ltId);
        return $this->blogModel->responseJson();
    }

    public function toggleActive($ltId){
        $row = $this->blogModel->select()->where('ltId','=',$ltId)->get();
        if(count($row) < 1){
            return LtResponse::json('Setting not found', 3004, 100);
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
            return LtResponse::json('Setting not found', 3004, 100);
        }

        $this->blogModel->delete('ltId','=',$ltId);
        return LtResponse::json('Deleted', 201, 200);
    }
    
    
}

