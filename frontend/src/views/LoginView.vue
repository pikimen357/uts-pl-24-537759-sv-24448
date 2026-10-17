<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import api from '../api'

const router = useRouter()
const email = ref('admin@example.com')
const password = ref('')
const error = ref('')
const loading = ref(false)

async function login() {
  error.value = ''
  loading.value = true
  try {
    const { data } = await api.post('/login', {
      email: email.value,
      password: password.value,
    })
    localStorage.setItem('token', data.token)
    router.push({ name: 'buku' })
  } catch (e) {
    error.value = e.response?.data?.message || 'Gagal terhubung ke server'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="container" style="max-width: 400px">
    <div class="card">
      <h2>Login</h2>
      <p v-if="error" class="error">{{ error }}</p>
      <label>Email</label>
      <input v-model="email" type="email" />
      <label>Password</label>
      <input v-model="password" type="password" @keyup.enter="login" />
      <button :disabled="loading" @click="login">
        {{ loading ? 'Memproses...' : 'Masuk' }}
      </button>
    </div>
  </div>
</template>