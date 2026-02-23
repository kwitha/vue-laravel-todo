<template>
  <div :class="darkMode ? 'bg-gray-900 text-gray-200' : 'bg-gray-100 text-gray-900'" class="min-h-screen flex justify-center p-6 transition-colors duration-500">
    <div class="w-full max-w-xl">

      <!-- Card Container -->
      <div :class="darkMode ? 'bg-gray-800' : 'bg-white'" class="rounded-2xl shadow-lg p-6 transition-colors duration-500">

        <!-- Header -->
        <div class="flex justify-between items-center mb-4">
          <h1 class="text-2xl font-bold text-center flex-1">🚀 My Todo App</h1>
          <div class="flex gap-2">
            <button @click="toggleDarkMode" class="px-3 py-1 rounded-lg border hover:bg-gray-200 dark:hover:bg-gray-700 transition">
              {{ darkMode ? 'Light Mode' : 'Dark Mode' }}
            </button>
            <button v-if="user" @click="logout" class="px-3 py-1 rounded-lg border hover:bg-red-500 hover:text-white transition">Logout</button>
          </div>
        </div>

        <!-- AUTH FORMS -->
        <div v-if="!user">

          <!-- Login -->
          <div v-if="!showRegister">
            <h2 class="text-xl font-bold mb-4">Login</h2>
            <input v-model="email" type="email" placeholder="Email" class="w-full mb-2 p-2 border rounded"/>
            <input v-model="password" type="password" placeholder="Password" class="w-full mb-2 p-2 border rounded"/>
            <button @click="login" class="bg-blue-500 text-white px-4 py-2 rounded w-full mb-2">Login</button>
            <p class="text-sm">Don't have an account? <span @click="showRegister = true" class="text-blue-500 cursor-pointer">Register</span></p>

            <div v-if="authErrors.length" class="mt-2 text-red-500 text-sm">
              <ul>
                <li v-for="(err,i) in authErrors" :key="i">{{ err }}</li>
              </ul>
            </div>
          </div>

          <!-- Register -->
          <div v-else>
            <h2 class="text-xl font-bold mb-4">Register</h2>
            <input v-model="name" type="text" placeholder="Name" class="w-full mb-2 p-2 border rounded"/>
            <input v-model="email" type="email" placeholder="Email" class="w-full mb-2 p-2 border rounded"/>
            <input v-model="password" type="password" placeholder="Password" class="w-full mb-2 p-2 border rounded"/>
            <input v-model="password_confirmation" type="password" placeholder="Confirm Password" class="w-full mb-2 p-2 border rounded"/>
            <button @click="registerUser" class="bg-green-500 text-white px-4 py-2 rounded w-full mb-2">Register</button>
            <p class="text-sm">Already have an account? <span @click="showRegister = false" class="text-blue-500 cursor-pointer">Login</span></p>

            <div v-if="authErrors.length" class="mt-2 text-red-500 text-sm">
              <ul>
                <li v-for="(err,i) in authErrors" :key="i">{{ err }}</li>
              </ul>
            </div>
          </div>

        </div>

        <!-- TODO APP -->
        <div v-else>

          <!-- Add Task -->
          <div class="flex mb-4">
            <input
              v-model="newTask"
              @keyup.enter="addTask"
              type="text"
              placeholder="What needs to be done?"
              :class="darkMode ? 'bg-gray-700 text-gray-200 placeholder-gray-400' : 'bg-white text-gray-900 placeholder-gray-500'"
              class="flex-1 border rounded-l-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400 transition-colors duration-500"
            />
            <button
              @click="addTask"
              class="bg-blue-500 text-white px-4 py-2 rounded-r-lg hover:bg-blue-600 transition"
            >
              Add
            </button>
          </div>

          <!-- Stats -->
          <div class="flex justify-between text-sm mb-4">
            <span>Total: {{ totalTasks }}</span>
            <span class="text-green-500">Completed: {{ completedTasks }}</span>
            <span class="text-blue-500">Remaining: {{ remainingTasks }}</span>
          </div>

          <!-- Filters -->
          <div class="flex justify-center gap-2 mb-4">
            <button
              @click="filter = 'all'"
              :class="filterButtonClass('all')"
            >
              All
            </button>
            <button
              @click="filter = 'active'"
              :class="filterButtonClass('active')"
            >
              Active
            </button>
            <button
              @click="filter = 'completed'"
              :class="filterButtonClass('completed')"
            >
              Completed
            </button>
          </div>

          <!-- Task List with Transition -->
          <transition-group name="list" tag="ul" class="divide-y">
            <li
              v-for="task in filteredTasks"
              :key="task.id"
              class="flex justify-between items-center py-3"
            >
              <div class="flex items-center flex-1">
                <input
                  type="checkbox"
                  v-model="task.completed"
                  @change="updateTask(task)"
                  class="mr-3"
                />
                <span
                  v-if="editingId !== task.id"
                  :class="{ 'line-through text-gray-400': task.completed }"
                  class="flex-1 transition-all duration-300"
                >
                  {{ task.title }}
                </span>
                <input
                  v-else
                  v-model="editingTitle"
                  @keyup.enter="saveEdit(task)"
                  class="border px-2 py-1 flex-1 rounded transition-colors duration-500"
                  :class="darkMode ? 'bg-gray-700 text-gray-200' : 'bg-white text-gray-900'"
                />
              </div>

              <div class="flex gap-2 ml-2 text-sm">
                <button v-if="editingId !== task.id" @click="startEdit(task)" class="text-blue-500 hover:underline transition">Edit</button>
                <button v-if="editingId === task.id" @click="saveEdit(task)" class="text-green-500 hover:underline transition">Save</button>
                <button v-if="editingId === task.id" @click="cancelEdit" class="text-gray-500 hover:underline transition">Cancel</button>
                <button @click="deleteTask(task)" class="text-red-500 hover:underline transition">Delete</button>
              </div>
            </li>
          </transition-group>

          <!-- Footer -->
          <div class="flex justify-between items-center mt-4 text-sm">
            <span>{{ remainingTasks }} items left</span>
            <button v-if="completedTasks > 0" @click="clearCompleted" class="text-red-500 hover:underline transition">Clear Completed</button>
          </div>

        </div>

      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted, computed } from 'vue'
import api, { initCsrf } from './api.js'

export default {
  setup() {

    const user = ref(null)
    const showRegister = ref(false)
    const darkMode = ref(false)
    const authErrors = ref([])

    const name = ref('')
    const email = ref('')
    const password = ref('')
    const password_confirmation = ref('')

    const tasks = ref([])
    const newTask = ref('')
    const editingId = ref(null)
    const editingTitle = ref('')
    const filter = ref('all')

    const totalTasks = computed(()=>tasks.value.length)
    const completedTasks = computed(()=>tasks.value.filter(t=>t.completed).length)
    const remainingTasks = computed(()=>totalTasks.value - completedTasks.value)
    const filteredTasks = computed(()=>{
      if(filter.value==='active') return tasks.value.filter(t=>!t.completed)
      if(filter.value==='completed') return tasks.value.filter(t=>t.completed)
      return tasks.value
    })

    const filterButtonClass = (type)=>[
      'px-3 py-1 rounded-full border transition',
      filter.value===type
        ? 'bg-blue-500 text-white border-blue-500'
        : darkMode.value
          ? 'bg-gray-700 text-gray-200 border-gray-600 hover:bg-gray-600'
          : 'bg-white text-gray-600 border-gray-300 hover:bg-gray-100'
    ]

    const toggleDarkMode = ()=>darkMode.value=!darkMode.value

    // ---------------- AUTH ----------------
    const registerUser = async ()=>{
      authErrors.value=[]
      try{
        await initCsrf()
        await api.post('/register',{
          name:name.value,
          email:email.value,
          password:password.value,
          password_confirmation:password_confirmation.value
        })
        alert('Registered! You can login now')
        showRegister.value=false
      }catch(err){
        if(err.response?.data?.errors){
          authErrors.value=Object.values(err.response.data.errors).flat()
        }else{
          authErrors.value=[err.response?.data?.message || 'Registration failed']
        }
      }
    }

    const login = async ()=>{
      authErrors.value=[]
      try{
        await initCsrf()
        const res = await api.post('/login',{email:email.value,password:password.value})
        localStorage.setItem('token',res.data.access_token)

        // FETCH current user info
        const me = await api.get('/me',{ headers:{ Authorization:`Bearer ${res.data.access_token}` } })
        user.value = me.data

        // Clear fields
        email.value=''
        password.value=''

        // Fetch tasks
        await fetchTasks()

      }catch(err){
        if(err.response?.data?.errors){
          authErrors.value=Object.values(err.response.data.errors).flat()
        }else{
          authErrors.value=[err.response?.data?.message || 'Login failed']
        }
      }
    }

    const logout = async ()=>{
      try{
        await api.post('/logout',{},{ headers:{ Authorization:`Bearer ${localStorage.getItem('token')}` } })
      }catch{}
      user.value=null
      tasks.value=[]
      email.value=''
      password.value=''
      localStorage.removeItem('token')
    }

    // ---------------- TODO ----------------
    const fetchTasks = async ()=>{
      const res = await api.get('/todos',{ headers:{ Authorization:`Bearer ${localStorage.getItem('token')}` } })
      tasks.value=res.data
    }

    const addTask = async ()=>{
      if(!newTask.value.trim()) return
      const res = await api.post('/todos',{title:newTask.value,completed:false},{ headers:{ Authorization:`Bearer ${localStorage.getItem('token')}` } })
      tasks.value.push(res.data)
      newTask.value=''
    }

    const updateTask = async task=> await api.put(`/todos/${task.id}`,task,{ headers:{ Authorization:`Bearer ${localStorage.getItem('token')}` } })
    const startEdit = task=>{ editingId.value=task.id; editingTitle.value=task.title }
    const saveEdit = async task=>{ if(!editingTitle.value.trim()) return; task.title=editingTitle.value; await api.put(`/todos/${task.id}`,task,{ headers:{ Authorization:`Bearer ${localStorage.getItem('token')}` } }); editingId.value=null; editingTitle.value='' }
    const cancelEdit = ()=>{ editingId.value=null; editingTitle.value='' }
    const deleteTask = async task=>{ await api.delete(`/todos/${task.id}`,{ headers:{ Authorization:`Bearer ${localStorage.getItem('token')}` } }); tasks.value=tasks.value.filter(t=>t.id!==task.id) }
    const clearCompleted = async ()=>{ const done=tasks.value.filter(t=>t.completed); for(let t of done) await api.delete(`/todos/${t.id}`,{ headers:{ Authorization:`Bearer ${localStorage.getItem('token')}` } }); tasks.value=tasks.value.filter(t=>!t.completed) }

    // ---------------- INIT ----------------
    onMounted(async ()=>{
      const token = localStorage.getItem('token')
      if(token){
        try{
          const me = await api.get('/me',{ headers:{ Authorization:`Bearer ${token}` } })
          user.value = me.data
          await fetchTasks()
        }catch{
          localStorage.removeItem('token')
        }
      }
    })

    return {
      user, showRegister, darkMode, toggleDarkMode, authErrors,
      name, email, password, password_confirmation,
      registerUser, login, logout,
      tasks, newTask, editingId, editingTitle, filter,
      totalTasks, completedTasks, remainingTasks, filteredTasks, filterButtonClass,
      addTask, updateTask, deleteTask, startEdit, saveEdit, cancelEdit, clearCompleted
    }
  }
}
</script>

<style>
body { font-family:'Inter',sans-serif; }
/* Transition animations */
.list-enter-active, .list-leave-active { transition: all 0.3s ease; }
.list-enter-from, .list-leave-to { opacity:0; transform:translateY(-10px); }
</style>