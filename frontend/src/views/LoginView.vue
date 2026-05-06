<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { apiFetch } from '../services/api'
import { setToken, msUntilExpiry, clearToken } from '../services/auth'

const router = useRouter()

const login = ref('')
const password = ref('')
const error = ref('')
const loading = ref(false)

async function onSubmit() {
  error.value = ''
  loading.value = true
  try {
    const data = await apiFetch('/api/login', {
      method: 'POST',
      body: JSON.stringify({ login: login.value, password: password.value }),
    })

    setToken(data.token)

    // Auto logout when token expires
    const ms = msUntilExpiry(data.token)
    if (ms > 0) {
      window.setTimeout(() => {
        clearToken()
      }, ms)
    }

    await router.push({ name: 'results' })
  } catch (e) {
    error.value = e?.message || 'Login failed'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <section class="wrap">
    <div class="crumbs">
      <RouterLink to="/">Strona główna</RouterLink>
      <span class="sep">›</span>
      <span>Logowanie</span>
    </div>

    <h1 class="title">Logowanie</h1>

    <div class="card">
      <div class="cardTitle">Zaloguj się</div>
      <div class="cardHint">
        Login: imię + nazwisko (np. <code>PiotrKowalski</code>). Hasło: data urodzenia (np. <code>1983-04-12</code>).
      </div>

      <form class="form" @submit.prevent="onSubmit">
        <div class="field">
          <label class="label" for="login">Login</label>
          <input id="login" v-model="login" class="input" autocomplete="username" placeholder="PiotrKowalski" />
        </div>

        <div class="field">
          <label class="label" for="password">Hasło</label>
          <input id="password" type="password" v-model="password" class="input" autocomplete="current-password" placeholder="YYYY-MM-DD" />
        </div>

        <button class="primary" type="submit" :disabled="loading">
          {{ loading ? 'Logowanie...' : 'Zaloguj się' }}
        </button>

        <div v-if="error" class="error" role="alert">{{ error }}</div>
      </form>
    </div>
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
.title {
  margin: 0;
  font-size: 34px;
  letter-spacing: -0.02em;
  color: #0b2f66;
}
.card {
  background: #fff;
  border: 1px solid #e6edf6;
  border-radius: 14px;
  box-shadow: 0 6px 18px rgba(12, 37, 78, 0.06);
  padding: 20px;
}
.cardTitle {
  font-size: 18px;
  font-weight: 800;
  color: #0b2f66;
  margin-bottom: 6px;
}
.cardHint {
  color: #5a6d86;
  font-size: 13px;
  margin-bottom: 16px;
}
.cardHint code {
  font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, 'Liberation Mono', 'Courier New', monospace;
  font-size: 12px;
  background: #f2f6fc;
  padding: 2px 6px;
  border-radius: 6px;
  color: #123a6d;
}
.form {
  display: grid;
  grid-template-columns: 1fr 1fr auto;
  align-items: end;
  gap: 12px;
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
  width: 100%;
  max-width: 250px;
  border-radius: 10px;
  border: 1px solid #d9e4f2;
  background: #fff;
  padding: 0 12px;
  color: #0b1f3b;
  outline: none;
}
.input::placeholder {
  color: #9aa9bc;
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
.primary:disabled {
  opacity: 0.65;
  cursor: not-allowed;
}
.primary:hover:enabled {
  background: #083062;
}
.error {
  grid-column: 1 / -1;
  margin-top: 10px;
  color: #7a1c1c;
  background: #fff2f2;
  border: 1px solid #f0c6c6;
  padding: 10px 12px;
  border-radius: 10px;
}

@media (max-width: 820px) {
  .title {
    font-size: 28px;
  }
  .form {
    grid-template-columns: 1fr;
  }
  .input {
    max-width: 200px;
  }
  .primary {
    width: 100%;
    justify-content: center;
  }
}
</style>
