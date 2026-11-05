// Entry point JavaScript yang dimuat oleh Vite.
// Kerangka halaman tetap dibuat dengan Blade; Vue hanya "menempel" pada elemen
// yang memang membutuhkannya. Pengecekan elemen dilakukan agar halaman Blade
// biasa tidak error saat app.js ikut dimuat.
import { createApp } from "vue";
import App from "./components/App.vue";
import TransactionForm from "./components/TransactionForm.vue";

const appElement = document.getElementById("app");

if (appElement) {
    createApp(App).mount(appElement);
}

// Form transaksi memakai Vue karena user perlu memilih satu sisi nominal
// (debit/credit). Props dikirim server sebagai JSON di `data-props`.
const transactionFormElement = document.getElementById("transaction-form");

if (transactionFormElement) {
    try {
        const props = JSON.parse(transactionFormElement.dataset.props ?? "{}");

        createApp(TransactionForm, props).mount(transactionFormElement);
    } catch (error) {
        // Jangan biarkan halaman diam-diam tidak interaktif; tampilkan error
        // di console agar masalah props/JSON mudah didiagnosis.
        console.error("Gagal membaca props form transaksi:", error);
    }
}
