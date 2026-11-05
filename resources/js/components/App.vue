<script setup>
// Komponen root sementara untuk membuktikan Vue + Vite + Tailwind sudah terhubung.
// Sengaja dibuat minimal: komponen interaktif untuk form transaksi baru akan
// dibuat pada Langkah 6, sehingga file ini nanti bisa dipakai sebagai root aplikasi.
</script>

<template>
    <!-- Utility Tailwind dipakai di sini untuk memastikan CSS ikut ter-build. -->
    <main class="flex min-h-screen items-center justify-center bg-slate-100">
        <div class="rounded-lg bg-white p-8 text-center shadow">
            <h1 class="text-2xl font-semibold text-slate-800">TBK Finance</h1>
            <p class="mt-2 text-slate-600">Vue berhasil dimuat melalui Vite.</p>
        </div>
    </main>
</template>
