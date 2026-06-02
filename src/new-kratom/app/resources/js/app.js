import './bootstrap';

import Alpine from 'alpinejs';

import header from './components/header.js';
import announcementBar from './components/announcement-bar.js';
import productCard from './components/product-card.js';
import productBuy from './components/product-buy.js';
import newsletterForm from './components/newsletter-form.js';
import cartPage from './components/cart-page.js';
import checkout from './components/checkout.js';
import catalogFilter from './components/catalog-filter.js';
import filterModal from './components/filter-modal.js';
import cartModal from './components/cart-modal.js';
import productReviews from './components/product-reviews.js';
import productQuestions from './components/product-questions.js';
import reviewsSlider from './components/reviews-slider.js';
import carousel from './components/carousel.js';
import reviewsPage from './components/reviews-page.js';
import authModal from './components/auth-modal.js';
import adultoModal from './components/adulto-modal.js';
import adultoWidget from './components/adulto-widget.js';
import accountPage from './components/account-page.js';
import confirmModal from './components/confirm-modal.js';
import phoneMask from './components/phone-mask.js';
import cityPicker from './components/city-picker.js';
import forumIndex from './components/forum-index.js';
import forumTopic from './components/forum-topic.js';
import forumNew from './components/forum-new.js';
import forumUser from './components/forum-user.js';
import './components/lab-batch.js';
import { initCart } from './cart.js';

window.Alpine = Alpine;

Alpine.data('header', header);
Alpine.data('announcementBar', announcementBar);
Alpine.data('productCard', productCard);
Alpine.data('productBuy', productBuy);
Alpine.data('newsletterForm', newsletterForm);
Alpine.data('cartPage', cartPage);
Alpine.data('checkout', checkout);
Alpine.data('catalogFilter', catalogFilter);
Alpine.data('filterModal', filterModal);

// Catalog filter state is exposed as a global store so the filter modal
// (which is teleported to <body> to escape <main>'s isolate stacking context)
// can share state with the in-page chip-row and product grid.
Alpine.store('catalog', catalogFilter());
Alpine.data('cartModal', cartModal);
Alpine.data('productReviews', productReviews);
Alpine.data('productQuestions', productQuestions);
Alpine.data('reviewsSlider', reviewsSlider);
Alpine.data('carousel', carousel);
Alpine.data('reviewsPage', reviewsPage);
Alpine.data('authModal', authModal);
Alpine.data('adultoModal', adultoModal);
Alpine.data('adultoWidget', adultoWidget);
Alpine.data('accountPage', accountPage);
Alpine.data('confirmModal', confirmModal);
Alpine.data('phoneMask', phoneMask);
Alpine.data('cityPicker', cityPicker);
Alpine.data('forumIndex', forumIndex);
Alpine.data('forumTopic', forumTopic);
Alpine.data('forumNew', forumNew);
Alpine.data('forumUser', forumUser);

Alpine.start();

// Initialize cart store with whatever was pre-rendered on the page.
const bootstrapEl = document.getElementById('cart-bootstrap');
const initial = bootstrapEl ? JSON.parse(bootstrapEl.textContent || '{}') : null;
initCart(initial);
