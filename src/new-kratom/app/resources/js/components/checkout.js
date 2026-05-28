// Checkout — local state for the radio groups (delivery / payment).
export default (initial = {}) => ({
    deliveryMethod: initial.deliveryMethod || 'courier',
    paymentMethod: initial.paymentMethod || 'card',
});
