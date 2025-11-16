<?php namespace Barryvdh\Elfinder;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Extended elFinder connector
 *
 * @author Dmitry (dio) Levashov
 **/
class Connector extends \elFinderConnector {

    /** @var Response */
    protected $response;

    /**
     * Constructor
     *
     * @param \elFinder $elFinder
     * @param bool $debug
     */
    public function __construct($elFinder, $debug = false)
    {
        $this->ensureServerVariables();
        
        // Debug logging for upload issues
        if (request()->isMethod('POST') && !empty($_FILES)) {
            \Log::info('ElFinder Upload Request', [
                '_FILES' => $_FILES,
                '_POST' => $_POST,
                '_GET' => $_GET,
            ]);
        }
        
        parent::__construct($elFinder, $debug);
    }

    /**
     * Ensure $_SERVER and global variables are set for Laravel/Octane compatibility
     */
    protected function ensureServerVariables()
    {
        $request = request();
        
        // Ensure $_SERVER['REQUEST_METHOD'] is set and always available
        $_SERVER['REQUEST_METHOD'] = $request->method();
        
        // Ensure other common server variables
        if (!isset($_SERVER['HTTP_HOST'])) {
            $_SERVER['HTTP_HOST'] = $request->getHost();
        }
        
        if (!isset($_SERVER['SERVER_NAME'])) {
            $_SERVER['SERVER_NAME'] = $request->getHost();
        }
        
        if (!isset($_SERVER['REQUEST_URI'])) {
            $_SERVER['REQUEST_URI'] = $request->getRequestUri();
        }
        
        // Ensure $_GET and $_POST are populated from Laravel Request
        if (empty($_GET) && $request->query->count() > 0) {
            $_GET = $request->query->all();
        }
        
        if (empty($_POST) && $request->request->count() > 0) {
            $_POST = $request->request->all();
        }
        
        // Ensure $_FILES is populated
        if (empty($_FILES) && $request->files->count() > 0) {
            foreach ($request->files->all() as $key => $file) {
                if (is_array($file)) {
                    $_FILES[$key] = [];
                    foreach ($file as $idx => $f) {
                        // For Swoole/Octane compatibility, copy temp file to ensure it persists
                        $tmpPath = $f->getPathname();
                        if (strpos($tmpPath, 'swoole.upfile') !== false) {
                            $newTmpPath = sys_get_temp_dir() . '/elfinder_' . uniqid() . '_' . $f->getClientOriginalName();
                            copy($tmpPath, $newTmpPath);
                            $tmpPath = $newTmpPath;
                        }
                        
                        $_FILES[$key]['name'][$idx] = $f->getClientOriginalName();
                        $_FILES[$key]['type'][$idx] = $f->getClientMimeType();
                        $_FILES[$key]['tmp_name'][$idx] = $tmpPath;
                        $_FILES[$key]['error'][$idx] = $f->getError();
                        $_FILES[$key]['size'][$idx] = $f->getSize();
                    }
                } else {
                    // For Swoole/Octane compatibility, copy temp file to ensure it persists
                    $tmpPath = $file->getPathname();
                    if (strpos($tmpPath, 'swoole.upfile') !== false) {
                        $newTmpPath = sys_get_temp_dir() . '/elfinder_' . uniqid() . '_' . $file->getClientOriginalName();
                        copy($tmpPath, $newTmpPath);
                        $tmpPath = $newTmpPath;
                    }
                    
                    $_FILES[$key] = [
                        'name' => $file->getClientOriginalName(),
                        'type' => $file->getClientMimeType(),
                        'tmp_name' => $tmpPath,
                        'error' => $file->getError(),
                        'size' => $file->getSize(),
                    ];
                }
            }
        }
    }

    /**
     * @return Response
     */
    public function getResponse(){
        return $this->response;
    }

    /**
     * Output json
     *
     * @param  array  data to output
     * @return void
     * @author Dmitry (dio) Levashov
     **/
    protected function output(array $data) {

        $header = isset($data['header']) ? $data['header'] : $this->header;
        unset($data['header']);

        $headers = array();
        if($header){
            foreach((array) $header as $headerString){
                if(strpos($headerString, ':') !== false){
                    list($key, $value) = explode(':', $headerString, 2);
                    $headers[$key] = $value;
                }
            }
        }

        if (isset($data['pointer'])) {
            $this->response = new StreamedResponse(function () use($data) {
                    if (stream_get_meta_data($data['pointer'])['seekable']) {
                        rewind($data['pointer']);
                    }
                    fpassthru($data['pointer']);
                    if (!empty($data['volume'])) {
                        $data['volume']->close($data['pointer'], $data['info']['hash']);
                    }
                }, 200, $headers);
        } else {
            if (!empty($data['raw']) && !empty($data['error'])) {
                $this->response = new JsonResponse($data['error'], 500);
            } else {
                $this->response = new JsonResponse($data, 200, $headers);
            }
        }
    }
}
