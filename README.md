# Clarity Labs USA

**Research-Grade Peptides. Third-Party Tested. Trusted Since 2018.**

Clarity Labs USA is a US-based research peptide supplier built on full transparency — every compound is independently tested, every batch comes with a Certificate of Analysis, and every product meets research-grade purity standards.

---

## What We Do

Clarity Labs provides high-purity peptides for the research community. Our catalog spans multiple categories:

- **Recovery & Repair** — BPC-157, TB-500, GHK-Cu, SS-31
- **Metabolic** — Retatrutide, Tirzepatide, 5-Amino-1MQ, AOD-9604
- **Growth Hormone** — CJC-1295 / Ipamorelin, Tesamorelin
- **Cognitive & Neuro** — MOTS-C, Semax, Selank
- **Immune & Wellness** — KPV, NAD+, PT-141, Thymosin Alpha-1

All compounds are sold for research and laboratory use only.

## Our Standards

- **Third-Party Tested** — Every compound undergoes independent laboratory analysis
- **COA on Every Batch** — Certificates of Analysis available for every lot
- **US-Based Fulfillment** — All orders ship from within the United States
- **Contaminant Screened** — Identity verified, purity tested, contaminants screened

## About

ClarityLabs wasn't started by a marketing team or a venture fund. It was started by someone who spent four decades in the trenches — training, coaching, competing, and constantly learning what the body actually needs to perform and recover. That perspective drives everything we do.

**Clarity. Confidence. Simplicity.**

---

## Tech Stack

- **Backend:** PHP (vanilla, no framework)
- **Frontend:** HTML, CSS, vanilla JavaScript
- **Design System:** Custom CSS with BEM naming, CSS custom properties
- **Email:** PHP `mail()` with MIME multipart for PDF attachments
- **Images:** WebP with responsive `<picture>` elements for mobile

## Project Structure

```
├── index.php                    # Home page
├── shop.php                     # Shop / product listing
├── about.php                    # About us
├── faq.php                      # FAQ
├── contact.php                  # Contact form
├── products/
│   ├── index.php                # Product router
│   └── product-template.php     # Product detail page template
├── includes/
│   ├── header.php               # Navigation with mega menu
│   ├── footer.php               # Site footer
│   ├── head.php                 # Meta tags, fonts, CSS
│   └── product-data.php         # Product database (PHP array)
├── php/
│   ├── order-mailer.php         # Order form email handler
│   └── contact-mailer.php       # Contact form email handler
├── css/
│   └── styles.css               # Full design system
├── js/
│   └── main.js                  # Image gallery, order modal, animations
├── images/
│   └── products/{slug}/
│       ├── images/              # Product images (hero, thumbs, modal)
│       └── pdf/                 # COA PDFs (auto-attached to emails)
└── Logo/                        # Brand assets
```

## Image Naming Conventions

Product images are auto-discovered by filename pattern:

| Pattern | Used For |
|---------|----------|
| `*800*` | Hero / main product image |
| `*220*` | Thumbnail preview |
| `check_*` | Order modal product image |
| `*mockup*` | Modal fallback image |
| `*mobile*` | Mobile-specific shop card |
| `*COA*` | Certificate of Analysis thumbnail |

---

*Built with care by [Evo Tech](https://github.com/johnathanmericamarketing)*
