<?=
/* Using an echo tag here so the `<? ... ?>` won't get parsed as short tags */
'<?xml version="1.0" encoding="UTF-8"?>'.PHP_EOL
?>
<rss version="2.0">
  <channel>
    <title><![CDATA[{{ $meta['title'] }}]]></title>
    <link><![CDATA[{{ url($meta['link']) }}]]></link>
    <description><![CDATA[{{ $meta['description'] }}]]></description>
    <language>{{ $meta['language'] }}</language>
    <pubDate>{{ $meta['updated'] }}</pubDate>

    @foreach($items as $item)
      <item>
        <id>{{ $item->id }}</id>
        <title><![CDATA[{{ $item->title }}]]></title>
        <description><![CDATA[{{ $item->summary }}]]></description>
        <link>{{ $item->link }}</link>
        <availability>{{ $item->availability }}</availability>
        <condition>{{ $item->condition }}</condition>
        <image_link>{{ $item->image }}</image_link>
        <additional_image_link>{{ $item->second_image }}</additional_image_link>
        <price>{{ $item->price }}</price>
        <sale_price>{{ $item->sale_price }}</sale_price>
        <brand><![CDATA[{{ $item->brand }}]]></brand>
        <custom_label_0>{{ $item->category_0 }}</custom_label_0>
        <custom_label_1>{{ $item->category_1 }}</custom_label_1>
        <custom_label_2>{{ $item->category_2 }}</custom_label_2>
        <custom_label_3>{{ $item->category_3 }}</custom_label_3>
        <custom_label_4>{{ $item->category_4 }}</custom_label_4>
        <google_product_category>{{ $item->google_product_category }}</google_product_category>
        <mpn>{{ $item->mpn }}</mpn>
        <gtin>{{ $item->gtin }}</gtin>
        <shipping>
          <country>UA</country>
          <service>Нова Пошта</service>
          <price>60.00 UAH</price>
        </shipping>
        <pubDate>{{ $item->updated->toRssString() }}</pubDate>
      </item>
    @endforeach
  </channel>
</rss>
