/**
 * ==========================================================
 * Discípulas Creativas - Cliente API REST Centralizado
 * ==========================================================
 * Conecta el frontend con el backend en PHP conforme a
 * la especificación de backend/api_contracts.md
 */

(function (window) {
  'use strict';

  // 1. Determinar URL Base de la API
  // Prioridad: 1. localStorage ('dc_api_url') -> 2. window.DC_API_URL -> 3. Auto-detección
  function getBaseUrl() {
    var custom = localStorage.getItem('dc_api_url') || window.DC_API_URL;
    if (custom) return custom.replace(/\/+$/, '');

    // Entorno local (localhost / 127.0.0.1)
    if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
      // Si la URL contiene el subdirectorio del proyecto
      if (window.location.pathname.indexOf('/DiscipulasCreativas') !== -1) {
        return window.location.origin + '/DiscipulasCreativas/backend';
      }
      return window.location.origin + '/backend';
    }

    // Producción (mismo dominio con la API en la subcarpeta /backend)
    return window.location.origin + '/backend';
  }

  // 2. Manejo seguro de Token y Sesión Local
  var TOKEN_KEY = 'dc_auth_token';
  var USER_KEY = 'dc_auth_user';

  var Storage = {
    getToken: function () {
      try {
        return localStorage.getItem(TOKEN_KEY) || '';
      } catch (e) {
        return '';
      }
    },
    setToken: function (token) {
      try {
        localStorage.setItem(TOKEN_KEY, token);
      } catch (e) {}
    },
    clearSession: function () {
      try {
        localStorage.removeItem(TOKEN_KEY);
        localStorage.removeItem(USER_KEY);
        localStorage.removeItem('dc-active-session');
      } catch (e) {}
    },
    getUser: function () {
      try {
        var raw = localStorage.getItem(USER_KEY);
        return raw ? JSON.parse(raw) : null;
      } catch (e) {
        return null;
      }
    },
    setUser: function (user) {
      try {
        localStorage.setItem(USER_KEY, JSON.stringify(user));
      } catch (e) {}
    }
  };

  // 3. Función Central para Peticiones HTTP
  async function request(endpoint, options) {
    options = options || {};
    var method = options.method || 'GET';
    var body = options.body || null;
    var requiresAuth = options.requiresAuth !== false;

    var headers = {
      'Accept': 'application/json'
    };

    if (body) {
      headers['Content-Type'] = 'application/json';
    }

    // Inyectar Bearer Token si la ruta lo requiere
    if (requiresAuth) {
      var token = Storage.getToken();
      if (token) {
        headers['Authorization'] = 'Bearer ' + token;
        headers['X-Auth-Token'] = token;
      }
    }

    var url = getBaseUrl() + endpoint;

    try {
      var response = await fetch(url, {
        method: method,
        headers: headers,
        body: body ? JSON.stringify(body) : null
      });

      var data;
      try {
        data = await response.json();
      } catch (jsonErr) {
        // En caso de respuesta HTML o error de servidor de bajo nivel
        throw new Error('Respuesta inválida del servidor (' + response.status + ').');
      }

      // Si el token expiró o es inválido (401), limpiar sesión
      if (response.status === 401 && requiresAuth) {
        Storage.clearSession();
        // Si no estamos ya en index.html, redirigir
        if (window.location.pathname.indexOf('index.html') === -1 && window.location.pathname !== '/' && !window.location.pathname.endsWith('/')) {
          window.location.href = 'index.html';
        }
      }

      if (!response.ok || !data.success) {
        var errMessage = data.message || 'Error en la petición (' + response.status + ')';
        var error = new Error(errMessage);
        error.code = data.error_code || 'API_ERROR';
        error.status = response.status;
        error.data = data.data;
        throw error;
      }

      return data;
    } catch (networkError) {
      // Re-lanzar error semántico
      throw networkError;
    }
  }

  // 4. Catálogo de Servicios API
  var API = {
    // Configuración y storage
    getBaseUrl: getBaseUrl,
    setCustomBaseUrl: function (url) {
      localStorage.setItem('dc_api_url', url);
    },
    storage: Storage,

    // --- Rutas Públicas ---

    /**
     * Obtener listado de distritos misioneros
     */
    getDistritos: function () {
      return request('/api/distritos', { method: 'GET', requiresAuth: false });
    },

    /**
     * Obtener iglesias (opcionalmente filtradas por distrito)
     */
    getIglesias: function (distritoId) {
      var query = distritoId ? '?distrito_id=' + encodeURIComponent(distritoId) : '';
      return request('/api/iglesias' + query, { method: 'GET', requiresAuth: false });
    },

    /**
     * Registro de nueva alumna
     */
    register: async function (userData) {
      var res = await request('/api/auth/register', {
        method: 'POST',
        body: {
          nombre: userData.nombre,
          correo: userData.correo,
          password: userData.password,
          distrito_id: parseInt(userData.distrito_id, 10),
          iglesia_id: parseInt(userData.iglesia_id, 10)
        },
        requiresAuth: false
      });

      if (res.data && res.data.token) {
        Storage.setToken(res.data.token);
        if (res.data.usuario) {
          Storage.setUser(res.data.usuario);
        }
      }
      return res;
    },

    /**
     * Inicio de sesión
     */
    login: async function (correo, password) {
      var res = await request('/api/auth/login', {
        method: 'POST',
        body: {
          correo: correo,
          password: password
        },
        requiresAuth: false
      });

      if (res.data && res.data.token) {
        Storage.setToken(res.data.token);
        if (res.data.usuario) {
          Storage.setUser(res.data.usuario);
        }
      }
      return res;
    },

    // --- Rutas Protegidas (Bajo Sesión) ---

    /**
     * Obtener datos del perfil actual
     */
    getMe: function () {
      return request('/api/auth/me', { method: 'GET' });
    },

    /**
     * Cerrar sesión
     */
    logout: async function () {
      try {
        await request('/api/auth/logout', { method: 'POST' });
      } catch (e) {
        // Ignorar error de red al salir
      } finally {
        Storage.clearSession();
      }
    },

    /**
     * Cambiar contraseña
     */
    changePassword: function (passwordActual, passwordNueva) {
      return request('/api/auth/change-password', {
        method: 'POST',
        body: {
          password_actual: passwordActual,
          password_nueva: passwordNueva
        }
      });
    },

    /**
     * Obtener los 11 módulos oficiales con sus 2 preguntas de reflexión
     */
    getModulos: function () {
      return request('/api/modulos', { method: 'GET' });
    },

    /**
     * Obtener el progreso de la alumna (módulos completados, % y reflexiones guardadas)
     */
    getProgreso: function () {
      return request('/api/progreso', { method: 'GET' });
    },

    /**
     * Enviar respuestas de reflexión y marcar módulo como completado
     * @param {number} moduloId 
     * @param {Array<{pregunta_id: number, texto: string}>} respuestas 
     */
    completarModulo: function (moduloId, respuestas) {
      return request('/api/progreso/completar', {
        method: 'POST',
        body: {
          modulo_id: parseInt(moduloId, 10),
          respuestas: respuestas
        }
      });
    },

    /**
     * Alternar visto/no visto de un módulo
     * @param {number} moduloId 
     * @param {boolean} completado 
     */
    toggleModulo: function (moduloId, completado) {
      return request('/api/progreso/toggle', {
        method: 'POST',
        body: {
          modulo_id: parseInt(moduloId, 10),
          completado: Boolean(completado)
        }
      });
    }
  };

  // Exponer API globalmente
  window.DC_API = API;

})(window);
