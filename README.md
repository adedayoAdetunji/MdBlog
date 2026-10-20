# MdBlog Module

> A comprehensive, feature-rich blogging module for LifetechOCMS with post management, comments, categories, tags, author/user profiles, moderation, and complete RESTful API coverage.

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
![Version](https://img.shields.io/badge/version-2.0.0-blue.svg)
![PHP](https://img.shields.io/badge/PHP-compatible-green.svg)
![JavaScript](https://img.shields.io/badge/JavaScript-ES6-yellow.svg)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind-CSS-38B2AC.svg)](https://tailwindcss.com)

---

## 📚 Documentation

This module includes complete documentation for developers, administrators, and API integrators:

- 📄 [**API Documentation (Markdown)**](./API_DOCUMENTATION.md) – Comprehensive specification of all 42 endpoints, route parameters, request payloads, response bodies, status codes, and schema mappings.
- 📘 [**MdBlog API Documentation (Word .docx)**](./MdBlog_API_Documentation.docx) – Formatted, print-friendly Word export for offline reference and distribution.
- 📖 [**Project README**](./README.md) – Module overview, installation guide, project architecture, and quick reference.

---

## 📋 Table of Contents

- [Features](#features)
- [Requirements](#requirements)
- [Installation](#installation)
- [Project Structure](#project-structure)
- [Configuration](#configuration)
- [Usage](#usage)
- [Module Components](#module-components)
- [Database Models](#database-models)
- [API Documentation & Endpoints](#api-documentation--endpoints)
  - [Response Envelope Format](#response-envelope-format)
  - [1. Category Management](#1-category-management-tbblogcategorycontroller)
  - [2. Tag Management](#2-tag-management-tbblogtagcontroller)
  - [3. Post Management](#3-post-management-tbblogpostcontroller)
  - [4. Comment Management](#4-comment-management-tbblogcommentcontroller)
  - [5. Report Management](#5-report-management-tbblogreportcontroller)
  - [6. Settings Management](#6-settings-management-tbblogsettingcontroller)
  - [7. Authentication & User Management](#7-authentication--user-management-tbblogusercontroller)
- [Customization](#customization)
- [Documentation & Resources](#documentation--resources)
- [License](#license)

---

## ✨ Features

- **Blog Post Management** – Full lifecycle management (draft, published, scheduled), slug generation, view tracking, and rich WYSIWYG editing.
- **Category Hierarchy** – Nested, organized taxonomy with menu visibility flags and post counters.
- **Tag Management** – Tag-based post categorization, filtering, and usage metrics.
- **Comment System** – Threaded discussions, parent-reply hierarchy, and status moderation (`pending`, `approved`, `spam`).
- **Reactions & Likes** – Post reaction tracking (likes, claps, bookmarks) and comment liking.
- **User Authentication & Profiles** – User sign-up, sign-in, profile management, and password reset workflows.
- **Content Moderation** – Community abuse/content reporting system with dedicated admin review tools.
- **Dashboard Metrics** – Admin dashboard metrics for posts, comments, reactions, and views.
- **Media Management** – Integrated media attachment and caching support.
- **Responsive UI** – Built with modern Tailwind CSS and Lifetech DDM templates.

---

## 🔧 Requirements

- **LifetechOCMS** >= v1.3 (MdLt module dependency)
- **PHP** – Tested with PHP 7.4+ / 8.x
- **Database** – MySQL / MariaDB support
- **Frontend** – Modern browser with ES6 JavaScript support
- **CSS Framework** – Tailwind CSS (included)

---

## 📦 Installation

### Package Details

```json
{
  "ltId": "178367835066411",
  "packageName": "MdBlog",
  "packageType": "module",
  "version": "2.0.0",
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

1. **Clone or copy the module into your LifetechOCMS modules directory:**

   ```bash
   cd your-lifetech-installation/modules
   git clone https://github.com/adedayoAdetunji/MdBlog.git
   ```

2. **Enable the module in LifetechOCMS:**
   - Open your LifetechOCMS administrative console.
   - Navigate to **Module Management**.
   - Locate **MdBlog** and click **Install** or **Enable**.

3. **Database migrations run automatically:**
   - Category table (`tb_blog_category`)
   - Tag table (`tb_blog_tag`)
   - Blog post table (`tb_blog_post`)
   - Comment table (`tb_blog_comment`)
   - Comment like table (`tb_blog_comment_like`)
   - Report table (`tb_blog_report`)
   - Settings table (`tb_blog_setting`)
   - Blog user table (`tb_blog_user`)

4. **Configure Blog Settings:**
   - Navigate to **Blog → Settings** in the dashboard.
   - Configure title, pagination, comments moderation, and notification settings.

---

## 📁 Project Structure

```
MdBlog/
├── API_DOCUMENTATION.md          # Complete REST API reference (all 42 endpoints)
├── MdBlog_API_Documentation.docx # Offline printable Word API documentation
├── README.md                     # Project overview and documentation guide
├── package.json                  # Module metadata and dependencies (v2.0.0)
├── LICENSE                       # MIT License
├── Controllers/                  # Request dispatchers & API handlers
│   ├── TbBlogCategoryController.php
│   ├── TbBlogCommentController.php
│   ├── TbBlogPostController.php
│   ├── TbBlogReportController.php
│   ├── TbBlogSettingController.php
│   ├── TbBlogTagController.php
│   └── TbBlogUserController.php
├── Models/                       # Lifetech database entities
│   ├── TbBlogCategory.php
│   ├── TbBlogComment.php
│   ├── TbBlogCommentLike.php
│   ├── TbBlogMedia.php
│   ├── TbBlogPost.php
│   ├── TbBlogReport.php
│   ├── TbBlogSetting.php
│   ├── TbBlogTag.php
│   └── TbBlogUser.php
├── Services/                     # Business logic and service layer
│   ├── Editor.js                 # Rich-text editor integration
│   ├── MdBlogRoutes.php          # 42 module route definitions
│   ├── Script.js                 # Frontend interactivity scripts
│   ├── Styles.css                # Custom styling rules
│   ├── Tailwind-config.js        # Tailwind CSS theme configuration
│   ├── TbBlogCategoryService.php
│   ├── TbBlogCommentService.php
│   ├── TbBlogMediaService.php
│   ├── TbBlogPostService.php
│   ├── TbBlogReportService.php
│   ├── TbBlogSettingService.php
│   ├── TbBlogTagService.php
│   └── TbBlogUserService.php
├── Views/                        # HTML views & templates
│   ├── Admin-dashboard.html
│   ├── Blog.html
│   ├── Category.html
│   ├── Manage-comment.html
│   ├── Manage-post-author.html
│   ├── Manage-post.html
│   ├── Manage-report.html
│   ├── Post.html
│   ├── Sign-in.html
│   ├── Sign-up.html
│   ├── Tags.html
│   └── User-profile.html
├── Export/                       # Export schemas and presets
│   ├── content.json
│   ├── menu.json
│   ├── page.json
│   ├── pageContent.json
│   └── table.json
├── Media/                        # Media assets & caching directory
└── ddm/                          # Lifetech Data Definition Models
```

---

## ⚙️ Configuration

### Blog Settings

Access settings programmatically or via the admin interface at `/blogs/settings`:

```php
// Example configuration values
$settings = [
    'blog_title'         => 'My Engineering Blog',
    'blog_description'   => 'Insights into software and systems engineering.',
    'posts_per_page'     => 10,
    'enable_comments'    => true,
    'moderate_comments'  => false,
    'enable_tags'        => true,
    'enable_categories'  => true,
    'featured_image'     => true
];
```

---

## 🚀 Usage

### Creating a Blog Post
1. Navigate to **Blog → Manage Posts** in admin.
2. Click **New Post**.
3. Fill in the title, content (WYSIWYG editor), select category, tags, and featured media.
4. Set status (`published` or `draft`) and save.

### Moderating Comments
1. Navigate to **Blog → Manage Comments**.
2. Review comments submitted across posts.
3. Approve, reject, or mark inappropriate content as spam.

### User & Author Management
1. Readers and contributors can sign up via `/blogs/auth/signup`.
2. Authors can access `/blogs/auth/signin` to manage their authored posts and profile details.

---

## 🔌 Module Components

### Controllers

| Controller | Description |
|---|---|
| `TbBlogPostController` | Post CRUD, dashboard metrics, search/filter, reactions, view counts |
| `TbBlogCategoryController` | Category creation, updates, menu visibility, status toggles |
| `TbBlogTagController` | Tag indexing, creation, editing, and status toggles |
| `TbBlogCommentController` | Comment submission, threaded replies, likes, and moderation |
| `TbBlogReportController` | Content abuse reporting, admin review, status workflow |
| `TbBlogSettingController` | Configuration and preference management |
| `TbBlogUserController` | Authentication (sign-up, sign-in), profile, and account management |

### Services

| Service | Responsibility |
|---|---|
| `TbBlogPostService` | Post queries, slug generation, reaction tracking, view counters |
| `TbBlogCategoryService` | Category hierarchies, menu ordering, dropdown filters |
| `TbBlogTagService` | Tag aggregation, usage counts, search indexes |
| `TbBlogCommentService` | Comment trees, author resolution, like tallies |
| `TbBlogMediaService` | Post asset bindings and file management |
| `TbBlogReportService` | Report workflow, notification triggers, resolution statuses |
| `TbBlogSettingService` | Key-value settings persistence and retrieval |
| `TbBlogUserService` | Credential hashing, JWT/session handling, user profiles |

---

## 💾 Database Models

| Model | Table | Purpose |
|---|---|---|
| `TbBlogPost` | `tb_blog_post` | Primary blog article entity, content, author, views, status |
| `TbBlogCategory` | `tb_blog_category` | Category taxonomy, description, menu visibility |
| `TbBlogTag` | `tb_blog_tag` | Search keywords and tags associated with posts |
| `TbBlogComment` | `tb_blog_comment` | Threaded reader comments and moderation state |
| `TbBlogCommentLike` | `tb_blog_comment_like` | Like records linking users and comments |
| `TbBlogMedia` | `tb_blog_media` | Media asset associations for posts and galleries |
| `TbBlogReport` | `tb_blog_report` | User content flags and admin moderation cases |
| `TbBlogSetting` | `tb_blog_setting` | Key-value global module configuration parameters |
| `TbBlogUser` | `tb_blog_user` | User and author account profiles |

---

## 🛠️ API Documentation & Endpoints

MdBlog provides **42 RESTful endpoints** registered in [`Services/MdBlogRoutes.php`](./Services/MdBlogRoutes.php).

> 📘 **Full API Reference**:
> Detailed parameter schemas, request bodies, sample payloads, and response structures for all 42 endpoints are documented in:
> - [**API_DOCUMENTATION.md**](./API_DOCUMENTATION.md) (Markdown)
> - [**MdBlog_API_Documentation.docx**](./MdBlog_API_Documentation.docx) (Word Document)

### Response Envelope Format

All API endpoints return responses encapsulated in the standard Lifetech `LtResponse` envelope:

```json
{
  "responseResult": "success | Action description",
  "responseCode": 200 | 201 | 2006 | 3004 | 3008 | 422,
  "responseCategory": 200 | 100,
  "responseData": {} | [] | null
}
```

- **`responseCategory`**: `200` for HTTP success / valid operations; `100` for validation errors or failures.
- **`responseCode`**: Application status code (`200` fetch success, `201` created/updated, `3004`/`3008`/`422` validation or auth failure).
- **`responseResult`**: Human-readable status description.
- **`responseData`**: Returned data payload.

---

### 1. Category Management (`TbBlogCategoryController`)

| HTTP Method | Route Endpoint | Route Name | Description |
|---|---|---|---|
| `GET` | `/blogs/filter/categories` | `admin-blog-category.select_filter` | Lightweight category list (`ltId`, `categoryName`) for dropdowns |
| `GET` | `/blogs/categories` | `admin-blog-category.index` | Full category listing with status filtering |
| `POST` | `/blogs/categories` | `admin-blog-category.store` | Create a new blog category |
| `PATCH` | `/blogs/categories/{ltId}` | `admin-blog-category.update` | Update existing category by `ltId` |
| `PATCH` | `/blogs/categories/{ltId}/toggle-active` | `admin-blog-category.toggle_active` | Toggle category active/inactive status |
| `DELETE` | `/blogs/categories/{ltId}` | `admin-blog-category.delete` | Delete category by `ltId` |

---

### 2. Tag Management (`TbBlogTagController`)

| HTTP Method | Route Endpoint | Route Name | Description |
|---|---|---|---|
| `GET` | `/blogs/tags` | `admin-blog-tag.index` | List all tags with usage count and status filter |
| `POST` | `/blogs/tags` | `admin-blog-tag.store` | Create a new blog tag |
| `PATCH` | `/blogs/tags/{ltId}` | `admin-blog-tag.update` | Update existing tag by `ltId` |
| `PATCH` | `/blogs/tags/{ltId}/toggle-active` | `admin-blog-tag.toggle_active` | Toggle tag active/inactive status |
| `DELETE` | `/blogs/tags/{ltId}` | `admin-blog-tag.delete` | Delete tag record by `ltId` |

---

### 3. Post Management (`TbBlogPostController`)

| HTTP Method | Route Endpoint | Route Name | Description |
|---|---|---|---|
| `GET` | `/blogs/admin/dashboard-metrics` | `admin-blog-post.metrics-dashboard` | Aggregate dashboard statistics (posts, comments, views, reactions) |
| `GET` | `/blogs/filter/posts` | `admin-blog-post.post_filter` | Search and filter posts by category, tag, author, or keyword |
| `GET` | `/blogs/posts/widget` | `admin-blog-post.post_widget` | Retrieve popular and recent posts for sidebar/footer widgets |
| `GET` | `/blogs/posts/{slug}/slug` | `admin-blog-post.post_slug` | Retrieve single blog post details by URL slug |
| `GET` | `/blogs/posts` | `admin-blog-post.index` | Paginated post listing |
| `POST` | `/blogs/posts` | `admin-blog-post.store` | Create new post with tags, category, and media attachments |
| `PATCH` | `/blogs/posts/{ltId}` | `admin-blog-post.update` | Update existing blog post details |
| `PATCH` | `/blogs/posts/{ltId}/toggle-active` | `admin-blog-post.toggle_active` | Toggle post publish/active state |
| `DELETE` | `/blogs/posts/{ltId}` | `admin-blog-post.delete` | Delete blog post record |
| `POST` | `/blogs/posts/view-update` | `admin-blog-post.views-update` | Increment post view counter |
| `POST` | `/blogs/posts/react` | `admin-blog-post.update-reaction` | Add or update post reaction (like, love, clap, etc.) |
| `GET` | `/blogs/menu/categories` | `admin-blog-post.menu-categories` | Retrieve categories with post counts for site navigation |

---

### 4. Comment Management (`TbBlogCommentController`)

| HTTP Method | Route Endpoint | Route Name | Description |
|---|---|---|---|
| `GET` | `/blogs/comments` | `admin-blog-comment.index` | List comments filtered by post, author, or approval status |
| `POST` | `/blogs/comments/create` | `admin-blog-comment.create` | Submit a new comment or threaded reply |
| `POST` | `/blogs/comments/like` | `admin-blog-report.comments-like` | Like or unlike a comment |
| `DELETE` | `/blogs/comments/{ltId}` | `admin-blog-comments.delete` | Delete comment by `ltId` |
| `POST` | `/blogs/comments/update-status` | `admin-blog-comments.update-status` | Update comment status (`approved`, `pending`, `spam`) |

---

### 5. Report Management (`TbBlogReportController`)

| HTTP Method | Route Endpoint | Route Name | Description |
|---|---|---|---|
| `POST` | `/blogs/reports/create` | `admin-blog-report.create` | Submit content abuse / moderation report |
| `GET` | `/blogs/reports` | `admin-blog-report.index` | List submitted moderation reports |
| `DELETE` | `/blogs/reports/{ltId}` | `admin-blog-report.delete` | Delete moderation report |
| `POST` | `/blogs/reports/update-status` | `admin-blog-report.update-status` | Update report status (`pending`, `reviewed`, `dismissed`) |

---

### 6. Settings Management (`TbBlogSettingController`)

| HTTP Method | Route Endpoint | Route Name | Description |
|---|---|---|---|
| `GET` | `/blogs/settings` | `admin-blog-setting.index` | Retrieve blog configuration settings |
| `POST` | `/blogs/settings` | `admin-blog-setting.store` | Save a new configuration key-value pair |
| `PATCH` | `/blogs/settings/{ltId}` | `admin-blog-setting.update` | Update existing setting value |
| `PATCH` | `/blogs/settings/{ltId}/toggle-active` | `admin-blog-setting.toggle_active` | Toggle setting active state |
| `DELETE` | `/blogs/settings/{ltId}` | `admin-blog-setting.delete` | Delete setting item |

---

### 7. Authentication & User Management (`TbBlogUserController`)

| HTTP Method | Route Endpoint | Route Name | Description |
|---|---|---|---|
| `POST` | `/blogs/auth/signup` | `admin-blog-post.sign-up` | Register a new user/author account |
| `POST` | `/blogs/auth/signin` | `admin-blog-post.sign-in` | Authenticate user credentials and start session |
| `GET` | `/blogs/user/{userId}/record` | `admin-blog-post.user-record` | Get user profile and account details |
| `POST` | `/blogs/user/update` | `admin-blog-post.user-update` | Update user profile data |
| `POST` | `/blogs/user/change-password` | `admin-blog-post.change-passowrd` | Update user password |
| `DELETE` | `/blogs/user/{ltId}` | `admin-blog-post.delete-user` | Delete user account |

---

## 🎨 Customization

### Styling
- **Tailwind CSS Configuration:** Located at [`Services/Tailwind-config.js`](./Services/Tailwind-config.js).
- **Custom CSS:** Located at [`Services/Styles.css`](./Services/Styles.css).
- **View Templates:** Fully editable HTML templates located in the [`Views/`](./Views/) directory.

### WYSIWYG Editor
Rich-text editor initialization and plugins are configured in [`Services/Editor.js`](./Services/Editor.js).

---

## 📚 Documentation & Resources

For detailed developer guides and Lifetech documentation:
- 📄 [API Documentation (Markdown)](./API_DOCUMENTATION.md)
- 📘 [API Documentation (Word .docx)](./MdBlog_API_Documentation.docx)
- 🌐 [LifetechOCMS Official Documentation](https://www.lifetech.host/hubs/Docs)
- 🌐 [API Reference Guide](https://www.lifetech.host/hubs/Docs#request)
- 🌐 [Module Development Guide](https://www.lifetech.host/hubs/Docs#accessing-modules-development)

---

## 📝 License

This project is licensed under the **MIT License**. See the [LICENSE](LICENSE) file for details.

**Copyright © 2026 LifetechOCMS** – All rights reserved.
