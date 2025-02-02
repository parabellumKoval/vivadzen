<?php

namespace App\Models;

use Illuminate\Support\Facades\Http;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class Bunny 
{
  private $client = null;
  private $folder = null;
  private $images = null;
  
  /**
   * __construct
   *
   * @param  mixed $folder
   * @return void
   */
  public function __construct($folder) {
    $this->client = new \Bunny\Storage\Client(
      config('bunny.key'),
      config('bunny.zone')
    );

    $this->folder = $folder;
  }

    
  /**
   * delete
   *
   * @param  mixed $url
   * @return void
   */
  public function delete($url) {

    try {
      $response = $this->client->delete($url);
    }catch(\Exception $e){
      return false;
    }

    $results = json_decode($response, true);

    if($results['HttpCode'] === 201) {
      return true;
    }else {
      return false;
    }
  }
    
  /**
   * store
   *
   * @return void
   */
  public function store($local_path, $remote_path) {
    
    if(!File::exists($local_path)) {
      throw new \Exception('File not exists: ' . $local_path);
      \Log::error('File not exists: ' . $local_path);
      return;
    }

    $uploads_result = $this->client->upload($local_path, $remote_path);
    $results = json_decode($uploads_result, true);

    if($results['HttpCode'] === 201) {
      // $this->info($results['Message']);
      return true;
    }else {
      // $this->error($results['Message'] . "\n");
      return false;
    }
  }
          
    /**
     * getRemoteUrl
     *
     * @param  mixed $src
     * @return void
     */
    public function getRemoteUrl($src) {
      return $this->folder . '/' . $src;
    }

    /**
     * removeSomeImages
     *
     * @param  mixed $images
     * @return void
     */
    public function removeSomeImages($new_images) {
      $images_to_remove = [];

      // foreach old images
      foreach($this->images as $image) {
        $image_src = $image['src'];
        $is_exists = false;

        // Try find in new images array
        foreach($new_images as $new_image) {
          if(Str::startsWith($new_image->src, 'data:image')) {
            continue;
          }

          // set to is_exists - true if find
          if($image_src === basename($new_image->src)) {
            $is_exists = true;
          }
        }

        if(!$is_exists) {
          $images_to_remove[] = $image_src;
        }
      }

      foreach($images_to_remove as $image) {
        $url = $this->getRemoteUrl($image);
        $this->delete($url);
      }
    }
    
    /**
     * removeAllImages
     *
     * @return void
     */
    public function removeAllImages($images = null) {
      
      if($images) {
        $this->images = $images;
      }
      
      if(empty($this->images)) {
        return;
      }

      foreach($this->images as $image) {
        $url = $this->getRemoteUrl($image['src']);
        $this->delete($url);
      }

    }

    /**
     * storeImage
     *
     * @param  mixed $value
     * @return void
     */
    public function storeImage($value) {
      
      $disk = 'temp';

      // if a base64 was sent or if this is remote image, store it in the db
      if (Str::startsWith($value, 'data:image') || (Str::startsWith($value, 'http') && $this->checkRemoteFile($value)))
      {

        // 0. Make the image
        $image = \Image::make($value)->encode('jpg');

        // 1. Generate a filename.
        $filename = md5($value.time()).'.jpg';

        // 2. Store the image on disk.
        \Storage::disk($disk)->put($this->folder . '/' . $filename, $image->stream());


        $image_path = \Storage::disk($disk)->path($this->folder . '/' . $filename);
      
        // dd($image_path);
        $response = $this->storeBunny($image_path, $filename);
        $this->tempImageDelete($image_path);

        return $filename;
      }else {
        return basename($value);
      }
      
    }
        
    /**
     * tempImageDelete
     *
     * @return void
     */
    public function tempImageDelete($image_url) {
      \File::delete($image_url);
    }

    /**
     * storeBunny
     *
     * @return void
     */
    public function storeBunny($local_image, $filename) {

      $remote_path = $this->folder . '/' . $filename;
      $this->store($local_image, $remote_path);
    }
    
    /**
     * The function `storeImages` stores new images while handling existing images in an array,
     * filtering out empty image sources and transforming objects to arrays.
     * 
     * @param values The `storeImages` function you provided seems to handle storing images based on
     * the input values. The `values` parameter is expected to be an array of objects representing
     * images, where each object has properties like `src`, `alt`, and `title`.
     * @param old_images The `storeImages` function you provided seems to handle storing images based
     * on the input values and old images. The `old_images` parameter is used to pass in any existing
     * images that need to be considered during the image storage process.
     * 
     * @return The `storeImages` function returns an array of new images with their `src`, `alt`, and
     * `title` values after processing the input values.
     */
    public function storeImages($values, $old_images = null) {

      $this->images = $old_images;

      $new_images_array = [];

      // Filter items with empty image src
      $images = array_filter($values, function($item) {
        $item_array = (array)$item;
        if(!empty($item_array['src'])) {
          return $item;
        }
      });

      // Transform object to array deep
      $images = array_map(function($item) {
        return (array)$item;
      }, $images);


      if(!empty($this->images)) {
        if(empty($images)) {
          $this->removeAllImages();
          return null;
        }else {
          $this->removeSomeImages($images);
        }
      }

      foreach($images as $image) {
        $src = $this->storeImage($image['src']);
        $new_images_array[] = [
          'src' => $src,
          'alt' => $image['alt'] ?? null,
          'title' => $image['title'] ?? null
        ];
      }

      return $new_images_array;
    }

    /**
     * The function `checkRemoteFile` checks if a remote file exists by sending an HTTP GET request to the
     * specified URL.
     * 
     * @param url The `checkRemoteFile` function is designed to check if a remote file exists by making an
     * HTTP GET request to the provided URL. If the response is successful (status code 2xx), it returns
     * `true`, indicating that the file exists. Otherwise, it returns `false`.
     * 
     * @return The function `checkRemoteFile()` returns a boolean value. It returns `true` if the HTTP
     * response from the given URL is successful (status code 2xx), and `false` otherwise.
     */
    public function checkRemoteFile($url) {
      $response = Http::get($url);

      if($response->ok()) {
        return true;
      }else {
        return false;
      }
    }
}