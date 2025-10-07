<!-- This file is used to store topbar (left) items -->

<li class="nav-item px-3">
	<a href="{{ backpack_url('order') }}" class="nav-link">
		<i class='nav-icon las la-shopping-cart'></i> {{ trans('backpack_menu.orders') }} <span class="badge badge-{{ $orders > 0? 'warning' : 'light' }}" style="position:initial">{{ $orders }}</span>
	</a>
</li>
<li class="nav-item px-3">
	<a href="{{ backpack_url('feedback') }}" class="nav-link">
		<i class='nav-icon las la-envelope-open-text'></i> {{ trans('backpack_menu.feedback') }} <span class="badge badge-{{ $feedback > 0? 'warning' : 'light' }}" style="position:initial">{{ $feedback }}</span>
	</a>
</li>
<li class="nav-item px-3">
	<a href="{{ backpack_url('review') }}" class="nav-link">
		<i class='nav-icon las la-comments'></i> {{ trans('backpack_menu.reviews') }} <span class="badge badge-{{ $reviews > 0? 'warning' : 'light' }}" style="position:initial">{{ $reviews }}</span>
	</a>
</li>
