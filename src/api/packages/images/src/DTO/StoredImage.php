<?php

namespace ParabellumKoval\BackpackImages\DTO;

class StoredImage
{
    public function __construct(
        public readonly string $url,
        public readonly string $path,
        public readonly string $filename,
        public readonly string $extension
    ) {
    }

    public function toArray(): array
    {
        return [
            'url' => $this->url,
            'path' => $this->path,
            'filename' => $this->filename,
            'extension' => $this->extension,
        ];
    }
}
