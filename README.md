# MdBlog Module

> A comprehensive, feature-rich blogging module for LifetechOCMS with post management, comments, categories, tags, and reporting capabilities.

## ✨ Features

- **Blog Post Management** - Create, read, update, and delete blog posts with full WYSIWYG editor support
- **Category System** - Organize posts with flexible category hierarchies
- **Tag Management** - Tag-based post organization and filtering
- **Comment System** - Threaded comments on blog posts with moderation capabilities
- **Comment Likes** - Users can like/unlike comments
- **Reporting System** - Report inappropriate content with admin dashboard
- **Media Management** - Integrated media handling with post attachments
- **Settings Configuration** - Blog-wide settings and customization options
- **Responsive UI** - Built with Tailwind CSS for mobile-first design
- **Author Management** - Multi-author support with post ownership tracking

## 🔧 Requirements

- **LifetechOCMS** >= v2.74 (MdLt module dependency)
- **PHP** - Tested with PHP 7.4+
- **Database** - MySQL support
- **Frontend** - Modern browser with ES6 JavaScript support
- **CSS Framework** - Tailwind CSS (included)

## 📦 Installation

### Package Details

```json
{
  "ltId": "178367835066411",
  "packageName": "MdBlog",
  "packageType": "module",
  "version": "1.0.0",
  "dependsOn": [
    {
      "packageName": "MdLt",
      "version": "1.3.0"
    }
  ],
  "isPublished": null,
  "readme": "Blog Module",
  "languageUsed": "html, tailwind/css, javascript, php",
  "description": "Blog Module",
  "lifetechOcmsVersion": null,
  "ltVersion": "2.7.4"
}
```

### Install Steps

1. **Clone or download the module to your LifetechOCMS modules directory:**

   ```bash
   cd your-lifetech-installation/modules
   git clone https://github.com/LifetechOCMS/MdBlog.git
   # or download the release tag
   ```

2. **Enable the module in LifetechOCMS:**
   - Navigate to Module Management
   - Find "MdBlog" in the modules list
   - Click "Install" or "Enable"

3. **Database migrations will run automatically:**
   - Blog post tables
   - Comment tables
   - Category tables
   - Tag tables
   - Settings storage

4. **Configure blog settings:**
   - Go to Blog Settings in the admin dashboard
   - Customize blog title, description, and other preferences

## 📁 Project Structure

```
MdBlog/
├── Controllers/              # Request handlers & routing
│   ├── TbBlogPostController.php
│   ├── TbBlogCategoryController.php
│   ├── TbBlogTagController.php
│   ├── TbBlogCommentController.php
│   ├── TbBlogReportController.php
│   └── TbBlogSettingController.php
├── Models/                   # Database models & entities
│   ├── TbBlogPost.php
│   ├── TbBlogCategory.php
│   ├── TbBlogTag.php
│   ├── TbBlogComment.php
│   ├── TbBlogCommentLike.php
│   ├── TbBlogMedia.php
│   ├── TbBlogReport.php
│   └── TbBlogSetting.php
├── Services/                 # Business logic layer
│   ├── TbBlogPostService.php
│   ├── TbBlogCategoryService.php
│   ├── TbBlogTagService.php
│   ├── TbBlogCommentService.php
│   ├── TbBlogMediaService.php
│   ├── TbBlogReportService.php
│   ├── TbBlogSettingService.php
│   ├── MdBlogRoutes.php
│   ├── Editor.js             # Rich text editor integration
│   ├── Script.js             # Frontend utilities
│   ├── Styles.css            # Additional styling
│   └── Tailwind-config.js    # Tailwind configuration
├── Views/                    # Frontend HTML templates
│   ├── Blog.html
│   ├── Post.html
│   ├── Category.html
│   ├── Tags.html
│   ├── Manage-post.html
│   ├── Manage-post-author.html
│   ├── Manage-comment.html
│   ├── Manage-report.html
│   └── ...
├── Media/                    # Media storage directory
├── Export/                   # Export functionality
│   ├── content.json
│   ├── menu.json
│   ├── page.json
│   ├── pageContent.json
│   └── table.json
├── ddm/                      # DDM (Data Definition Models)
│   └── [Lifetech view/model definitions]
├── package.json              # Module metadata
├── LICENSE                   # MIT License
└── README.md                 # This file
```

## ⚙️ Configuration

### Blog Settings

Access blog settings through the admin dashboard at `/admin/blog/settings`:

```php
// Example settings structure
$settings = [
    'blog_title' => 'My Blog',
    'blog_description' => 'A great blog about...',
    'posts_per_page' => 10,
    'enable_comments' => true,
    'moderate_comments' => false,
    'enable_tags' => true,
    'enable_categories' => true,
    'featured_image' => true
];

```

## 🚀 Usage

### Creating a Blog Post

1. Navigate to **Blog → Manage Posts** in admin
2. Click **New Post**
3. Fill in:
   - Title
   - Content (using the WYSIWYG editor)
   - Category
   - Tags
   - Featured Image
   - Author
4. Set visibility (Draft/Published)
5. Click **Publish**

### Managing Comments

1. Go to **Blog → Comments**
2. View all comments with post association
3. Approve, edit, or delete comments
4. Mark as spam or report abuse

### Organizing Content

- **Categories** - Group related posts
- **Tags** - Add searchable keywords to posts
- **Reports** - Monitor and manage community reports

## 🔌 Module Components

### Controllers

Each controller handles a specific aspect of the blog:

| Controller                 | Purpose                        |
| -------------------------- | ------------------------------ |
| `TbBlogPostController`     | Post CRUD operations & listing |
| `TbBlogCategoryController` | Category management            |
| `TbBlogTagController`      | Tag management                 |
| `TbBlogCommentController`  | Comment CRUD & moderation      |
| `TbBlogReportController`   | Report submission & management |
| `TbBlogSettingController`  | Blog configuration             |

### Services

Business logic abstraction layer:

| Service                 | Responsibility                    |
| ----------------------- | --------------------------------- |
| `TbBlogPostService`     | Post logic & queries              |
| `TbBlogCategoryService` | Category hierarchy & filtering    |
| `TbBlogTagService`      | Tag aggregation & search          |
| `TbBlogCommentService`  | Comment threading & notifications |
| `TbBlogMediaService`    | Media upload & processing         |
| `TbBlogReportService`   | Report handling & analytics       |
| `TbBlogSettingService`  | Settings persistence              |

## 💾 Database Models

### TbBlogPost

Core blog post entity with timestamps, author tracking, and content management.

### TbBlogComment

Threaded comments with parent-child relationships for replies.

### TbBlogCategory

Hierarchical category structure for post organization.

### TbBlogTag

Flexible tagging system for posts.

### TbBlogCommentLike

Like/unlike functionality for comments.

### TbBlogMedia

Asset management linked to posts.

### TbBlogReport

Content moderation and abuse reporting system.

### TbBlogSetting

Module-level configuration storage.

## 🛠️ API & Controllers

### Blog Posts Endpoints

```

GET /api/blog/posts - List all published posts
GET /api/blog/posts/:id - Get single post
POST /api/blog/posts - Create post (authenticated)
PUT /api/blog/posts/:id - Update post
DELETE /api/blog/posts/:id - Delete post

```

### Comments Endpoints

```

GET /api/blog/posts/:id/comments - List post comments
POST /api/blog/posts/:id/comments - Add comment
DELETE /api/blog/comments/:id - Delete comment
POST /api/blog/comments/:id/like - Like comment

```

### Categories & Tags

```

GET /api/blog/categories - List categories
GET /api/blog/tags - List tags
GET /api/blog/posts?category=x - Filter by category
GET /api/blog/posts?tag=x - Filter by tag

```

## 🎨 Customization

### Styling

The module uses Tailwind CSS. Customize styles by:

1. **Modify Tailwind configuration** at `Services/Tailwind-config.js`
2. **Override CSS** in `Services/Styles.css`
3. **Custom theme** - Edit individual view templates in `Views/`

### Editor Integration

The rich text editor is configured in `Services/Editor.js`. Customize:

- Toolbar options
- Allowed HTML tags
- File upload handlers
- Plugin configuration

## 📝 License

This module is licensed under the **MIT License**. See the [LICENSE](LICENSE) file for details.

**Copyright © 2026 LifetechOCMS** - All rights reserved.

---

## 🤝 Support & Contributing

For issues, feature requests, or contributions:

1. Open an issue on GitHub
2. Follow our contribution guidelines
3. Submit pull requests to the development branch

## 📚 Documentation

For complete LifetechOCMS documentation, visit:

- [LifetechOCMS Official Docs](https://www.lifetech.host/hubs/Docs)
- [API Reference](https://www.lifetech.host/hubs/Docs#request)
- [Module Development Guide](https://www.lifetech.host/hubs/Docs#accessing-modules-development)

---

---

## 📦 Package Metadata

```json
{
  "ltId": "178367835066411",
  "packageName": "MdBlog",
  "packageType": "module",
  "version": "1.0.0",
  "dependsOn": [
    {
      "packageName": "MdLt",
      "version": "1.3.0"
    }
  ],
  "isPublished": null,
  "readme": "Blog Module",
  "languageUsed": "html, tailwind/css, javascript, php",
  "description": "Blog Module",
  "lifetechOcmsVersion": null,
  "ltVersion": "2.7.4"
}
```
