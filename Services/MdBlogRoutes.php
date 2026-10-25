<?php

/* ===== CATEGORY (tb_blog_category) ===== */
LtRoute::get('/blogs/filter/categories','MdBlog@TbBlogCategoryController@selectFilter')->name('admin-blog-category.select_filter');

LtRoute::get('/blogs/categories','MdBlog@TbBlogCategoryController@index')->name('admin-blog-category.index');
LtRoute::post('/blogs/categories','MdBlog@TbBlogCategoryController@store')->name('admin-blog-category.store');
LtRoute::patch('/blogs/categories/{ltId}','MdBlog@TbBlogCategoryController@update')->name('admin-blog-category.update');
LtRoute::patch('/blogs/categories/{ltId}/toggle-active','MdBlog@TbBlogCategoryController@toggleActive')->name('admin-blog-category.toggle_active');
LtRoute::delete('/blogs/categories/{ltId}','MdBlog@TbBlogCategoryController@destroy')->name('admin-blog-category.delete');


/* ===== TAG (tb_blog_tag) ===== */

LtRoute::get('/blogs/tags','MdBlog@TbBlogTagController@index')->name('admin-blog-tag.index');
LtRoute::post('/blogs/tags','MdBlog@TbBlogTagController@store')->name('admin-blog-tag.store');
LtRoute::patch('/blogs/tags/{ltId}','MdBlog@TbBlogTagController@update')->name('admin-blog-tag.update');
LtRoute::patch('/blogs/tags/{ltId}/toggle-active','MdBlog@TbBlogTagController@toggleActive')->name('admin-blog-tag.toggle_active');
LtRoute::delete('/blogs/tags/{ltId}','MdBlog@TbBlogTagController@destroy')->name('admin-blog-tag.delete');


/* ===== POST (tb_blog_post) ===== */
LtRoute::get('/blogs/admin/dashboard-metrics','MdBlog@TbBlogPostController@dashboardMetrics')->name('admin-blog-post.metrics-dashboard');

LtRoute::get('/blogs/filter/posts','MdBlog@TbBlogPostController@filterPosts')->name('admin-blog-post.post_filter');
LtRoute::get('/blogs/posts/widget','MdBlog@TbBlogPostController@widget')->name('admin-blog-post.post_widget');
LtRoute::get('/blogs/posts/{slug}/slug','MdBlog@TbBlogPostController@postSlug')->name('admin-blog-post.post_slug');

LtRoute::get('/blogs/posts','MdBlog@TbBlogPostController@index')->name('admin-blog-post.index');
LtRoute::post('/blogs/posts','MdBlog@TbBlogPostController@store')->name('admin-blog-post.store');
LtRoute::patch('/blogs/posts','MdBlog@TbBlogPostController@update')->name('admin-blog-post.update');
LtRoute::patch('/blogs/posts/update-status','MdBlog@TbBlogPostController@updateStatus')->name('admin-blog-post.update-status');
LtRoute::delete('/blogs/posts/{ltId}','MdBlog@TbBlogPostController@destroy')->name('admin-blog-post.delete');
LtRoute::post('/blogs/posts/view-update','MdBlog@TbBlogPostController@viewsUpdate')->name('admin-blog-post.views-update');

LtRoute::post('/blogs/posts/react','MdBlog@TbBlogPostController@postReaction')->name('admin-blog-post.update-reaction');

LtRoute::get('/blogs/menu/categories','MdBlog@TbBlogPostController@menuCategories')->name('admin-blog-post.menu-categories');


LtRoute::get('/blogs/comments','MdBlog@TbBlogCommentController@index')->name('admin-blog-comment.index');
LtRoute::post('/blogs/comments/create','MdBlog@TbBlogCommentController@create')->name('admin-blog-comment.create');
LtRoute::post('/blogs/comments/like','MdBlog@TbBlogCommentController@commentsLike')->name('admin-blog-report.comments-like');
LtRoute::delete('/blogs/comments/{ltId}','MdBlog@TbBlogCommentController@destroy')->name('admin-blog-comments.delete');
LtRoute::post('/blogs/comments/update-status','MdBlog@TbBlogCommentController@updateStatus')->name('admin-blog-comments.update-status');

LtRoute::post('/blogs/reports/create','MdBlog@TbBlogReportController@createReport')->name('admin-blog-report.create');
LtRoute::get('/blogs/reports','MdBlog@TbBlogReportController@index')->name('admin-blog-report.index');
LtRoute::delete('/blogs/reports/{ltId}','MdBlog@TbBlogReportController@destroy')->name('admin-blog-report.delete');
LtRoute::post('/blogs/reports/update-status','MdBlog@TbBlogReportController@updateStatus')->name('admin-blog-report.update-status');

LtRoute::get('/blogs/settings','MdBlog@TbBlogSettingController@index')->name('admin-blog-setting.index');
LtRoute::post('/blogs/settings','MdBlog@TbBlogSettingController@store')->name('admin-blog-setting.store');
LtRoute::patch('/blogs/settings/{ltId}','MdBlog@TbBlogSettingController@update')->name('admin-blog-setting.update');
LtRoute::patch('/blogs/settings/{ltId}/toggle-active','MdBlog@TbBlogSettingController@toggleActive')->name('admin-blog-setting.toggle_active');
LtRoute::delete('/blogs/settings/{ltId}','MdBlog@TbBlogSettingController@destroy')->name('admin-blog-setting.delete');

/* ===== POST (tb_blog_user) ===== */
LtRoute::post('/blogs/auth/signup','MdBlog@TbBlogUserController@signup')->name('admin-blog-post.sign-up');
LtRoute::post('/blogs/auth/signin','MdBlog@TbBlogUserController@signin')->name('admin-blog-post.sign-in');
LtRoute::get('/blogs/user/record','MdBlog@TbBlogUserController@userRecord')->name('admin-blog-post.user-record');
LtRoute::post('/blogs/user/update','MdBlog@TbBlogUserController@update')->name('admin-blog-post.user-update');
LtRoute::post('/blogs/user/change-password','MdBlog@TbBlogUserController@changePassword')->name('admin-blog-post.change-passowrd');
LtRoute::delete('/blogs/user','MdBlog@TbBlogUserController@deleteUser')->name('admin-blog-post.delete-user');
