import { refreshSettingsNow } from '../../server/utils/settings-cache'
import { countDatasetEntries } from '../../utils/settings-helpers'

export default defineEventHandler(async (event) => {
  if (getMethod(event).toUpperCase() !== 'POST') {
    setResponseStatus(event, 405)
    return { ok: false, error: 'Method Not Allowed' }
  }
  // TODO: Add your auth/secret check here if needed
  const data = await refreshSettingsNow()
  return { ok: true, count: countDatasetEntries(data || {}) }
})
