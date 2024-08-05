
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

				<items>
        @foreach($items as $item)
          <item id="{{ $item->id }}">
          	<categoryId><![CDATA[{{ 110341818 }}]]></categoryId>
            <name><![CDATA[{{ $item->title }}]]></name>
            <link>{{ $item->link }}</link>
            <pubDate>{{ $item->updated->toRssString() }}</pubDate>
						<presence>{{ $item->presence }}</presence>
						<available>{{ $item->presence }}</available>
						<quantity_in_stock>{{ $item->quantity_in_stock }}</quantity_in_stock>
						<description><![CDATA[{!! $item->description !!}]]></description>
						<vendorCode><![CDATA[{!! $item->vendorCode !!}]]></vendorCode>
						<vendor><![CDATA[{!! $item->brand !!}]]></vendor>
						<image><![CDATA[{{ $item->image }}]]></image>
						<price>{{ $item->price }}</price>
						<mpn><![CDATA[{{ $item->mpn }}]]></mpn>
          </item>
        @endforeach
        </items>
    </channel>
</rss>
