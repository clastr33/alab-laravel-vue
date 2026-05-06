const TOKEN_KEY = 'alab.jwt'

export function setToken(token) {
  localStorage.setItem(TOKEN_KEY, token)
}

export function getToken() {
  return localStorage.getItem(TOKEN_KEY)
}

export function clearToken() {
  localStorage.removeItem(TOKEN_KEY)
}

function base64UrlDecode(input) {
  const base64 = input.replace(/-/g, '+').replace(/_/g, '/')
  const padded = base64 + '='.repeat((4 - (base64.length % 4)) % 4)
  const binary = atob(padded)
  const bytes = Uint8Array.from(binary, (c) => c.charCodeAt(0))
  return new TextDecoder().decode(bytes)
}

export function decodeJwt(token) {
  try {
    const payload = token.split('.')[1]
    if (!payload) return null
    return JSON.parse(base64UrlDecode(payload))
  } catch {
    return null
  }
}

export function isTokenValid(token) {
  const payload = decodeJwt(token)
  if (!payload?.exp) return false
  return Date.now() < payload.exp * 1000
}

export function msUntilExpiry(token) {
  const payload = decodeJwt(token)
  if (!payload?.exp) return 0
  return payload.exp * 1000 - Date.now()
}
