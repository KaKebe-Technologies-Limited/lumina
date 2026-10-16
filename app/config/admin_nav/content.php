<?php
/**
 * Admin sidebar fragment: CONTENT. OWNER: B4 "admin-content" (programs, projects, posts, activities, gallery, jobs).
 * Format: docs/ARCHITECTURE.md section 12. Item keys must stay unique across all fragments; they equal the 'nav' option of the controllers.
 */
return [
    'key'   => 'content',
    'label' => 'Content',
    'order' => 20,
    'items' => [
        ['key' => 'programs',   'label' => 'Programs',   'url' => 'admin/programs',   'icon' => 'layers',    'can' => 'content.manage', 'order' => 10],
        ['key' => 'projects',   'label' => 'Projects',   'url' => 'admin/projects',   'icon' => 'folder',    'can' => 'content.manage', 'order' => 20],
        ['key' => 'posts',      'label' => 'Posts',      'url' => 'admin/posts',      'icon' => 'file-text', 'can' => 'content.manage', 'order' => 30],
        ['key' => 'activities', 'label' => 'Activities', 'url' => 'admin/activities', 'icon' => 'activity',  'can' => 'content.manage', 'order' => 40],
        ['key' => 'gallery',    'label' => 'Gallery',    'url' => 'admin/gallery',    'icon' => 'image',     'can' => 'content.manage', 'order' => 50],
        ['key' => 'jobs',       'label' => 'Jobs',       'url' => 'admin/jobs',       'icon' => 'briefcase', 'can' => 'content.manage', 'order' => 60],
    ],
];
