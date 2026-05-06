import { getToken } from './auth'

export async function apiFetch(path, { headers, ...init } = {}) {
  const token = getToken()
  const res = await fetch(path, {
    ...init,
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
      ...(token ? { Authorization: `Bearer ${token}` } : {}),
      ...(headers || {}),
    },
  })

  const contentType = res.headers.get('content-type') || ''
  const body = contentType.includes('application/json') ? await res.json().catch(() => null) : await res.text().catch(() => null)

  if (!res.ok) {
    const message = body?.message || `Request failed (${res.status})`
    const err = new Error(message)
    err.status = res.status
    err.body = body
    throw err
  }

  return body
}
