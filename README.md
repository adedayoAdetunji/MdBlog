# MdBlog Module

MdBlog is a blog management module providing RESTful APIs for managing blog categories, tags, posts, media assets, comments, reports, settings, and author profiles.

## API Documentation

Comprehensive API documentation with payload samples, query parameters, and responses for all 42 endpoints is available in two formats:

- 📄 **Word Document (.docx):** [`MdBlog_API_Documentation.docx`](./MdBlog_API_Documentation.docx)
- 📝 **Markdown Document:** [`API_DOCUMENTATION.md`](./API_DOCUMENTATION.md)

### Resource Summary

- **Categories:** `GET|POST|PATCH|DELETE /blogs/categories/*`
- **Tags:** `GET|POST|PATCH|DELETE /blogs/tags/*`
- **Posts & Media:** `GET|POST|PATCH|DELETE /blogs/posts/*`, `/blogs/filter/posts`, `/blogs/menu/categories`
- **Comments & Likes:** `GET|POST|DELETE /blogs/comments/*`
- **Reports & Moderation:** `GET|POST|DELETE /blogs/reports/*`
- **Settings:** `GET|POST|PATCH|DELETE /blogs/settings/*`
- **Authentication & Authors:** `POST|GET|DELETE /blogs/auth/*`, `/blogs/user/*`
