<!-- This file is used to store sidebar items, starting with Backpack\Base 0.9.0 -->
<li class="nav-item"><a class="nav-link" href="{{ backpack_url('dashboard') }}"><i class="la la-home nav-icon"></i> {{ trans('backpack_menu.dashboard') }}</a></li>

<li class="divider"></li>
<li class="nav-title">{{ trans('backpack_menu.commerce') }}</li>


<li class="nav-item d-block d-lg-none"><a class="nav-link" href="{{ backpack_url('order') }}"><i class="nav-icon las la-shopping-cart"></i> <span>{{ trans('backpack_menu.orders') }}</span> <span class="badge badge-{{ $orders > 0? 'warning' : 'light' }}" style="position:initial">{{ $orders }}</span></a></li>
<li class="nav-item d-block d-lg-none"><a class="nav-link" href="{{ backpack_url('feedback') }}"><i class="nav-icon las la-envelope-open-text"></i> <span>{{ trans('backpack_menu.feedback') }}</span> <span class="badge badge-{{ $feedback > 0? 'warning' : 'light' }}" style="position:initial">{{ $feedback }}</span></a></li>
<li class="nav-item d-block d-lg-none"><a class="nav-link" href="{{ backpack_url('review') }}"><i class="nav-icon las la-comments"></i> <span>{{ trans('backpack_menu.reviews') }}</span> <span class="badge badge-{{ $reviews > 0? 'warning' : 'light' }}" style="position:initial">{{ $reviews }}</span></a></li>

<!-- START SHOP -->
<li class="nav-item nav-dropdown">
	<a class="nav-link nav-dropdown-toggle" href="#"><i class="nav-icon las la-store"></i> {{ trans('backpack_menu.shop') }}</a>
	<ul class="nav-dropdown-items">
	  <li class='nav-item'><a class='nav-link' href='{{ backpack_url('product') }}'><i class='nav-icon las la-shopping-bag'></i> {{ trans('backpack_menu.products') }}</a></li>
	  <li class='nav-item'><a class='nav-link' href='{{ backpack_url('attribute') }}'><i class='nav-icon las la-tag'></i> {{ trans('backpack_menu.attributes') }}</a></li>
    <li class='nav-item'>
      <a class='nav-link' href='{{ backpack_url('promocode') }}'>
        <i class='nav-icon las la-percentage'></i> {{ trans('backpack_menu.promocodes') }}
      </a>
    </li>
	</ul>
</li>

<li class="nav-item nav-dropdown">
	<a class="nav-link nav-dropdown-toggle" href="#"><i class="nav-icon las la-list-ul"></i> {{ trans('backpack_menu.catalogs') }}</a>
	<ul class="nav-dropdown-items">
	  <li class='nav-item'><a class='nav-link' href='{{ backpack_url('category') }}'><i class='nav-icon las la-tags'></i> {{ trans('backpack_menu.categories') }}</a></li>
    <!-- <li class='nav-item'><a class='nav-link' href='{{ backpack_url('region') }}'><i class='nav-icon las la-map-marker-alt'></i> {{ trans('backpack_menu.regions') }}</a></li> -->
    <li class='nav-item'><a class='nav-link' href='{{ backpack_url('brand') }}'><i class='nav-icon las la-copyright'></i> {{ trans('backpack_menu.brands') }}</a></li>
	</ul>
</li>


<li class="nav-item nav-dropdown">
	<a class="nav-link nav-dropdown-toggle" href="#"><i class="nav-icon las la-warehouse"></i> {{ trans('backpack_menu.warehouse') }}</a>
	<ul class="nav-dropdown-items">
    <li class='nav-item'><a class='nav-link' href='{{ backpack_url('supplier') }}'><i class="nav-icon las la-boxes"></i> {{ trans('backpack_menu.suppliers') }}</a></li>
	</ul>
</li>

<!-- <li class='nav-item'>
	<a class='nav-link' href='{{ backpack_url('feed') }}'>
		<i class='nav-icon las la-cloud-download-alt'></i> {{ trans('backpack_menu.downloads') }}
	</a>
</li> -->
<!-- END SHOP -->

<li class="divider"></li>
<li class="nav-title">{{ trans('backpack_menu.website') }}</li>

<!-- Tags -->
<li class='nav-item'><a class='nav-link' href='{{ backpack_url('tag') }}'><i class='nav-icon las la-tags'></i> {{ trans('backpack_menu.tags') }}</a></li>

<!-- Guidebook -->
<li class="nav-item"><a class="nav-link" href="{{ backpack_url('article') }}"><i class="nav-icon la la-newspaper-o"></i> {{ trans('backpack_menu.articles') }}</a></li>

<!-- <li class="nav-item"><a class="nav-link" href="{{ backpack_url('elfinder') }}"><i class="nav-icon la la-files-o"></i> <span>{{ trans('backpack::crud.file_manager') }}</span></a></li> -->

<li class="divider"></li>
<li class="nav-title">Автоматизация</li>

<!-- DeepL -->
<li class='nav-item'>
	<a class='nav-link' href='{{ backpack_url('translation-history') }}'>
		<!-- <img class="nav-icon" src="/deepl-blue-logo_24x24.svg" width="20" height="20" alt="DeepL" />   -->
		<i class='nav-icon las la-language'></i>
		{{ trans('backpack_menu.deepl_translations') }}
	</a>
</li>

<!-- Pages -->
<!-- <li class='nav-item'><a class='nav-link' href='{{ backpack_url('page') }}'><i class='nav-icon las la-file'></i> <span>Страницы</span></a></li> -->

<!-- Banners -->
<!-- <li class='nav-item'><a class='nav-link' href='{{ backpack_url('banner') }}'><i class='nav-icon la la-desktop'></i> Баннеры</a></li> -->

<!-- Users -->
<!-- <li class="nav-item"><a class="nav-link" href="{{ backpack_url('profile') }}"><i class="nav-icon la la-user"></i> Пользователи</a></li> -->


<li class="divider"></li>

<li class="nav-title">{{ trans('backpack_menu.management') }}</li>

<li class="nav-item nav-dropdown">
	<a class="nav-link nav-dropdown-toggle" href="#"><i class="nav-icon la la-gear"></i> Настройки</a>
	<ul class="nav-dropdown-items">
		<li class="nav-item"><a class="nav-link" href="{{ backpack_url('settings/site') }}"><i class="nav-icon la la-info-circle"></i> <span>Общие</span></a></li>
		<!-- <li class="nav-item"><a class="nav-link" href="{{ backpack_url('settings/store') }}"><i class="nav-icon la la-user"></i> <span>Магазин</span></a></li> -->
		<li class="nav-item"><a class="nav-link" href="{{ backpack_url('settings/delivery') }}"><i class="nav-icon la la-shipping-fast"></i> <span>Доставка</span></a></li>
		<li class="nav-item"><a class="nav-link" href="{{ backpack_url('settings/payment') }}"><i class="nav-icon la la-credit-card"></i> <span>Оплата</span></a></li>
		<li class="nav-item"><a class="nav-link" href="{{ backpack_url('settings/search') }}"><i class="nav-icon la la-search"></i> <span>Поиск</span></a></li>
		<li class="nav-item"><a class="nav-link" href="{{ backpack_url('product-list') }}"><i class="nav-icon la la-list"></i> <span>Списки</span></a></li>
	</ul>
</li>


<li class="nav-item nav-dropdown">
	<a class="nav-link nav-dropdown-toggle" href="#"><i class="nav-icon la la-cubes"></i> Сервисы</a>
	<ul class="nav-dropdown-items">
		<li class="nav-item"><a class="nav-link" href="{{ backpack_url('seo-page') }}"><i class="nav-icon la la-bullseye"></i> <span>Посадочные страницы</span></a></li>
		<li class="nav-item"><a class="nav-link" href="{{ backpack_url('search-queries') }}"><i class="nav-icon la la-search"></i> <span>Поиск</span></a></li>
		<li class="nav-item"><a class="nav-link" href="{{ backpack_url('currency-rates') }}"><i class="nav-icon la la-exchange"></i> <span>Курсы валют</span></a></li>
	</ul>
</li>

<li class="nav-item nav-dropdown">
	<a class="nav-link nav-dropdown-toggle" href="#"><i class="nav-icon la la-group"></i> Пользователи</a>
	<ul class="nav-dropdown-items">
		<!-- <li class="nav-item"><a class="nav-link" href="{{ backpack_url('profile-dashboard') }}"><i class="nav-icon la la-columns"></i> <span>Дашбоард</span></a></li> -->
		<li class="nav-item"><a class="nav-link" href="{{ backpack_url('profile') }}"><i class="nav-icon la la-user-circle"></i> <span>Профили</span></a></li>
		<!-- <li class="nav-item"><a class="nav-link" href="{{ backpack_url('referrals') }}"><i class="nav-icon la la-user-plus"></i> <span>Реферальная сеть</span></a></li> -->
		<li class="nav-item"><a class="nav-link" href="{{ backpack_url('settings/profile') }}"><i class="nav-icon la la-user-edit"></i> <span>Настройки</span></a></li>
		<li class="nav-item"><a class="nav-link" href="{{ backpack_url('withdrawals') }}"><i class="nav-icon la la-money-bill-wave"></i> <span>Вывод средств</span></a></li>

		<li class="nav-item"><a class="nav-link" href="{{ backpack_url('reward-events') }}"><i class="nav-icon la la-hand-holding-usd"></i> <span>События</span></a></li>
		<li class="nav-item"><a class="nav-link" href="{{ backpack_url('rewards') }}"><i class="nav-icon la la-clipboard-list"></i> <span>Вознаграждения</span></a></li>
		<li class="nav-item"><a class="nav-link" href="{{ backpack_url('wallet-ledger') }}"><i class="nav-icon la la-exchange-alt"></i> <span>Транзакции</span></a></li>
	</ul>
</li>

<!-- Users, Roles, Permissions -->
<!-- <li class="nav-item nav-dropdown">
	<a class="nav-link nav-dropdown-toggle" href="#"><i class="nav-icon la la-group"></i> {{ trans('backpack_menu.administration') }}</a>
	<ul class="nav-dropdown-items">
	  <li class="nav-item"><a class="nav-link" href="{{ backpack_url('user') }}"><i class="nav-icon la la-user"></i> <span>{{ trans('backpack_menu.users') }}</span></a></li>
	  <li class="nav-item"><a class="nav-link" href="{{ backpack_url('role') }}"><i class="nav-icon la la-group"></i> <span>{{ trans('backpack_menu.roles') }}</span></a></li>
	  <li class="nav-item"><a class="nav-link" href="{{ backpack_url('permission') }}"><i class="nav-icon la la-key"></i> <span>{{ trans('backpack_menu.permissions') }}</span></a></li>
	</ul>
</li>

<li class='nav-item'><a class='nav-link' href='{{ backpack_url('vercel') }}'><i class='nav-icon las la-code-branch'></i> {{ trans('backpack_menu.vercel') }}</a></li>
<li class='nav-item'><a class='nav-link' href='{{ backpack_url('backup') }}'><i class='nav-icon la la-hdd-o'></i> {{ trans('backpack_menu.backups') }}</a></li>
<li class='nav-item'><a class='nav-link' href='{{ backpack_url('log') }}'><i class='nav-icon la la-terminal'></i> {{ trans('backpack_menu.logs') }}</a></li> -->
