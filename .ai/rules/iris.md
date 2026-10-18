---
paths:
  - 'resources/js/Components/Iris/**'
---

# Iris

## New Iris component files get no Tailwind CSS until the iris dev server restarts
tailwind.iris.config.js scans only the files Iris imports, via app-module-graph.cjs, which caches that list once per process. A .vue file created after `npm run dev.iris` started is missing from the scan, so classes only it uses (size-9, bg-green-50/70, ...) are never generated, and images render at full size. Restart dev.iris after adding a new file Iris imports, or give layout-critical sizing scoped CSS. Production builds are unaffected.
