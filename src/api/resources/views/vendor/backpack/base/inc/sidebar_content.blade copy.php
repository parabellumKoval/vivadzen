<!-- This file is used to store sidebar items, starting with Backpack\Base 0.9.0 -->
<li class="nav-item"><a class="nav-link" href="{{ backpack_url('dashboard') }}"><i class="la la-home nav-icon"></i> {{ trans('backpack::base.dashboard') }}</a></li>
<li class="nav-item"><a class="nav-link" href="{{ backpack_url('review') }}"><i class="la la-comments nav-icon"></i> Отзывы</a></li>
<li class="nav-item"><a class="nav-link" href="{{ backpack_url('article') }}"><i class="la la-comments nav-icon"></i> Статьи</a></li>
<li class="nav-item"><a class="nav-link" href="{{ backpack_url('banner') }}"><i class="la la-comments nav-icon"></i> Баннеры</a></li>
<li class="nav-item"><a class="nav-link" href="{{ backpack_url('feedback') }}"><i class="la la-comments nav-icon"></i> Обратная связь</a></li>


<li class="nav-item nav-dropdown">
	<a class="nav-link nav-dropdown-toggle" href="#"><i class="nav-icon las la-store"></i> Магазин</a>
	<ul class="nav-dropdown-items">
	  <li class='nav-item'><a class='nav-link' href='{{ backpack_url('product') }}'><i class='nav-icon las la-shopping-bag'></i> Товары</a></li>
	  <li class='nav-item'><a class='nav-link' href='{{ backpack_url('category') }}'><i class='nav-icon las la-tags'></i> Категории</a></li>
	  <li class='nav-item'><a class='nav-link' href='{{ backpack_url('attribute') }}'><i class='nav-icon las la-tag'></i> Атрибуты</a></li>
    <li class='nav-item'><a class='nav-link' href='{{ backpack_url('order') }}'><i class='nav-icon las la-shopping-cart'></i> Заказы</a></li>
    <li class='nav-item'><a class='nav-link' href='{{ backpack_url('brand') }}'><i class='nav-icon las la-shopping-cart'></i> Бренды</a></li>
    <li class='nav-item'><a class='nav-link' href='{{ backpack_url('supplier') }}'><i class='nav-icon las la-shopping-cart'></i> Поставщики</a></li>
    <li class='nav-item'><a class='nav-link' href='{{ backpack_url('source') }}'><i class='nav-icon las la-shopping-cart'></i> XML-источники</a></li>
    <li class='nav-item'><a class='nav-link' href='{{ backpack_url('upload') }}'><i class='nav-icon las la-shopping-cart'></i> История выгрузок</a></li>
		
	</ul>
</li>

<li class="nav-item nav-dropdown">
	<a class="nav-link nav-dropdown-toggle" href="#"><i class="nav-icon la la-gear"></i> Настройки</a>
	<ul class="nav-dropdown-items">
		<li class="nav-item"><a class="nav-link" href="{{ backpack_url('settings/common') }}"><i class="nav-icon la la-user"></i> <span>Общие</span></a></li>
		<li class="nav-item"><a class="nav-link" href="{{ backpack_url('settings/store') }}"><i class="nav-icon la la-user"></i> <span>Магазин</span></a></li>
		<li class="nav-item"><a class="nav-link" href="{{ backpack_url('settings/store-modules') }}"><i class="nav-icon la la-user"></i> <span>Модули</span></a></li>
		<li class="nav-item"><a class="nav-link" href="{{ backpack_url('settings/search') }}"><i class="nav-icon la la-search"></i> <span>Поиск</span></a></li>
		<li class="nav-item"><a class="nav-link" href="{{ backpack_url('product-list') }}"><i class="nav-icon la la-search"></i> <span>Списки</span></a></li>
		<li class="nav-item"><a class="nav-link" href="{{ backpack_url('product-list-item') }}"><i class="nav-icon la la-search"></i> <span>Списки items</span></a></li>
	</ul>
</li>

<li class="nav-item nav-dropdown">
	<a class="nav-link nav-dropdown-toggle" href="#"><i class="nav-icon la la-cubes"></i> Сервисы</a>
	<ul class="nav-dropdown-items">
		<li class="nav-item"><a class="nav-link" href="{{ backpack_url('seo-page') }}"><i class="nav-icon la la-search"></i> <span>Посадочные страницы</span></a></li>
		<li class="nav-item"><a class="nav-link" href="{{ backpack_url('search-queries') }}"><i class="nav-icon la la-search"></i> <span>Поиск</span></a></li>
		<li class="nav-item"><a class="nav-link" href="{{ backpack_url('currency-rates') }}"><i class="nav-icon la la-exchange"></i> <span>Курсы валют</span></a></li>
	</ul>
</li>


<li class="nav-item nav-dropdown">
	<a class="nav-link nav-dropdown-toggle" href="#"><i class="nav-icon la la-group"></i> Пользователи</a>
	<ul class="nav-dropdown-items">
		<li class="nav-item"><a class="nav-link" href="{{ backpack_url('profile-dashboard') }}"><i class="nav-icon la la-columns"></i> <span>Дашбоард</span></a></li>
		<li class="nav-item"><a class="nav-link" href="{{ backpack_url('profile') }}"><i class="nav-icon la la-user-circle"></i> <span>Профили</span></a></li>
		<li class="nav-item"><a class="nav-link" href="{{ backpack_url('referrals') }}"><i class="nav-icon la la-user-plus"></i> <span>Реферальная сеть</span></a></li>
		<li class="nav-item"><a class="nav-link" href="{{ backpack_url('settings/profile') }}"><i class="nav-icon la la-user-edit"></i> <span>Настройки</span></a></li>
		<li class="nav-item"><a class="nav-link" href="{{ backpack_url('withdrawals') }}"><i class="nav-icon la la-money-bill-wave"></i> <span>Вывод средств</span></a></li>

		<li class="nav-item"><a class="nav-link" href="{{ backpack_url('reward-events') }}"><i class="nav-icon la la-hand-holding-usd"></i> <span>События</span></a></li>
		<li class="nav-item"><a class="nav-link" href="{{ backpack_url('rewards') }}"><i class="nav-icon la la-clipboard-list"></i> <span>Вознаграждения</span></a></li>
		<li class="nav-item"><a class="nav-link" href="{{ backpack_url('wallet-ledger') }}"><i class="nav-icon la la-exchange-alt"></i> <span>Транзакции</span></a></li>
	</ul>
</li>