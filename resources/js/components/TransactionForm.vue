<script setup>
// Komponen ini HANYA dipakai untuk form transaksi.
// Tujuannya: user memilih satu sisi (debit/credit) dan mengisi satu nominal,
// sehingga tidak mungkin mengirim dua nilai sekaligus dari UI.
// Backend tetap memvalidasi karena request bisa dimanipulasi.
import { computed, ref } from "vue";

const props = defineProps({
    action: { type: String, required: true },
    method: { type: String, default: "POST" },
    csrf: { type: String, required: true },
    cancelUrl: { type: String, required: true },
    coas: { type: Array, default: () => [] },
    initial: { type: Object, required: true },
    errors: { type: Object, default: () => ({}) },
});

// Nilai form disimpan sebagai string agar cocok dengan input HTML.
// `errors` bersifat statis (dikirim server saat halaman dimuat).
const errors = props.errors;
const date = ref(props.initial.date);
const coaId = ref(props.initial.coa_id);
const description = ref(props.initial.description);
const mode = ref(props.initial.mode === "credit" ? "credit" : "debit");
const amount = ref(props.initial.amount);

const isEdit = computed(() => props.method !== "POST");

// Menormalkan nominal agar tidak pernah negatif atau NaN.
function normalizedAmount() {
    const value = Number(amount.value);

    return Number.isFinite(value) && value > 0 ? value : 0;
}

// Hanya sisi yang dipilih yang berisi nominal. Sisi lain dikirim 0 agar
// aturan backend "tepat satu sisi > 0" selalu terpenuhi.
const debit = computed(() => (mode.value === "debit" ? normalizedAmount() : 0));
const credit = computed(() => (mode.value === "credit" ? normalizedAmount() : 0));

const incomeCoas = computed(() => props.coas.filter((coa) => coa.type === "income"));
const expenseCoas = computed(() => props.coas.filter((coa) => coa.type === "expense"));

function optionLabel(coa) {
    return `${coa.code} - ${coa.name} (${coa.category})`;
}

// Utility class yang sama dipakai semua input agar tampilan konsisten,
// dengan border merah bila field terkait punya error dari server.
function inputClass(hasError) {
    return [
        "w-full rounded-md border px-3 py-2 text-sm shadow-sm focus:outline-none focus:ring-2",
        hasError
            ? "border-red-500 focus:ring-red-200"
            : "border-slate-300 focus:ring-slate-200",
    ];
}
</script>

<template>
    <form
        :action="action"
        method="POST"
        class="max-w-2xl space-y-6 rounded-lg border border-slate-200 bg-white p-6"
    >
        <input type="hidden" name="_token" :value="csrf" />
        <input v-if="isEdit" type="hidden" name="_method" :value="method" />

        <!-- Nilai yang benar-benar dikirim ke backend. Input nominal yang
             terlihat di layar tidak punya `name` agar tidak terkirim dobel. -->
        <input type="hidden" name="debit" :value="debit" />
        <input type="hidden" name="credit" :value="credit" />

        <div>
            <label for="date" class="mb-1 block text-sm font-medium text-slate-700">
                Tanggal
            </label>
            <input
                id="date"
                name="date"
                type="date"
                v-model="date"
                required
                :class="inputClass(!!errors.date)"
            />
            <p v-if="errors.date" class="mt-1 text-sm text-red-600">{{ errors.date }}</p>
        </div>

        <div>
            <label for="coa_id" class="mb-1 block text-sm font-medium text-slate-700">
                COA
            </label>
            <select
                id="coa_id"
                name="coa_id"
                v-model="coaId"
                required
                :class="inputClass(!!errors.coa_id)"
            >
                <option value="" disabled>Pilih COA</option>

                <optgroup v-if="incomeCoas.length" label="Income">
                    <option v-for="coa in incomeCoas" :key="coa.id" :value="String(coa.id)">
                        {{ optionLabel(coa) }}
                    </option>
                </optgroup>

                <optgroup v-if="expenseCoas.length" label="Expense">
                    <option v-for="coa in expenseCoas" :key="coa.id" :value="String(coa.id)">
                        {{ optionLabel(coa) }}
                    </option>
                </optgroup>
            </select>
            <p v-if="errors.coa_id" class="mt-1 text-sm text-red-600">{{ errors.coa_id }}</p>
        </div>

        <div>
            <label for="description" class="mb-1 block text-sm font-medium text-slate-700">
                Deskripsi <span class="font-normal text-slate-400">(opsional)</span>
            </label>
            <input
                id="description"
                name="description"
                type="text"
                v-model="description"
                maxlength="255"
                placeholder="Contoh: Gaji bulan Januari"
                :class="inputClass(!!errors.description)"
            />
            <p v-if="errors.description" class="mt-1 text-sm text-red-600">
                {{ errors.description }}
            </p>
        </div>

        <div>
            <span class="mb-2 block text-sm font-medium text-slate-700">Tipe Nominal</span>

            <!-- Hanya satu radio yang bisa aktif; `mode` menentukan hidden input
                 mana yang berisi nominal. -->
            <div class="flex flex-wrap gap-2">
                <label class="cursor-pointer">
                    <input class="peer sr-only" type="radio" value="debit" v-model="mode" />
                    <span
                        class="inline-block rounded-md border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 transition peer-checked:border-slate-900 peer-checked:bg-slate-900 peer-checked:text-white"
                    >
                        Debit
                    </span>
                </label>

                <label class="cursor-pointer">
                    <input class="peer sr-only" type="radio" value="credit" v-model="mode" />
                    <span
                        class="inline-block rounded-md border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 transition peer-checked:border-slate-900 peer-checked:bg-slate-900 peer-checked:text-white"
                    >
                        Credit
                    </span>
                </label>
            </div>

            <label for="amount" class="mt-4 mb-1 block text-sm font-medium text-slate-700">
                Nominal
            </label>
            <input
                id="amount"
                type="number"
                min="0"
                step="0.01"
                v-model="amount"
                required
                placeholder="0"
                :class="inputClass(!!errors.amount)"
            />
            <p class="mt-1 text-xs text-slate-500">
                Isi salah satu sisi. Sisi lainnya otomatis bernilai 0.
            </p>
            <p v-if="errors.amount" class="mt-1 text-sm text-red-600">{{ errors.amount }}</p>
        </div>

        <div class="flex items-center gap-3">
            <button
                type="submit"
                class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700"
            >
                {{ isEdit ? "Simpan Perubahan" : "Simpan" }}
            </button>

            <a
                :href="cancelUrl"
                class="rounded-md border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
            >
                Batal
            </a>
        </div>
    </form>
</template>
