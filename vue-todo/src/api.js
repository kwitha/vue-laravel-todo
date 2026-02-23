import axios from 'axios'

const api = axios.create({
  baseURL: 'http://127.0.0.1:8000/api',
  withCredentials: true
})

// Add a helper to initialize CSRF
export const initCsrf = async () => {
  await axios.get('http://127.0.0.1:8000/sanctum/csrf-cookie', { withCredentials: true })
}

export default api