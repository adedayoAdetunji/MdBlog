<?php
namespace Lt\Modules\MdBlog\Services;

use Lt\Modules\MdBlog\Models\TbBlogReport;

use Lt\Modules\MdLt\Services\LtResponse;
use Lt\Modules\MdLt\Services\LtRequest;
use Lt\Modules\MdLt\Services\LtSession;
use Lt\Modules\MdBlog\Models\TbBlogPost;

use DbConnect;
use PDO;

class TbBlogReportService{

    protected $reportModel;
    protected $request;
    protected $ltId;
    protected $userId;
    protected $ltNow;
    protected $sqlConnect;
    
    

    public function __construct(){
        $this->reportModel = new TbBlogReport();
        $this->request = new LtRequest;
        $this->ltNow = date('Y-m-d H:i:s');
        $this->ltId = ltId();
        $this->userId = LtSession::get('ltUid');
        $this->sqlConnect = \DbConnect::dbDriver();

    }
    
    public function create(){
        $postSlug = trim($this->request->postSlug);
        $reportsReason = trim($this->request->reason);
        $comments = trim($this->request->comments);
        $postModel = new TbBlogPost();
        // Fetch post Id
        $queryPost = $postModel->select('lt_id')->where('slug', '=', $postSlug)->get()[0];
        $postId = $queryPost->ltId;
        
        $this->reportModel->ltId = $this->ltId;
        $this->reportModel->postId = $postId;
        $this->reportModel->userId = $this->userId;
        $this->reportModel->reason = $reportsReason;
        $this->reportModel->comments = $comments;

        $this->reportModel->createdAt = $this->ltNow;
        $this->reportModel->updatedAt = $this->ltNow;
        
        $this->reportModel->insert();
        return $this->reportModel->responseJson();
    }
    
    public function index(){
 
        $sql = "
            SELECT
                c.lt_id AS ltId,
                c.post_id AS postId,
                c.user_id AS userId,
                p.slug AS postSlug,
                p.title,
                p.author_id,
                c.comments,
                c.reason,
                c.status,
                c.created_at AS createdAt,
                c.updated_at AS updatedAt,
                COALESCE(NULLIF(u.fullname, ''), u.username) AS reportedBy,
                COALESCE(NULLIF(pu.fullname, ''), pu.username) AS postAuthor
            FROM tb_blog_report AS c
            LEFT JOIN tb_blog_user u ON u.user_id = c.user_id
            LEFT JOIN tb_blog_post p ON p.lt_id = c.post_id
            LEFT JOIN tb_blog_user pu ON pu.user_id = p.author_id
            ORDER BY c.created_at DESC
        ";
        
        // Prepare & Execute
        $stmt = $this->sqlConnect->prepare($sql);
        $stmt->execute();
        
        // Fetch as Associative Array
        $reports = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
       
        // If no comments, return an empty array early
        if (empty($reports)) {
             return LtResponse::json('No Reports Found', 101, 100);
        }
        
        return LtResponse::json('success', 201, 200, $reports);
    }
    
    public function destroy() {
        $ltId = $this->request->ltId;
        // 1. Check if post exists
        $exist = $this->reportModel->select()->where('ltId', '=', $ltId)->get();
        if (count($exist) < 1) {
            return LtResponse::json('Report not found', 3004, 100);
        }
        
        $this->reportModel->delete('ltId','=',$ltId);
    
        return $this->reportModel->responseJson();
    }
    
    public function update(){
        $ltId = $this->request->ltId;
        $status = $this->request->status;
        
        $exist = $this->reportModel->select()->where('ltId', '=', $ltId)->get();
        if (count($exist) < 1) {
            return LtResponse::json('Report not found', 3004, 100);
        }

        $this->reportModel->status = $status;
        $this->reportModel->updatedAt = $this->ltNow;

        $this->reportModel->update('ltId','=',$ltId);
        return $this->reportModel->responseJson();
    }

}

