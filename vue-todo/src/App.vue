<template>
  <div class="min-h-screen bg-gray-100 p-6">
    <h1 class="text-3xl font-bold mb-6 text-center">Vue 3 Todo List (Laravel Backend)</h1>

    <!-- Add Task -->
    <div class="flex mb-4 justify-center">
      <input
        v-model="newTask"
        @keyup.enter="addTask"
        type="text"
        placeholder="Enter a task"
        class="border border-gray-300 rounded-l px-4 py-2 w-1/2"
      />
      <button
        @click="addTask"
        class="bg-blue-500 text-white px-4 py-2 rounded-r hover:bg-blue-600"
      >
        Add
      </button>
    </div>

    <!-- Task List -->
    <ul class="max-w-md mx-auto bg-white rounded shadow divide-y divide-gray-200">
      <li v-for="task in tasks" :key="task.id" class="flex justify-between items-center px-4 py-2">
        <div>
          <input type="checkbox" v-model="task.completed" @change="updateTask(task)" />
          <span :class="{ 'line-through text-gray-400': task.completed }" class="ml-2">
            {{ task.title }}
          </span>
        </div>
        <button @click="deleteTask(task)" class="text-red-500 hover:text-red-700">Delete</button>
      </li>
    </ul>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue'
import api from './api.js'

export default {
  setup() {
    const tasks = ref([])
    const newTask = ref('')

    // Fetch tasks from backend
    const fetchTasks = async () => {
      try {
        const response = await api.get('/todos')
        tasks.value = response.data
      } catch (err) {
        console.error('Error fetching tasks:', err)
      }
    }

    // Add a task
    const addTask = async () => {
      if (!newTask.value.trim()) return
      try {
        const response = await api.post('/todos', { title: newTask.value })
        tasks.value.push(response.data)
        newTask.value = ''
      } catch (err) {
        console.error('Error adding task:', err)
      }
    }

    // Update a task (toggle completed)
    const updateTask = async (task) => {
      try {
        await api.put(`/todos/${task.id}`, { title: task.title, completed: task.completed })
      } catch (err) {
        console.error('Error updating task:', err)
      }
    }

    // Delete a task
    const deleteTask = async (task) => {
      try {
        await api.delete(`/todos/${task.id}`)
        tasks.value = tasks.value.filter(t => t.id !== task.id)
      } catch (err) {
        console.error('Error deleting task:', err)
      }
    }

    onMounted(fetchTasks)

    return { tasks, newTask, addTask, updateTask, deleteTask }
  }
}
</script>

<style>
body {
  font-family: 'Inter', sans-serif;
}
</style>
