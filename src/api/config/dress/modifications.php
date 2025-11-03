<?php

return [
  'enabled' => true,

  'mode' => 'vertical', // vertical|horizontal

  'parent_listable' => true, // вертикально: скрываем родителя из каталога/сейла

  'inherit' => [
    'brand' => true,
    'categories' => true,
    'images' => 'fallback', // child→own|fallback(parent)|merge
    'seo' => 'fallback',    // title/description/slug policy
  ],
];