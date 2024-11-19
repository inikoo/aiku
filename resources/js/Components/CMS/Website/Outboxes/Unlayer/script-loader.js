import { ref, computed } from "vue";

const scriptUrl = "//editor.unlayer.com/embed.js";

// flag apakah script unlayer sedang dalam proses load
const isLoading = ref(false);
// flag apakah script unlayer sudah diload
const isLoaded = ref(false);
// id otomatis untuk nantinya digunakan untuk meng-embed editor unlayer
const lastEditorId = ref(0);

let embedScript = null;

export const useUnlayer = () => ({
  isLoaded: computed(() => isLoaded.value),
  isLoading: computed(() => isLoading.value),
  lastEditorId: computed(() => lastEditorId.value)
});

export const getNextEditorId = () => `unlayer_${++lastEditorId.value}`;

export function loadScript() {
  // return promise
  return new Promise((resolve) => {
    // jika sudah loaded, maka just return
    if (isLoaded.value) {
      resolve();
    } else if (isLoading.value) {
      embedScript.addEventListener("load", () => {
        resolve();
      });
    } else {
      isLoading.value = true;
      // load script and wait for loaded
      embedScript = document.createElement("script");
      embedScript.setAttribute("src", scriptUrl);
      embedScript.addEventListener("load", () => {
        isLoaded.value = true;
        isLoading.value = false;
        resolve();
      });
      document.head.appendChild(embedScript);
    }
  });
}
