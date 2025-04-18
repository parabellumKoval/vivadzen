<div @class(['product-admin-name', 'product-admin-name--needs-moderation' => $needModeration])>
    @if($needModeration)
        <div class="product-admin-name__moderation-icon" title="Есть непроверенные данные">
            <img src="/lamp.png" width="20" height="20" />
        </div>
    @endif

    <div class="product-admin-name__tags">
        @if($isTrans)
            <button class="btn btn-sm" title="Название и Контент переведен автоматически">
                <img src="/deepl-blue-logo_24x24.svg" width="15" height="15" />
                <b style="color: #0f2b46;">DeepL</b>
            </button>
        @endif

        @if($isAnyAi)
            <button class="btn btn-sm" title="Контент сгенерирован chatGPT">
                <img src="/openai.png" width="15" height="15" />
                <b>OpenAi</b>
            </button>
        @endif

        @if($isImagesGenerated)
            <button class="btn btn-sm" title="Изображения загружены автоматически">
                <img src="/serper.png" width="15" height="15" />
                <b>Serper.dev</b>
            </button>
        @endif
    </div>

    <div class="product-admin-name__title">{{ $name }}</div>

    <div class="product-admin-name__metadata">
        @if($brand)
            <span class="product-admin-name__metadata-label">Бренд:</span>
            <b>{!! $brandLinkAdmin !!}</b>&nbsp;&nbsp;
        @endif
        
        @if($category)
            <span class="product-admin-name__metadata-label">Категории:</span>
            <b>{!! $categoryLinksAdmin !!}</b>
        @endif
    </div>
</div>