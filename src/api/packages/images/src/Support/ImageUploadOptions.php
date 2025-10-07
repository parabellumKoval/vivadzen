<?php

namespace ParabellumKoval\BackpackImages\Support;

class ImageUploadOptions
{
    public function __construct(
        public ?string $provider = null,
        public ?string $folder = null,
        public ?bool $preserveOriginalName = null,
        public ?bool $generateUniqueName = null,
        public ?string $logChannel = null
    ) {
    }

    public static function fromConfig(array $config): self
    {
        return new self(
            provider: $config['default_provider'] ?? 'local',
            folder: $config['default_folder'] ?? '',
            preserveOriginalName: $config['preserve_original_name'] ?? false,
            generateUniqueName: $config['generate_unique_name'] ?? true,
            logChannel: $config['logging_channel'] ?? 'stack'
        );
    }

    public function merge(self $fallback): self
    {
        return new self(
            provider: $this->provider ?? $fallback->provider,
            folder: $this->folder ?? $fallback->folder,
            preserveOriginalName: $this->preserveOriginalName ?? $fallback->preserveOriginalName,
            generateUniqueName: $this->generateUniqueName ?? $fallback->generateUniqueName,
            logChannel: $this->logChannel ?? $fallback->logChannel
        );
    }

    public function withOverrides(array $overrides): self
    {
        $options = clone $this;

        foreach ($overrides as $key => $value) {
            if (property_exists($options, $key)) {
                $options->{$key} = $value;
            }
        }

        return $options;
    }

    public function resolvedFolder(string $defaultFolder): string
    {
        $baseFolder = trim($defaultFolder, '/');
        $customFolder = trim((string) ($this->folder ?? ''), '/');

        if ($baseFolder === '') {
            return $customFolder;
        }

        if ($customFolder === '') {
            return $baseFolder;
        }

        return $baseFolder . '/' . $customFolder;
    }
}
