import { useLayoutStore } from "@/Stores/layout";
import { useLocaleStore } from "@/Stores/locale";
import { usePage } from "@inertiajs/vue3";
import { loadLanguageAsync } from "laravel-vue-i18n";
import { watchEffect } from "vue";

export const initialiseApp = () => {
  const layout = useLayoutStore();
  const locale = useLocaleStore();

  if (usePage().props.localeData) {
    loadLanguageAsync(usePage().props.localeData.language.code);
  }
  watchEffect(() => {
    // Aiku
    if (usePage().props.layout) {
      layout.groupNavigation = usePage().props.layout.groupNavigation ?? null;
      layout.orgNavigation = usePage().props.layout.orgNavigation ?? null;

      layout.secondaryNavigation = usePage().props.layout.secondaryNavigation ?? null;
      if (usePage().props.layout.shopsInDropDown) {
        layout.shopsInDropDown = usePage().props.layout.shopsInDropDown.data ??
          {};
      }
      if (usePage().props.layout.websitesInDropDown) {
        layout.websitesInDropDown = usePage().props.layout.websitesInDropDown.data ??
          {};
      }
      if (usePage().props.layout.warehousesInDropDown) {
        layout.warehousesInDropDown = usePage().props.layout.warehousesInDropDown.data ??
          {};
      }
    }
    if (usePage().props.tenant) {
      layout.tenant = usePage().props.tenant ?? null;
    }
    if (usePage().props.tenant) {
      layout.tenant = usePage().props.tenant ?? null;
    }

    layout.currentRouteParameters = route().params;
    layout.currentRoute = route().current();
    layout.currentModule = layout.currentRoute.substring(0, layout.currentRoute.indexOf("."));


    if (usePage().props.layoutShopsList) {
      layout.shops = usePage().props.layoutShopsList;
    }

    if (usePage().props.layoutWebsitesList) {
      layout.websites = usePage().props.layoutWebsitesList;
    }

    if (usePage().props.layoutWarehousesList) {
      layout.warehouses = usePage().props.layoutWarehousesList;
    }

    if (!layout.booted) {
      if (Object.keys(layout.shops).length === 1) {
        layout.currentShopData = {
          slug: layout.shops[Object.keys(layout.shops)[0]].slug,
          name: layout.shops[Object.keys(layout.shops)[0]].name,
          code: layout.shops[Object.keys(layout.shops)[0]].code
        };
      }
    }

    if (!layout.booted) {
      if (Object.keys(layout.websites).length === 1) {
        layout.currentWebsiteData = {
          slug: layout.websites[Object.keys(layout.websites)[0]].slug,
          name: layout.websites[Object.keys(layout.websites)[0]].name,
          code: layout.websites[Object.keys(layout.websites)[0]].code
        };
      }
    }

    if (!layout.booted) {
      if (Object.keys(layout.warehouses).length === 1) {
        layout.currentWarehouseData = {
          slug: layout.warehouses[Object.keys(layout.warehouses)[0]].slug,
          name: layout.warehouses[Object.keys(layout.warehouses)[0]].name,
          code: layout.warehouses[Object.keys(layout.warehouses)[0]].code
        };
      }
    }

    layout.booted = true;

    // ===============================================

    // Set data of Navigation
    if (usePage().props.layout) {
      layout.navigation = usePage().props.layout.navigation ?? null;
      layout.secondaryNavigation = usePage().props.layout.secondaryNavigation ?? null;
    }

    // Organisations
    if (usePage().props.layout?.organisations?.data) {
      console.log(usePage().props.layout?.organisations?.data)
      layout.organisations.currentOrganisations = 'xxx'
      usePage().props.layout.organisations.data.forEach(item => {
        layout.organisations.data[item.slug] = item
      })
    }

    // Set data of Locale (Language)
    if (usePage().props.localeData) {
      locale.language = usePage().props.localeData.language;
      locale.languageOptions = usePage().props.localeData.languageOptions;
    }

    if (usePage().props.organisation) {
      layout.organisation = usePage().props.organisation ?? null;
    }

    // Set data of User
    if (usePage().props.user) {
      layout.user = usePage().props.user ?? null;
    }

    // Set avatar thumbnail
    if (usePage().props.auth.user.avatar_thumbnail) {
      layout.avatar_thumbnail = usePage().props.auth.user.avatar_thumbnail;
    }

    // Set logo app
    if (usePage().props.app) {
      layout.app = usePage().props.app;
    }

    layout.systemName = "Aiku";

    layout.currentRouteParameters = route().params;
    layout.currentRoute = route().current();

    let moduleName = layout.currentRoute.split(".");
    layout.currentModule = moduleName[1];

    layout.booted = true;
  });
};
