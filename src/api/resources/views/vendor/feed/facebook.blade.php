<?=
/* Using an echo tag here so the `<? ... ?>` won't get parsed as short tags */
'<?xml version="1.0" encoding="UTF-8"?>'.PHP_EOL
?>
<rss xmlns:g="http://base.google.com/ns/1.0" version="2.0">
  <channel>
    <title><![CDATA[{{ $meta['title'] }}]]></title>
    <link><![CDATA[{{ url($meta['link']) }}]]></link>
    <description><![CDATA[{{ $meta['description'] }}]]></description>
    <language>{{ $meta['language'] }}</language>
    <pubDate>{{ $meta['updated'] }}</pubDate>

    @foreach($items as $item)
      <item>
        <g:id>{{ $item->id }}</g:id>
        <g:title><![CDATA[{{ $item->title }}]]></g:title>
        <g:description><![CDATA[{{ $item->summary }}]]></g:description>
        <g:link>{{ $item->link }}</g:link>
        <g:availability>{{ $item->availability }}</g:availability>
        <g:condition>{{ $item->condition }}</g:condition>
        <g:image_link>{{ $item->image }}</g:image_link>
        <g:additional_image_link>{{ $item->second_image }}</g:additional_image_link>
        <g:price>{{ $item->price }}</g:price>
        <g:sale_price>{{ $item->sale_price }}</g:sale_price>
        <g:brand><![CDATA[{{ $item->brand }}]]></g:brand>
        <g:custom_label_0>{{ $item->category_0 }}</g:custom_label_0>
        <g:custom_label_1>{{ $item->category_1 }}</g:custom_label_1>
        <g:custom_label_2>{{ $item->category_2 }}</g:custom_label_2>
        <g:custom_label_3>{{ $item->category_3 }}</g:custom_label_3>
        <g:custom_label_4>{{ $item->category_4 }}</g:custom_label_4>
        <g:google_product_category>{{ $item->google_product_category }}</g:google_product_category>
        <g:mpn>{{ $item->mpn }}</g:mpn>
        <g:gtin>{{ $item->gtin }}</g:gtin>
        <g:shipping>
          <g:country>UA</g:country>
          <g:service>Нова Пошта</g:service>
          <g:price>60.00 UAH</g:price>
        </g:shipping>
      </item>
    @endforeach
  </channel>
</rss>
