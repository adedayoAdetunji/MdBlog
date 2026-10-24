<?php
namespace Lt\Modules\MdBlog\Services;

use Lt\Modules\MdBlog\Models\TbBlogPost;
use Lt\Modules\MdLt\Services\LtResponse;
use Lt\Modules\MdLt\Services\LtRequest;
use Lt\Modules\MdLt\Services\LtSession;
use Lt\Modules\MdLt\Services\LtFile;

use Lt\Modules\MdBlog\Services\TbBlogCommentService;
use Lt\Modules\MdBlog\Services\TbBlogSettingService;
use Lt\Modules\MdBlog\Models\TbBlogMedia;

use DbConnect;
use PDO;
use DOMDocument;

class TbBlogPostService
{
    protected $postModel;
    protected $request;
    protected $ltId;
    protected $userId;
    protected $ltNow;
    protected $sqlConnect;

    public function __construct(){
        $this->postModel = new TbBlogPost();
        $this->request = new LtRequest;
        $this->ltNow = date('Y-m-d H:i:s');
        $this->ltId = ltId();
        $this->userId = LtSession::get('ltUid');
        $this->sqlConnect = \DbConnect::dbDriver();
    }
    
    public function categoryPostMenu() {
        $settingServices = new TbBlogSettingService();
        $defaultMenuSetting = $settingServices->defaultSettings(null);
    
        $parentMenuName = $defaultMenuSetting->settingValue ?? 'Blog';
        $parentMenuId   = $defaultMenuSetting->ltId ?? $defaultMenuSetting->LtId ?? 0;
    
        // 1. Fetch categories marked for menu display
        $sql = "SELECT lt_id AS ltId, category_name AS title 
                FROM tb_blog_category 
                WHERE show_on_menu = :showOnMenu";
        
        $stmt = $this->sqlConnect->prepare($sql);
        $stmt->execute([':showOnMenu' => 1]);
        $categories = $stmt->fetchAll(PDO::FETCH_OBJ);
    
        // 2. Query posts using raw snake_case columns
        // The ORM will map:
        // lt_id       -> $post->LtId
        $posts = $this->postModel
            ->select('lt_id, category_id, title')
            ->where('showOnMenu', '=', 1)
            ->get();
    
        // 3. Group posts by CategoryId in O(N) time
        $postsByCategory = [];
        foreach ($posts as $post) {
            $catId = $post->categoryId ?? null;
            if ($catId !== null) {
                $postsByCategory[$catId][] = [
                    'title' => $post->title,
                    'ltId'  => $post->ltId
                ];
            }
        }
    
        // 4. Attach grouped children to each category
        foreach ($categories as &$cat) {
            $cat->children = $postsByCategory[$cat->ltId] ?? [];
        }
        unset($cat);
    
        // 5. Structure the nested menu hierarchy
        $menu = [
            'title'    => $parentMenuName,
            'ltId'     => $parentMenuId,
            'children' => $categories
        ];
    
        return LtResponse::json('fetched', 201, 200, $menu);
    }
    

    public function index(){
        $status = $this->request->status;
        $authorId = $this->request->id;

        $hasWhere = false;
        $query = $this->postModel->select();

        if($authorId !== null && $authorId !== ''){
            $query->where('authorId','=', $authorId);
            $hasWhere = true;
        }
        
        if($status !== null && $status !== ''){
            if($hasWhere){
                $query->andWhere('status','=', $status);
            }else{
                $query->where('status','=', $status);
            }
        }
        
        $posts = $query->get();
        if(empty($posts)){
            return LtResponse::json('No Post found', 101, 100);
        }
        
        $groupedMedia = $this->getPostMedia($posts);
        // 6. Merge the media back into the posts
        foreach ($posts as &$post) {
            $postId = $post->ltId;
            // Attach the grouped data, or set defaults if no media exists
            $post->media = $groupedMedia[$postId]['media'] ?? [];
        }
        
        return LtResponse::json('success', 201, 200, $posts);
        
    }
    
    public function getAuthorPost($authorId){
        $query = $this->postModel->select()->where('authorId','=', $authorId);
        
        $posts = $query->get();
        if(empty($posts)){
            return LtResponse::json('No Post found', 101, 100);
        }
        
        $groupedMedia = $this->getPostMedia($posts);
        // 6. Merge the media back into the posts
        foreach ($posts as &$post) {
            $postId = $post->ltId;
            // Attach the grouped data, or set defaults if no media exists
            $post->media = $groupedMedia[$postId]['media'] ?? [];
        }
        
        return LtResponse::json('success', 201, 200, $posts);
    }
    
    private function getPostMedia($posts){
        $postIds = [];
        $inClause= '';
         // Detect if $posts is a single record or multiple
            if (isset($posts['ltId'])) {
                // Single record
                $postIds = [$posts['ltId']];
                $inClause = '?';
            } else {
                // Multiple records
                $postIds = array_column($posts ?? [], 'ltId');
                $inClause = implode(',', array_fill(0, count($postIds), '?'));
            }
            
        
            // Fetch all media for these posts
            $mediaSql = "
                SELECT lt_id AS media_id, post_id, filename, original_name, mime_type, 
                       file_size, storage_path, media_category, alt_text
                FROM tb_blog_media
                WHERE post_id IN ($inClause)
            ";
            
        $mediaStmt = $this->sqlConnect->prepare($mediaSql);
        $mediaStmt->execute($postIds);
        $allMedia = $mediaStmt->fetchAll(PDO::FETCH_ASSOC);
        
        // 5. Organize the media by post_id so it is easy to map
        $groupedMedia = [];
        foreach ($allMedia as $media) {
            $postId = $media['post_id'];
            $category = $media['media_category'];
            $newMedia = [
                'postId' => $media['post_id'],
                'mediaCategory' => $media['media_category'],
                'mediaId' => $media['media_id'],
                'originalName' => $media['original_name'],
                'mimeType' => $media['mime_type'],
                'fileSize' => $media['file_size'],
                'storagePath' => $media['storage_path'],
                'altText' => $media['alt_text'],
                'filename' => $media['filename'],
                ];
                
            $groupedMedia[$postId]['media'][] = $newMedia;

        }
        
        return $groupedMedia;
    }
    
    public function dashboardMetrics() {
        // 1. Total Metrics (Cards)
        $statsSql = "
            SELECT 
                (SELECT COALESCE(SUM(view_count), 0) FROM tb_blog_post WHERE status = 'published') AS totalViews,
                (SELECT COUNT(lt_id) FROM tb_blog_post) AS totalArticles,
                (SELECT COUNT(lt_id) FROM tb_blog_comment) AS totalComments,
                (SELECT COUNT(lt_id) FROM tb_blog_category) AS totalCategories,
                (SELECT COUNT(lt_id) FROM tb_blog_tag) AS totalTags,
                (SELECT COUNT(lt_id) FROM tb_blog_comment WHERE status = 'pending') AS pendingComments,
                (SELECT COUNT(lt_id) FROM tb_blog_report WHERE status = 'pending') AS pendingReports
        ";
        $stats = $this->sqlConnect->query($statsSql)->fetch(PDO::FETCH_ASSOC);
    
        // 2. Category Distribution
        $catSql = "
            SELECT 
                c.category_name AS name, 
                COUNT(p.lt_id) AS postCount,
                COALESCE(SUM(p.view_count), 0) AS viewCount
            FROM tb_blog_category c
            LEFT JOIN tb_blog_post p ON c.lt_id = p.category_id
            GROUP BY c.lt_id, c.category_name
            ORDER BY viewCount DESC
            LIMIT 5
        ";
        $topCategories = $this->sqlConnect->query($catSql)->fetchAll(PDO::FETCH_ASSOC);
        
        $trendSql = "
            SELECT
                DATE_FORMAT(publish_date, '%b %d') AS label,
                postCount,
                views
            FROM (
                SELECT
                    DATE(published_at) AS publish_date,
                    COUNT(lt_id) AS postCount,
                    COALESCE(SUM(view_count), 0) AS views
                FROM tb_blog_post
                WHERE status = 'published'
                  AND published_at >= DATE_SUB(NOW(), INTERVAL 120 DAY)
                GROUP BY DATE(published_at)
            ) AS daily_metrics
            ORDER BY publish_date ASC
        ";
        
        $trendData = $this->sqlConnect
            ->query($trendSql)
            ->fetchAll(PDO::FETCH_ASSOC);
    
        $response = [
            'cards' => [
                'totalViews'      => (int)$stats['totalViews'],
                'totalArticles'   => (int)$stats['totalArticles'],
                'totalComments'   => (int)$stats['totalComments'],
                'totalTaxonomies' => (int)$stats['totalCategories'] + (int)$stats['totalTags'],
                'pendingComments' => (int)$stats['pendingComments'],
                'pendingReports'  => (int)$stats['pendingReports']
            ],
            'topCategories' => $topCategories,
            'trend'         => $trendData
        ];
    
        return LtResponse::json('Dashboard metrics retrieved', 200, 200, $response);
    }
    
    
    public function filterPosts() {
    $request = new LtRequest;

    // 1. Pagination Math
    $page = max((int)($request->page ?? 1), 1);
    $limit = max((int)($request->limit ?? 12), 1);
    $offset = ($page - 1) * $limit;

    // 2. Input Sanitization
    // If category is '1' (All) or empty/0, treat as all categories
    $rawCategory = $request->category ?? 0;
    $categoryId = ($rawCategory == 1) ? 0 : (int)$rawCategory;
    $q = trim($request->q ?? '');

    // 3. Sorting Whitelist
    $sort = $request->sort ?? 'latest';
    $sortOptions = [
        'latest'     => 'p.published_at DESC',
        'oldest'     => 'p.published_at ASC',
        'popular'    => 'p.view_count DESC, p.published_at DESC',
        'liked'      => 'p.like_count DESC, p.published_at DESC',
        'comments'   => 'p.comment_count DESC, p.published_at DESC',
        'title_asc'  => 'p.title ASC',
        'title_desc' => 'p.title DESC'
    ];
    $orderBy = $sortOptions[$sort] ?? $sortOptions['latest'];

    // 4. Dynamic Where Clauses & Parameter Preparation
    $whereConditions = ["p.status = 'published'"];
    $bindParams = [];

    if ($categoryId > 0) {
        $whereConditions[] = "p.category_id = :categoryId";
        $bindParams[':categoryId'] = [$categoryId, PDO::PARAM_INT];
    }

    if ($q !== '') {
        $whereConditions[] = "(p.title LIKE :searchTitle OR p.excerpt LIKE :searchExcerpt)";
        $searchWildcard = "%{$q}%";
        $bindParams[':searchTitle'] = [$searchWildcard, PDO::PARAM_STR];
        $bindParams[':searchExcerpt'] = [$searchWildcard, PDO::PARAM_STR];
    }

    $whereSql = implode(' AND ', $whereConditions);

    // 5. Total Count Query
    $countSql = "
        SELECT COUNT(p.lt_id)
        FROM tb_blog_post p
        WHERE {$whereSql}
    ";

    $countStmt = $this->sqlConnect->prepare($countSql);
    foreach ($bindParams as $key => [$val, $type]) {
        $countStmt->bindValue($key, $val, $type);
    }
    $countStmt->execute();

    $totalItems = (int) $countStmt->fetchColumn();
    $totalPages = (int) ceil($totalItems / $limit);
    $hasMore = $page < $totalPages;

    // 6. Data Query
    $sql = "
        SELECT 
            p.lt_id AS ltId, 
            p.title, 
            p.slug, 
            p.excerpt, 
            p.reading_time AS readingTime, 
            p.category_id AS categoryId, 
            c.category_name AS category, 
            p.published_at AS date, 
            p.like_count AS likes, 
            p.view_count AS views, 
            p.author_id AS authorId, 
            COALESCE(NULLIF(u.fullname, ''), u.username) AS author, 
            u.avatar,
            p.is_featured AS isFeatured
        FROM tb_blog_post p
        LEFT JOIN tb_blog_category c ON p.category_id = c.lt_id
        LEFT JOIN tb_blog_user u ON p.author_id = u.user_id
        WHERE {$whereSql}
        ORDER BY {$orderBy}
        LIMIT :offset, :limit
    ";

    $stmt = $this->sqlConnect->prepare($sql);

    // Bind filter parameters
    foreach ($bindParams as $key => [$val, $type]) {
        $stmt->bindValue($key, $val, $type);
    }

    // Bind pagination limits
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();

    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 7. Group & Merge Media
    if (!empty($posts)) {
        $groupedMedia = $this->getPostMedia($posts);
        foreach ($posts as &$post) {
            $postId = $post['ltId'];
            $post['media'] = $groupedMedia[$postId]['media'] ?? [];
        }
        unset($post); // Detach pointer reference
    }

    // 8. Output Response
    $payload = [
        'data' => $posts,
        'pagination' => [
            'totalItems'   => $totalItems,
            'totalPages'   => $totalPages,
            'currentPage'  => $page,
            'itemsPerPage' => $limit,
            'hasMore'      => $hasMore
        ]
    ];

    if (empty($posts)) {
        return LtResponse::json('No posts found matching criteria', 200, 200, $payload);
    }

    return LtResponse::json('Posts retrieved successfully', 200, 200, $payload);
}
    
    // Popular post
    public function widget() {
    $request = new LtRequest;
    $limit = max((int)($request->limit ?: 12), 1);
    $widgetType = trim($request->type ?? 'latest');
    $postId = trim($request->postId ?? '');
    $categoryId = trim($request->categoryId ?? '');

    $params = [];

    // 1. Build Query Conditions & Sorting
    switch ($widgetType) {
        case 'popular':
            $where = "p.status = 'published'";
            $order = "p.view_count DESC, p.published_at DESC";
            break;

        case 'trending':
            // Weights views, likes, and comments from the past 14 days
            $where = "p.status = 'published' AND p.published_at >= DATE_SUB(NOW(), INTERVAL 14 DAY)";
            $order = "((p.view_count / 100) + (p.like_count * 5) + (p.comment_count * 20)) DESC, p.published_at DESC";
            break;

        case 'related':
            $where = "p.status = 'published' AND p.category_id = :category AND p.lt_id <> :postId";
            $order = "p.published_at DESC";
            $params[':category'] = $categoryId;
            $params[':postId'] = $postId;
            break;

        case 'latest':
        default:
            $where = "p.status = 'published'";
            $order = "p.published_at DESC";
            break;
    }

    // 2. The Main Query (Removed trailing comma after u.avatar)
    $sql = "
        SELECT 
            p.lt_id AS ltId, 
            p.title, 
            p.slug, 
            p.excerpt, 
            p.reading_time AS readingTime, 
            p.category_id AS categoryId, 
            c.category_name AS category, 
            p.published_at AS date, 
            p.like_count AS likes, 
            p.view_count AS views, 
            p.author_id AS authorId, 
            COALESCE(NULLIF(u.fullname, ''), u.username) AS author, 
            u.avatar
        FROM tb_blog_post p
        JOIN tb_blog_category c ON p.category_id = c.lt_id
        LEFT JOIN tb_blog_user u ON p.author_id = u.user_id
        WHERE {$where}
        ORDER BY {$order}
        LIMIT :limit
    ";

    $stmtWidget = $this->sqlConnect->prepare($sql);
    $stmtWidget->bindValue(':limit', $limit, PDO::PARAM_INT);

    foreach ($params as $placeholder => $value) {
        $stmtWidget->bindValue($placeholder, $value);
    }

    $stmtWidget->execute();
    $posts = $stmtWidget->fetchAll(PDO::FETCH_ASSOC);

    // 3. Fallback: If trending or related yields no results, fallback to latest posts
    if (empty($posts) && in_array($widgetType, ['trending', 'related'])) {
        $fallbackSql = "
            SELECT 
                p.lt_id AS ltId, p.title, p.slug, p.excerpt, p.reading_time AS readingTime, 
                p.category_id AS categoryId, c.category_name AS category, p.published_at AS date, 
                p.like_count AS likes, p.view_count AS views, p.author_id AS authorId, 
                COALESCE(NULLIF(u.fullname, ''), u.username) AS author, u.avatar
            FROM tb_blog_post p
            JOIN tb_blog_category c ON p.category_id = c.lt_id
            LEFT JOIN tb_blog_user u ON p.author_id = u.user_id
            WHERE p.status = 'published' AND (:excludeId = '' OR p.lt_id <> :excludeId)
            ORDER BY p.published_at DESC
            LIMIT :limit
        ";
        $fallbackStmt = $this->sqlConnect->prepare($fallbackSql);
        $fallbackStmt->bindValue(':excludeId', $postId, PDO::PARAM_STR);
        $fallbackStmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $fallbackStmt->execute();
        $posts = $fallbackStmt->fetchAll(PDO::FETCH_ASSOC);
    }

    if (empty($posts)) {
        return LtResponse::json('No posts found', 200, 200, []);
    }

    // 4. Attach Media
    $groupedMedia = $this->getPostMedia($posts);

    foreach ($posts as &$post) {
        $postIdKey = $post['ltId'];
        $post['media'] = $groupedMedia[$postIdKey]['media'] ?? [];
    }
    unset($post);

    return LtResponse::json('success', 200, 200, $posts);
}
    
    // public function widget() {
    //     $request = new LtRequest;
    //     $limit = $request->limit ?: 12;
    //     $widgetType = trim($request->type);
    //     $postId = trim($request->postId);
    //     $categoryId = trim($request->categoryId);
    //     // 1. Initialize an empty array to hold our dynamic bindings
    //     $params = [];
        
    //     // 2. Build the query parts and collect parameters based on the widget type
    //     switch ($widgetType) {
    //         case 'popular':
    //             $where = "p.status='published'";
    //             $order = "p.view_count DESC, p.published_at DESC";
    //             break;
        
    //         case 'latest':
    //             $where = "p.status='published'";
    //             $order = "p.published_at DESC";
    //             break;
        
    //         case 'trending':
    //             $where = "p.status='published' AND p.published_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
    //             $order = "((p.view_count/100) + (p.like_count*5) + (p.comment_count*20)) DESC";
    //             break;
        
    //         case 'related':
    //             $where = "p.status='published' AND p.category_id = :category AND p.lt_id <> :postId";
    //             $order = "p.published_at DESC";
                
    //             // Add the required parameters to our array
    //             $params[':category'] = $categoryId;
    //             $params[':postId'] = $postId;
    //             break;
        
    //         default:
    //             $where = "p.status='published'";
    //             $order = "p.published_at DESC";
    //     }
        
    //     // 3. The Main Query
    //     $sql = "
    //         SELECT p.lt_id AS ltId, p.title, p.slug, p.excerpt, p.reading_time AS readingTime, p.category_id, c.category_name AS category, p.published_at AS date, p.like_count AS likes, p.view_count AS views, p.author_id, COALESCE(NULLIF(u.fullname, ''), u.username) AS author, 
    //         u.avatar,  
    //         FROM tb_blog_post p
    //         JOIN 
    //             tb_blog_category c ON p.category_id = c.lt_id
    //         LEFT JOIN tb_blog_user u ON p.author_id = u.user_id
    //         WHERE {$where}
    //         ORDER BY {$order}
    //         LIMIT :limit
    //     ";
        
    //     $stmtWidget = $this->sqlConnect->prepare($sql);
        
    //     // 4. Bind the mandatory parameters
    //     $stmtWidget->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        
    //     // 5. Dynamically bind any extra parameters collected in the switch statement
    //     foreach ($params as $placeholder => $value) {
    //         // PDO handles standard strings/ints well natively
    //         $stmtWidget->bindValue($placeholder, $value);
    //     }
        
    //     // 6. Execute safely
    //     $stmtWidget->execute();
    //     $posts = $stmtWidget->fetchAll(PDO::FETCH_ASSOC);
        
    //     $groupedMedia = $this->getPostMedia($posts);
        
    //     // 6. Merge the media back into the posts
    //     foreach ($posts as &$post) {
    //         $postId = $post['ltId'];
    //         // Attach the grouped data, or set defaults if no media exists
    //         $post['media'] = $groupedMedia[$postId]['media'] ?? [];
    //     }
        
    //     if(empty($posts)) return LtResponse::json('No post Found', 101, 100, $posts);
    //     return LtResponse::json('success', 201, 200, $posts);
    // }
    
    public function postSlug() {
        $request = new LtRequest;
        $commentsService = new TbBlogCommentService;
        $slug = trim($request->slug ?? '');
    
        if (empty($slug)) {
            return LtResponse::json('Slug parameter is required', 101, 100);
        }
        
        $sql = "
            SELECT 
                p.lt_id AS ltId, 
                p.title, 
                p.slug, 
                p.excerpt, 
                p.content,
                p.reading_time AS readingTime,
                p.category_id AS categoryId, 
                c.category_name AS category, 
                p.published_at AS date, 
                p.like_count AS likes, 
                p.view_count AS views, 
                p.author_id AS authorId,
                p.created_at AS createdAt,
                COALESCE(NULLIF(u.fullname, ''), u.username) AS author, 
                u.avatar,u.linkedin,u.twitter,u.facebook,u.bio,
                
                -- User reaction subquery
                (
                    SELECT reaction_type 
                    FROM tb_blog_post_reaction 
                    WHERE post_id = p.lt_id 
                      AND user_id = :current_user_id
                    LIMIT 1
                ) AS userReaction
                
            FROM tb_blog_post p
            LEFT JOIN tb_blog_category c ON p.category_id = c.lt_id
            LEFT JOIN tb_blog_user u ON p.author_id = u.user_id
            WHERE p.slug = :slug
            LIMIT 1
        ";
    
        $stmtSlug = $this->sqlConnect->prepare($sql);
        $stmtSlug->bindValue(':slug', $slug);
        $stmtSlug->bindValue(':current_user_id', $this->userId ?? 0);
        $stmtSlug->execute();
    
        // Fetch single record as an associative array
        $post = $stmtSlug->fetch(PDO::FETCH_ASSOC);
    
        if (!$post) {
            return LtResponse::json('No post found', 101, 100);
        }
    
        $postId = $post['ltId'];
    
        // 1. Fetch & attach media attachments
        // Passing [$post] to maintain compatibility with your existing getPostMedia batch helper
        $groupedMedia = $this->getPostMedia([$post]);
        $post['media'] = $groupedMedia[$postId]['media'] ?? [];
    
        // 2. Fetch & attach comments
        $post['comments'] = $commentsService->fetchPostComments($postId, $this->userId ?? 0) ?? [];
    
        // 3. Return clean JSON response
        return LtResponse::json('success', 200, 200, $post);
    }
    
    
    public function store() {
        $this->postModel->processRequest();
        $postId = $this->ltId;
        
        $title = trim((string)$this->postModel->title);
        $slug = trim((string)$this->postModel->slug);
        $allowedComment = $this->postModel->allowedComment;
        $coAuthors = $this->request->coAuthors ?? null;
        $authorId = $this->request->author ?? $this->userId;

        $additionalMedia = $_FILES['additionalMedia'] ?? null;
        
        $path = "storage/uploads/blogs/post_{$postId}";
        $fileObj = [];
        $dataMedia = [];
         
        // 1. Validation
        if ($title === '') {
            return LtResponse::json('Post Title is required', 3004, 100);
        }
        
        // FIX: Check if count is greater than 0 (meaning it already exists)
        $exist = $this->postModel->select()->where('slug','=', $slug)->get();
        if (count($exist) > 0) {
            return LtResponse::json('Post already exists', 3004, 100);
        }
         
        // 3. Process Additional Media
        if (isset($additionalMedia) && is_array($additionalMedia['name'])) {
            $fileCount = count($additionalMedia['name']);
            
            for ($i = 0; $i < $fileCount; $i++) {
                // Skip empty slots
                if ($additionalMedia['error'][$i] === UPLOAD_ERR_NO_FILE || empty($additionalMedia['name'][$i])) {
                    continue;
                }
        
                if ($additionalMedia['error'][$i] === UPLOAD_ERR_OK) {
                    $tmpName = $additionalMedia['tmp_name'][$i];
                    $originalName = $additionalMedia['name'][$i];
                    $fileSize = $additionalMedia['size'][$i];
                    
                    // Detect MIME type safely
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $mimeType = finfo_file($finfo, $tmpName);
                    finfo_close($finfo);
                    
                    // Generate unique filename
                    $extension = pathinfo($originalName, PATHINFO_EXTENSION);
                    $newFilename = uniqid('media_', true) . '.' . $extension; 
                    $filePath = $path . '/' . $newFilename;
                    
                    $dataMedia[] = [
                        'postId' => $postId,
                        'uploadedBy' => $this->userId,
                        'filename' => $newFilename,
                        'originalName' => $originalName,
                        'mimeType' => $mimeType,
                        'fileSize' => $fileSize,
                        'altText' => "",
                        'storagePath' => $filePath,
                        'mediaCategory' => 'gallery'
                    ];
                    
                    // Mimic $_FILES structure for LtFile helper
                    $fileObj[] = [
                        "name" => $newFilename, // FIX: Pass the NEW secure filename here, not the original!
                        "full_path" => $newFilename,
                        "type" => $mimeType, // Use the secure mime type
                        "tmp_name" => $tmpName,
                        "error" => $additionalMedia['error'][$i],
                        "size" => $fileSize
                    ];
                
                   
                }
            }
        }
        
         
        
        // 4. Process Content & Save Post
        $processedContent = $this->processContentBeforeSave($this->postModel->content, $postId);
        
        $this->postModel->ltId = $postId;
        $this->postModel->coAuthors = $coAuthors;
        $this->postModel->authorId = $authorId;
        $this->postModel->content = $processedContent;
        $this->postModel->allowedComments = $allowedComment == 'on' ? 1 : 0;
        $this->postModel->createdAt = $this->ltNow;
        $this->postModel->updatedAt = $this->ltNow;
        
        $this->postModel->insert();
        
        if($this->postModel->responseCategory == 200){
            // Storing Media
            if(!empty($dataMedia)){
                foreach($dataMedia as $media){
                    
                    $blogMediaModel = new TbBlogMedia();
                    foreach ($media as $key => $value) {
                        
                        $blogMediaModel->$key = $value;
                        
                    } 
                        $blogMediaModel->ltId = ltId();
                        $blogMediaModel->updatedAt = $this->ltNow;
                        $blogMediaModel->createdAt = $this->ltNow;
                     // Store in DB
                        $blogMediaModel->insert();
                }
            }
            
            $overwrite = true;
            if (!is_dir($path)) {
                // Directory does not exist, so create it
                mkdir($path, 0755, true);
            }
            
            // Upload Additional Media
            if (!empty($fileObj)) {
                foreach ($fileObj as $customFileArray) {
                    LtFile::path($path)->overwrite($overwrite)->uploadFile($customFileArray);
                }
            }
            return $this->postModel->responseJson();
        }else{
            return $this->postModel->responseJson();
        }
        
    
    }
    
    public function update($postId) {
        // 1. Fetch existing post & validate
        $exist = $this->postModel->select()->where('ltId','=', $postId)->get();
        if(count($exist) < 1){
            return LtResponse::json('Post not found', 3004, 100);
        }
        
        // Capture the old content for your cleanup function later
        $oldContentFromDB = $exist[0]->content ?? ''; 
    
        $this->postModel->processRequest();
        
        // 2. Define all required variables safely
        $title = trim((string)$this->postModel->title);
        if ($title === '') {
            return LtResponse::json('Post Title is required', 3004, 100);
        }
        
        $processedContent = $this->processContentBeforeSave($this->postModel->content, $postId);
        $coAuthors = $this->request->coAuthors ?? null;
        $allowedComment = $this->postModel->allowedComment ?? null;
        $featuredImageAlt = $this->postModel->featuredImageAlt ?? '';
        
        // Safely decode the deleted media IDs (assuming it comes as a JSON string from frontend)
        $deleteMediaIds = json_decode($this->request->deletedMedia ?? '[]', true);
 
        $additionalMedia = $_FILES['additionalMedia'] ?? null;
        $path = "storage/uploads/blogs/post_{$postId}";
        
        $fileObj = [];
        $dataMedia = [];

        // 4. Process Additional Media
        if (isset($additionalMedia) && is_array($additionalMedia['name'])) {
            $fileCount = count($additionalMedia['name']);
            
            for ($i = 0; $i < $fileCount; $i++) {
                if ($additionalMedia['error'][$i] === UPLOAD_ERR_NO_FILE || empty($additionalMedia['name'][$i])) {
                    continue;
                }
        
                if ($additionalMedia['error'][$i] === UPLOAD_ERR_OK) {
                    $tmpName = $additionalMedia['tmp_name'][$i];
                    $originalName = $additionalMedia['name'][$i];
                    $fileSize = $additionalMedia['size'][$i];
                    
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $mimeType = finfo_file($finfo, $tmpName);
                    finfo_close($finfo);
                    
                    $extension = pathinfo($originalName, PATHINFO_EXTENSION);
                    $newFilename = uniqid('media_', true) . '.' . $extension; 
                    $filePath = $path . '/' . $newFilename;
                    
                    $dataMedia[] = [
                        'postId' => $postId,
                        'uploadedBy' => $this->userId,
                        'filename' => $newFilename,
                        'originalName' => $originalName,
                        'mimeType' => $mimeType,
                        'fileSize' => $fileSize,
                        'altText' => "",
                        'storagePath' => $filePath,
                        'mediaCategory' => 'gallery'
                    ];
                    
                    $fileObj[] = [
                        "name" => $newFilename, 
                        "full_path" => $newFilename,
                        "type" => $mimeType, 
                        "tmp_name" => $tmpName,
                        "error" => $additionalMedia['error'][$i],
                        "size" => $fileSize
                    ];
                }
            }
        }
        
        

            // A. Update the Post Table
            $this->postModel->updatedAt = $this->ltNow;
            $this->postModel->coAuthors = $coAuthors;
            $this->postModel->content = $processedContent;
            $this->postModel->allowedComments = $allowedComment == 'on' ? 1 : 0;
            $this->postModel->update('ltId','=', $postId);
    
            // B. Handle Deleted Media (from the Frontend Remove Buttons)
            if (!empty($deleteMediaIds) && is_array($deleteMediaIds)) {
                $placeholders = implode(',', array_fill(0, count($deleteMediaIds), '?'));
                
                // Fetch paths to delete physical files
                $fetchStmt = $this->sqlConnect->prepare("SELECT storage_path FROM tb_blog_media WHERE lt_id IN ($placeholders) AND post_id = ?");
                $params = $deleteMediaIds;
                $params[] = $postId;
                $fetchStmt->execute($params);
                $filesToDelete = $fetchStmt->fetchAll(PDO::FETCH_COLUMN);
                
                foreach ($filesToDelete as $relativePath) {
                    if (file_exists($relativePath) && is_file($relativePath)) {
                        unlink($relativePath);
                    }
                }
                
                // Delete the rows from the database
                $deleteStmt = $this->sqlConnect->prepare("DELETE FROM tb_blog_media WHERE lt_id IN ($placeholders) AND post_id = ?");
                $deleteStmt->execute($params);
            }
    
            // C. Insert New Media Records
            if (!empty($dataMedia)) {
                foreach ($dataMedia as $mediaItem) {
                    $blogMediaModel = new TbBlogMedia();
                    foreach ($mediaItem as $key => $value) {
                        $blogMediaModel->$key = $value;
                    }
                    
                    $blogMediaModel->ltId = ltId();
                    $blogMediaModel->updatedAt = $this->ltNow;
                    $blogMediaModel->createdAt = $this->ltNow;
                    $blogMediaModel->insert();
                }
            }


            // Additional Media Uploads
            if (!empty($fileObj)) {
                
                $overwrite = true;
                if (!is_dir($path)) {
                    // Directory does not exist, so create it
                    mkdir($path, 0755, true);
                }
                foreach ($fileObj as $customFileArray) {
                     LtFile::path($path)->overwrite($overwrite)->uploadFile($customFileArray);
                }
            }
        
            // E. Quill Embedded Images Cleanup
            $this->cleanupUnusedImages($oldContentFromDB, $processedContent);
    
            return $this->postModel->responseJson();
    
    }

    public function toggleActive($ltId){
        $row = $this->postModel->select()->where('ltId','=',$ltId)->get();
        if(count($row) < 1){
            return LtResponse::json('Category not found', 3004, 100);
        }

        $current = $row[0]->status;
        $newVal = ($current == 'isActive') ? 'isNotActive' : 'isActive';

        $this->postModel->status = $newVal;
        $this->postModel->updatedAt = $this->ltNow;
        $this->postModel->update('ltId','=',$ltId);

        return LtResponse::json('Updated', 201, 200, ['ltId'=>$ltId,'isActive'=>$newVal]);
    }

    public function destroy($ltId) {
        // 1. Check if post exists
        $exist = $this->postModel->select()->where('ltId', '=', $ltId)->get();
        if (count($exist) < 1) {
            return LtResponse::json('Post not found', 3004, 100);
        }
    
        // 2. Define the main directory path
        $path = "storage/uploads/blogs/post_{$ltId}";
    
        // 3. Delete the records from the database
        $this->postModel->delete('ltId', '=', $ltId);
        
        //delete from the bloog media table
        $deleteStmt = $this->sqlConnect->prepare("DELETE FROM tb_blog_media WHERE post_id = :postId");
        $deleteStmt->execute([':postId' => $ltId]);
    
        // 4. Delete the physical folder and everything inside it
        if (is_dir($path)) {
            $this->deleteDirectory($path);
        }
        
        // 5. Run Quill cleanup (in case embedded images were saved outside the main folder)
        $oldContentFromDB = $exist[0]->content ?? '';
        if (!empty($oldContentFromDB)) {
            $this->cleanupUnusedImages($oldContentFromDB, '');
        }
    
        return LtResponse::json('Deleted successfully', 201, 200);
    }
    
    /**
     * Helper function to forcefully delete a directory and all its contents
     */
    private function deleteDirectory($dirPath) {
        if (!is_dir($dirPath)) {
            return false;
        }
        
        // Scan the directory and remove the '.' and '..' pointers
        $files = array_diff(scandir($dirPath), array('.', '..'));
        
        foreach ($files as $file) {
            $filePath = "$dirPath/$file";
            
            // If it's a sub-directory, call this function again. Otherwise, unlink the file.
            if (is_dir($filePath)) {
                $this->deleteDirectory($filePath);
            } else {
                unlink($filePath);
            }
        }
        
        // Now that the folder is empty, rmdir() will successfully delete it
        return rmdir($dirPath);
    }

   // Convert base64_encoded and img tag to normal img path

    function processContentBeforeSave($html, $postId) {

        if (empty($html)) return $html;
    
        // Decode if needed
        $html = html_entity_decode($html);
    
        libxml_use_internal_errors(true);
    
        $dom = new DOMDocument();
    
        $wrapped = '<!DOCTYPE html><html><body>' . $html . '</body></html>';
        $dom->loadHTML($wrapped);
    
        $imageNodes = [];
        foreach ($dom->getElementsByTagName('img') as $img) {
            $imageNodes[] = $img;
        }
    
        foreach ($imageNodes as $img) {

            $src = $img->getAttribute('src');
            $altText = $img->getAttribute('alt');
        
            if (empty(trim($altText))) {
                $altText = 'image';
            }
        
            // ==========================================
            // CASE 1: BASE64 IMAGE (NEW UPLOAD)
            // ==========================================
            if (stripos($src, 'data:image/') === 0) {
        
                $parts = explode(',', $src, 2);
                if (count($parts) !== 2) continue;
        
                if (!preg_match('/data:image\/([^;]+);base64/i', $parts[0], $match)) {
                    continue;
                }
        
                $type = strtolower($match[1]);
                $data = base64_decode($parts[1]);
        
                if ($data === false) continue;
                
                // Generate hash (content-based)
                $hash = md5($data);
        
                $dir = "storage/uploads/blogs/post_{$postId}/";
                if (!is_dir($dir)) mkdir($dir, 0777, true);
                
                // Try to find existing file
                $existingFile = glob($dir . "blog_img_". $hash . '.*');
                
                if (!empty($existingFile)) {
                    // File already exists
                    $fileName = basename($existingFile[0]);
                } else {
                    $fileName = "blog_img_". $hash . '.' . $type;
                    file_put_contents($dir . $fileName, $data);
                }
        
                $markdownImage = ' ![' . $altText . '](' . $fileName . ')';
                $marker = $dom->createTextNode($markdownImage);
        
                $img->parentNode->replaceChild($marker, $img);
            }
        
            // ==========================================
            // CASE 2: EXISTING IMAGE (FROM EDITOR)
            // ==========================================
            else {

                // Extract filename from full URL
                $parsedPath = parse_url($src, PHP_URL_PATH);
                $fileName = basename($parsedPath);
        
                if (!$fileName) continue;
                if (strpos($src, "storage/uploads/blogs/post_{$postId}") !== false) {
                    // convert to markdown
                    $markdownImage = ' ![' . $altText . '](' . $fileName . ')';
                    $marker = $dom->createTextNode($markdownImage);
            
                    $img->parentNode->replaceChild($marker, $img);
                }
   
            }
        }
    
        libxml_clear_errors();
    
        // Extract only body content (remove wrapper)
        $body = $dom->getElementsByTagName('body')->item(0);
        $output = '';
    
        foreach ($body->childNodes as $child) {
            $output .= $dom->saveHTML($child);
        }
    
        return trim($output);
    }    
    
        // extract image file name from formated markdown image
    public function extractImageFilenames($content) {
        preg_match_all('/!\[[^\]]*\]\(([^)]+)\)/', trim($content), $matches);
        return $matches[1] ?? [];
    }
    
    // Clean up old uploaded image record from directory
    public function cleanupUnusedImages($oldContent, $newContent) {
    
        $dir = 'packagers/modules/MdDocs/Media/';
    
        $oldImages = $this->extractImageFilenames($oldContent);
        $newImages = $this->extractImageFilenames($newContent);
    
        $unused = array_diff($oldImages, $newImages);
    
        foreach ($unused as $file) {
            $path = $dir . $file;
    
            if (file_exists($path)) {
                unlink($path);
            }
        }
    }
    
    public function updateViews(){
        $request = new LtRequest;
        $slug = trim($request->slug);
        
        $exist = $this->postModel->select()->where('slug','=', $slug)->get();
        if(count($exist) < 1){
            return LtResponse::json('Post not found', 3004, 100);
        }
        $postId = $exist[0]->ltId;
        $viewCount = $exist[0]->viewCount;
        $this->postModel->viewCount = $viewCount ? (int) $viewCount + 1 : 1; 
        $this->postModel->update('ltId','=', $postId);
        return $this->postModel->responseJson();
    }
    
    public function postReaction(){
        $request = new LtRequest;
        
        $postSlug = trim($request->postSlug);
        $reactionType = trim($request->reaction);
        $currentUserId = $this->userId;
        
        $exist = $this->postModel->select()->where('slug','=', $postSlug)->get();
        if(count($exist) < 1){
            return LtResponse::json('Post not found', 3004, 100);
        }
        
        $postId = $exist[0]->ltId;
        // Inside your PHP method handling the POST request:
        $sql = "
            INSERT INTO tb_blog_post_reaction (lt_id, user_id, post_id, reaction_type) 
            VALUES (:lt_id, :user_id, :post_id, :reaction_type)
            ON DUPLICATE KEY UPDATE reaction_type = :reaction_type;
        ";
        
        $stmt = $this->sqlConnect->prepare($sql);
        $stmt->execute([
            ':lt_id' => $this->ltId,
            ':user_id' => $currentUserId,
            ':post_id' => $postId,
            ':reaction_type' => $reactionType
        ]);
        
        return LtResponse::json('Reaction recorded successfully', 201, 200);
    }
    
}

