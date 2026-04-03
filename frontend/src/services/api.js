import axios from 'axios'

const api = axios.create({
    baseURL: import.meta.env.VITE_API_URL || 'http://127.0.0.1:8000',
    withCredentials: true,
    withXSRFToken: true,
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
    },
})

export default api
