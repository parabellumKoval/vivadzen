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
import cartModal from './components/cart-modal.js';
import productReviews from './components/product-reviews.js';
import productQuestions from './components/product-questions.js';
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
Alpine.data('cartModal', cartModal);
Alpine.data('productReviews', productReviews);
Alpine.data('productQuestions', productQuestions);

Alpine.start();

// Initialize cart store with whatever was pre-rendered on the page.
const bootstrapEl = document.getElementById('cart-bootstrap');
const initial = bootstrapEl ? JSON.parse(bootstrapEl.textContent || '{}') : null;
initCart(initial);
