import { ref as S, onMounted as Q, onBeforeUnmount as me, openBlock as a, createElementBlock as h, renderSlot as y, watch as U, createBlock as x, withCtx as C, createElementVNode as s, normalizeClass as $, withModifiers as B, withDirectives as D, vShow as R, resolveDynamicComponent as I, toDisplayString as p, createCommentVNode as k, computed as _, Fragment as P, renderList as O, unref as t, createVNode as z, createTextVNode as M, nextTick as be, getCurrentInstance as ye, onUnmounted as we, Transition as xe } from "vue";
import { createPopper as ke } from "@popperjs/core/lib/popper-lite";
import _e from "@popperjs/core/lib/modifiers/preventOverflow";
import $e from "@popperjs/core/lib/modifiers/flip";
import Ce from "lodash-es/uniq";
import Se from "lodash-es/find";
import K from "qs";
import qe from "lodash-es/clone";
import Be from "lodash-es/filter";
import Fe from "lodash-es/findKey";
import T from "lodash-es/forEach";
import Pe from "lodash-es/isEqual";
import Oe from "lodash-es/map";
import Te from "lodash-es/pickBy";
const je = {
  __name: "OnClickOutside",
  props: {
    do: {
      type: Function,
      required: !0
    }
  },
  setup(e) {
    const o = e, u = S(null), i = S(null);
    return Q(() => {
      u.value = (c) => {
        c.target === i.value || i.value.contains(c.target) || o.do();
      }, document.addEventListener("click", u.value), document.addEventListener("touchstart", u.value);
    }), me(() => {
      document.removeEventListener("click", u.value), document.removeEventListener("touchstart", u.value);
    }), (c, r) => (a(), h("div", {
      ref_key: "root",
      ref: i
    }, [
      y(c.$slots, "default")
    ], 512));
  }
}, Ie = { class: "relative" }, Ve = ["dusk", "disabled", "onClick"], Le = { class: "mt-2 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5" }, Y = {
  __name: "ButtonWithDropdown",
  props: {
    placement: {
      type: String,
      default: "bottom-start",
      required: !1
    },
    active: {
      type: Boolean,
      default: !1,
      required: !1
    },
    dusk: {
      type: String,
      default: null,
      required: !1
    },
    disabled: {
      type: Boolean,
      default: !1,
      required: !1
    }
  },
  setup(e, { expose: o }) {
    const u = e, i = S(!1), c = S(null);
    function r() {
      i.value = !i.value;
    }
    function l() {
      i.value = !1;
    }
    U(i, () => {
      c.value.update();
    });
    const f = S(null), m = S(null);
    return Q(() => {
      c.value = ke(f.value, m.value, {
        placement: u.placement,
        modifiers: [$e, _e]
      });
    }), o({ hide: l }), (F, q) => (a(), x(je, { do: l }, {
      default: C(() => [
        s("div", Ie, [
          s("button", {
            ref_key: "button",
            ref: f,
            type: "button",
            dusk: e.dusk,
            disabled: e.disabled,
            class: $(["w-full bg-white border rounded-md shadow-sm px-4 py-2 inline-flex justify-center text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500", { "border-green-300": e.active, "border-gray-300": !e.active, "cursor-not-allowed": e.disabled }]),
            "aria-haspopup": "true",
            onClick: B(r, ["prevent"])
          }, [
            y(F.$slots, "button")
          ], 10, Ve),
          D(s("div", {
            ref_key: "tooltip",
            ref: m,
            class: "absolute z-10"
          }, [
            s("div", Le, [
              y(F.$slots, "default")
            ])
          ], 512), [
            [R, i.value]
          ])
        ])
      ]),
      _: 3
    }));
  }
}, Me = { class: "flex flex-row items-center" }, ze = { class: "uppercase" }, De = ["sorted"], Re = {
  key: 0,
  fill: "currentColor",
  d: "M41 288h238c21.4 0 32.1 25.9 17 41L177 448c-9.4 9.4-24.6 9.4-33.9 0L24 329c-15.1-15.1-4.4-41 17-41zm255-105L177 64c-9.4-9.4-24.6-9.4-33.9 0L24 183c-15.1 15.1-4.4 41 17 41h238c21.4 0 32.1-25.9 17-41z"
}, Ee = {
  key: 1,
  fill: "currentColor",
  d: "M279 224H41c-21.4 0-32.1-25.9-17-41L143 64c9.4-9.4 24.6-9.4 33.9 0l119 119c15.2 15.1 4.5 41-16.9 41z"
}, We = {
  key: 2,
  fill: "currentColor",
  d: "M41 288h238c21.4 0 32.1 25.9 17 41L177 448c-9.4 9.4-24.6 9.4-33.9 0L24 329c-15.1-15.1-4.4-41 17-41z"
}, Ne = {
  __name: "HeaderCell",
  props: {
    cell: {
      type: Object,
      required: !0
    }
  },
  setup(e) {
    const o = e;
    function u() {
      o.cell.sortable && o.cell.onSort(o.cell.key);
    }
    return (i, c) => D((a(), h("th", null, [
      (a(), x(I(e.cell.sortable ? "button" : "div"), {
        class: "py-3 px-6 w-full",
        dusk: e.cell.sortable ? `sort-${e.cell.key}` : null,
        onClick: B(u, ["prevent"])
      }, {
        default: C(() => [
          s("span", Me, [
            y(i.$slots, "label", {}, () => [
              s("span", ze, p(e.cell.label), 1)
            ]),
            y(i.$slots, "sort", {}, () => [
              e.cell.sortable ? (a(), h("svg", {
                key: 0,
                "aria-hidden": "true",
                class: $(["w-3 h-3 ml-2", {
                  "text-gray-400": !e.cell.sorted,
                  "text-green-500": e.cell.sorted
                }]),
                xmlns: "http://www.w3.org/2000/svg",
                viewBox: "0 0 320 512",
                sorted: e.cell.sorted
              }, [
                e.cell.sorted ? k("", !0) : (a(), h("path", Re)),
                e.cell.sorted === "asc" ? (a(), h("path", Ee)) : k("", !0),
                e.cell.sorted === "desc" ? (a(), h("path", We)) : k("", !0)
              ], 10, De)) : k("", !0)
            ])
          ])
        ]),
        _: 3
      }, 8, ["dusk", "onClick"]))
    ], 512)), [
      [R, !e.cell.hidden]
    ]);
  }
}, J = {
  translations: {
    next: "Next",
    no_results_found: "No results found",
    of: "of",
    per_page: "per page",
    previous: "Previous",
    results: "results",
    to: "to"
  }
};
function ce() {
  return J.translations;
}
function qr(e, o) {
  J.translations[e] = o;
}
function Br(e) {
  J.translations = e;
}
const Ae = ["dusk", "value"], He = ["value"], ie = {
  __name: "PerPageSelector",
  props: {
    dusk: {
      type: String,
      default: null,
      required: !1
    },
    value: {
      type: Number,
      default: 15,
      required: !1
    },
    options: {
      type: Array,
      default() {
        return [15, 30, 50, 100];
      },
      required: !1
    },
    onChange: {
      type: Function,
      required: !0
    }
  },
  setup(e) {
    const o = e, u = ce(), i = _(() => {
      let c = [...o.options];
      return c.push(parseInt(o.value)), Ce(c).sort((r, l) => r - l);
    });
    return (c, r) => (a(), h("select", {
      name: "per_page",
      dusk: e.dusk,
      value: e.value,
      class: "block focus:ring-indigo-500 focus:border-indigo-500 min-w-max shadow-sm text-sm border-gray-300 rounded-md",
      onChange: r[0] || (r[0] = (l) => e.onChange(l.target.value))
    }, [
      (a(!0), h(P, null, O(t(i), (l) => (a(), h("option", {
        key: l,
        value: l
      }, p(l) + " " + p(t(u).per_page), 9, He))), 128))
    ], 40, Ae));
  }
}, Ge = {
  key: 0,
  class: "bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6"
}, Ke = { key: 0 }, Qe = /* @__PURE__ */ s("svg", {
  xmlns: "http://www.w3.org/2000/svg",
  class: "h-5 w-5 text-gray-400",
  fill: "none",
  viewBox: "0 0 24 24",
  stroke: "currentColor",
  "stroke-width": "2"
}, [
  /* @__PURE__ */ s("path", {
    "stroke-linecap": "round",
    "stroke-linejoin": "round",
    d: "M7 16l-4-4m0 0l4-4m-4 4h18"
  })
], -1), Ue = { class: "hidden sm:inline ml-2" }, Ye = { class: "hidden sm:inline mr-2" }, Je = /* @__PURE__ */ s("svg", {
  xmlns: "http://www.w3.org/2000/svg",
  class: "h-5 w-5 text-gray-400",
  fill: "none",
  viewBox: "0 0 24 24",
  stroke: "currentColor",
  "stroke-width": "2"
}, [
  /* @__PURE__ */ s("path", {
    "stroke-linecap": "round",
    "stroke-linejoin": "round",
    d: "M17 8l4 4m0 0l-4 4m4-4H3"
  })
], -1), Xe = {
  key: 2,
  class: "hidden sm:flex-1 sm:flex sm:items-center sm:justify-between"
}, Ze = { class: "flex flex-row space-x-4 items-center flex-grow" }, et = { class: "hidden lg:block text-sm text-gray-700 flex-grow" }, tt = { class: "font-medium" }, rt = { class: "font-medium" }, lt = { class: "font-medium" }, nt = {
  class: "relative z-0 inline-flex rounded-md shadow-sm -space-x-px",
  "aria-label": "Pagination"
}, st = { class: "sr-only" }, at = /* @__PURE__ */ s("svg", {
  xmlns: "http://www.w3.org/2000/svg",
  class: "h-5 w-5",
  viewBox: "0 0 20 20",
  fill: "currentColor"
}, [
  /* @__PURE__ */ s("path", {
    "fill-rule": "evenodd",
    d: "M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z",
    "clip-rule": "evenodd"
  })
], -1), ot = { class: "sr-only" }, ut = /* @__PURE__ */ s("svg", {
  xmlns: "http://www.w3.org/2000/svg",
  class: "h-5 w-5",
  viewBox: "0 0 20 20",
  fill: "currentColor"
}, [
  /* @__PURE__ */ s("path", {
    "fill-rule": "evenodd",
    d: "M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z",
    "clip-rule": "evenodd"
  })
], -1), it = {
  __name: "Pagination",
  props: {
    onClick: {
      type: Function,
      required: !1
    },
    perPageOptions: {
      type: Array,
      default() {
        return () => [15, 30, 50, 100];
      },
      required: !1
    },
    onPerPageChange: {
      type: Function,
      default() {
        return () => {
        };
      },
      required: !1
    },
    hasData: {
      type: Boolean,
      required: !0
    },
    meta: {
      type: Object,
      required: !1
    }
  },
  setup(e) {
    const o = e, u = ce(), i = _(() => "links" in r.value ? r.value.links.length > 0 : !1), c = _(() => Object.keys(r.value).length > 0), r = _(() => o.meta), l = _(() => "prev_page_url" in r.value ? r.value.prev_page_url : null), f = _(() => "next_page_url" in r.value ? r.value.next_page_url : null), m = _(() => parseInt(r.value.per_page));
    return (F, q) => t(c) ? (a(), h("nav", Ge, [
      !e.hasData || t(r).total < 1 ? (a(), h("p", Ke, p(t(u).no_results_found), 1)) : k("", !0),
      e.hasData ? (a(), h("div", {
        key: 1,
        class: $(["flex-1 flex justify-between", { "sm:hidden": t(i) }])
      }, [
        (a(), x(I(t(l) ? "a" : "div"), {
          class: $([{
            "cursor-not-allowed text-gray-400": !t(l),
            "text-gray-700 hover:text-gray-500": t(l)
          }, "relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md bg-white"]),
          href: t(l),
          dusk: t(l) ? "pagination-simple-previous" : null,
          onClick: q[0] || (q[0] = B((w) => e.onClick(t(l)), ["prevent"]))
        }, {
          default: C(() => [
            Qe,
            s("span", Ue, p(t(u).previous), 1)
          ]),
          _: 1
        }, 8, ["class", "href", "dusk"])),
        z(ie, {
          dusk: "per-page-mobile",
          value: t(m),
          options: e.perPageOptions,
          "on-change": e.onPerPageChange
        }, null, 8, ["value", "options", "on-change"]),
        (a(), x(I(t(f) ? "a" : "div"), {
          class: $([{
            "cursor-not-allowed text-gray-400": !t(f),
            "text-gray-700 hover:text-gray-500": t(f)
          }, "ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md bg-white"]),
          href: t(f),
          dusk: t(f) ? "pagination-simple-next" : null,
          onClick: q[1] || (q[1] = B((w) => e.onClick(t(f)), ["prevent"]))
        }, {
          default: C(() => [
            s("span", Ye, p(t(u).next), 1),
            Je
          ]),
          _: 1
        }, 8, ["class", "href", "dusk"]))
      ], 2)) : k("", !0),
      e.hasData && t(i) ? (a(), h("div", Xe, [
        s("div", Ze, [
          z(ie, {
            dusk: "per-page-full",
            value: t(m),
            options: e.perPageOptions,
            "on-change": e.onPerPageChange
          }, null, 8, ["value", "options", "on-change"]),
          s("p", et, [
            s("span", tt, p(t(r).from), 1),
            M(" " + p(t(u).to) + " ", 1),
            s("span", rt, p(t(r).to), 1),
            M(" " + p(t(u).of) + " ", 1),
            s("span", lt, p(t(r).total), 1),
            M(" " + p(t(u).results), 1)
          ])
        ]),
        s("div", null, [
          s("nav", nt, [
            (a(), x(I(t(l) ? "a" : "div"), {
              class: $([{
                "cursor-not-allowed text-gray-400": !t(l),
                "text-gray-500 hover:bg-gray-50": t(l)
              }, "relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium"]),
              href: t(l),
              dusk: t(l) ? "pagination-previous" : null,
              onClick: q[2] || (q[2] = B((w) => e.onClick(t(l)), ["prevent"]))
            }, {
              default: C(() => [
                s("span", st, p(t(u).previous), 1),
                at
              ]),
              _: 1
            }, 8, ["class", "href", "dusk"])),
            (a(!0), h(P, null, O(t(r).links, (w, j) => (a(), h("div", { key: j }, [
              y(F.$slots, "link", {}, () => [
                !isNaN(w.label) || w.label === "..." ? (a(), x(I(w.url ? "a" : "div"), {
                  key: 0,
                  href: w.url,
                  dusk: w.url ? `pagination-${w.label}` : null,
                  class: $(["relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700", {
                    "cursor-not-allowed": !w.url,
                    "hover:bg-gray-50": w.url,
                    "bg-gray-100": w.active
                  }]),
                  onClick: B((W) => e.onClick(w.url), ["prevent"])
                }, {
                  default: C(() => [
                    M(p(w.label), 1)
                  ]),
                  _: 2
                }, 1032, ["href", "dusk", "class", "onClick"])) : k("", !0)
              ])
            ]))), 128)),
            (a(), x(I(t(f) ? "a" : "div"), {
              class: $([{
                "cursor-not-allowed text-gray-400": !t(f),
                "text-gray-500 hover:bg-gray-50": t(f)
              }, "relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium"]),
              href: t(f),
              dusk: t(f) ? "pagination-next" : null,
              onClick: q[3] || (q[3] = B((w) => e.onClick(t(f)), ["prevent"]))
            }, {
              default: C(() => [
                s("span", ot, p(t(u).next), 1),
                ut
              ]),
              _: 1
            }, 8, ["class", "href", "dusk"]))
          ])
        ])
      ])) : k("", !0)
    ])) : k("", !0);
  }
}, ct = /* @__PURE__ */ s("svg", {
  xmlns: "http://www.w3.org/2000/svg",
  class: "h-5 w-5 text-gray-400",
  viewBox: "0 0 20 20",
  fill: "currentColor"
}, [
  /* @__PURE__ */ s("path", {
    "fill-rule": "evenodd",
    d: "M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z",
    "clip-rule": "evenodd"
  })
], -1), dt = {
  role: "menu",
  "aria-orientation": "horizontal",
  "aria-labelledby": "add-search-input-menu",
  class: "min-w-max"
}, ht = ["dusk", "onClick"], ft = {
  __name: "TableAddSearchRow",
  props: {
    searchInputs: {
      type: Object,
      required: !0
    },
    hasSearchInputsWithoutValue: {
      type: Boolean,
      required: !0
    },
    onAdd: {
      type: Function,
      required: !0
    }
  },
  setup(e) {
    const o = e, u = S(null);
    function i(c) {
      o.onAdd(c), u.value.hide();
    }
    return (c, r) => (a(), x(Y, {
      ref_key: "dropdown",
      ref: u,
      dusk: "add-search-row-dropdown",
      disabled: !e.hasSearchInputsWithoutValue,
      class: "w-auto"
    }, {
      button: C(() => [
        ct
      ]),
      default: C(() => [
        s("div", dt, [
          (a(!0), h(P, null, O(e.searchInputs, (l, f) => (a(), h("button", {
            key: f,
            dusk: `add-search-row-${l.key}`,
            class: "text-left w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900",
            role: "menuitem",
            onClick: B((m) => i(l.key), ["prevent"])
          }, p(l.label), 9, ht))), 128))
        ])
      ]),
      _: 1
    }, 8, ["disabled"]));
  }
}, gt = /* @__PURE__ */ s("path", { d: "M10 12a2 2 0 100-4 2 2 0 000 4z" }, null, -1), pt = /* @__PURE__ */ s("path", {
  "fill-rule": "evenodd",
  d: "M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z",
  "clip-rule": "evenodd"
}, null, -1), vt = [
  gt,
  pt
], mt = {
  role: "menu",
  "aria-orientation": "horizontal",
  "aria-labelledby": "toggle-columns-menu",
  class: "min-w-max"
}, bt = { class: "px-2" }, yt = { class: "divide-y divide-gray-200" }, wt = { class: "text-sm text-gray-900" }, xt = ["aria-pressed", "aria-labelledby", "aria-describedby", "dusk", "onClick"], kt = /* @__PURE__ */ s("span", { class: "sr-only" }, "Column status", -1), _t = {
  __name: "TableColumns",
  props: {
    columns: {
      type: Object,
      required: !0
    },
    hasHiddenColumns: {
      type: Boolean,
      required: !0
    },
    onChange: {
      type: Function,
      required: !0
    }
  },
  setup(e) {
    const o = e;
    return (u, i) => (a(), x(Y, {
      placement: "bottom-end",
      dusk: "columns-dropdown",
      active: e.hasHiddenColumns
    }, {
      button: C(() => [
        (a(), h("svg", {
          xmlns: "http://www.w3.org/2000/svg",
          class: $(["h-5 w-5", {
            "text-gray-400": !e.hasHiddenColumns,
            "text-green-400": e.hasHiddenColumns
          }]),
          viewBox: "0 0 20 20",
          fill: "currentColor"
        }, vt, 2))
      ]),
      default: C(() => [
        s("div", mt, [
          s("div", bt, [
            s("ul", yt, [
              (a(!0), h(P, null, O(o.columns, (c, r) => D((a(), h("li", {
                key: r,
                class: "py-2 flex items-center justify-between"
              }, [
                s("p", wt, p(c.label), 1),
                s("button", {
                  type: "button",
                  class: $(["ml-4 relative inline-flex flex-shrink-0 h-6 w-11 border-2 border-transparent rounded-full cursor-pointer transition-colors ease-in-out duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-light-blue-500", {
                    "bg-green-500": !c.hidden,
                    "bg-gray-200": c.hidden
                  }]),
                  "aria-pressed": !c.hidden,
                  "aria-labelledby": `toggle-column-${c.key}`,
                  "aria-describedby": `toggle-column-${c.key}`,
                  dusk: `toggle-column-${c.key}`,
                  onClick: B((l) => e.onChange(c.key, c.hidden), ["prevent"])
                }, [
                  kt,
                  s("span", {
                    "aria-hidden": "true",
                    class: $([{
                      "translate-x-5": !c.hidden,
                      "translate-x-0": c.hidden
                    }, "inline-block h-5 w-5 rounded-full bg-white shadow transform ring-0 transition ease-in-out duration-200"])
                  }, null, 2)
                ], 10, xt)
              ])), [
                [R, c.can_be_hidden]
              ])), 128))
            ])
          ])
        ])
      ]),
      _: 1
    }, 8, ["active"]));
  }
}, $t = /* @__PURE__ */ s("path", {
  "fill-rule": "evenodd",
  d: "M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-.293.707L12 11.414V15a1 1 0 01-.293.707l-2 2A1 1 0 018 17v-5.586L3.293 6.707A1 1 0 013 6V3z",
  "clip-rule": "evenodd"
}, null, -1), Ct = [
  $t
], St = {
  role: "menu",
  "aria-orientation": "horizontal",
  "aria-labelledby": "filter-menu",
  class: "min-w-max"
}, qt = { class: "text-xs uppercase tracking-wide bg-gray-100 p-3" }, Bt = { class: "p-2" }, Ft = ["name", "value", "onChange"], Pt = ["value"], Ot = {
  __name: "TableFilter",
  props: {
    hasEnabledFilters: {
      type: Boolean,
      required: !0
    },
    filters: {
      type: Object,
      required: !0
    },
    onFilterChange: {
      type: Function,
      required: !0
    }
  },
  setup(e) {
    return (o, u) => (a(), x(Y, {
      placement: "bottom-end",
      dusk: "filters-dropdown",
      active: e.hasEnabledFilters
    }, {
      button: C(() => [
        (a(), h("svg", {
          xmlns: "http://www.w3.org/2000/svg",
          class: $(["h-5 w-5", {
            "text-gray-400": !e.hasEnabledFilters,
            "text-green-400": e.hasEnabledFilters
          }]),
          viewBox: "0 0 20 20",
          fill: "currentColor"
        }, Ct, 2))
      ]),
      default: C(() => [
        s("div", St, [
          (a(!0), h(P, null, O(e.filters, (i, c) => (a(), h("div", { key: c }, [
            s("h3", qt, p(i.label), 1),
            s("div", Bt, [
              i.type === "select" ? (a(), h("select", {
                key: 0,
                name: i.key,
                value: i.value,
                class: "block focus:ring-indigo-500 focus:border-indigo-500 w-full shadow-sm text-sm border-gray-300 rounded-md",
                onChange: (r) => e.onFilterChange(i.key, r.target.value)
              }, [
                (a(!0), h(P, null, O(i.options, (r, l) => (a(), h("option", {
                  key: l,
                  value: l
                }, p(r), 9, Pt))), 128))
              ], 40, Ft)) : k("", !0)
            ])
          ]))), 128))
        ])
      ]),
      _: 1
    }, 8, ["active"]));
  }
}, Tt = { class: "relative" }, jt = ["placeholder", "value"], It = /* @__PURE__ */ s("div", { class: "absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none" }, [
  /* @__PURE__ */ s("svg", {
    xmlns: "http://www.w3.org/2000/svg",
    class: "h-5 w-5 text-gray-400",
    viewBox: "0 0 20 20",
    fill: "currentColor"
  }, [
    /* @__PURE__ */ s("path", {
      "fill-rule": "evenodd",
      d: "M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z",
      "clip-rule": "evenodd"
    })
  ])
], -1), Vt = {
  __name: "TableGlobalSearch",
  props: {
    label: {
      type: String,
      default: "Search...",
      required: !1
    },
    value: {
      type: String,
      default: "",
      required: !1
    },
    onChange: {
      type: Function,
      required: !0
    }
  },
  setup(e) {
    return (o, u) => (a(), h("div", Tt, [
      s("input", {
        class: "block w-full pl-9 text-sm rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 border-gray-300",
        placeholder: e.label,
        value: e.value,
        type: "text",
        name: "global",
        onInput: u[0] || (u[0] = (i) => e.onChange(i.target.value))
      }, null, 40, jt),
      It
    ]));
  }
}, Lt = { class: "flex rounded-md shadow-sm relative mt-3" }, Mt = ["for"], zt = /* @__PURE__ */ s("svg", {
  xmlns: "http://www.w3.org/2000/svg",
  class: "h-5 w-5 mr-2 text-gray-400",
  viewBox: "0 0 20 20",
  fill: "currentColor"
}, [
  /* @__PURE__ */ s("path", {
    "fill-rule": "evenodd",
    d: "M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z",
    "clip-rule": "evenodd"
  })
], -1), Dt = ["id", "name", "value", "onInput"], Rt = { class: "absolute inset-y-0 right-0 pr-3 flex items-center" }, Et = ["dusk", "onClick"], Wt = /* @__PURE__ */ s("span", { class: "sr-only" }, "Remove search", -1), Nt = /* @__PURE__ */ s("svg", {
  xmlns: "http://www.w3.org/2000/svg",
  class: "h-5 w-5",
  fill: "none",
  viewBox: "0 0 24 24",
  stroke: "currentColor"
}, [
  /* @__PURE__ */ s("path", {
    "stroke-linecap": "round",
    "stroke-linejoin": "round",
    "stroke-width": "2",
    d: "M6 18L18 6M6 6l12 12"
  })
], -1), At = [
  Wt,
  Nt
], Ht = {
  __name: "TableSearchRows",
  props: {
    searchInputs: {
      type: Object,
      required: !0
    },
    forcedVisibleSearchInputs: {
      type: Array,
      required: !0
    },
    onChange: {
      type: Function,
      required: !0
    },
    onRemove: {
      type: Function,
      required: !0
    }
  },
  setup(e) {
    const o = e, u = { el: S([]) };
    let i = _(() => u.el.value);
    function c(r) {
      return o.forcedVisibleSearchInputs.includes(r);
    }
    return U(o.forcedVisibleSearchInputs, (r) => {
      const l = r.length > 0 ? r[r.length - 1] : null;
      !l || be().then(() => {
        const f = Se(i.value, (m) => m.__vnode.key === l);
        f && f.focus();
      });
    }, { immediate: !0 }), (r, l) => (a(!0), h(P, null, O(e.searchInputs, (f, m) => D((a(), h("div", {
      key: m,
      class: "px-4 sm:px-0"
    }, [
      s("div", Lt, [
        s("label", {
          for: f.key,
          class: "inline-flex items-center px-4 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-500 text-sm"
        }, [
          zt,
          s("span", null, p(f.label), 1)
        ], 8, Mt),
        (a(), h("input", {
          id: f.key,
          ref_for: !0,
          ref: u.el,
          key: f.key,
          name: f.key,
          value: f.value,
          type: "text",
          class: "flex-1 min-w-0 block w-full px-3 py-2 rounded-none rounded-r-md focus:ring-indigo-500 focus:border-indigo-500 text-sm border-gray-300",
          onInput: (F) => e.onChange(f.key, F.target.value)
        }, null, 40, Dt)),
        s("div", Rt, [
          s("button", {
            class: "rounded-md text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500",
            dusk: `remove-search-row-${f.key}`,
            onClick: B((F) => e.onRemove(f.key), ["prevent"])
          }, At, 8, Et)
        ])
      ])
    ])), [
      [R, f.value !== null || c(f.key)]
    ])), 128));
  }
}, Gt = /* @__PURE__ */ s("svg", {
  xmlns: "http://www.w3.org/2000/svg",
  class: "h-5 w-5 mr-2 text-gray-400",
  viewBox: "0 0 20 20",
  fill: "currentColor"
}, [
  /* @__PURE__ */ s("path", {
    "fill-rule": "evenodd",
    d: "M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z",
    "clip-rule": "evenodd"
  })
], -1), Kt = /* @__PURE__ */ s("span", null, "Reset", -1), Qt = [
  Gt,
  Kt
], Ut = {
  __name: "TableReset",
  props: {
    onClick: {
      type: Function,
      required: !0
    }
  },
  setup(e) {
    return (o, u) => (a(), h("button", {
      ref: "button",
      type: "button",
      dusk: "reset-table",
      class: "w-full bg-white border rounded-md shadow-sm px-4 py-2 inline-flex justify-center text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 border-gray-300",
      "aria-haspopup": "true",
      onClick: u[0] || (u[0] = B((...i) => e.onClick && e.onClick(...i), ["prevent"]))
    }, Qt, 512));
  }
}, Yt = (e, o) => {
  const u = e.__vccOpts || e;
  for (const [i, c] of o)
    u[i] = c;
  return u;
}, Jt = {}, Xt = { class: "flex flex-col" }, Zt = { class: "-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8" }, er = { class: "py-2 align-middle inline-block min-w-full sm:px-6 lg:px-8" }, tr = { class: "shadow border-b border-gray-200 relative" };
function rr(e, o) {
  return a(), h("div", Xt, [
    s("div", Zt, [
      s("div", er, [
        s("div", tr, [
          y(e.$slots, "default")
        ])
      ])
    ])
  ]);
}
const lr = /* @__PURE__ */ Yt(Jt, [["render", rr]]), nr = ["dusk"], sr = { class: "flex flex-row flex-wrap sm:flex-nowrap justify-start px-4 sm:px-0" }, ar = { class: "order-2 sm:order-1 mr-2 sm:mr-4" }, or = {
  key: 0,
  class: "flex flex-row w-full sm:w-auto sm:flex-grow order-1 sm:order-2 mb-2 sm:mb-0 sm:mr-4"
}, ur = {
  key: 0,
  class: "order-5 sm:order-3 sm:mr-4 ml-auto"
}, ir = { class: "min-w-full divide-y divide-gray-200 bg-white" }, cr = { class: "bg-gray-50" }, dr = { class: "font-medium text-xs uppercase text-left tracking-wider text-gray-500 py-3 px-6" }, hr = { class: "bg-white divide-y divide-gray-200" }, Fr = {
  __name: "Table",
  props: {
    inertia: {
      type: Object,
      default: () => ({}),
      required: !1
    },
    name: {
      type: String,
      default: "default",
      required: !1
    },
    striped: {
      type: Boolean,
      default: !1,
      required: !1
    },
    preventOverlappingRequests: {
      type: Boolean,
      default: !0,
      required: !1
    },
    inputDebounceMs: {
      type: Number,
      default: 350,
      required: !1
    },
    preserveScroll: {
      type: [Boolean, String],
      default: !1,
      required: !1
    },
    resource: {
      type: Object,
      default: () => ({}),
      required: !1
    },
    meta: {
      type: Object,
      default: () => ({}),
      required: !1
    },
    data: {
      type: Object,
      default: () => ({}),
      required: !1
    }
  },
  setup(e) {
    const o = e, u = ye(), i = u ? u.appContext.config.globalProperties.$inertia : o.inertia, c = S(0), r = _(() => {
      let n = i.page.props.queryBuilderProps ? i.page.props.queryBuilderProps[o.name] || {} : {};
      return n._updates = c.value, n;
    }), l = S(r.value), f = _(() => r.value.pageName), m = S([]), F = S(null), q = _(() => !(r.value.hasToggleableColumns || r.value.hasFilters || r.value.hasSearchInputs || r.value.globalSearch)), w = _(() => Object.keys(o.resource).length === 0 ? o.data : "data" in o.resource ? o.resource.data : o.resource), j = _(() => Object.keys(o.resource).length === 0 ? o.meta : "links" in o.resource && "meta" in o.resource && Object.keys(o.resource.links).length === 4 && "next" in o.resource.links && "prev" in o.resource.links ? {
      ...o.resource.meta,
      next_page_url: o.resource.links.next,
      prev_page_url: o.resource.links.prev
    } : "meta" in o.resource ? o.resource.meta : o.resource), W = _(() => w.value.length > 0 || j.value.total > 0);
    function de(n) {
      m.value = m.value.filter((d) => d != n), E(n, null);
    }
    function X(n) {
      m.value.push(n);
    }
    const he = _(() => {
      if (m.value.length > 0)
        return !0;
      const n = K.parse(location.search.substring(1));
      if (n[f.value] > 1)
        return !0;
      const g = o.name === "default" ? "" : o.name + "_";
      let v = !1;
      return T(["filter", "columns", "cursor", "sort"], (b) => {
        const L = n[g + b];
        b === "sort" && L === r.value.defaultSort || L !== void 0 && (v = !0);
      }), v;
    });
    function Z() {
      m.value = [], T(l.value.filters, (n, d) => {
        l.value.filters[d].value = null;
      }), T(l.value.searchInputs, (n, d) => {
        l.value.searchInputs[d].value = null;
      }), T(l.value.columns, (n, d) => {
        l.value.columns[d].hidden = n.can_be_hidden ? !r.value.defaultVisibleToggleableColumns.includes(n.key) : !1;
      }), l.value.sort = null, l.value.cursor = null, l.value.page = 1;
    }
    const ee = {};
    function E(n, d) {
      clearTimeout(ee[n]), ee[n] = setTimeout(() => {
        A.value && o.preventOverlappingRequests && A.value.cancel();
        const g = V("searchInputs", n);
        l.value.searchInputs[g].value = d, l.value.cursor = null, l.value.page = 1;
      }, o.inputDebounceMs);
    }
    function te(n) {
      E("global", n);
    }
    function re(n, d) {
      const g = V("filters", n);
      l.value.filters[g].value = d, l.value.cursor = null, l.value.page = 1;
    }
    function le(n) {
      l.value.cursor = null, l.value.perPage = n, l.value.page = 1;
    }
    function V(n, d) {
      return Fe(l.value[n], (g) => g.key == d);
    }
    function ne(n, d) {
      const g = V("columns", n);
      l.value.columns[g].hidden = !d;
    }
    function fe() {
      let n = {};
      return T(l.value.searchInputs, (d) => {
        d.value !== null && (n[d.key] = d.value);
      }), T(l.value.filters, (d) => {
        d.value !== null && (n[d.key] = d.value);
      }), n;
    }
    function ge() {
      const n = l.value.columns;
      let d = Be(n, (v) => !v.hidden), g = Oe(d, (v) => v.key).sort();
      return Pe(g, r.value.defaultVisibleToggleableColumns) ? {} : g;
    }
    function pe() {
      const n = fe(), d = ge(), g = {};
      Object.keys(n).length > 0 && (g.filter = n), Object.keys(d).length > 0 && (g.columns = d);
      const v = l.value.cursor, b = l.value.page, L = l.value.sort, ue = l.value.perPage;
      return v && (g.cursor = v), b > 1 && (g.page = b), ue > 1 && (g.perPage = ue), L && (g.sort = L), g;
    }
    function ve() {
      const n = K.parse(location.search.substring(1)), d = o.name === "default" ? "" : o.name + "_";
      T(["filter", "columns", "cursor", "sort"], (v) => {
        delete n[d + v];
      }), delete n[f.value], T(pe(), (v, b) => {
        b === "page" ? n[f.value] = v : b === "perPage" ? n.perPage = v : n[d + b] = v;
      });
      let g = K.stringify(n, {
        filter(v, b) {
          return typeof b == "object" && b !== null ? Te(b) : b;
        },
        skipNulls: !0,
        strictNullHandling: !0
      });
      return (!g || g === f.value + "=1") && (g = ""), g;
    }
    const N = S(!1), A = S(null);
    function H(n) {
      !n || i.get(
        n,
        {},
        {
          replace: !0,
          preserveState: !0,
          preserveScroll: o.preserveScroll !== !1,
          onBefore() {
            N.value = !0;
          },
          onCancelToken(d) {
            A.value = d;
          },
          onFinish() {
            N.value = !1;
          },
          onSuccess() {
            if ("queryBuilderProps" in i.page.props && (l.value.cursor = r.value.cursor, l.value.page = r.value.page), o.preserveScroll === "table-top") {
              const g = F.value.getBoundingClientRect().top + window.pageYOffset + -8;
              window.scrollTo({ top: g });
            }
            c.value++;
          }
        }
      );
    }
    U(l, () => {
      H(location.pathname + "?" + ve());
    }, { deep: !0 });
    const se = () => {
      c.value++;
    };
    Q(() => {
      document.addEventListener("inertia:success", se);
    }), we(() => {
      document.removeEventListener("inertia:success", se);
    });
    function ae(n) {
      l.value.sort == n ? l.value.sort = `-${n}` : l.value.sort = n, l.value.cursor = null, l.value.page = 1;
    }
    function G(n) {
      const d = V("columns", n);
      return !l.value.columns[d].hidden;
    }
    function oe(n) {
      const d = V("columns", n), g = qe(r.value.columns[d]);
      return g.onSort = ae, g;
    }
    return (n, d) => (a(), x(xe, null, {
      default: C(() => [
        (a(), h("fieldset", {
          ref_key: "tableFieldset",
          ref: F,
          key: `table-${e.name}`,
          dusk: `table-${e.name}`,
          class: $(["min-w-0", { "opacity-75": N.value }])
        }, [
          s("div", sr, [
            s("div", ar, [
              y(n.$slots, "tableFilter", {
                hasFilters: t(r).hasFilters,
                hasEnabledFilters: t(r).hasEnabledFilters,
                filters: t(r).filters,
                onFilterChange: re
              }, () => [
                t(r).hasFilters ? (a(), x(Ot, {
                  key: 0,
                  "has-enabled-filters": t(r).hasEnabledFilters,
                  filters: t(r).filters,
                  "on-filter-change": re
                }, null, 8, ["has-enabled-filters", "filters"])) : k("", !0)
              ])
            ]),
            t(r).globalSearch ? (a(), h("div", or, [
              y(n.$slots, "tableGlobalSearch", {
                hasGlobalSearch: t(r).globalSearch,
                label: t(r).globalSearch ? t(r).globalSearch.label : null,
                value: t(r).globalSearch ? t(r).globalSearch.value : null,
                onChange: te
              }, () => [
                t(r).globalSearch ? (a(), x(Vt, {
                  key: 0,
                  class: "flex-grow",
                  label: t(r).globalSearch.label,
                  value: t(r).globalSearch.value,
                  "on-change": te
                }, null, 8, ["label", "value"])) : k("", !0)
              ])
            ])) : k("", !0),
            y(n.$slots, "tableReset", {
              canBeReset: "canBeReset",
              onClick: Z
            }, () => [
              t(he) ? (a(), h("div", ur, [
                z(Ut, { "on-click": Z })
              ])) : k("", !0)
            ]),
            y(n.$slots, "tableAddSearchRow", {
              hasSearchInputs: t(r).hasSearchInputs,
              hasSearchInputsWithoutValue: t(r).hasSearchInputsWithoutValue,
              searchInputs: t(r).searchInputsWithoutGlobal,
              onAdd: X
            }, () => [
              t(r).hasSearchInputs ? (a(), x(ft, {
                key: 0,
                class: "order-3 sm:order-4 mr-2 sm:mr-4",
                "search-inputs": t(r).searchInputsWithoutGlobal,
                "has-search-inputs-without-value": t(r).hasSearchInputsWithoutValue,
                "on-add": X
              }, null, 8, ["search-inputs", "has-search-inputs-without-value"])) : k("", !0)
            ]),
            y(n.$slots, "tableColumns", {
              hasColumns: t(r).hasToggleableColumns,
              columns: t(r).columns,
              hasHiddenColumns: t(r).hasHiddenColumns,
              onChange: ne
            }, () => [
              t(r).hasToggleableColumns ? (a(), x(_t, {
                key: 0,
                class: "order-4 mr-4 sm:mr-0 sm:order-5",
                columns: t(r).columns,
                "has-hidden-columns": t(r).hasHiddenColumns,
                "on-change": ne
              }, null, 8, ["columns", "has-hidden-columns"])) : k("", !0)
            ])
          ]),
          y(n.$slots, "tableSearchRows", {
            hasSearchRowsWithValue: t(r).hasSearchInputsWithValue,
            searchInputs: t(r).searchInputsWithoutGlobal,
            forcedVisibleSearchInputs: m.value,
            onChange: E
          }, () => [
            t(r).hasSearchInputsWithValue || m.value.length > 0 ? (a(), x(Ht, {
              key: 0,
              "search-inputs": t(r).searchInputsWithoutGlobal,
              "forced-visible-search-inputs": m.value,
              "on-change": E,
              "on-remove": de
            }, null, 8, ["search-inputs", "forced-visible-search-inputs"])) : k("", !0)
          ]),
          y(n.$slots, "tableWrapper", { meta: t(j) }, () => [
            z(lr, {
              class: $({ "mt-3": !t(q) })
            }, {
              default: C(() => [
                y(n.$slots, "table", {}, () => [
                  s("table", ir, [
                    s("thead", cr, [
                      y(n.$slots, "head", {
                        show: G,
                        sortBy: ae,
                        header: oe
                      }, () => [
                        s("tr", dr, [
                          (a(!0), h(P, null, O(t(r).columns, (g) => (a(), x(Ne, {
                            key: `table-${e.name}-header-${g.key}`,
                            cell: oe(g.key)
                          }, null, 8, ["cell"]))), 128))
                        ])
                      ])
                    ]),
                    s("tbody", hr, [
                      y(n.$slots, "body", { show: G }, () => [
                        (a(!0), h(P, null, O(t(w), (g, v) => (a(), h("tr", {
                          key: `table-${e.name}-row-${v}`,
                          class: $(["", {
                            "bg-gray-50": e.striped && v % 2,
                            "hover:bg-gray-100": e.striped,
                            "hover:bg-gray-50": !e.striped
                          }])
                        }, [
                          (a(!0), h(P, null, O(t(r).columns, (b) => D((a(), h("td", {
                            key: `table-${e.name}-row-${v}-column-${b.key}`,
                            class: "text-sm py-4 px-6 text-gray-500 whitespace-nowrap"
                          }, [
                            y(n.$slots, `cell(${b.key})`, { item: g }, () => [
                              M(p(g[b.key]), 1)
                            ])
                          ])), [
                            [R, G(b.key)]
                          ])), 128))
                        ], 2))), 128))
                      ])
                    ])
                  ])
                ]),
                y(n.$slots, "pagination", {
                  onClick: H,
                  hasData: t(W),
                  meta: t(j),
                  perPageOptions: t(r).perPageOptions,
                  onPerPageChange: le
                }, () => [
                  z(it, {
                    "on-click": H,
                    "has-data": t(W),
                    meta: t(j),
                    "per-page-options": t(r).perPageOptions,
                    "on-per-page-change": le
                  }, null, 8, ["has-data", "meta", "per-page-options"])
                ])
              ]),
              _: 3
            }, 8, ["class"])
          ])
        ], 10, nr))
      ]),
      _: 3
    }));
  }
};
export {
  Y as ButtonWithDropdown,
  Ne as HeaderCell,
  je as OnClickOutside,
  it as Pagination,
  Fr as Table,
  ft as TableAddSearchRow,
  _t as TableColumns,
  Ot as TableFilter,
  Vt as TableGlobalSearch,
  Ut as TableReset,
  Ht as TableSearchRows,
  lr as TableWrapper,
  ce as getTranslations,
  qr as setTranslation,
  Br as setTranslations
};
