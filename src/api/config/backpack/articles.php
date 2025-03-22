<?php

return [
  'per_page' => 12,

  'class' => 'App\Models\Article',

  'image' => [
    'enable' => true,
    'base_path' => 'https://djini-v2.b-cdn.net/blog/',
  ],

  'resource' => [
    'small' => 'App\Http\Resources\ArticleSmallResource',
    'large' => 'App\Http\Resources\ArticleLargeResource'
  ]
];
