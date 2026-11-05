// Entry point JavaScript yang dimuat oleh Vite.
// Kerangka halaman tetap dibuat dengan Blade; Vue hanya "menempel" pada elemen
// yang memang membutuhkannya. Karena itu kita cek dulu apakah `#app` ada,
// supaya halaman Blade biasa tidak menghasilkan error saat app.js ikut dimuat.
import { createApp } from "vue";
import App from "./components/App.vue";

const appElement = document.getElementById("app");

if (appElement) {
    createApp(App).mount(appElement);
}
