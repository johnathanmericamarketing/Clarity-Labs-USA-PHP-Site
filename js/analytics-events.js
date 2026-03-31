/* ============================================================
   ClarityLabsUSA — GA4 E-Commerce Event Tracking
   Fires standard GA4 e-commerce events for the shop.
   Only fires if gtag() is available.
   ============================================================ */

var ClarityAnalytics = {
  /**
   * Check if gtag is loaded
   */
  ready: function() {
    return typeof gtag === 'function';
  },

  /**
   * view_item — fired when a product detail page loads
   * @param {Object} product - { name, sku, price, category }
   */
  viewItem: function(product) {
    if (!this.ready()) return;
    gtag('event', 'view_item', {
      currency: 'USD',
      value: parseFloat(product.price) || 0,
      items: [{
        item_id: product.sku || '',
        item_name: product.name || '',
        price: parseFloat(product.price) || 0,
        item_category: product.category || '',
        quantity: 1
      }]
    });
  },

  /**
   * add_to_cart — fired when user adds a product to cart
   * @param {Object} item - { name, sku, price, category, quantity }
   */
  addToCart: function(item) {
    if (!this.ready()) return;
    gtag('event', 'add_to_cart', {
      currency: 'USD',
      value: (parseFloat(item.price) || 0) * (parseInt(item.quantity) || 1),
      items: [{
        item_id: item.sku || '',
        item_name: item.name || '',
        price: parseFloat(item.price) || 0,
        item_category: item.category || '',
        quantity: parseInt(item.quantity) || 1
      }]
    });
  },

  /**
   * remove_from_cart — fired when user removes item from cart
   * @param {Object} item - { name, sku, price, quantity }
   */
  removeFromCart: function(item) {
    if (!this.ready()) return;
    gtag('event', 'remove_from_cart', {
      currency: 'USD',
      value: (parseFloat(item.price) || 0) * (parseInt(item.quantity) || 1),
      items: [{
        item_id: item.sku || '',
        item_name: item.name || '',
        price: parseFloat(item.price) || 0,
        quantity: parseInt(item.quantity) || 1
      }]
    });
  },

  /**
   * begin_checkout — fired when user starts checkout
   * @param {Array} items - array of { name, sku, price, quantity }
   * @param {Number} total - cart total
   */
  beginCheckout: function(items, total) {
    if (!this.ready()) return;
    gtag('event', 'begin_checkout', {
      currency: 'USD',
      value: parseFloat(total) || 0,
      items: items.map(function(item) {
        return {
          item_id: item.sku || '',
          item_name: item.name || '',
          price: parseFloat(item.price) || 0,
          quantity: parseInt(item.quantity) || 1
        };
      })
    });
  },

  /**
   * add_shipping_info — fired when user selects shipping
   * @param {Number} shippingCost
   * @param {String} shippingMethod
   */
  addShippingInfo: function(shippingCost, shippingMethod) {
    if (!this.ready()) return;
    gtag('event', 'add_shipping_info', {
      currency: 'USD',
      value: parseFloat(shippingCost) || 0,
      shipping_tier: shippingMethod || ''
    });
  },

  /**
   * purchase — fired when order is successfully placed
   * @param {Object} order - { id, total, shipping, tax, items }
   */
  purchase: function(order) {
    if (!this.ready()) return;
    gtag('event', 'purchase', {
      transaction_id: order.id || '',
      value: parseFloat(order.total) || 0,
      currency: 'USD',
      shipping: parseFloat(order.shipping) || 0,
      tax: parseFloat(order.tax) || 0,
      items: (order.items || []).map(function(item) {
        return {
          item_id: item.sku || '',
          item_name: item.name || '',
          price: parseFloat(item.price) || 0,
          quantity: parseInt(item.quantity) || 1
        };
      })
    });
  },

  /**
   * sign_up — fired on new account registration
   */
  signUp: function(method) {
    if (!this.ready()) return;
    gtag('event', 'sign_up', { method: method || 'email' });
  },

  /**
   * login — fired on customer login
   */
  login: function(method) {
    if (!this.ready()) return;
    gtag('event', 'login', { method: method || 'email' });
  }
};
