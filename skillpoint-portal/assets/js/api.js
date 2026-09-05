/**
 * MB Internet And Digital Studio | Central REST API Client
 * Automatically manages CSRF Security Tokens & Standardizes JSON Requests for XAMPP
 */

class API {
  static csrfToken = null;

  static getBasePath() {
    // Determine the base folder dynamically
    const path = window.location.pathname;
    if (path.includes('/admin/')) {
      return '../';
    }
    return '';
  }

  static getApiPath(endpoint) {
    if (endpoint.startsWith('http://') || endpoint.startsWith('https://')) {
      return endpoint;
    }
    const cleanEndpoint = endpoint.startsWith('/') ? endpoint.substring(1) : endpoint;
    return this.getBasePath() + cleanEndpoint;
  }

  static async getCsrfToken() {
    if (this.csrfToken) return this.csrfToken;
    try {
      const url = this.getApiPath('api/auth/csrf.php');
      const res = await fetch(url, {
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        credentials: 'same-origin'
      });
      const data = await res.json();
      if (data.success && data.data && data.data.csrf_token) {
        this.csrfToken = data.data.csrf_token;
        return this.csrfToken;
      }
    } catch (e) {
      console.warn('Could not fetch CSRF token:', e);
    }
    return '';
  }

  static async get(endpoint, params = {}) {
    let url = this.getApiPath(endpoint);
    const query = new URLSearchParams();

    for (const [key, val] of Object.entries(params)) {
      if (val !== undefined && val !== null && val !== '') {
        query.append(key, val);
      }
    }

    const queryString = query.toString();
    if (queryString) {
      url += (url.includes('?') ? '&' : '?') + queryString;
    }

    try {
      const response = await fetch(url, {
        method: 'GET',
        headers: {
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        },
        credentials: 'same-origin'
      });

      const json = await response.json();
      return json;
    } catch (err) {
      console.error(`API GET error on ${url}:`, err);
      return { success: false, message: 'Network error communicating with server.', errors: {} };
    }
  }

  static async post(endpoint, data = {}) {
    const url = this.getApiPath(endpoint);
    const csrf = await this.getCsrfToken();

    try {
      const response = await fetch(url, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-Token': csrf
        },
        credentials: 'same-origin',
        body: JSON.stringify(data)
      });

      const json = await response.json();
      return json;
    } catch (err) {
      console.error(`API POST error on ${url}:`, err);
      return { success: false, message: 'Network error submitting data to server.', errors: {} };
    }
  }

  static async put(endpoint, data = {}) {
    const url = this.getApiPath(endpoint);
    const csrf = await this.getCsrfToken();

    try {
      const response = await fetch(url, {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-Token': csrf
        },
        credentials: 'same-origin',
        body: JSON.stringify(data)
      });

      const json = await response.json();
      return json;
    } catch (err) {
      console.error(`API PUT error on ${url}:`, err);
      return { success: false, message: 'Network error updating data on server.', errors: {} };
    }
  }

  static async delete(endpoint, params = {}) {
    let url = this.getApiPath(endpoint);
    const query = new URLSearchParams();

    for (const [key, val] of Object.entries(params)) {
      if (val !== undefined && val !== null && val !== '') {
        query.append(key, val);
      }
    }

    const queryString = query.toString();
    if (queryString) {
      url += (url.includes('?') ? '&' : '?') + queryString;
    }

    const csrf = await this.getCsrfToken();

    try {
      const response = await fetch(url, {
        method: 'DELETE',
        headers: {
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-Token': csrf
        },
        credentials: 'same-origin'
      });

      const json = await response.json();
      return json;
    } catch (err) {
      console.error(`API DELETE error on ${url}:`, err);
      return { success: false, message: 'Network error deleting resource from server.', errors: {} };
    }
  }

  static async upload(endpoint, formData) {
    const url = this.getApiPath(endpoint);
    const csrf = await this.getCsrfToken();

    try {
      const response = await fetch(url, {
        method: 'POST',
        headers: {
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-Token': csrf
        },
        credentials: 'same-origin',
        body: formData
      });

      const json = await response.json();
      return json;
    } catch (err) {
      console.error(`API Upload error on ${url}:`, err);
      return { success: false, message: 'File upload failed due to network error.', errors: {} };
    }
  }
}
