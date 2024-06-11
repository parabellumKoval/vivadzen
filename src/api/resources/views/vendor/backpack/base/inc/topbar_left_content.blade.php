<!-- This file is used to store topbar (left) items -->

<li class="nav-item px-3">
	<a href="{{ backpack_url('order') }}" class="nav-link">
		<i class='nav-icon las la-shopping-cart'></i> Заказы <span class="badge badge-{{ $orders > 0? 'warning' : 'light' }}" style="position:initial">{{ $orders }}</span>
	</a>
</li>
<li class="nav-item px-3">
	<a href="{{ backpack_url('feedback') }}" class="nav-link">
		<i class='nav-icon las la-envelope-open-text'></i> Обратная связь <span class="badge badge-{{ $feedback > 0? 'warning' : 'light' }}" style="position:initial">{{ $feedback }}</span>
	</a>
</li>
<li class="nav-item px-3">
	<a href="{{ backpack_url('review') }}" class="nav-link">
		<i class='nav-icon las la-comments'></i> Отзывы <span class="badge badge-{{ $reviews > 0? 'warning' : 'light' }}" style="position:initial">{{ $reviews }}</span>
	</a>
</li>
