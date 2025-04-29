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
            <g:id>{{ $item->id }}</g:id>
            <title><![CDATA[{{ $item->title }}]]></title>
            <link>{{ $item->link }}</link>
            <description><![CDATA[{{ $item->summary }}]]></description>
            <g:availability>{{ $item->availability }}</g:availability>
            <g:image_link>{{ $item->image }}</g:image_link>
            <g:additional_image_link>{{ $item->second_image }}</g:additional_image_link>
            <g:price>{{ $item->price }}</g:price>
            <g:sale_price>{{ $item->sale_price }}</g:sale_price>
            <g:brand><![CDATA[{{ $item->brand }}]]></g:brand>
            <g:google_product_category>{{ $item->google_product_category }}</g:google_product_category>
            <g:product_type>{{ $item->product_type }}</g:product_type>
            <g:mpn>{{ $item->mpn }}</g:mpn>
            <g:gtin>{{ $item->gtin }}</g:gtin>
            <g:shipping>
              <g:country>UA</g:country>
              <g:service>Нова Пошта</g:service>
              <g:price>60.00 UAH</g:price>
            </g:shipping>
            <pubDate>{{ $item->updated->toRssString() }}</pubDate>
          </item>
        @endforeach

    </channel>
</rss>
