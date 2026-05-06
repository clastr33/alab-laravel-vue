<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { apiFetch } from '../services/api'
import { clearToken, decodeJwt, isTokenValid } from '../services/auth'

const router = useRouter()

const loading = ref(true)
const error = ref('')
const data = ref(null)

const jwtInfo = computed(() => {
  const token = localStorage.getItem('alab.jwt')
  return token ? decodeJwt(token) : null
})

async function load() {
  loading.value = true
  error.value = ''
  try {
    data.value = await apiFetch('/api/results')
  } catch (e) {
    if (e?.status === 401) {
      clearToken()
      await router.push({ name: 'login' })
      return
    }
    error.value = e?.message || 'Failed to load results'
  } finally {
    loading.value = false
  }
}

function logout() {
  clearToken()
  router.push({ name: 'login' })
}

onMounted(async () => {
  const token = localStorage.getItem('alab.jwt')
  if (!token || !isTokenValid(token)) {
    clearToken()
    await router.push({ name: 'login' })
    return
  }
  await load()
})
</script>

<template>
  <section class="wrap">
    <div class="crumbs">
      <RouterLink to="/">Strona główna</RouterLink>
      <span class="sep">›</span>
      <span>Twoje badania i zlecenia</span>
    </div>

    <div class="top">
      <h1 class="title">Twoje badania i zlecenia</h1>
      <button class="ghost" type="button" @click="logout">Wyloguj</button>
    </div>

    <div v-if="loading" class="card">Ładowanie…</div>
    <div v-else-if="error" class="error" role="alert">{{ error }}</div>
    <template v-else>
      <div v-if="!data?.orders?.length" class="empty">Brak badań oraz zleceń</div>

      <div v-else class="cards">
        <div class="card">
          <h2>Pacjent</h2>
          <dl class="dl">
            <div><dt>Imię</dt><dd>{{ data.patient.name }}</dd></div>
            <div><dt>Nazwisko</dt><dd>{{ data.patient.surname }}</dd></div>
            <div><dt>Płeć</dt><dd>{{ data.patient.sex }}</dd></div>
            <div><dt>Data urodzenia</dt><dd>{{ data.patient.birthDate }}</dd></div>
          </dl>
        </div>

        <div class="card">
          <h2>Wyniki</h2>
          <div v-for="order in data.orders" :key="order.orderId" class="order">
            <div class="orderTitle">Zlecenie: {{ order.orderId }}</div>
            <div class="table">
              <div class="tr head">
                <div>Nazwa</div>
                <div>Wartość</div>
                <div>Referencja</div>
              </div>
              <div v-for="r in order.results" :key="r.name" class="tr">
                <div class="name">{{ r.name }}</div>
                <div class="val">{{ r.value ?? '-' }}</div>
                <div class="ref">{{ r.reference ?? '-' }}</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </template>
  </section>
</template>

<style scoped>
.wrap {
  display: grid;
  gap: 18px;
}
.crumbs {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 13px;
  color: #5a6d86;
}
.crumbs a {
  color: #2d66b3;
  text-decoration: none;
}
.crumbs a:hover {
  text-decoration: underline;
}
.sep {
  opacity: 0.6;
}

.top {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
}
.title {
  margin: 0;
  font-size: 34px;
  letter-spacing: -0.02em;
  color: #0b2f66;
}
.ghost {
  height: 40px;
  padding: 0 18px;
  border-radius: 999px;
  border: 1px solid #cfe0f2;
  background: #fff;
  color: #0b2f66;
  font-weight: 800;
  cursor: pointer;
}
.ghost:hover {
  background: #f2f6fc;
}

.field {
  display: grid;
  gap: 6px;
}
.label {
  font-size: 12px;
  color: #5a6d86;
  font-weight: 700;
}
.input {
  height: 44px;
  border-radius: 10px;
  border: 1px solid #d9e4f2;
  padding: 0 12px;
  outline: none;
}
.input:focus {
  border-color: rgba(45, 102, 179, 0.55);
  box-shadow: 0 0 0 4px rgba(45, 102, 179, 0.12);
}
.primary {
  height: 44px;
  padding: 0 18px;
  border-radius: 999px;
  border: 1px solid #0b2f66;
  background: #0b2f66;
  color: #fff;
  font-weight: 800;
  cursor: pointer;
  white-space: nowrap;
}
.primary.small {
  height: 36px;
  padding: 0 14px;
  font-size: 13px;
}
.primary:disabled {
  opacity: 0.65;
  cursor: not-allowed;
}
.primary:hover:enabled {
  background: #083062;
}

.empty {
  text-align: center;
  color: #123a6d;
  padding: 26px 0;
}

.cards {
  display: grid;
  grid-template-columns: 340px 1fr;
  gap: 14px;
}
.card {
  background: #fff;
  border: 1px solid #e6edf6;
  border-radius: 14px;
  box-shadow: 0 6px 18px rgba(12, 37, 78, 0.06);
  padding: 16px;
}
.error {
  color: #7a1c1c;
  background: #fff2f2;
  border: 1px solid #f0c6c6;
  padding: 10px 12px;
  border-radius: 10px;
}
h2 {
  margin: 0 0 10px;
  font-size: 16px;
  color: #0b2f66;
}
.dl {
  display: grid;
  gap: 8px;
}
.dl > div {
  display: grid;
  grid-template-columns: 120px 1fr;
  gap: 10px;
}
dt {
  color: #5a6d86;
  font-size: 12px;
  font-weight: 700;
}
dd {
  margin: 0;
}
.order {
  margin-top: 2rem;
  margin-bottom: 3rem;
  padding-top: 12px;
  border-top: 1px solid #edf3fb;
}
.orderTitle {
  font-weight: 600;
  margin-bottom: 10px;
  color: #123a6d;
}
.table {
  display: grid;
  gap: 6px;
}
.tr {
  display: grid;
  grid-template-columns: 2fr 1fr 1fr;
  gap: 10px;
  padding: 8px 10px;
  border-radius: 12px;
  border: 1px solid #edf3fb;
  background: #fff;
}
.tr.head {
  background: #f2f6fc;
  font-size: 12px;
  color: #5a6d86;
  font-weight: 800;
}
.name {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}
.val,
.ref {
  font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, 'Liberation Mono', 'Courier New', monospace;
  font-size: 13px;
}

@media (max-width: 980px) {
  .title {
    font-size: 28px;
  }
  .cards {
    grid-template-columns: 1fr;
  }
}
@media (max-width: 720px) {
  .primary {
    width: 100%;
  }
}
</style>
