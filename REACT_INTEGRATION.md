# React Integration Guide

## Setup Axios untuk API Integration

### 1. Install Dependencies

```bash
npm install axios
```

### 2. Create API Configuration File

**File: `src/services/api.js`**

```javascript
import axios from 'axios';

const API_BASE_URL = process.env.REACT_APP_API_URL || 'http://localhost:8000/api';

// Create axios instance
const api = axios.create({
  baseURL: API_BASE_URL,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
});

// Request interceptor - Add token to headers
api.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('token');
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);

// Response interceptor - Handle errors
api.interceptors.response.use(
  (response) => {
    return response.data;
  },
  (error) => {
    if (error.response) {
      // Handle 401 Unauthorized
      if (error.response.status === 401) {
        localStorage.removeItem('token');
        localStorage.removeItem('user');
        window.location.href = '/login';
      }
      
      // Return error message
      return Promise.reject(error.response.data);
    }
    return Promise.reject(error);
  }
);

export default api;
```

### 3. Create Auth Service

**File: `src/services/authService.js`**

```javascript
import api from './api';

export const authService = {
  // Register
  register: async (userData) => {
    const response = await api.post('/auth/register', userData);
    if (response.success && response.data.token) {
      localStorage.setItem('token', response.data.token);
      localStorage.setItem('user', JSON.stringify(response.data.user));
    }
    return response;
  },

  // Login
  login: async (credentials) => {
    const response = await api.post('/auth/login', credentials);
    if (response.success && response.data.token) {
      localStorage.setItem('token', response.data.token);
      localStorage.setItem('user', JSON.stringify(response.data.user));
    }
    return response;
  },

  // Logout
  logout: async () => {
    await api.post('/auth/logout');
    localStorage.removeItem('token');
    localStorage.removeItem('user');
  },

  // Get current user
  getCurrentUser: async () => {
    const response = await api.get('/auth/me');
    if (response.success) {
      localStorage.setItem('user', JSON.stringify(response.data));
    }
    return response;
  },

  // Update profile
  updateProfile: async (profileData) => {
    const response = await api.put('/auth/profile', profileData);
    if (response.success) {
      localStorage.setItem('user', JSON.stringify(response.data));
    }
    return response;
  },

  // Change password
  changePassword: async (passwordData) => {
    return await api.put('/auth/change-password', passwordData);
  },

  // Check if user is authenticated
  isAuthenticated: () => {
    return !!localStorage.getItem('token');
  },

  // Get stored user
  getStoredUser: () => {
    const user = localStorage.getItem('user');
    return user ? JSON.parse(user) : null;
  },
};
```

### 4. Create Ticket Service

**File: `src/services/ticketService.js`**

```javascript
import api from './api';

export const ticketService = {
  // Get all tickets with filters
  getTickets: async (filters = {}) => {
    const params = new URLSearchParams(filters).toString();
    const url = params ? `/tickets?${params}` : '/tickets';
    return await api.get(url);
  },

  // Get ticket statistics
  getStatistics: async () => {
    return await api.get('/tickets/statistics');
  },

  // Get lecturers list
  getLecturers: async () => {
    return await api.get('/tickets/lecturers');
  },

  // Get ticket detail
  getTicketById: async (id) => {
    return await api.get(`/tickets/${id}`);
  },

  // Create ticket (mahasiswa)
  createTicket: async (ticketData) => {
    return await api.post('/tickets', ticketData);
  },

  // Update ticket (mahasiswa)
  updateTicket: async (id, ticketData) => {
    return await api.put(`/tickets/${id}`, ticketData);
  },

  // Review ticket (dosen)
  reviewTicket: async (id, notes) => {
    return await api.post(`/tickets/${id}/review`, { lecturer_notes: notes });
  },

  // Approve ticket (dosen)
  approveTicket: async (id, notes) => {
    return await api.post(`/tickets/${id}/approve`, { lecturer_notes: notes });
  },

  // Reject ticket (dosen)
  rejectTicket: async (id, reason) => {
    return await api.post(`/tickets/${id}/reject`, { rejection_reason: reason });
  },

  // Complete ticket (admin)
  completeTicket: async (id, notes) => {
    return await api.post(`/tickets/${id}/complete`, { admin_notes: notes });
  },

  // Delete ticket (admin)
  deleteTicket: async (id) => {
    return await api.delete(`/tickets/${id}`);
  },
};
```

### 5. Create Document Service

**File: `src/services/documentService.js`**

```javascript
import api from './api';

export const documentService = {
  // Get documents by ticket
  getDocumentsByTicket: async (ticketId) => {
    return await api.get(`/tickets/${ticketId}/documents`);
  },

  // Upload document
  uploadDocument: async (ticketId, file, documentType) => {
    const formData = new FormData();
    formData.append('file', file);
    formData.append('document_type', documentType);

    return await api.post(`/tickets/${ticketId}/documents`, formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    });
  },

  // Download document
  downloadDocument: async (documentId) => {
    const response = await api.get(`/documents/${documentId}/download`, {
      responseType: 'blob',
    });
    return response;
  },

  // Delete document
  deleteDocument: async (documentId) => {
    return await api.delete(`/documents/${documentId}`);
  },
};
```

### 6. Create Auth Context (Optional - for state management)

**File: `src/contexts/AuthContext.js`**

```javascript
import React, { createContext, useState, useContext, useEffect } from 'react';
import { authService } from '../services/authService';

const AuthContext = createContext();

export const useAuth = () => {
  const context = useContext(AuthContext);
  if (!context) {
    throw new Error('useAuth must be used within AuthProvider');
  }
  return context;
};

export const AuthProvider = ({ children }) => {
  const [user, setUser] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    // Check if user is logged in
    const storedUser = authService.getStoredUser();
    if (storedUser) {
      setUser(storedUser);
    }
    setLoading(false);
  }, []);

  const login = async (credentials) => {
    const response = await authService.login(credentials);
    setUser(response.data.user);
    return response;
  };

  const logout = async () => {
    await authService.logout();
    setUser(null);
  };

  const register = async (userData) => {
    const response = await authService.register(userData);
    setUser(response.data.user);
    return response;
  };

  const updateProfile = async (profileData) => {
    const response = await authService.updateProfile(profileData);
    setUser(response.data);
    return response;
  };

  const value = {
    user,
    login,
    logout,
    register,
    updateProfile,
    isAuthenticated: authService.isAuthenticated(),
    loading,
  };

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
};
```

### 7. Protected Route Component

**File: `src/components/ProtectedRoute.js`**

```javascript
import React from 'react';
import { Navigate } from 'react-router-dom';
import { useAuth } from '../contexts/AuthContext';

const ProtectedRoute = ({ children, allowedRoles = [] }) => {
  const { user, isAuthenticated, loading } = useAuth();

  if (loading) {
    return <div>Loading...</div>;
  }

  if (!isAuthenticated) {
    return <Navigate to="/login" replace />;
  }

  if (allowedRoles.length > 0 && !allowedRoles.includes(user?.role)) {
    return <Navigate to="/unauthorized" replace />;
  }

  return children;
};

export default ProtectedRoute;
```

### 8. Example Usage in Components

#### Login Component

```javascript
import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { useAuth } from '../contexts/AuthContext';

const LoginPage = () => {
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState('');
  const [loading, setLoading] = useState(false);
  
  const { login } = useAuth();
  const navigate = useNavigate();

  const handleSubmit = async (e) => {
    e.preventDefault();
    setError('');
    setLoading(true);

    try {
      const response = await login({ email, password });
      
      // Redirect based on role
      if (response.data.user.role === 'mahasiswa') {
        navigate('/student/dashboard');
      } else if (response.data.user.role === 'dosen') {
        navigate('/lecturer/dashboard');
      } else if (response.data.user.role === 'admin') {
        navigate('/admin/dashboard');
      }
    } catch (err) {
      setError(err.message || 'Login failed');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div>
      <h1>Login</h1>
      {error && <div className="error">{error}</div>}
      <form onSubmit={handleSubmit}>
        <input
          type="email"
          placeholder="Email"
          value={email}
          onChange={(e) => setEmail(e.target.value)}
          required
        />
        <input
          type="password"
          placeholder="Password"
          value={password}
          onChange={(e) => setPassword(e.target.value)}
          required
        />
        <button type="submit" disabled={loading}>
          {loading ? 'Loading...' : 'Login'}
        </button>
      </form>
    </div>
  );
};

export default LoginPage;
```

#### Create Ticket Component (Mahasiswa)

```javascript
import React, { useState, useEffect } from 'react';
import { ticketService } from '../services/ticketService';

const CreateTicket = () => {
  const [lecturers, setLecturers] = useState([]);
  const [formData, setFormData] = useState({
    lecturer_id: '',
    title: '',
    description: '',
    type: 'surat_rekomendasi',
    priority: 'medium',
  });
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');

  useEffect(() => {
    loadLecturers();
  }, []);

  const loadLecturers = async () => {
    try {
      const response = await ticketService.getLecturers();
      setLecturers(response.data);
    } catch (err) {
      setError('Failed to load lecturers');
    }
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true);
    setError('');

    try {
      await ticketService.createTicket(formData);
      alert('Ticket created successfully!');
      // Reset form or redirect
    } catch (err) {
      setError(err.message || 'Failed to create ticket');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div>
      <h2>Create New Ticket</h2>
      {error && <div className="error">{error}</div>}
      
      <form onSubmit={handleSubmit}>
        <select
          value={formData.lecturer_id}
          onChange={(e) => setFormData({ ...formData, lecturer_id: e.target.value })}
          required
        >
          <option value="">Select Lecturer</option>
          {lecturers.map((lecturer) => (
            <option key={lecturer.id} value={lecturer.id}>
              {lecturer.name}
            </option>
          ))}
        </select>

        <input
          type="text"
          placeholder="Title"
          value={formData.title}
          onChange={(e) => setFormData({ ...formData, title: e.target.value })}
          required
        />

        <textarea
          placeholder="Description"
          value={formData.description}
          onChange={(e) => setFormData({ ...formData, description: e.target.value })}
          required
        />

        <select
          value={formData.type}
          onChange={(e) => setFormData({ ...formData, type: e.target.value })}
        >
          <option value="surat_keterangan">Surat Keterangan</option>
          <option value="surat_rekomendasi">Surat Rekomendasi</option>
          <option value="ijin">Ijin</option>
          <option value="lainnya">Lainnya</option>
        </select>

        <select
          value={formData.priority}
          onChange={(e) => setFormData({ ...formData, priority: e.target.value })}
        >
          <option value="low">Low</option>
          <option value="medium">Medium</option>
          <option value="high">High</option>
        </select>

        <button type="submit" disabled={loading}>
          {loading ? 'Creating...' : 'Create Ticket'}
        </button>
      </form>
    </div>
  );
};

export default CreateTicket;
```

#### Ticket List Component

```javascript
import React, { useState, useEffect } from 'react';
import { ticketService } from '../services/ticketService';

const TicketList = () => {
  const [tickets, setTickets] = useState([]);
  const [loading, setLoading] = useState(true);
  const [filters, setFilters] = useState({
    status: '',
    priority: '',
    search: '',
  });

  useEffect(() => {
    loadTickets();
  }, [filters]);

  const loadTickets = async () => {
    setLoading(true);
    try {
      const response = await ticketService.getTickets(filters);
      setTickets(response.data.data); // Pagination data
    } catch (err) {
      console.error('Failed to load tickets', err);
    } finally {
      setLoading(false);
    }
  };

  if (loading) return <div>Loading...</div>;

  return (
    <div>
      <h2>Tickets</h2>
      
      {/* Filters */}
      <div>
        <select
          value={filters.status}
          onChange={(e) => setFilters({ ...filters, status: e.target.value })}
        >
          <option value="">All Status</option>
          <option value="pending">Pending</option>
          <option value="in_review">In Review</option>
          <option value="approved">Approved</option>
          <option value="rejected">Rejected</option>
          <option value="completed">Completed</option>
        </select>

        <input
          type="text"
          placeholder="Search..."
          value={filters.search}
          onChange={(e) => setFilters({ ...filters, search: e.target.value })}
        />
      </div>

      {/* Ticket List */}
      <div>
        {tickets.map((ticket) => (
          <div key={ticket.id} className="ticket-card">
            <h3>{ticket.title}</h3>
            <p>Ticket Number: {ticket.ticket_number}</p>
            <p>Status: {ticket.status}</p>
            <p>Priority: {ticket.priority}</p>
            <p>Created: {new Date(ticket.created_at).toLocaleDateString()}</p>
          </div>
        ))}
      </div>
    </div>
  );
};

export default TicketList;
```

### 9. App.js with Routes

```javascript
import React from 'react';
import { BrowserRouter, Routes, Route } from 'react-router-dom';
import { AuthProvider } from './contexts/AuthContext';
import ProtectedRoute from './components/ProtectedRoute';
import LoginPage from './pages/LoginPage';
import StudentDashboard from './pages/student/Dashboard';
import LecturerDashboard from './pages/lecturer/Dashboard';
import AdminDashboard from './pages/admin/Dashboard';

function App() {
  return (
    <AuthProvider>
      <BrowserRouter>
        <Routes>
          <Route path="/login" element={<LoginPage />} />
          
          {/* Student Routes */}
          <Route
            path="/student/*"
            element={
              <ProtectedRoute allowedRoles={['mahasiswa']}>
                <StudentDashboard />
              </ProtectedRoute>
            }
          />

          {/* Lecturer Routes */}
          <Route
            path="/lecturer/*"
            element={
              <ProtectedRoute allowedRoles={['dosen']}>
                <LecturerDashboard />
              </ProtectedRoute>
            }
          />

          {/* Admin Routes */}
          <Route
            path="/admin/*"
            element={
              <ProtectedRoute allowedRoles={['admin']}>
                <AdminDashboard />
              </ProtectedRoute>
            }
          />
        </Routes>
      </BrowserRouter>
    </AuthProvider>
  );
}

export default App;
```

### 10. Environment Variables

**File: `.env`**

```env
REACT_APP_API_URL=http://localhost:8000/api
```

## Tips & Best Practices

1. **Error Handling**: Always wrap API calls in try-catch blocks
2. **Loading States**: Show loading indicators during API calls
3. **Token Refresh**: Implement token refresh if needed
4. **File Upload**: Use FormData for file uploads
5. **Pagination**: Handle paginated responses from the API
6. **Validation**: Validate forms before submitting to API
7. **Toast Notifications**: Use toast/notification library for user feedback

## Testing the Integration

1. Start Laravel backend: `php artisan serve`
2. Start React app: `npm start`
3. Test authentication flow
4. Test CRUD operations for tickets
5. Test file uploads

Happy coding! 🚀
