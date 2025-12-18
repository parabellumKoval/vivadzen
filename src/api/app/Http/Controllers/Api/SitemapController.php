<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Carbon\Carbon;

use Backpack\Store\app\Models\Category;
use Backpack\Store\app\Models\Product;

use App\Http\Resources\CategorySlugResource;

class SitemapController extends \App\Http\Controllers\Controller
{
    
  /**
   * getArticles
   *
   * @param  mixed $request
   * @return void
   */
  public function getArticles(Request $request) {
    $articles = \DB::table('ak_articles')
      ->select('ak_articles.slug')
      ->where('status', 'PUBLISHED')
      ->get();

    $articles_array = $articles->all();

    $articles_links = array_map(function($item) {
      return '/blog/' . $item->slug;
    }, $articles_array);

    $articles_links_ru = array_map(function($item) {
      return '/ru/blog/' . $item->slug;
    }, $articles_array);
    

    return [
      ...$articles_links,
      ...$articles_links_ru
    ];
  }

  /**
   * getBrands
   *
   * @param  mixed $request
   * @return void
   */
  public function getBrands(Request $request) {
    $brands = \DB::table('ak_brands')
      ->select('ak_brands.slug')
      ->where('is_active', 1)
      ->get();

    $brands_array = $brands->all();

    $brands_links = array_map(function($item) {
      return '/brands/' . $item->slug;
    }, $brands_array);

    $brands_links_ru = array_map(function($item) {
      return '/ru/brands/' . $item->slug;
    }, $brands_array);
    

    return [
      ...$brands_links,
      ...$brands_links_ru
    ];
  }
    
  /**
   * getProducts
   *
   * @param  mixed $request
   * @return void
   */
  public function getProducts(Request $request) {
    $products = \DB::table('ak_products')
      ->select('ak_products.slug')
      ->where('is_active', 1)
      ->get();

    $products_array = $products->all();

    $products_links = array_map(function($item) {
      return '/' . $item->slug;
    }, $products_array);

    $products_links_ru = array_map(function($item) {
      return '/ru/' . $item->slug;
    }, $products_array);
    

    return [
      ...$products_links,
      ...$products_links_ru
    ];
  }
  
  /**
   * getCategories
   *
   * @param  mixed $request
   * @return void
   */
  public function getCategories(Request $request) {
    
    $categories = \DB::table('ak_product_categories')
      ->select('ak_product_categories.slug')
      ->where('is_active', 1)
      ->get();

    $categories_array = $categories->all();

    $categories_links = array_map(function($item) {
      return '/' . $item->slug;
    }, $categories_array);

    $categories_links_ru = array_map(function($item) {
      return '/ru/' . $item->slug;
    }, $categories_array);

    return [
      ...$categories_links,
      ...$categories_links_ru
    ];
  }

  /**
   * getRegions
   *
   * @param  mixed $request
   * @return void
   */
  public function getRegions(Request $request) {
    
    $regions = \DB::table('regions')
      ->select('regions.slug')
      ->where('is_active', 1)
      ->get();

    $regions_array = $regions->all();

    $regions_links = array_map(function($item) {
      return '/' . $item->slug;
    }, $regions_array);

    $regions_links_ru = array_map(function($item) {
      return '/ru/' . $item->slug;
    }, $regions_array);

    return [
      ...$regions_links,
      ...$regions_links_ru
    ];
  }

  /**
   * Возвращает единый набор элементов для генерации регионально-зависимых sitemap.
   */
  public function getFull(Request $request)
  {
    $defaultRegions = $this->defaultRegions();

    $items = array_merge(
      $this->collectProducts($defaultRegions),
      $this->collectCategories($defaultRegions),
      $this->collectBrands($defaultRegions),
      $this->collectArticles($defaultRegions),
      $this->collectRegions($defaultRegions),
      $this->collectSeoPages($defaultRegions)
    );

    return [
      'items' => array_values($items),
    ];
  }

  /**
   * Собираем товары из денормализованной таблицы каталога, учитывая каноникал модификаций.
   */
  protected function collectProducts(array $defaultRegions): array
  {
    $catalog = \DB::table('ak_catalog')
      ->select('slug', 'country_code', 'group_id', 'product_id', 'item_type', 'extras', 'seo', 'is_available')
      ->where('is_available', 1)
      ->whereNotNull('slug')
      ->get();

    // Если денормализованная таблица пустая, fallback на основной список без региональной разметки.
    if ($catalog->isEmpty()) {
      $products = \DB::table('ak_products')
        ->select('slug', 'updated_at', 'extras', 'seo')
        ->where('is_active', 1)
        ->get();

      return $this->mapSimpleItems(
        $products,
        'product',
        $defaultRegions,
        function ($row) {
          return $row->slug ?? null;
        },
        function ($row) {
          return $this->isDisableBaseCanonical($row->extras ?? null, $row->seo ?? null);
        }
      );
    }

    $groups = [];

    foreach ($catalog as $row) {
      $region = $this->normalizeRegion($row->country_code ?? null);
      $slug = $this->normalizeSlug($row->slug ?? null);
      if (!$region || !$slug) {
        continue;
      }

      $groupId = $row->group_id ?: $row->product_id;
      $disableCanonical = $this->isDisableBaseCanonical($row->extras ?? null, $row->seo ?? null);

      if (!isset($groups[$groupId])) {
        $groups[$groupId] = [];
      }

      if (!isset($groups[$groupId][$region])) {
        $groups[$groupId][$region] = [
          'base' => null,
          'items' => [],
        ];
      }

      // Предпочитаем canonical для simple варианта, иначе первая запись.
      if (!$groups[$groupId][$region]['base'] || $row->item_type === 's') {
        $groups[$groupId][$region]['base'] = $slug;
      }

      $groups[$groupId][$region]['items'][] = [
        'slug' => $slug,
        'disable' => $disableCanonical,
      ];
    }

    $result = [];

    foreach ($groups as $groupItems) {
      foreach ($groupItems as $region => $data) {
        $baseSlug = $data['base'] ?? ($data['items'][0]['slug'] ?? null);
        if (!$baseSlug) {
          continue;
        }

        $result[$baseSlug] = $this->appendRegionToItem(
          $result[$baseSlug] ?? null,
          $baseSlug,
          $region,
          false
        );

        foreach ($data['items'] as $item) {
          if ($item['disable']) {
            $result[$item['slug']] = $this->appendRegionToItem(
              $result[$item['slug']] ?? null,
              $item['slug'],
              $region,
              true
            );
          }
        }
      }
    }

    // Если по каким-то причинам список пуст — возвращаем пустой массив, чтобы не ломать генерацию.
    return array_values($result);
  }

  protected function collectCategories(array $defaultRegions): array
  {
    $categories = \DB::table('ak_product_categories')
      ->select('slug', 'updated_at')
      ->where('is_active', 1)
      ->get();

    return $this->mapSimpleItems($categories, 'category', $defaultRegions);
  }

  protected function collectBrands(array $defaultRegions): array
  {
    $brands = \DB::table('ak_brands')
      ->select('slug', 'updated_at')
      ->where('is_active', 1)
      ->get();

    return $this->mapSimpleItems($brands, 'brand', $defaultRegions, function ($row) {
      return $row->slug ?? null;
    });
  }

  protected function collectArticles(array $defaultRegions): array
  {
    $articles = \DB::table('ak_articles')
      ->select('slug', 'updated_at', 'countries')
      ->where('status', 'PUBLISHED')
      ->get();

    return $this->mapSimpleItems(
      $articles,
      'article',
      $defaultRegions,
      function ($row) {
        return 'blog/' . ($row->slug ?? '');
      },
      null,
      function ($row) use ($defaultRegions) {
        return $this->normalizeRegions($row->countries ?? null, $defaultRegions);
      }
    );
  }

  protected function collectRegions(array $defaultRegions): array
  {
    $regions = \DB::table('regions')
      ->select('slug', 'updated_at')
      ->where('is_active', 1)
      ->get();

    return $this->mapSimpleItems($regions, 'region', $defaultRegions);
  }

  protected function collectSeoPages(array $defaultRegions): array
  {
    $pages = \DB::table('ak_seo_pages')
      ->select('slug', 'countries', 'updated_at')
      ->where('is_active', true)
      ->where('show_in_sitemap', true)
      ->get();

    return $this->mapSimpleItems(
      $pages,
      'seo_page',
      $defaultRegions,
      function ($row) {
        return $row->slug ?? null;
      },
      null,
      function ($row) use ($defaultRegions) {
        return $this->normalizeRegions($row->countries ?? null, $defaultRegions);
      }
    );
  }

  protected function mapSimpleItems($rows, string $type, array $defaultRegions, ?callable $slugResolver = null, ?callable $canonicalResolver = null, ?callable $regionsResolver = null): array
  {
    $items = [];

    foreach ($rows as $row) {
      $slug = $slugResolver ? $slugResolver($row) : ($row->slug ?? null);
      $slug = $this->normalizeSlug($slug);

      if (!$slug) {
        continue;
      }

      $availableRegions = $regionsResolver
        ? $regionsResolver($row)
        : $defaultRegions;

      $items[] = [
        'type' => $type,
        'slug' => $slug,
        'available_regions' => $this->normalizeRegions($availableRegions, $defaultRegions),
        'lastmod' => $this->formatLastmod($row->updated_at ?? null),
        'disable_base_canonical' => $canonicalResolver ? (bool) $canonicalResolver($row) : false,
      ];
    }

    return $items;
  }

  protected function normalizeSlug(?string $slug): ?string
  {
    if (!$slug) {
      return null;
    }

    $clean = trim($slug);
    $clean = preg_replace('#^/+|/+$#', '', $clean);

    return $clean ?: null;
  }

  protected function normalizeRegion($region): ?string
  {
    $normalized = strtolower(trim((string) ($region ?? '')));
    return $normalized ?: null;
  }

  protected function normalizeRegions($regions, array $fallback): array
  {
    if (is_string($regions)) {
      $decoded = json_decode($regions, true);
      if (json_last_error() === JSON_ERROR_NONE) {
        $regions = $decoded;
      } else {
        $regions = [$regions];
      }
    }

    if (!is_array($regions) || empty($regions)) {
      return $fallback;
    }

    $normalized = array_map(function ($item) {
      return $this->normalizeRegion($item);
    }, $regions);

    $filtered = array_values(array_unique(array_filter($normalized)));

    return $filtered ?: $fallback;
  }

  protected function defaultRegions(): array
  {
    $regions = config('backpack-settings.available_regions', []);
    if (is_array($regions)) {
      $regions = array_keys($regions);
    }

    $normalized = $this->normalizeRegions($regions, []);

    if (!in_array('global', $normalized, true)) {
      $normalized[] = 'global';
    }

    return $normalized ?: ['global'];
  }

  protected function formatLastmod($value): ?string
  {
    if ($value instanceof \DateTimeInterface) {
      return $value->format('c');
    }

    if (is_string($value) && $value) {
      try {
        return Carbon::parse($value)->toAtomString();
      } catch (\Throwable $e) {
        return null;
      }
    }

    return null;
  }

  protected function isDisableBaseCanonical($extras, $seo): bool
  {
    $extrasArr = $this->decodePayload($extras);
    $seoArr = $this->decodePayload($seo);

    return (bool) (($extrasArr['disable_base_canonical'] ?? false) || ($seoArr['disable_base_canonical'] ?? false));
  }

  protected function decodePayload($payload): array
  {
    if (is_null($payload)) {
      return [];
    }

    if (is_string($payload)) {
      $decoded = json_decode($payload, true);
      if (json_last_error() === JSON_ERROR_NONE) {
        return is_array($decoded) ? $decoded : [];
      }
    }

    if (is_object($payload)) {
      return (array) $payload;
    }

    return is_array($payload) ? $payload : [];
  }

  protected function appendRegionToItem(?array $current, string $slug, string $region, bool $disableCanonical): array
  {
    if (!$current) {
      $current = [
        'type' => 'product',
        'slug' => $slug,
        'available_regions' => [],
        'lastmod' => null,
        'disable_base_canonical' => $disableCanonical,
      ];
    }

    if ($disableCanonical) {
      $current['disable_base_canonical'] = true;
    }

    $region = $this->normalizeRegion($region);
    $current['available_regions'] = array_values(array_unique(array_filter(array_merge(
      $current['available_regions'] ?? [],
      $region ? [$region] : []
    ))));

    return $current;
  }
}
