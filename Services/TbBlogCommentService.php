<?php
namespace Lt\Modules\MdBlog\Services;

use Lt\Modules\MdBlog\Models\TbBlogComment;
use Lt\Modules\MdLt\Services\LtResponse;
use Lt\Modules\MdLt\Services\LtRequest;
use Lt\Modules\MdLt\Services\LtSession;

use Lt\Modules\MdLt\Models\TbUser;
use Lt\Modules\MdBlog\Models\TbBlogPost;

use DbConnect;
use PDO;

class TbBlogCommentService
{
    
    protected $commentModel;
    protected $request;
    protected $ltId;
    protected $userId;
    protected $ltNow;
    protected $sqlConnect;
    protected $user;

    public function __construct(){
        $this->commentModel = new TbBlogComment();
        $this->request = new LtRequest;
        $this->user = new TbUser;
        $this->ltNow = date('Y-m-d H:i:s');
        $this->ltId = ltId();
        $this->userId = LtSession::get('ltUid');
        $this->sqlConnect = \DbConnect::dbDriver();
    }
    
    public function index(){
        $sql = "
            SELECT
                c.lt_id AS ltId,
                c.post_id AS postId,
                c.user_id AS userId,
                c.parent_id AS parentId,
                p.slug AS postSlug,
                p.title,
                p.author_id AS authorId,
                c.body,
                c.status,
                c.like_count AS likes,
                c.created_at AS createdAt,
                c.updated_at AS updatedAt,
                COALESCE(NULLIF(u.fullname, ''), u.username) AS commentedBy,
                COALESCE(NULLIF(pu.fullname, ''), pu.username) AS postAuthor
            FROM tb_blog_comment AS c
            LEFT JOIN tb_blog_user u ON u.user_id = c.user_id
            LEFT JOIN tb_blog_post p ON p.lt_id = c.post_id
            LEFT JOIN tb_blog_user pu ON pu.user_id = p.author_id
            ORDER BY c.created_at DESC;
        ";
        
        // Prepare & Execute
        $stmt = $this->sqlConnect->prepare($sql);
        $stmt->execute();
        
        // Fetch as Associative Array
        $flatComments = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // If no comments, return an empty array early
        if (empty($flatComments)) {
             return LtResponse::json('No Comments Found', 101, 100);
        }
        
        // Pass the flat array to the tree builder and return the nested result
        $comments =  $this->buildTree($flatComments);
        return LtResponse::json('success', 201, 200, $comments);
    }
    
    function getUsername($userId){
        $user = $this->user->select('username, surname')->where('ltId', '=', $userId)->get()[0];
        return $user->username ?: $user->surname;
    }
    
    public function create(){
        $postSlug = trim($this->request->postSlug);
        $parentId = trim($this->request->parentId);
        $body = trim($this->request->body);
        $postModel = new TbBlogPost();
        // Fetch post Id
        $queryPost = $postModel->select('lt_id, comment_count')->where('slug', '=', $postSlug)->get();
        if (empty($queryPost)) {
            return LtResponse::json('Post not found', 101, 100);
        }
        $postId = $queryPost[0]->ltId;
        $username = $this->getUsername($this->userId);
        
        $this->commentModel->ltId = $this->ltId;
        $this->commentModel->postId = $postId;
        $this->commentModel->userId = $this->userId;
        $this->commentModel->parentId = !empty($parentId) ? (int) $parentId : null;
        $this->commentModel->body = $body;
        $this->commentModel->status = 'approved';
        $this->commentModel->createdAt = $this->ltNow;
        $this->commentModel->updatedAt = $this->ltNow;
        
        $this->commentModel->insert();
        $this->commentModel->responseData->postSlug = $postSlug;
        $this->commentModel->responseData->author = $username;
        
        if($this->commentModel->responseCategory == 200){
            //using sql to avoid potential race conditions
            $sql = "UPDATE tb_blog_post SET comment_count = comment_count + 1 WHERE lt_id = :postId";
            $stmt = $this->sqlConnect->prepare($sql);
            $stmt->execute([':postId' => $postId]);
        }

        return $this->commentModel->responseJson();

    }
    
    public function fetchPostComments($postId, $userId) {
        $sql = "
            SELECT
                c.lt_id AS ltId,
                c.post_id AS postId,
                c.user_id AS userId,
                c.parent_id AS parentId,
                p.slug AS postSlug,
                c.body,
                c.status,
                c.like_count AS likes,
                c.created_at AS createdAt,
                COALESCE(NULLIF(u.fullname, ''), u.username) AS author, 
                u.avatar,
                EXISTS (
                    SELECT 1 
                    FROM tb_blog_comment_like cl 
                    WHERE cl.comment_id = c.lt_id 
                      AND cl.user_id = :current_user_id
                ) AS isLikedByUser
            
            FROM tb_blog_comment AS c
            LEFT JOIN tb_blog_user u ON c.user_id = u.user_id
            LEFT JOIN tb_blog_post p ON p.lt_id = c.post_id
            WHERE c.post_id = :post_id
              AND c.status = 'approved'
            ORDER BY c.created_at DESC;
        ";
        
        // Prepare & Execute
        $stmt = $this->sqlConnect->prepare($sql);
        $stmt->bindValue(':post_id', $postId, PDO::PARAM_INT);
        $stmt->bindValue(':current_user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        
        // Fetch as Associative Array
        $flatComments = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // If no comments, return an empty array early
        if (empty($flatComments)) {
            return [];
        }
        
        // Pass the flat array to the tree builder and return the nested result
        return $this->buildTree($flatComments);
    }
    
    public function fetchUserComments($userId){
        $sql = "
            SELECT
                c.lt_id AS ltId,
                c.post_id AS postId,
                c.parent_id AS parentId,
                p.slug AS postSlug,
                p.title AS postTitle,
                c.body,
                c.status,
                c.like_count AS likes,
                c.created_at AS createdAt
            FROM tb_blog_comment AS c
            LEFT JOIN tb_blog_post p ON p.lt_id = c.post_id
            WHERE c.user_id = :current_user_id
              AND c.status = 'approved'
            ORDER BY c.created_at DESC;
        ";
        
        // Prepare & Execute
        $stmt = $this->sqlConnect->prepare($sql);
        $stmt->bindValue(':current_user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        
        // Fetch as Associative Array
        $flatComments = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // If no comments, return an empty array early
        if (empty($flatComments)) {
            return [];
        }
        
        // Pass the flat array to the tree builder and return the nested result
        return $this->buildTree($flatComments);
    }
    
    public function buildTree(array $comments) {
        $branch = [];
        $indexed = [];
        
        // 1. Indexing
        // We create a lookup array where the key is the comment's lt_id
        foreach ($comments as $comment) {
            // Initialize the empty replies array using array syntax
            $comment['replies'] = []; 
            $indexed[$comment['ltId']] = $comment; 
        }
        
        // 2. Linking (The Magic part)
        // We iterate over the referenced indexed array. The '&' is crucial here!
        foreach ($indexed as $id => &$node) {
            $pId = $node['parentId']; // Use the actual DB column name
            
            // If this comment has a parent, and that parent exists in our index
            if ($pId !== null && $pId != 0 && isset($indexed[$pId])) {
                // Add this node (by reference) to its parent's replies array
                $indexed[$pId]['replies'][] = &$node;
            } else {
                // If it has no parent, it's a top-level comment. Add it to the main branch.
                $branch[] = &$node;
            }
        }
        
        return $branch;
    }
    
    public function commentsLike() {
        $userId = $this->userId;
        $commentId = trim($this->request->commentId);
        $ltId = $this->ltId;
        $createdAt = $this->ltNow;
        try {
            $this->sqlConnect->beginTransaction();
    
            // 1. Check if the user already liked this comment
            $checkSql = "SELECT 1 FROM tb_blog_comment_like WHERE comment_id = :comment_id AND user_id = :user_id";
            $stmtCheck = $this->sqlConnect->prepare($checkSql);
            $stmtCheck->execute([':comment_id' => $commentId, ':user_id' => $userId]);
            $hasLiked = $stmtCheck->fetchColumn();
    
            if ($hasLiked) {
                // UNLIKE: Remove the record and decrement the count
                $delSql = "DELETE FROM tb_blog_comment_like WHERE comment_id = :comment_id AND user_id = :user_id";
                $this->sqlConnect->prepare($delSql)->execute([':comment_id' => $commentId, ':user_id' => $userId]);
    
                $updateSql = "UPDATE tb_blog_comment SET like_count = GREATEST(0, like_count - 1) WHERE lt_id = :comment_id";
                $this->sqlConnect->prepare($updateSql)->execute([':comment_id' => $commentId]);
                
                $action = 'unliked';
            } else {
                // LIKE: Insert the record and increment the count
                $insSql = "INSERT INTO tb_blog_comment_like (comment_id, user_id, lt_id, created_at) VALUES (:comment_id, :user_id, :ltId, :createdAt)";
                $this->sqlConnect->prepare($insSql)->execute([':comment_id' => $commentId, ':user_id' => $userId, ':ltId' => $ltId, ':createdAt' => $createdAt]);
    
                $updateSql = "UPDATE tb_blog_comment SET like_count = like_count + 1 WHERE lt_id = :comment_id";
                $this->sqlConnect->prepare($updateSql)->execute([':comment_id' => $commentId]);
                
                $action = 'liked';
            }
    
            $this->sqlConnect->commit();
            return LtResponse::json('success', 201, 200, $action);

        } catch (Exception $e) {
            $this->sqlConnect->rollBack();
            return LtResponse::json('success', 201, 200, 'Database error occurred');
        }
    }
    
    public function destroy() {
        $ltId = $this->request->ltId;
        // 1. Check if post exists
        $exist = $this->commentModel->select()->where('ltId', '=', $ltId)->get();
        if (count($exist) < 1) {
            return LtResponse::json('Comment not found', 3004, 100);
        }
        
        $this->commentModel->delete('ltId','=',$ltId);
    
        return $this->commentModel->responseJson();
    }
    
    public function update(){
        $ltId = $this->request->ltId;
        $status = $this->request->status;
        
        $exist = $this->commentModel->select()->where('ltId', '=', $ltId)->get();
        if (count($exist) < 1) {
            return LtResponse::json('Comment not found', 3004, 100);
        }

        $this->commentModel->status = $status;
        $this->commentModel->updatedAt = $this->ltNow;

        $this->commentModel->update('ltId','=',$ltId);
        return $this->commentModel->responseJson();
    }
    
}

