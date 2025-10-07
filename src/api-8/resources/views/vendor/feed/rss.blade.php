
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

        @foreach($items as $item)
            <item>
                <title><![CDATA[{{ $item->title }}]]></title>
                <link>{{ $item->link }}</link>
                <pubDate>{{ $item->updated->toRssString() }}</pubDate>
                
                <g:id><![CDATA[{{ $item->id }}]]></g:id>
				<g:availability><![CDATA[{{ $item->availability }}]]></g:availability>
				<g:condition><![CDATA[{{ $item->condition }}]]></g:condition>
				<g:description><![CDATA[{!! $item->summary !!}]]></g:description>
				<g:image_link><![CDATA[{{ $item->image }}]]></g:image_link>
				<g:price>{{ $item->price }} UAH</g:price>
				<g:mpn><![CDATA[{{ $item->mpn }}]]></g:mpn>									
				<g:brand><![CDATA[{{ $item->brand }}]]></g:brand>
				<g:is_bundle>no [нет]</g:is_bundle>
				<g:google_product_category><![CDATA[{{ $item->google_product_category }}]]></g:google_product_category>
				<g:additional_image_link><![CDATA[{{ $item->second_image }}]]></g:additional_image_link>
				<g:sale_price>{{ $item->sale_price }} UAH</g:sale_price>
				<g:product_type><![CDATA[{{ $item->categoryName }}]]></g:product_type>		
            </item>
        @endforeach
    </channel>
</rss>
