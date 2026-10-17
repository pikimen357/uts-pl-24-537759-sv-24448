<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '../api'

const router = useRouter()
const bukus = ref([])
const errors = ref({})
const message = ref('')
const editingId = ref(null)

const emptyForm = () => ({ judul: '', penulis: '', tahun_terbit: '', stok: 0 })
const form = ref(emptyForm())

async function load() {
  const { data } = await api.get('/buku')
  bukus.value = data.data
}

async function save() {
  errors.value = {}
  message.value = ''
  try {
    if (editingId.value) {
      await api.put(`/buku/${editingId.value}`, form.value)
      message.value = 'Buku berhasil diubah'
    } else {
      await api.post('/buku', form.value)
      message.value = 'Buku berhasil ditambahkan'
    }
    resetForm()
    await load()
  } catch (e) {
    if (e.response?.status === 422) {
      errors.value = e.response.data.errors
    } else {
      message.value = 'Terjadi kesalahan'
    }
  }
}

function edit(buku) {
  editingId.value = buku.id
  form.value = {
    judul: buku.judul,
    penulis: buku.penulis,
    tahun_terbit: buku.tahun_terbit,
    stok: buku.stok,
  }
  errors.value = {}
}

function resetForm() {
  editingId.value = null
  form.value = emptyForm()
}

async function remove(id) {
  if (!confirm('Hapus buku ini?')) return
  await api.delete(`/buku/${id}`)
  message.value = 'Buku berhasil dihapus'
  await load()
}

async function logout() {
  try {
    await api.post('/logout')
  } finally {
    localStorage.removeItem('token')
    router.push({ name: 'login' })
  }
}

onMounted(load)
</script>

<template>
  <div class="container">
    <div class="header">
      <h1>Manajemen Buku</h1>
      <button class="secondary" @click="logout">Logout</button>
    </div>

    <p v-if="message">{{ message }}</p>

    <div class="card">
      <h3>{{ editingId ? 'Ubah Buku' : 'Tambah Buku' }}</h3>

      <label>Judul</label>
      <input v-model="form.judul" />
      <p v-if="errors.judul" class="error">{{ errors.judul[0] }}</p>

      <label>Penulis</label>
      <input v-model="form.penulis" />
      <p v-if="errors.penulis" class="error">{{ errors.penulis[0] }}</p>

      <label>Tahun Terbit</label>
      <input v-model="form.tahun_terbit" type="number" />
      <p v-if="errors.tahun_terbit" class="error">{{ errors.tahun_terbit[0] }}</p>

      <label>Stok</label>
      <input v-model="form.stok" type="number" />
      <p v-if="errors.stok" class="error">{{ errors.stok[0] }}</p>

      <button @click="save">{{ editingId ? 'Simpan Perubahan' : 'Tambah' }}</button>
      <button v-if="editingId" class="secondary" style="margin-left: .5rem" @click="resetForm">Batal</button>
    </div>

    <div class="card">
      <table>
        <thead>
          <tr><th>Judul</th><th>Penulis</th><th>Tahun</th><th>Stok</th><th>Aksi</th></tr>
        </thead>
        <tbody>
          <tr v-for="b in bukus" :key="b.id">
            <td>{{ b.judul }}</td>
            <td>{{ b.penulis }}</td>
            <td>{{ b.tahun_terbit }}</td>
            <td>{{ b.stok }}</td>
            <td>
              <button @click="edit(b)">Ubah</button>
              <button class="danger" style="margin-left: .5rem" @click="remove(b.id)">Hapus</button>
            </td>
          </tr>
          <tr v-if="!bukus.length"><td colspan="5">Belum ada data</td></tr>
        </tbody>
      </table>
    </div>
  </div>
</template>