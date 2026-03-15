/* ============================================================
   ClarityLabsUSA — Main JavaScript
   Navigation, Scroll Animations, Interactions
   ============================================================ */

(function () {
  'use strict';

  /* ── Mobile Navigation ── */
  const hamburger = document.getElementById('hamburger');
  const nav = document.getElementById('nav');

  if (hamburger && nav) {
    hamburger.addEventListener('click', function () {
      hamburger.classList.toggle('active');
      nav.classList.toggle('open');
      document.body.style.overflow = nav.classList.contains('open') ? 'hidden' : '';
    });

    nav.querySelectorAll('a').forEach(function (link) {
      link.addEventListener('click', function () {
        hamburger.classList.remove('active');
        nav.classList.remove('open');
        document.body.style.overflow = '';
      });
    });
  }

  /* ── Sticky Header ── */
  const header = document.getElementById('header');
  if (header) {
    var lastY = 0;
    window.addEventListener('scroll', function () {
      var y = window.pageYOffset;
      if (y > 50) {
        header.classList.add('header--scrolled');
      } else {
        header.classList.remove('header--scrolled');
      }
      lastY = y;
    }, { passive: true });
  }

  /* ── Smooth Scroll for Anchor Links ── */
  document.querySelectorAll('a[href^="#"]').forEach(function (anchor) {
    anchor.addEventListener('click', function (e) {
      var href = this.getAttribute('href');
      if (href === '#') return;
      var target = document.querySelector(href);
      if (target) {
        e.preventDefault();
        var offset = header ? header.offsetHeight : 0;
        var top = target.getBoundingClientRect().top + window.pageYOffset - offset;
        window.scrollTo({ top: top, behavior: 'smooth' });
      }
    });
  });

  /* ── Scroll Animations (IntersectionObserver) ── */
  var animElements = document.querySelectorAll('.fade-up, .slide-left, .slide-right');
  if (animElements.length > 0 && 'IntersectionObserver' in window) {
    var animObserver = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          entry.target.classList.add('visible');
          animObserver.unobserve(entry.target);
        }
      });
    }, { threshold: 0.15, rootMargin: '0px 0px -40px 0px' });

    animElements.forEach(function (el) {
      animObserver.observe(el);
    });
  } else {
    // Fallback: show everything immediately
    animElements.forEach(function (el) {
      el.classList.add('visible');
    });
  }

  /* ── Counter Animation ── */
  var counters = document.querySelectorAll('[data-count]');
  if (counters.length > 0 && 'IntersectionObserver' in window) {
    var counterObserver = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          animateCounter(entry.target);
          counterObserver.unobserve(entry.target);
        }
      });
    }, { threshold: 0.5 });

    counters.forEach(function (el) {
      counterObserver.observe(el);
    });
  }

  function animateCounter(el) {
    var target = parseInt(el.getAttribute('data-count'), 10);
    var suffix = el.textContent.replace(/[\d,]/g, '').trim();
    var duration = 2000;
    var start = 0;
    var startTime = null;

    function step(timestamp) {
      if (!startTime) startTime = timestamp;
      var progress = Math.min((timestamp - startTime) / duration, 1);
      var ease = 1 - Math.pow(1 - progress, 3); // ease-out cubic
      var current = Math.floor(ease * target);
      el.textContent = current.toLocaleString() + suffix;
      if (progress < 1) {
        requestAnimationFrame(step);
      }
    }
    requestAnimationFrame(step);
  }

  /* ── Product Size Selector ── */
  var sizeOptions = document.querySelectorAll('.size-option');
  var priceDisplay = document.getElementById('product-price');

  sizeOptions.forEach(function (option) {
    option.addEventListener('click', function () {
      sizeOptions.forEach(function (o) { o.classList.remove('active'); });
      option.classList.add('active');
      if (priceDisplay && option.dataset.price) {
        priceDisplay.textContent = option.dataset.price;
      }
    });
  });

  /* ── Product Image Gallery ── */
  var mainImg = document.querySelector('.product-hero__main-img img');
  var thumbs = document.querySelectorAll('.product-hero__thumb');

  thumbs.forEach(function (thumb) {
    thumb.addEventListener('click', function () {
      thumbs.forEach(function (t) { t.classList.remove('active'); });
      thumb.classList.add('active');
      if (mainImg && thumb.dataset.src) {
        mainImg.style.opacity = '0';
        setTimeout(function () {
          mainImg.src = thumb.dataset.src;
          mainImg.style.opacity = '1';
        }, 200);
      }
    });
  });

  /* ── Order Form Modal (3-Step) ── */
  var orderModal = document.getElementById('order-modal');
  var orderClose = document.getElementById('order-modal-close');
  var orderOverlay = orderModal ? orderModal.querySelector('.order-modal__overlay') : null;
  var orderBtns = document.querySelectorAll('.js-order-modal-open');
  var orderSizeInput = document.getElementById('order-size-input');
  var orderPriceInput = document.getElementById('order-price-input');
  var currentStep = 1;

  // Size option radios in modal
  var orderSizeOptions = document.querySelectorAll('.order-size-option');
  orderSizeOptions.forEach(function (opt) {
    opt.addEventListener('click', function () {
      orderSizeOptions.forEach(function (o) { o.classList.remove('active'); });
      opt.classList.add('active');
      var radio = opt.querySelector('input[type="radio"]');
      if (radio) {
        radio.checked = true;
        if (orderSizeInput) orderSizeInput.value = radio.value;
        if (orderPriceInput) orderPriceInput.value = radio.dataset.price;
        // Update price display
        var priceDisplay = document.getElementById('order-price-display');
        if (priceDisplay) priceDisplay.textContent = '$' + radio.dataset.price;
      }
    });
  });

  function goToStep(step) {
    currentStep = step;
    // Update step panels
    document.querySelectorAll('.order-step').forEach(function (s) { s.classList.remove('active'); });
    var target = document.querySelector('.order-step[data-step="' + step + '"]');
    if (target) target.classList.add('active');
    // Update step indicators
    document.querySelectorAll('.order-steps__item').forEach(function (item) {
      var s = parseInt(item.dataset.step, 10);
      item.classList.remove('active', 'done');
      if (s === step) item.classList.add('active');
      else if (s < step) item.classList.add('done');
    });
    // Scroll modal body to top
    var body = document.querySelector('.order-modal__body');
    if (body) body.scrollTop = 0;
  }

  function validateStep(step) {
    var stepEl = document.querySelector('.order-step[data-step="' + step + '"]');
    if (!stepEl) return true;
    var valid = true;
    var errorMsg = document.getElementById('order-form-error');
    stepEl.querySelectorAll('[required]').forEach(function (field) {
      if (field.type === 'checkbox') {
        if (!field.checked) { field.style.outline = '2px solid #D32F2F'; valid = false; }
        else { field.style.outline = ''; }
      } else if (!field.value.trim()) {
        field.style.borderColor = '#D32F2F'; valid = false;
      } else {
        field.style.borderColor = '';
      }
    });
    // Email validation on step 2
    if (step === 2) {
      var emailField = document.getElementById('order-email');
      if (emailField && emailField.value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailField.value)) {
        emailField.style.borderColor = '#D32F2F'; valid = false;
      }
    }
    if (!valid && errorMsg) {
      errorMsg.textContent = 'Please fill in all required fields.';
      errorMsg.style.display = 'block';
    } else if (errorMsg) {
      errorMsg.style.display = 'none';
    }
    return valid;
  }

  function openOrderModal(size) {
    if (!orderModal) return;
    // Pre-select size if provided
    if (size) {
      orderSizeOptions.forEach(function (opt) {
        var radio = opt.querySelector('input[type="radio"]');
        if (radio && radio.value === size) {
          orderSizeOptions.forEach(function (o) { o.classList.remove('active'); });
          opt.classList.add('active');
          radio.checked = true;
          if (orderSizeInput) orderSizeInput.value = radio.value;
          if (orderPriceInput) orderPriceInput.value = radio.dataset.price;
        }
      });
    } else {
      // Sync from hero size selector
      var activeSize = document.querySelector('.size-option.active .size-option__mg');
      if (activeSize) {
        var mg = activeSize.textContent;
        orderSizeOptions.forEach(function (opt) {
          var radio = opt.querySelector('input[type="radio"]');
          if (radio && radio.value === mg) {
            orderSizeOptions.forEach(function (o) { o.classList.remove('active'); });
            opt.classList.add('active');
            radio.checked = true;
            if (orderSizeInput) orderSizeInput.value = mg;
            if (orderPriceInput) orderPriceInput.value = radio.dataset.price;
          }
        });
      }
    }
    goToStep(1);
    orderModal.classList.add('open');
    document.body.style.overflow = 'hidden';
  }

  function closeOrderModal() {
    if (orderModal) {
      orderModal.classList.remove('open');
      document.body.style.overflow = '';
    }
  }

  orderBtns.forEach(function (btn) {
    btn.addEventListener('click', function () {
      openOrderModal(btn.dataset.size || '');
    });
  });

  if (orderClose) orderClose.addEventListener('click', closeOrderModal);
  if (orderOverlay) orderOverlay.addEventListener('click', closeOrderModal);
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && orderModal && orderModal.classList.contains('open')) {
      closeOrderModal();
    }
  });

  // Next / Back buttons
  document.querySelectorAll('.order-step__next').forEach(function (btn) {
    btn.addEventListener('click', function () {
      if (validateStep(currentStep)) {
        goToStep(parseInt(btn.dataset.next, 10));
      }
    });
  });
  document.querySelectorAll('.order-step__back').forEach(function (btn) {
    btn.addEventListener('click', function () {
      goToStep(parseInt(btn.dataset.back, 10));
    });
  });

  /* ── Order Form Submission ── */
  var orderForm = document.getElementById('order-form');
  if (orderForm) {
    orderForm.addEventListener('submit', function (e) {
      e.preventDefault();
      if (!validateStep(3)) return;

      var btn = orderForm.querySelector('.order-form__submit');
      var successMsg = document.getElementById('order-form-success');
      var errorMsg = document.getElementById('order-form-error');

      btn.textContent = 'Submitting...';
      btn.disabled = true;

      var formData = new FormData(orderForm);

      fetch('../php/order-mailer.php', {
        method: 'POST',
        body: formData
      })
        .then(function (res) { return res.json(); })
        .then(function (data) {
          if (data.success) {
            orderForm.style.display = 'none';
            document.querySelector('.order-steps').style.display = 'none';
            if (successMsg) successMsg.classList.add('show');
          } else {
            if (errorMsg) {
              errorMsg.textContent = data.message || 'Something went wrong. Please try again.';
              errorMsg.style.display = 'block';
            }
          }
        })
        .catch(function () {
          if (errorMsg) {
            errorMsg.textContent = 'Network error. Please try again later.';
            errorMsg.style.display = 'block';
          }
        })
        .finally(function () {
          btn.textContent = 'Submit Order';
          btn.disabled = false;
        });
    });
  }

  /* ── Shop Category Filters ── */
  var filterPills = document.querySelectorAll('.filter-pill');
  var shopCards = document.querySelectorAll('.shop-card');

  filterPills.forEach(function (pill) {
    pill.addEventListener('click', function () {
      filterPills.forEach(function (p) { p.classList.remove('active'); });
      pill.classList.add('active');
      var cat = pill.dataset.category;

      shopCards.forEach(function (card) {
        if (cat === 'all' || card.dataset.category === cat) {
          card.classList.remove('hidden');
          card.style.display = '';
        } else {
          card.classList.add('hidden');
        }
      });
    });
  });

  /* ── FAQ Accordion ── */
  var faqItems = document.querySelectorAll('.faq-item');

  faqItems.forEach(function (item) {
    var question = item.querySelector('.faq-question');
    var answer = item.querySelector('.faq-answer');

    if (question && answer) {
      question.addEventListener('click', function () {
        var isOpen = item.classList.contains('open');

        // Close all others
        faqItems.forEach(function (other) {
          if (other !== item) {
            other.classList.remove('open');
            var otherAnswer = other.querySelector('.faq-answer');
            if (otherAnswer) otherAnswer.style.maxHeight = null;
          }
        });

        // Toggle current
        if (isOpen) {
          item.classList.remove('open');
          answer.style.maxHeight = null;
        } else {
          item.classList.add('open');
          answer.style.maxHeight = answer.scrollHeight + 'px';
        }
      });
    }
  });

  /* ── FAQ Category Tabs ── */
  var faqTabs = document.querySelectorAll('.faq-tab');
  var faqAllItems = document.querySelectorAll('.faq-item');

  faqTabs.forEach(function (tab) {
    tab.addEventListener('click', function () {
      faqTabs.forEach(function (t) { t.classList.remove('active'); });
      tab.classList.add('active');
      var cat = tab.dataset.category;

      faqAllItems.forEach(function (item) {
        if (cat === 'all' || item.dataset.category === cat) {
          item.classList.remove('hidden');
          item.style.display = '';
        } else {
          item.classList.add('hidden');
          item.classList.remove('open');
          var answer = item.querySelector('.faq-answer');
          if (answer) answer.style.maxHeight = null;
        }
      });
    });
  });

  /* ── Contact Form AJAX ── */
  var contactForm = document.getElementById('contact-form');
  if (contactForm) {
    contactForm.addEventListener('submit', function (e) {
      e.preventDefault();
      var btn = contactForm.querySelector('.btn');
      var successMsg = document.getElementById('form-success');
      var errorMsg = document.getElementById('form-error');

      // Clear previous messages
      if (successMsg) successMsg.classList.remove('show');
      if (errorMsg) errorMsg.style.display = 'none';

      // Client-side validation
      var name = contactForm.querySelector('[name="name"]');
      var email = contactForm.querySelector('[name="email"]');
      var message = contactForm.querySelector('[name="message"]');
      var valid = true;

      [name, email, message].forEach(function (field) {
        if (field && !field.value.trim()) {
          field.style.borderColor = '#D32F2F';
          valid = false;
        } else if (field) {
          field.style.borderColor = '';
        }
      });

      if (email && email.value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value)) {
        email.style.borderColor = '#D32F2F';
        valid = false;
      }

      if (!valid) return;

      // Submit
      btn.textContent = 'Sending...';
      btn.disabled = true;

      var formData = new FormData(contactForm);

      fetch('php/contact-mailer.php', {
        method: 'POST',
        body: formData
      })
        .then(function (res) { return res.json(); })
        .then(function (data) {
          if (data.success) {
            if (successMsg) {
              successMsg.textContent = data.message || 'Message sent successfully. We\'ll be in touch soon.';
              successMsg.classList.add('show');
            }
            contactForm.reset();
          } else {
            if (errorMsg) {
              errorMsg.textContent = data.message || 'Something went wrong. Please try again.';
              errorMsg.style.display = 'block';
            }
          }
        })
        .catch(function () {
          if (errorMsg) {
            errorMsg.textContent = 'Network error. Please try again later.';
            errorMsg.style.display = 'block';
          }
        })
        .finally(function () {
          btn.textContent = 'Send Message';
          btn.disabled = false;
        });
    });
  }

})();
