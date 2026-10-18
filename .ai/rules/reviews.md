---
paths:
  - 'app/Actions/Iris/Reviews/**'
---

# Reviews

## Review email star links never save on GET
The stars in the review reminder email open /review/{order}/{token}?rating=N with the rating preselected; the review is only stored when the customer presses Send (POST). Raul, 18 Oct 2026 (HELP-4098): email security scanners (Safe Links, Mimecast, Proofpoint) open every link, so saving on click or on page load would publish fake ratings under real customers. Revisit only with click-vs-submit numbers, and then save on a real interaction (scroll/tap), never on load.
