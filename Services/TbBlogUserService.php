<?php
namespace Lt\Modules\MdBlog\Services;

use Lt\Modules\MdBlog\Models\TbBlogUser;

use Lt\Modules\MdBlog\Services\TbBlogPostService;
use Lt\Modules\MdBlog\Services\TbBlogCommentService;
        
use Lt\Modules\MdLt\Services\LtResponse;
use Lt\Modules\MdLt\Services\LtRequest;
use Lt\Modules\MdLt\Services\LtSession;
use Lt\Modules\MdLt\Services\LtFile;

use Lt\Modules\MdLt\Models\TbUser;
use Lt\Modules\MdLt\Services\LtAuth;
use LtDdm;
        
class TbBlogUserService
{
    protected $blogModel;
    protected $userModel;
    protected $request;
    protected $ltId;
    protected $userId;
    protected $ltNow;

    public function __construct(){
        $this->blogModel = new TbBlogUser();
        $this->userModel = new TbUser();
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

    public function store(){
        $this->blogModel->processRequest();
        
        $email = trim($this->blogModel->email);
        $username = trim($this->blogModel->username);
        $password = trim($this->blogModel->password);
        $name = trim($this->blogModel->name);
        $confirmPass = trim($this->blogModel->confirmPass);
        $formName = 'blogUserForm';
        
        if ($password !== $confirmPass) {
            return LtResponse::json('Passwords do not match!', 3014, 100);
        }
        
        if (empty($username) || empty($email)) {
            return LtResponse::json('Username or Email must not be empty', 3015, 100);
        }
    
        if (strlen($password) < 8) {
            return LtResponse::json('Password must be at least 8 characters!', 3016, 100);
        }
        
        $registrationExist = $this->userModel->select()->where('username', '=', $username)->orWhere('email', '=', $email)->get();
            
        if(count($registrationExist) > 0){
            return  LtResponse::json('User record already exist', 3017, 100); 
        }
        
        $blogExist = $this->blogModel->select()->where('username','=', $username)->orWhere('email', '=', $email)->get();
        if(count($blogExist) > 1){
            return LtResponse::json('User already exist', 3018, 100);
        }
        
        $getformRoleData = LtAuth::getFormRole($formName);
        $roleId = $getformRoleData->formRole;
        
        $togetPasswordDetails = LtDdm::createPassword($password);
        $salt = $togetPasswordDetails->salt;
        $encrypt = $togetPasswordDetails->hash;
        $ltId = $this->ltId;
        
        $this->userModel->ltId = $ltId;
        $this->userModel->roleId = $roleId;
        $this->userModel->updatedBy = $this->userId;
        $this->userModel->password = $encrypt;
        $this->userModel->salt = $salt;
        $this->userModel->username = $username;
        $this->userModel->email = $email;
        $this->userModel->formType = $formName;
        $this->userModel->createdAt = $this->ltNow;
        $this->userModel->updatedAt = $this->ltNow;
        
        $this->userModel->insert();
        
        if($this->userModel->responseCategory != 200){
            return LtResponse::json('Failed to register user', 3019, 100);
        }
        
        
        $this->blogModel->ltId = $this->ltId;
        $this->blogModel->userId = $ltId;
        $this->blogModel->username = $username;
        $this->blogModel->email = $email;
        $this->blogModel->fullname = $name;
        $this->blogModel->createdAt = $this->ltNow;
        $this->blogModel->updatedAt = $this->ltNow;


        $this->blogModel->insert();
        return $this->blogModel->responseJson();
    }
    
    public function signIn(){
        $this->blogModel->processRequest();
        
        $emailOrUsername = trim($this->blogModel->email);
        $password = trim($this->blogModel->password);
        $loginFrom = "blog_site";
        $blogExist = $this->blogModel->select()->where('username','=', $emailOrUsername)->orWhere('email', '=', $emailOrUsername)->get();
        if(count($blogExist) < 1){
            return LtResponse::json('No User Found, check your login credentials', 3018, 100);
        }
        
        $authResponse = LtAuth::loginWithAudit($emailOrUsername, $password, $loginFrom); 
        return $authResponse; 
        
    }
    
    public function userRecord(){
        $postService = new TbBlogPostService();
        $commentsService = new TbBlogCommentService();
        $userId = $this->request->userId;
        $fetchUserRecord = false;
        $userExist = $this->blogModel->select()->where('userId','=', $userId)->get();
        
        if(count($userExist) < 1){
            
            $registrationExist = $this->userModel->select()->where('ltId','=', $userId)->get();
            
            if(count($registrationExist) < 1){
                return  LtResponse::json('No User Record Found', 3018, 100); 
            }else{
                $username = $registrationExist[0]->username;
                $email = $registrationExist[0]->email;
                $ltId = $registrationExist[0]->ltId;
                $createdAt = $this->ltNow;
                
                $this->blogModel->ltId = $this->ltId;
                $this->blogModel->userId = $ltId;
                $this->blogModel->username = $username;
                $this->blogModel->email = $email;
                $this->blogModel->createdAt = $createdAt;
                $this->blogModel->updatedAt = $createdAt;
                $this->blogModel->insert();
                
                if($this->blogModel->responseCategory != 200){
                    return LtResponse::json('Failed to update user', 3019, 100);
                }else{
                  $fetchUserRecord = true;  
                }
    
            }

        }else{
            $fetchUserRecord = true;
        }
        
        $postsArr = [];
        $commentsArr = [];
        
        if($fetchUserRecord){
            // fetching user post
           $posts = $postService->getAuthorPost($userId);
           
           $result =  json_decode($posts, true);
           if($result['responseCategory'] == 200){
              $postsArr = $result['responseData'];
           }
           
           // fetching user comments;
           $comments = $commentsService->fetchUserComments('178669573835648');
           $commentsArr = $comments;
           
           $user =  [
                       'ltId' => $userExist[0]->userId ?? $ltId, // getting registration
                       'username' => $userExist[0]->username ?? $username,
                       'email' => $userExist[0]->email ?? $email,
                       'fullname' => $userExist[0]->fullname ?? '',
                       'avatar' => $userExist[0]->avatar ?? '',
                       'bio' => $userExist[0]->bio ?? '',
                       'location' => $userExist[0]->location ?? '',
                       'facebook' => $userExist[0]->facebook ?? '',
                       'twitter' => $userExist[0]->twitter ?? '',
                       'linkedin' => $userExist[0]->linkedin ?? '',
                       'joined' => $userExist[0]->createdAt ?? $createdAt,
                       'posts' => $postsArr,
                       'comments' => $commentsArr
                    ];
                    
            return LtResponse::json('Success', 3004, 200, $user);
        }
        
    }

    public function update() {
        $ltId = $this->request->ltId;
        $exist = $this->blogModel->select()->where('userId', '=', $ltId)->get();
        if (count($exist) < 1) {
            return LtResponse::json('User not found', 3004, 100);
        }

        $this->blogModel->processRequest(['facebook', 'twitter', 'bio', 'location', 'fullname', 'linkedin']);
        $oldAvatarPath = $exist[0]->avatar;
        $userId = $exist[0]->ltId;
        $path = "storage/uploads/blogs/profile_{$userId}";
    
        // Handle Avatar File Upload
        $hasNewAvatar = false;
        $uploadedFile = null;
    
        if (isset($_FILES['avatarFile']) && !empty($_FILES['avatarFile']['name'])) {
            $file = $_FILES['avatarFile'];
    
            // Normalize single vs array file structure
            $error    = is_array($file['error']) ? $file['error'][0] : $file['error'];
            $tmpName  = is_array($file['tmp_name']) ? $file['tmp_name'][0] : $file['tmp_name'];
            $origName = is_array($file['name']) ? $file['name'][0] : $file['name'];
    
            if ($error === UPLOAD_ERR_OK) {
                // Validate MIME type safely
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mimeType = finfo_file($finfo, $tmpName);
                finfo_close($finfo);
    
                $allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];
                if (in_array($mimeType, $allowedMimes)) {
                    $extension   = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
                    $newFilename = uniqid('avatar_', true) . '.' . $extension;
                    $filePath    = $path . '/' . $newFilename;
    
                    // Prepare file package for LtFile uploader
                    $uploadedFile = [
                        'name'     => $newFilename,
                        'type'     => $mimeType,
                        'tmp_name' => $tmpName,
                        'error'    => $error,
                        'size'     => is_array($file['size']) ? $file['size'][0] : $file['size']
                    ];
    
                    // Assign path to model for database update
                    $this->blogModel->avatar = $filePath;
                    $hasNewAvatar = true;
                }
            }
        }
    
        $this->blogModel->updatedAt = $this->ltNow;
        $this->blogModel->update('ltId', '=', $userId);
    
        // Save physical file and delete old one only after database update
        if ($hasNewAvatar && $uploadedFile) {
            if (!is_dir($path)) {
                // Directory does not exist, so create it
                mkdir($path, 0755, true);
            }
            LtFile::path($path)->overwrite(true)->uploadFile($uploadedFile);
    
            if (!empty($oldAvatarPath) && file_exists($oldAvatarPath) && is_file($oldAvatarPath)) {
                @unlink($oldAvatarPath);
            }
        }
    
        return $this->blogModel->responseJson();
    }

    public function changePassword(){
        $ltId = $this->request->userId;
        $currentPassword = trim($this->request->currentPassword);
        $newPassword = trim($this->request->newPassword);
        $confirmPassword = trim($this->request->confirmPassword);
        
        $exist = $this->blogModel->select()->where('userId','=', $ltId)->get();
        if(count($exist) < 1){
            return LtResponse::json('User not found', 3004, 100);
        }
        
        $userId = $exist[0]->userId;
        
        // 2. Validate input fields
        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            return LtResponse::json('All password fields are required.', 3008, 100);
        }
    
        if (strlen($newPassword) < 8) {
            return LtResponse::json('New password must be at least 8 characters long.', 3008, 100);
        }
    
        if ($newPassword !== $confirmPassword) {
            return LtResponse::json('New password and confirmation do not match.', 3008, 100);
        }
    
        if ($currentPassword === $newPassword) {
            return LtResponse::json('New password must be different from current password.', 3008, 100);
        }
        
        $auth = json_decode(LtAuth::loginWithId($ltId, $currentPassword));
        
        if($auth->responseCategory == 100) return LtResponse::json('Current Password is incorrect', 3008, 100);

        $togetPasswordDetails = LtDdm::createPassword($newPassword);
        $salt = $togetPasswordDetails->salt;
        $encrypt = $togetPasswordDetails->hash;

        $this->userModel->updatedBy = LtSession::get('ltUid');
        $this->userModel->password = $encrypt;
        $this->userModel->salt = $salt;
        
        $this->userModel->update('ltId', '=', $userId);
        return $this->userModel->responseJson(); 
    }
    
    public function destroy(){
        
        $ltId = $this->request->ltId;
        $confirmation = trim($this->request->confirmation ?? '');
        $exist = $this->blogModel->select()->where('userId', '=', $ltId)->get();
        if(count($exist) < 1){
            return LtResponse::json('User not found', 3004, 100);
        }
        $expectedKeyword = $exist[0]->username;
        $userId = $exist[0]->ltId;
        
        // Reject if validation affirmation doesn't match
        if ($confirmation !== $expectedKeyword && $confirmation !== 'DELETE') {
            return LtResponse::json('Invalid confirmation text. Deletion rejected.', 422, 100);
        }
            // Clean up profile storage directory
        $profileDir = "storage/uploads/blogs/profile_{$userId}";
        if (is_dir($profileDir)) {
            array_map('unlink', glob("{$profileDir}/*.*"));
            @rmdir($profileDir);
        }
        
        // $this->blogModel->delete('ltId','=',$ltId);
        return LtResponse::json('Deleted', 201, 200, $exist);
    }
    
}




