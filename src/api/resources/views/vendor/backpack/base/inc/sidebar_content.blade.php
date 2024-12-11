<!-- This file is used to store sidebar items, starting with Backpack\Base 0.9.0 -->
<li class="nav-item"><a class="nav-link" href="{{ backpack_url('dashboard') }}"><i class="la la-home nav-icon"></i> {{ trans('backpack::base.dashboard') }}</a></li>

<li class="divider"></li>
<li class="nav-title">Коммерция</li>

<!-- START SHOP -->
<li class="nav-item nav-dropdown">
	<a class="nav-link nav-dropdown-toggle" href="#"><i class="nav-icon las la-store"></i> Магазин</a>
	<ul class="nav-dropdown-items">
	  <li class='nav-item'><a class='nav-link' href='{{ backpack_url('product') }}'><i class='nav-icon las la-shopping-bag'></i> Товары</a></li>
	  <li class='nav-item'><a class='nav-link' href='{{ backpack_url('attribute') }}'><i class='nav-icon las la-tag'></i> Атрибуты</a></li>
    <li class='nav-item'>
      <a class='nav-link' href='{{ backpack_url('promocode') }}'>
        <i class='nav-icon las la-percentage'></i> Купоны
      </a>
    </li>
	</ul>
</li>

<li class="nav-item nav-dropdown">
	<a class="nav-link nav-dropdown-toggle" href="#"><i class="nav-icon las la-list-ul"></i> Каталоги</a>
	<ul class="nav-dropdown-items">
	  <li class='nav-item'><a class='nav-link' href='{{ backpack_url('category') }}'><i class='nav-icon las la-tags'></i> Категории</a></li>
    <li class='nav-item'><a class='nav-link' href='{{ backpack_url('region') }}'><i class='nav-icon las la-map-marker-alt'></i> Регионы</a></li>
    <li class='nav-item'><a class='nav-link' href='{{ backpack_url('brand') }}'><i class='nav-icon las la-copyright'></i> Бренды</a></li>
	</ul>
</li>


<li class="nav-item nav-dropdown">
	<a class="nav-link nav-dropdown-toggle" href="#"><i class="nav-icon las la-warehouse"></i> Склад</a>
	<ul class="nav-dropdown-items">
    <li class='nav-item'><a class='nav-link' href='{{ backpack_url('supplier') }}'><i class="nav-icon las la-boxes"></i> Поставщики</a></li>
    <li class='nav-item'><a class='nav-link' href='{{ backpack_url('source') }}'><i class='nav-icon las la-link'></i> XML-источники</a></li>
    <li class='nav-item'><a class='nav-link' href='{{ backpack_url('upload') }}'><i class='nav-icon las la-history'></i> История выгрузок</a></li>
	</ul>
</li>

<li class='nav-item'>
	<a class='nav-link' href='{{ backpack_url('feed') }}'>
		<i class='nav-icon las la-cloud-download-alt'></i> Выгрузки
	</a>
</li>

<li class='nav-item'>
	<a class='nav-link' href='{{ backpack_url('payment') }}'>
		<i class='nav-icon las la-credit-card'></i> Платежи
	</a>
</li>
<!-- END SHOP -->

<li class="divider"></li>
<li class="nav-title">Сайт</li>

<!-- Tags -->
<li class='nav-item'><a class='nav-link' href='{{ backpack_url('tag') }}'><i class='nav-icon las la-tags'></i> Теги</a></li>

<!-- Guidebook -->
<li class="nav-item"><a class="nav-link" href="{{ backpack_url('article') }}"><i class="nav-icon la la-newspaper-o"></i> Статьи</a></li>

<!-- Prompts -->
<li class='nav-item'><a class='nav-link' href='{{ backpack_url('prompt') }}'><i class='nav-icon las la-brain'></i> AI Prompts</a></li>

<!-- Pages -->
<!-- <li class='nav-item'><a class='nav-link' href='{{ backpack_url('page') }}'><i class='nav-icon las la-file'></i> <span>Страницы</span></a></li> -->

<!-- Banners -->
<!-- <li class='nav-item'><a class='nav-link' href='{{ backpack_url('banner') }}'><i class='nav-icon la la-desktop'></i> Баннеры</a></li> -->

<!-- Users -->
<!-- <li class="nav-item"><a class="nav-link" href="{{ backpack_url('profile') }}"><i class="nav-icon la la-user"></i> Пользователи</a></li> -->


<li class="divider"></li>
<li class="nav-title">Управление</li>

<li class='nav-item'><a class='nav-link' href='{{ backpack_url('settings') }}'><i class='nav-icon la la-cog'></i> Настройки</a></li>
<!-- Users, Roles, Permissions -->
<li class="nav-item nav-dropdown">
	<a class="nav-link nav-dropdown-toggle" href="#"><i class="nav-icon la la-group"></i> Администрация</a>
	<ul class="nav-dropdown-items">
	  <li class="nav-item"><a class="nav-link" href="{{ backpack_url('user') }}"><i class="nav-icon la la-user"></i> <span>Пользователи</span></a></li>
	  <li class="nav-item"><a class="nav-link" href="{{ backpack_url('role') }}"><i class="nav-icon la la-group"></i> <span>Роли</span></a></li>
	  <li class="nav-item"><a class="nav-link" href="{{ backpack_url('permission') }}"><i class="nav-icon la la-key"></i> <span>Права</span></a></li>
	</ul>
</li>
<li class='nav-item'><a class='nav-link' href='{{ backpack_url('vercel') }}'><i class='nav-icon las la-code-branch'></i> Vercel</a></li>
<li class='nav-item'><a class='nav-link' href='{{ backpack_url('backup') }}'><i class='nav-icon la la-hdd-o'></i> Копии</a></li>
<li class='nav-item'><a class='nav-link' href='{{ backpack_url('log') }}'><i class='nav-icon la la-terminal'></i> Логи</a></li>
<li class="nav-item"><a class="nav-link" href="{{ backpack_url('elfinder') }}"><i class="nav-icon la la-files-o"></i> <span>{{ trans('backpack::crud.file_manager') }}</span></a></li>