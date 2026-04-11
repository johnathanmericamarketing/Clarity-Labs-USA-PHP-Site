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

  /* ── Sticky Header ──
     Uses hysteresis (add at 60px, remove at 30px) to prevent oscillation:
     when the sticky header shrinks on scroll, it changes document height
     and can pull pageYOffset back under the threshold, which would toggle
     the class off, grow the header, and re-trigger the whole loop. With
     separate add/remove thresholds the small height change can never flip
     the state back. Also rAF-throttled so rapid scroll events coalesce. */
  const header = document.getElementById('header');
  if (header) {
    var isScrolled = false;
    var rafPending = false;
    window.addEventListener('scroll', function () {
      if (rafPending) return;
      rafPending = true;
      window.requestAnimationFrame(function () {
        rafPending = false;
        var y = window.pageYOffset;
        if (!isScrolled && y > 60) {
          header.classList.add('header--scrolled');
          isScrolled = true;
        } else if (isScrolled && y < 30) {
          header.classList.remove('header--scrolled');
          isScrolled = false;
        }
      });
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

  /* ── Legacy Order Modal — Redirect to Shop ── */
  /* Order form removed 2026-04-11. All "Order Now" buttons redirect to the
     authenticated shop checkout instead of opening the old unauthenticated
     form modal. */
  var orderBtns = document.querySelectorAll('.js-order-modal-open');
  orderBtns.forEach(function (btn) {
    btn.addEventListener('click', function (e) {
      e.preventDefault();
      window.location.href = 'https://shop.claritylabsusa.com/';
    });
  });

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
