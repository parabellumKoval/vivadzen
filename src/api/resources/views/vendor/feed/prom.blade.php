<?=
/* Using an echo tag here so the `<? ... ?>` won't get parsed as short tags */
'<?xml version="1.0" encoding="UTF-8"?>'.PHP_EOL
?>
<rss version="2.0" xmlns:g="http://base.google.com/ns/1.0">
    <channel>
        <title><![CDATA[{{ $meta['title'] }}]]></title>
        <link><![CDATA[{{ url($meta['link']) }}]]></link>
        <description><![CDATA[{{ $meta['description'] }}]]></description>
        <language>{{ $meta['language'] }}</language>
        <pubDate>{{ $meta['updated'] }}</pubDate>

        <categories>
        @foreach($items['categories']->items as $category)
          <category id="{{ $category->prom_id }}">{{ $category->prom_name }}</category>
        @endforeach
        </categories>

				<items>
        @foreach($items['products']->items as $item)
          <item id="{{ $item->id }}">
          	<categoryId>{{ $item->promCategoryId }}</categoryId>
            <name><![CDATA[{{ $item->title }}]]></name>
            <name_ua><![CDATA[{{ $item->title_uk }}]]></name_ua>
            <link>{{ $item->link }}</link>
            <pubDate>{{ $item->updated->toRssString() }}</pubDate>
						<presence>{{ $item->presence }}</presence>
						<available>{{ $item->presence }}</available>
						<quantity_in_stock>{{ $item->inStock }}</quantity_in_stock>
						<description><![CDATA[{!! $item->summary !!}]]></description>
						<description_ua><![CDATA[{!! $item->summary_uk !!}]]></description_ua>
						<vendorCode><![CDATA[{!! $item->vendorCode !!}]]></vendorCode>
						<vendor><![CDATA[{!! $item->vendor !!}]]></vendor>
            @foreach($item->images as $image)
              <image><![CDATA[{{ $image }}]]></image>
            @endforeach
						<price>{{ $item->price }}</price>
            <oldprice>{{ $item->oldprice }}</oldprice>
						<mpn><![CDATA[{{ $item->mpn }}]]></mpn>
            @foreach($item->attributes as $attr)
              <param name="{{ $attr['name'] }}" unit="{{ $attr['si'] }}">{{ $attr['value'] }}</param>
            @endforeach
          </item>
        @endforeach
        </items>
    </channel>
</rss>
