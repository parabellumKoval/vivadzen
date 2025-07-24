<?php

namespace App\Console\Commands\Image;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

use Illuminate\Support\Facades\Http;


use Google\Cloud\Vision\V1\ImageAnnotatorClient;
use Google\Cloud\Vision\V1\Feature\Type;
use GuzzleHttp\Client as GuzzleClient; // Для скачивания изображения по URL

use OpenAI;

class GoogleVision extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vision:analyze-url {imageUrl?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update catalog cache';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
      parent::__construct();

      // available languages
      $this->available_languages = config('backpack.crud.locales');
      $this->langs_list = array_keys($this->available_languages);

    //     $path = env('GOOGLE_APPLICATION_CREDENTIALS');
    //     $path = 'app/google/djini-419110-d89dd1d53393.json';
    //     $contents = Storage::get($path);
    //     dd($contents);
    }


    public function handle()
    {

        // 1. Получаем URL изображения
        // Если аргумент передан, используем его. Иначе - хардкодим для быстрого теста.
        $imageUrl = $this->argument('imageUrl');

        if (!$imageUrl) {
            $imageUrl = 'https://www.biotechusa.ru/shop/assets/img/product/p/BioTechUSA-Iso-Whey-Zero-500g-RU.png'; // Пример лицевой стороны
            // $imageUrl = 'https://www.protein-store.co.uk/media/catalog/product/cache/1/image/1000x/040ec09b1e35df139433887a97daa66f/i/s/iso-whey-zero-500g-ingredienti.png'; // Пример состава
            $this->info("URL изображения не был предоставлен. Используется URL по умолчанию: " . $imageUrl);
        } else {
            $this->info("Анализ изображения по URL: " . $imageUrl);
        }

        $imageContents = null;

        try {
            // Скачиваем изображение по URL
            $guzzleClient = new GuzzleClient();
            $response = $guzzleClient->get($imageUrl);

            if ($response->getStatusCode() === 200) {
                $imageContents = $response->getBody()->getContents();
            } else {
                $this->error('Не удалось скачать изображение по указанному URL. Код статуса: ' . $response->getStatusCode());
                return Command::FAILURE;
            }

        } catch (\Exception $e) {
            $this->error('Ошибка при скачивании изображения: ' . $e->getMessage());
            return Command::FAILURE;
        }

        if (empty($imageContents)) {
            $this->error('Не удалось получить содержимое изображения по URL.');
            return Command::FAILURE;
        }

        // 2. Создаем клиент Vision API и делаем запрос
        $imageAnnotator = new ImageAnnotatorClient();

        try {
            $visionResponse = $imageAnnotator->annotateImage(
                new \Google\Cloud\Vision\V1\Image(['content' => $imageContents]),
                [
                    new \Google\Cloud\Vision\V1\Feature(['type' => Type::LABEL_DETECTION]),       // Обнаружение меток
                    new \Google\Cloud\Vision\V1\Feature(['type' => Type::TEXT_DETECTION]),        // Обнаружение текста (OCR)
                    new \Google\Cloud\Vision\V1\Feature(['type' => Type::OBJECT_LOCALIZATION]),   // Обнаружение объектов (часто полезно)
                    new \Google\Cloud\Vision\V1\Feature(['type' => Type::WEB_DETECTION]),         // Веб-сущности (для поиска похожих в интернете)
                ]
            );

            $imageAnnotator->close(); // Закрываем клиент после использования

            // 3. Собираем нужные данные для вывода
            $result = [
                'image_url_analyzed' => $imageUrl,
                'labels' => [],
                'full_text' => '',
                'text_blocks' => [],
                'objects' => [], // Обнаруженные объекты
                'web_entities' => [], // Веб-сущности
            ];

            // Метки (Labels)
            if ($visionResponse->getLabelAnnotations()) {
                foreach ($visionResponse->getLabelAnnotations() as $label) {
                    $result['labels'][] = [
                        'description' => $label->getDescription(),
                        'score' => $label->getScore(),
                    ];
                }
            }

            // Полный текст (OCR)
            if ($visionResponse->getFullTextAnnotation()) {
                $result['full_text'] = $visionResponse->getFullTextAnnotation()->getText();
            }

            // Блоки текста (с координатами)
            if ($visionResponse->getTextAnnotations()) {
                foreach ($visionResponse->getTextAnnotations() as $textAnnotation) {
                    // Пропускаем первый элемент, который обычно является полным текстом
                    if ($textAnnotation->getDescription() === $result['full_text'] && count($result['text_blocks']) === 0) {
                        continue;
                    }
                    $result['text_blocks'][] = [
                        'text' => $textAnnotation->getDescription(),
                        'bounds' => $textAnnotation->getBoundingPoly() ? array_map(function($vertex) {
                            return ['x' => $vertex->getX(), 'y' => $vertex->getY()];
                        }, iterator_to_array($textAnnotation->getBoundingPoly()->getVertices())) : [],
                    ];
                }
            }

            // Обнаруженные объекты
            if ($visionResponse->getLocalizedObjectAnnotations()) {
                foreach ($visionResponse->getLocalizedObjectAnnotations() as $object) {
                    $result['objects'][] = [
                        'name' => $object->getName(),
                        'score' => $object->getScore(),
                        'bounding_box' => array_map(function($vertex) {
                            return ['x' => $vertex->getX(), 'y' => $vertex->getY()];
                        }, iterator_to_array($object->getBoundingPoly()->getNormalizedVertices()))
                    ];
                }
            }

            // Веб-сущности (полезно для поиска похожих изображений/страниц)
            if ($visionResponse->getWebDetection()) {
                if ($webEntities = $visionResponse->getWebDetection()->getWebEntities()) {
                    foreach ($webEntities as $entity) {
                        $result['web_entities'][] = [
                            'description' => $entity->getDescription(),
                            'score' => $entity->getScore(),
                        ];
                    }
                }
                // Также можно посмотреть getPagesWithMatchingImages, getFullMatchingImages, getPartialMatchingImages
                // но для dd() это может быть слишком много.
            }

            // 4. Выводим результат через dd()
            dd($result);

            return Command::SUCCESS;

        } catch (\Exception $e) {
            dd('EX', $e->getCode());
            // Если произошла ошибка при работе с Vision API
            $imageAnnotator->close();
            $this->error('Ошибка при работе с Google Cloud Vision API: ' . $e->getMessage());
            $this->error('Пожалуйста, убедитесь, что ваш файл GOOGLE_APPLICATION_CREDENTIALS настроен правильно и API включен.');
            return Command::FAILURE;
        }
    }
       

}
